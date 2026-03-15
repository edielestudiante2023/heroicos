<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'rol_id',
        'email',
        'password',
        'estado',
        'email_verificado',
        'token_verificacion',
        'token_recuperacion',
        'token_expira',
        'ultimo_acceso'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'email'    => 'required|valid_email|is_unique[usuarios.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'rol_id'   => 'required|integer',
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
     * Find user by email (active users only)
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)
                    ->where('estado', 'activo')
                    ->first();
    }

    /**
     * Get user with role info and profile data
     */
    public function getUserWithRole(int $id): ?array
    {
        $user = $this->select('usuarios.*, roles.nombre as rol_nombre')
                     ->join('roles', 'roles.id = usuarios.rol_id')
                     ->where('usuarios.id', $id)
                     ->first();

        if (!$user) {
            return null;
        }

        // Get profile data from the appropriate table based on role
        $profileData = $this->getProfileData($id, $user['rol_nombre']);
        if ($profileData) {
            $user = array_merge($user, $profileData);
        }

        return $user;
    }

    /**
     * Get profile data from role-specific table
     */
    public function getProfileData(int $userId, string $rolNombre): ?array
    {
        $db = \Config\Database::connect();

        switch ($rolNombre) {
            case 'admin':
                $result = $db->table('administradores')
                             ->where('usuario_id', $userId)
                             ->get()
                             ->getRowArray();
                break;
            case 'profesor':
                $result = $db->table('profesores')
                             ->where('usuario_id', $userId)
                             ->get()
                             ->getRowArray();
                break;
            case 'acudiente':
                $result = $db->table('acudientes')
                             ->where('usuario_id', $userId)
                             ->get()
                             ->getRowArray();
                break;
            default:
                return null;
        }

        // Normalize field names (nombres -> nombre, apellidos -> apellido)
        if ($result) {
            if (isset($result['nombres']) && !isset($result['nombre'])) {
                $result['nombre'] = $result['nombres'];
            }
            if (isset($result['apellidos']) && !isset($result['apellido'])) {
                $result['apellido'] = $result['apellidos'];
            }
        }

        return $result;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $id): bool
    {
        return $this->update($id, [
            'ultimo_acceso' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get users by role
     */
    public function getByRole(string $rolNombre): array
    {
        return $this->select('usuarios.*')
                    ->join('roles', 'roles.id = usuarios.rol_id')
                    ->where('roles.nombre', $rolNombre)
                    ->where('usuarios.estado', 'activo')
                    ->findAll();
    }

    /**
     * Generate password reset token
     */
    public function generateResetToken(int $userId): ?string
    {
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Use DB builder directly to bypass model validation (cleanValidationRules
        // has issues with is_unique placeholders on partial updates).
        $saved = $this->db->table('usuarios')
            ->where('id', $userId)
            ->update([
                'token_recuperacion' => hash('sha256', $token),
                'token_expira'       => $expires,
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);

        return $saved ? $token : null;
    }

    /**
     * Verify reset token and get user
     */
    public function verifyResetToken(string $token): ?array
    {
        return $this->where('token_recuperacion', hash('sha256', $token))
                    ->where('token_expira >', date('Y-m-d H:i:s'))
                    ->where('estado', 'activo')
                    ->first();
    }

    /**
     * Clear reset token
     */
    public function clearResetToken(int $userId): bool
    {
        return $this->db->table('usuarios')
            ->where('id', $userId)
            ->update([
                'token_recuperacion' => null,
                'token_expira'       => null,
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Create user with profile
     */
    public function createUserWithProfile(array $userData, array $profileData, string $rolNombre): ?int
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Create user
        $this->insert($userData);
        $userId = $this->getInsertID();

        if (!$userId) {
            $db->transRollback();
            return null;
        }

        // Create profile
        $profileData['usuario_id'] = $userId;
        $profileTable = match ($rolNombre) {
            'admin'     => 'administradores',
            'profesor'  => 'profesores',
            'acudiente' => 'acudientes',
            default     => null
        };

        if ($profileTable) {
            $db->table($profileTable)->insert($profileData);
        }

        $db->transComplete();

        return $db->transStatus() ? $userId : null;
    }

    /**
     * Get full name from profile
     */
    public function getFullName(array $user): string
    {
        $nombre = $user['nombre'] ?? '';
        $apellido = $user['apellido'] ?? $user['apellidos'] ?? '';
        return trim($nombre . ' ' . $apellido);
    }
}
