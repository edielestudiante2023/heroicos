<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoDetalleModel extends Model
{
    protected $table            = 'pago_detalles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pago_id',
        'cargo_id',
        'valor_aplicado',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Get details for a payment
     */
    public function getByPago(int $pagoId): array
    {
        return $this->select('pago_detalles.*, c.descripcion as cargo_descripcion, c.valor_original, c.saldo_pendiente, cc.nombre as concepto_nombre, e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos')
            ->join('cargos c', 'c.id = pago_detalles.cargo_id')
            ->join('conceptos_cobro cc', 'cc.id = c.concepto_id')
            ->join('estudiantes e', 'e.id = c.estudiante_id')
            ->where('pago_detalles.pago_id', $pagoId)
            ->findAll();
    }

    /**
     * Get payment history for a cargo
     */
    public function getByCargo(int $cargoId): array
    {
        return $this->select('pago_detalles.*, p.numero_recibo, p.fecha_pago, p.estado as pago_estado')
            ->join('pagos p', 'p.id = pago_detalles.pago_id')
            ->where('pago_detalles.cargo_id', $cargoId)
            ->orderBy('p.fecha_pago', 'DESC')
            ->findAll();
    }
}
