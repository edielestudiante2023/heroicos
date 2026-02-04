<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nombre',
        'descripcion'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Role constants (using 'nombre' values from DB)
    const ADMIN     = 'admin';
    const PROFESOR  = 'profesor';
    const ACUDIENTE = 'acudiente';

    /**
     * Find role by nombre
     */
    public function findByNombre(string $nombre): ?array
    {
        return $this->where('nombre', $nombre)->first();
    }

    /**
     * Get all roles
     */
    public function getAllRoles(): array
    {
        return $this->orderBy('nombre', 'ASC')->findAll();
    }

    /**
     * Check if user has specific role
     */
    public function userHasRole(int $userId, string $rolNombre): bool
    {
        $db = \Config\Database::connect();
        $result = $db->table('usuarios')
                     ->select('roles.nombre')
                     ->join('roles', 'roles.id = usuarios.rol_id')
                     ->where('usuarios.id', $userId)
                     ->get()
                     ->getRowArray();

        return $result && $result['nombre'] === $rolNombre;
    }

    /**
     * Get role ID by nombre
     */
    public function getIdByNombre(string $nombre): ?int
    {
        $role = $this->findByNombre($nombre);
        return $role ? (int)$role['id'] : null;
    }
}
