<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriaModel extends Model
{
    protected $table            = 'categorias';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nombre' => 'required|min_length[2]|max_length[100]|is_unique[categorias.nombre,id,{id}]',
        'estado' => 'required|in_list[activa,inactiva]',
    ];

    /**
     * Get all active categories
     */
    public function getActive(): array
    {
        return $this->where('estado', 'activa')
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    /**
     * Get all categories with group count
     */
    public function getWithGroupCount(): array
    {
        return $this->select('categorias.*, COUNT(grupos.id) as grupos_count')
            ->join('grupos', 'grupos.categoria_id = categorias.id', 'left')
            ->groupBy('categorias.id')
            ->orderBy('categorias.nombre', 'ASC')
            ->findAll();
    }
}
