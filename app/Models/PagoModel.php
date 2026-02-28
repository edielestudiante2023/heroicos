<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table            = 'pagos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'acudiente_id',
        'numero_recibo',
        'fecha_pago',
        'fecha_registro',
        'valor_total',
        'metodo_pago',
        'banco',
        'referencia_banco',
        'observaciones',
        'estado',
        'revisado_por',
        'fecha_revision',
        'motivo_rechazo',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Generate unique receipt number: REC-{YEAR}-{CONSECUTIVE}
     */
    public function generateNumeroRecibo(): string
    {
        $year = date('Y');
        $db = \Config\Database::connect();

        $last = $db->table('pagos')
            ->like('numero_recibo', "REC-{$year}-")
            ->orderBy('numero_recibo', 'DESC')
            ->get()
            ->getRowArray();

        if ($last && $last['numero_recibo']) {
            $lastNumber = (int)substr($last['numero_recibo'], -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "REC-{$year}-" . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get pagos with filters
     */
    public function getFiltered(array $filters = []): array
    {
        $builder = $this->select('pagos.*, a.nombres as acudiente_nombres, a.apellidos as acudiente_apellidos')
            ->join('acudientes a', 'a.id = pagos.acudiente_id');

        if (!empty($filters['estado'])) {
            $builder->where('pagos.estado', $filters['estado']);
        }

        if (!empty($filters['metodo_pago'])) {
            $builder->where('pagos.metodo_pago', $filters['metodo_pago']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('a.nombres', $filters['search'])
                ->orLike('a.apellidos', $filters['search'])
                ->orLike('pagos.numero_recibo', $filters['search'])
                ->orLike('pagos.referencia_banco', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['fecha_desde'])) {
            $builder->where('pagos.fecha_pago >=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $builder->where('pagos.fecha_pago <=', $filters['fecha_hasta']);
        }

        return $builder->orderBy('pagos.created_at', 'DESC')->findAll();
    }

    /**
     * Get pago with full details (detalles, comprobantes, acudiente)
     */
    public function getWithDetails(int $id): ?array
    {
        $pago = $this->select('pagos.*, a.nombres as acudiente_nombres, a.apellidos as acudiente_apellidos, a.telefono as acudiente_telefono, u.email as acudiente_email')
            ->join('acudientes a', 'a.id = pagos.acudiente_id')
            ->join('usuarios u', 'u.id = a.usuario_id')
            ->find($id);

        if (!$pago) return null;

        $db = \Config\Database::connect();

        // Get payment details
        $pago['detalles'] = $db->table('pago_detalles pd')
            ->select('pd.*, c.descripcion as cargo_descripcion, c.valor_original as cargo_valor, cc.nombre as concepto_nombre, e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos')
            ->join('cargos c', 'c.id = pd.cargo_id')
            ->join('conceptos_cobro cc', 'cc.id = c.concepto_id')
            ->join('estudiantes e', 'e.id = c.estudiante_id')
            ->where('pd.pago_id', $id)
            ->get()
            ->getResultArray();

        // Get attachments
        $pago['comprobantes'] = $db->table('comprobantes')
            ->where('pago_id', $id)
            ->get()
            ->getResultArray();

        // Get reviewer info
        if ($pago['revisado_por']) {
            $pago['revisor'] = $db->table('usuarios')
                ->select('nombre, apellido, email')
                ->where('id', $pago['revisado_por'])
                ->get()
                ->getRowArray();
        }

        return $pago;
    }

    /**
     * Get payments pending review
     */
    public function getPendientesRevision(): array
    {
        return $this->select('pagos.*, a.nombres as acudiente_nombres, a.apellidos as acudiente_apellidos')
            ->join('acudientes a', 'a.id = pagos.acudiente_id')
            ->where('pagos.estado', 'pendiente_revision')
            ->orderBy('pagos.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Approve payment
     */
    public function aprobar(int $pagoId, int $revisadoPor): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Update payment status
        $this->update($pagoId, [
            'estado'         => 'aprobado',
            'revisado_por'   => $revisadoPor,
            'fecha_revision' => date('Y-m-d H:i:s'),
        ]);

        // Apply payments to cargos
        $detalles = $db->table('pago_detalles')
            ->where('pago_id', $pagoId)
            ->get()
            ->getResultArray();

        $cargoModel = new CargoModel();
        foreach ($detalles as $detalle) {
            $cargoModel->aplicarPago((int)$detalle['cargo_id'], (float)$detalle['valor_aplicado']);
        }

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Reject payment
     */
    public function rechazar(int $pagoId, int $revisadoPor, string $motivo): bool
    {
        return $this->update($pagoId, [
            'estado'         => 'rechazado',
            'revisado_por'   => $revisadoPor,
            'fecha_revision' => date('Y-m-d H:i:s'),
            'motivo_rechazo' => $motivo,
        ]);
    }

    /**
     * Get payments by acudiente
     */
    public function getByAcudiente(int $acudienteId): array
    {
        return $this->where('acudiente_id', $acudienteId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get stats
     */
    public function getStats(): array
    {
        return [
            'pendientes_revision' => $this->where('estado', 'pendiente_revision')->countAllResults(),
            'aprobados_mes' => $this->where('estado', 'aprobado')
                ->where('MONTH(fecha_revision)', date('m'))
                ->where('YEAR(fecha_revision)', date('Y'))
                ->countAllResults(),
        ];
    }
}
