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
        'slug',
        'descripcion',
        'permisos'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nombre' => 'required|min_length[3]|max_length[50]',
        'slug'   => 'required|alpha_dash|is_unique[roles.slug,id,{id}]',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required'   => 'El nombre del rol es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 3 caracteres.',
        ],
        'slug' => [
            'required'  => 'El slug es obligatorio.',
            'is_unique' => 'Este slug ya existe.',
        ],
    ];

    // Role constants
    const ADMIN     = 'admin';
    const PROFESOR  = 'profesor';
    const ACUDIENTE = 'acudiente';

    /**
     * Find role by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get all active roles
     */
    public function getActiveRoles(): array
    {
        return $this->orderBy('nombre', 'ASC')->findAll();
    }

    /**
     * Check if user has specific role
     */
    public function userHasRole(int $userId, string $roleSlug): bool
    {
        $db = \Config\Database::connect();
        $result = $db->table('users')
                     ->select('roles.slug')
                     ->join('roles', 'roles.id = users.role_id')
                     ->where('users.id', $userId)
                     ->get()
                     ->getRowArray();

        return $result && $result['slug'] === $roleSlug;
    }

    /**
     * Get permissions for role
     */
    public function getPermissions(int $roleId): array
    {
        $role = $this->find($roleId);
        if (!$role || empty($role['permisos'])) {
            return [];
        }
        return json_decode($role['permisos'], true) ?? [];
    }

    /**
     * Check if role has permission
     */
    public function hasPermission(int $roleId, string $permission): bool
    {
        $permissions = $this->getPermissions($roleId);
        return in_array($permission, $permissions);
    }

    /**
     * Get role ID by slug
     */
    public function getIdBySlug(string $slug): ?int
    {
        $role = $this->findBySlug($slug);
        return $role ? (int)$role['id'] : null;
    }
}
