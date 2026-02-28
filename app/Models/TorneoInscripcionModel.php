<?php

namespace App\Models;

use CodeIgniter\Model;

class TorneoInscripcionModel extends Model
{
    protected $table            = 'torneo_inscripciones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'torneo_id',
        'estudiante_id',
        'acudiente_id',
        'fecha_inscripcion',
        'cargo_id',
        'estado',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Check if student is already enrolled in tournament
     */
    public function isEnrolled(int $estudianteId, int $torneoId): bool
    {
        return $this->where('estudiante_id', $estudianteId)
            ->where('torneo_id', $torneoId)
            ->where('estado !=', 'cancelado')
            ->countAllResults() > 0;
    }

    /**
     * Enroll student in tournament (creates cargo automatically)
     */
    public function enrollStudent(int $torneoId, int $estudianteId, int $acudienteId): bool
    {
        $torneoModel = new TorneoModel();
        $torneo = $torneoModel->find($torneoId);

        if (!$torneo) return false;

        $db = \Config\Database::connect();
        $db->transStart();

        // Create cargo for tournament cost
        $cargoId = null;
        if ($torneo['costo'] > 0) {
            // Find TORN concept
            $concepto = $db->table('conceptos_cobro')
                ->where('codigo', 'TORN')
                ->get()
                ->getRowArray();

            // Find active period
            $periodo = $db->table('periodos')
                ->where('estado', 'activo')
                ->get()
                ->getRowArray();

            if ($concepto && $periodo) {
                $cargoModel = new CargoModel();
                $cargoId = $cargoModel->insert([
                    'estudiante_id'    => $estudianteId,
                    'concepto_id'      => $concepto['id'],
                    'periodo_id'       => $periodo['id'],
                    'descripcion'      => 'Torneo: ' . $torneo['nombre'],
                    'valor_original'   => $torneo['costo'],
                    'valor_pagado'     => 0,
                    'saldo_pendiente'  => $torneo['costo'],
                    'estado'           => 'pendiente',
                    'generado_auto'    => 1,
                ]);
            }
        }

        // Create tournament enrollment
        $this->insert([
            'torneo_id'         => $torneoId,
            'estudiante_id'     => $estudianteId,
            'acudiente_id'      => $acudienteId,
            'fecha_inscripcion' => date('Y-m-d H:i:s'),
            'cargo_id'          => $cargoId,
            'estado'            => $torneo['costo'] > 0 ? 'pendiente_pago' : 'pagado',
        ]);

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Cancel enrollment
     */
    public function cancelEnrollment(int $inscripcionId): bool
    {
        return $this->update($inscripcionId, ['estado' => 'cancelado']);
    }
}
