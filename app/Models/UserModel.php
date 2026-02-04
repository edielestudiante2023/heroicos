<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'role_id',
        'email',
        'password',
        'nombre',
        'apellido',
        'telefono',
        'direccion',
        'documento_tipo',
        'documento_numero',
        'foto',
        'activo',
        'email_verified_at',
        'remember_token',
        'last_login',
        'created_by',
        'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'email'     => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password'  => 'required|min_length[8]',
        'nombre'    => 'required|min_length[2]|max_length[100]',
        'apellido'  => 'required|min_length[2]|max_length[100]',
        'role_id'   => 'required|integer|is_not_unique[roles.id]',
        'telefono'  => 'permit_empty|max_length[20]',
        'documento_tipo'   => 'permit_empty|in_list[CC,TI,CE,PP,RC]',
        'documento_numero' => 'permit_empty|max_length[20]',
    ];

    protected $validationMessages = [
        'email' => [
            'required'    => 'El correo electrónico es obligatorio.',
            'valid_email' => 'Ingrese un correo electrónico válido.',
            'is_unique'   => 'Este correo ya está registrado.',
        ],
        'password' => [
            'required'   => 'La contraseña es obligatoria.',
            'min_length' => 'La contraseña debe tener al menos 8 caracteres.',
        ],
        'nombre' => [
            'required'   => 'El nombre es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 2 caracteres.',
        ],
        'apellido' => [
            'required'   => 'El apellido es obligatorio.',
            'min_length' => 'El apellido debe tener al menos 2 caracteres.',
        ],
        'role_id' => [
            'required'      => 'El rol es obligatorio.',
            'is_not_unique' => 'El rol seleccionado no existe.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    /**
     * Hash password before insert/update
     */
    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash(
                $data['data']['password'],
                PASSWORD_DEFAULT
            );
        }
        return $data;
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)
                    ->where('activo', 1)
                    ->first();
    }

    /**
     * Get user with role info
     */
    public function getUserWithRole(int $id): ?array
    {
        return $this->select('users.*, roles.nombre as rol_nombre, roles.slug as rol_slug')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('users.id', $id)
                    ->first();
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $id): bool
    {
        return $this->update($id, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get users by role
     */
    public function getByRole(string $roleSlug): array
    {
        return $this->select('users.*')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('roles.slug', $roleSlug)
                    ->where('users.activo', 1)
                    ->findAll();
    }

    /**
     * Generate password reset token
     */
    public function generateResetToken(int $userId): ?string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db = \Config\Database::connect();
        $db->table('password_resets')->insert([
            'user_id'    => $userId,
            'token'      => hash('sha256', $token),
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $token;
    }

    /**
     * Verify reset token and get user
     */
    public function verifyResetToken(string $token): ?array
    {
        $db = \Config\Database::connect();
        $reset = $db->table('password_resets')
                    ->where('token', hash('sha256', $token))
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->where('used_at', null)
                    ->get()
                    ->getRowArray();

        if (!$reset) {
            return null;
        }

        return $this->find($reset['user_id']);
    }

    /**
     * Mark reset token as used
     */
    public function markTokenUsed(string $token): bool
    {
        $db = \Config\Database::connect();
        return $db->table('password_resets')
                  ->where('token', hash('sha256', $token))
                  ->update(['used_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get full name
     */
    public function getFullName(array $user): string
    {
        return trim($user['nombre'] . ' ' . $user['apellido']);
    }
}
