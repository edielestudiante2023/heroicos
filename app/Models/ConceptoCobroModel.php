<?php

namespace App\Models;

use CodeIgniter\Model;

class ConceptoCobroModel extends Model
{
    protected $table            = 'conceptos_cobro';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'aplica_al_inscribir',
        'orden',
        'estado',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get active concepts ordered
     */
    public function getActive(): array
    {
        return $this->where('estado', 'activo')
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    /**
     * Get all concepts
     */
    public function getAll(): array
    {
        return $this->orderBy('orden', 'ASC')->findAll();
    }

    /**
     * Get concepts that apply on enrollment
     */
    public function getInscripcionConcepts(): array
    {
        return $this->where('estado', 'activo')
            ->where('aplica_al_inscribir', 1)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }
}
