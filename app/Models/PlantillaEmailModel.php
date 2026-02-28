<?php

namespace App\Models;

use CodeIgniter\Model;

class PlantillaEmailModel extends Model
{
    protected $table            = 'plantillas_email';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'codigo',
        'nombre',
        'asunto',
        'cuerpo_html',
        'variables',
        'estado',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get active template by code
     */
    public function getByCode(string $codigo): ?array
    {
        return $this->where('codigo', $codigo)
                    ->where('estado', 'activa')
                    ->first();
    }
}
