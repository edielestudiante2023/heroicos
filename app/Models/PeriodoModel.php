<?php

namespace App\Models;

use CodeIgniter\Model;

class PeriodoModel extends Model
{
    protected $table            = 'periodos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get the currently active period
     */
    public function getActivo(): ?array
    {
        return $this->where('estado', 'activo')->first();
    }

    /**
     * Get all periods ordered by date
     */
    public function getAll(): array
    {
        return $this->orderBy('fecha_inicio', 'DESC')->findAll();
    }
}
