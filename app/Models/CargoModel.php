<?php

namespace App\Models;

use CodeIgniter\Model;

class CargoModel extends Model
{
    protected $table            = 'cargos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'estudiante_id',
        'concepto_id',
        'periodo_id',
        'descripcion',
        'mes',
        'anio',
        'valor_original',
        'valor_pagado',
        'saldo_pendiente',
        'fecha_vencimiento',
        'estado',
        'generado_auto',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get cargos for a student with details
     */
    public function getByEstudiante(int $estudianteId): array
    {
        return $this->select('cargos.*, cc.nombre as concepto_nombre, cc.codigo as concepto_codigo, p.nombre as periodo_nombre')
            ->join('conceptos_cobro cc', 'cc.id = cargos.concepto_id')
            ->join('periodos p', 'p.id = cargos.periodo_id')
            ->where('cargos.estudiante_id', $estudianteId)
            ->orderBy('cargos.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get pending cargos for a student
     */
    public function getPendientesByEstudiante(int $estudianteId): array
    {
        return $this->select('cargos.*, cc.nombre as concepto_nombre, cc.codigo as concepto_codigo')
            ->join('conceptos_cobro cc', 'cc.id = cargos.concepto_id')
            ->where('cargos.estudiante_id', $estudianteId)
            ->whereIn('cargos.estado', ['pendiente', 'parcial'])
            ->orderBy('cargos.fecha_vencimiento', 'ASC')
            ->findAll();
    }

    /**
     * Get all cargos with filters for cartera view
     */
    public function getCarteraGeneral(array $filters = []): array
    {
        $builder = $this->select('cargos.*, cc.nombre as concepto_nombre, cc.codigo as concepto_codigo, e.nombres, e.apellidos, e.codigo as estudiante_codigo, p.nombre as periodo_nombre')
            ->join('conceptos_cobro cc', 'cc.id = cargos.concepto_id')
            ->join('estudiantes e', 'e.id = cargos.estudiante_id')
            ->join('periodos p', 'p.id = cargos.periodo_id');

        if (!empty($filters['estado'])) {
            $builder->where('cargos.estado', $filters['estado']);
        }

        if (!empty($filters['concepto_id'])) {
            $builder->where('cargos.concepto_id', $filters['concepto_id']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('e.nombres', $filters['search'])
                ->orLike('e.apellidos', $filters['search'])
                ->orLike('e.codigo', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('cargos.fecha_vencimiento', 'ASC')
            ->orderBy('e.apellidos', 'ASC')
            ->findAll();
    }

    /**
     * Get financial stats
     */
    public function getStats(): array
    {
        $db = \Config\Database::connect();

        $totalPorCobrar = $db->table('cargos')
            ->selectSum('saldo_pendiente')
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->get()->getRowArray();

        $recaudoMes = $db->table('cargos')
            ->selectSum('valor_pagado')
            ->where('MONTH(updated_at)', date('m'))
            ->where('YEAR(updated_at)', date('Y'))
            ->whereIn('estado', ['pagado', 'parcial'])
            ->get()->getRowArray();

        $morosos = $db->table('cargos')
            ->select('DISTINCT estudiante_id')
            ->where('estado', 'pendiente')
            ->where('fecha_vencimiento <', date('Y-m-d'))
            ->countAllResults();

        $totalPagado = $db->table('cargos')
            ->selectSum('valor_pagado')
            ->get()->getRowArray();

        return [
            'total_por_cobrar'  => (float)($totalPorCobrar['saldo_pendiente'] ?? 0),
            'recaudo_mes'       => (float)($recaudoMes['valor_pagado'] ?? 0),
            'morosos'           => $morosos,
            'total_pagado'      => (float)($totalPagado['valor_pagado'] ?? 0),
        ];
    }

    /**
     * Get morosos (students with overdue charges)
     */
    public function getMorosos(): array
    {
        $db = \Config\Database::connect();
        return $db->table('cargos c')
            ->select('e.id, e.codigo, e.nombres, e.apellidos, SUM(c.saldo_pendiente) as total_deuda, COUNT(c.id) as cargos_vencidos')
            ->join('estudiantes e', 'e.id = c.estudiante_id')
            ->where('c.estado', 'pendiente')
            ->where('c.fecha_vencimiento <', date('Y-m-d'))
            ->groupBy('e.id, e.codigo, e.nombres, e.apellidos')
            ->orderBy('total_deuda', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Apply payment to a cargo
     */
    public function aplicarPago(int $cargoId, float $valor): bool
    {
        $cargo = $this->find($cargoId);
        if (!$cargo) return false;

        $nuevoValorPagado = (float)$cargo['valor_pagado'] + $valor;
        $nuevoSaldo = (float)$cargo['valor_original'] - $nuevoValorPagado;

        $estado = 'parcial';
        if ($nuevoSaldo <= 0) {
            $estado = 'pagado';
            $nuevoSaldo = 0;
        }

        return $this->update($cargoId, [
            'valor_pagado'     => $nuevoValorPagado,
            'saldo_pendiente'  => $nuevoSaldo,
            'estado'           => $estado,
        ]);
    }

    /**
     * Reverse payment application
     */
    public function revertirPago(int $cargoId, float $valor): bool
    {
        $cargo = $this->find($cargoId);
        if (!$cargo) return false;

        $nuevoValorPagado = max(0, (float)$cargo['valor_pagado'] - $valor);
        $nuevoSaldo = (float)$cargo['valor_original'] - $nuevoValorPagado;

        $estado = 'pendiente';
        if ($nuevoValorPagado > 0) {
            $estado = 'parcial';
        }

        return $this->update($cargoId, [
            'valor_pagado'     => $nuevoValorPagado,
            'saldo_pendiente'  => $nuevoSaldo,
            'estado'           => $estado,
        ]);
    }

    /**
     * Get cuenta (account statement) for a student
     */
    public function getCuentaEstudiante(int $estudianteId): array
    {
        $cargos = $this->getByEstudiante($estudianteId);

        $totalCargos = 0;
        $totalPagado = 0;
        $totalPendiente = 0;

        foreach ($cargos as $cargo) {
            $totalCargos += (float)$cargo['valor_original'];
            $totalPagado += (float)$cargo['valor_pagado'];
            $totalPendiente += (float)$cargo['saldo_pendiente'];
        }

        return [
            'cargos' => $cargos,
            'total_cargos' => $totalCargos,
            'total_pagado' => $totalPagado,
            'total_pendiente' => $totalPendiente,
        ];
    }
}
