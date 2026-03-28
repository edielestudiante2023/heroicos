<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificacionModel extends Model
{
    protected $table         = 'notificaciones';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $updatedField  = '';
    protected $allowedFields = [
        'usuario_id', 'tipo', 'titulo', 'mensaje',
        'url', 'leida', 'fecha_lectura', 'data_json',
    ];

    public function crearNotificacion(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?string $url = null, ?array $data = null): int|false
    {
        return $this->insert([
            'usuario_id' => $usuarioId,
            'tipo'       => $tipo,
            'titulo'     => $titulo,
            'mensaje'    => $mensaje,
            'url'        => $url,
            'leida'      => false,
            'data_json'  => $data ? json_encode($data) : null,
        ]);
    }

    public function getUnreadCount(int $usuarioId): int
    {
        return $this->where('usuario_id', $usuarioId)
            ->where('leida', false)
            ->countAllResults();
    }

    public function getByUsuario(int $usuarioId, int $limit = 20): array
    {
        return $this->where('usuario_id', $usuarioId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
