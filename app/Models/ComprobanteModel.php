<?php

namespace App\Models;

use CodeIgniter\Model;

class ComprobanteModel extends Model
{
    protected $table            = 'comprobantes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pago_id',
        'archivo',
        'archivo_original',
        'tipo_archivo',
        'tamano',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Get comprobantes for a payment
     */
    public function getByPago(int $pagoId): array
    {
        return $this->where('pago_id', $pagoId)->findAll();
    }
}
