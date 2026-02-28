<?php

namespace App\Models;

use CodeIgniter\Model;

class TokenInscripcionModel extends Model
{
    protected $table            = 'tokens_inscripcion';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'token',
        'email',
        'nombre_acudiente',
        'profesor_id',
        'usado',
        'fecha_uso',
        'acudiente_id',
        'expira_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Find a valid (unused, not expired) token
     */
    public function findValidToken(string $token): ?array
    {
        return $this->where('token', $token)
            ->where('usado', 0)
            ->where('expira_at >', date('Y-m-d H:i:s'))
            ->first();
    }

    /**
     * Get tokens created by a specific professor
     */
    public function getByProfesor(int $profesorId): array
    {
        return $this->where('profesor_id', $profesorId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(int $id, int $acudienteId): bool
    {
        return $this->update($id, [
            'usado'        => 1,
            'fecha_uso'    => date('Y-m-d H:i:s'),
            'acudiente_id' => $acudienteId,
        ]);
    }
}
