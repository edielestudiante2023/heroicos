<?php

namespace App\Controllers;

use App\Models\NotificacionModel;
use CodeIgniter\HTTP\ResponseInterface;

class NotificacionController extends BaseController
{
    protected NotificacionModel $notificacionModel;

    public function __construct()
    {
        $this->notificacionModel = new NotificacionModel();
    }

    /**
     * JSON: Get recent notifications + unread count for the bell dropdown
     */
    public function recientes(): ResponseInterface
    {
        $userId = (int) session()->get('user_id');

        $notificaciones = $this->notificacionModel
            ->where('usuario_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();

        $unreadCount = $this->notificacionModel->getUnreadCount($userId);

        return $this->response->setJSON([
            'unreadCount'     => $unreadCount,
            'notificaciones'  => $notificaciones,
        ]);
    }

    /**
     * JSON: Mark one notification as read
     */
    public function marcarLeida(int $id): ResponseInterface
    {
        $userId = (int) session()->get('user_id');

        $notificacion = $this->notificacionModel
            ->where('id', $id)
            ->where('usuario_id', $userId)
            ->first();

        if (!$notificacion) {
            return $this->response->setJSON(['ok' => false]);
        }

        $this->notificacionModel->update($id, [
            'leida'         => true,
            'fecha_lectura' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * JSON: Mark all as read
     */
    public function marcarTodasLeidas(): ResponseInterface
    {
        $userId = (int) session()->get('user_id');

        $this->notificacionModel
            ->where('usuario_id', $userId)
            ->where('leida', false)
            ->set(['leida' => true, 'fecha_lectura' => date('Y-m-d H:i:s')])
            ->update();

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * Full page: list all notifications
     */
    public function index(): string
    {
        $userId = (int) session()->get('user_id');

        $notificaciones = $this->notificacionModel
            ->where('usuario_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(50)
            ->findAll();

        // Mark displayed as read
        $this->notificacionModel
            ->where('usuario_id', $userId)
            ->where('leida', false)
            ->set(['leida' => true, 'fecha_lectura' => date('Y-m-d H:i:s')])
            ->update();

        $rolNombre = session()->get('rol_nombre');

        $data = [
            'title'          => 'Notificaciones - Academia Heroicos',
            'pageTitle'      => 'Notificaciones',
            'activePage'     => 'notificaciones',
            'notificaciones' => $notificaciones,
            'rolNombre'      => $rolNombre,
        ];

        return view('notificaciones/index', $data);
    }
}
