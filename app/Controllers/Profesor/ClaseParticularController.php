<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\SolicitudClaseParticularModel;
use App\Models\NotificacionModel;
use CodeIgniter\HTTP\ResponseInterface;

class ClaseParticularController extends BaseController
{
    protected SolicitudClaseParticularModel $solicitudModel;

    public function __construct()
    {
        $this->solicitudModel = new SolicitudClaseParticularModel();
    }

    public function index(): string
    {
        $profesorId = $this->getProfesorId();
        $solicitudes = $this->solicitudModel->getByProfesor($profesorId);

        $data = [
            'title'       => 'Clases Particulares - Academia Heroicos',
            'pageTitle'   => 'Solicitudes de Clases Particulares',
            'activePage'  => 'clases_particulares',
            'solicitudes' => $solicitudes,
        ];

        return view('profesor/clases_particulares/index', $data);
    }

    public function ver(int $id): string
    {
        $profesorId = $this->getProfesorId();
        $solicitud = $this->solicitudModel->getWithDetails($id);

        if (!$solicitud || (int)$solicitud['profesor_id'] !== $profesorId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Solicitud no encontrada');
        }

        $data = [
            'title'      => 'Detalle Solicitud - Academia Heroicos',
            'pageTitle'  => 'Detalle de Solicitud',
            'activePage' => 'clases_particulares',
            'solicitud'  => $solicitud,
        ];

        return view('profesor/clases_particulares/ver', $data);
    }

    public function responder(int $id): ResponseInterface
    {
        $profesorId = $this->getProfesorId();
        $solicitud = $this->solicitudModel->getWithDetails($id);

        if (!$solicitud || (int)$solicitud['profesor_id'] !== $profesorId) {
            return redirect()->back()->with('error', 'Solicitud no encontrada.');
        }

        if ($solicitud['estado'] !== 'pendiente') {
            return redirect()->back()->with('error', 'Esta solicitud ya fue respondida.');
        }

        $accion = $this->request->getPost('accion');

        if ($accion === 'aprobar') {
            $rules = [
                'precio_propuesto' => 'required|numeric|greater_than[0]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', 'Debes indicar un precio valido.');
            }

            $this->solicitudModel->update($id, [
                'estado'             => 'aprobada',
                'precio_propuesto'   => $this->request->getPost('precio_propuesto'),
                'respuesta_profesor' => $this->request->getPost('respuesta_profesor'),
            ]);

            $seccionPrecio = '<p><strong>Precio propuesto:</strong> $' . number_format($this->request->getPost('precio_propuesto'), 0, ',', '.') . '</p><p>Ingresa a la plataforma para aceptar o rechazar el precio.</p>';

        } elseif ($accion === 'rechazar') {
            $motivoRechazo = $this->request->getPost('motivo_rechazo');
            if (empty($motivoRechazo)) {
                return redirect()->back()->withInput()->with('error', 'Debes indicar un motivo de rechazo.');
            }

            $this->solicitudModel->update($id, [
                'estado'             => 'rechazada',
                'motivo_rechazo'     => $motivoRechazo,
                'respuesta_profesor' => $this->request->getPost('respuesta_profesor'),
            ]);

            $seccionPrecio = '';
        } else {
            return redirect()->back()->with('error', 'Accion no valida.');
        }

        // Refresh solicitud data
        $solicitud = $this->solicitudModel->getWithDetails($id);

        // Send email to acudiente
        try {
            $sendgrid = new \App\Libraries\SendGridService();
            $sendgrid->enviar(
                ['email' => $solicitud['acudiente_email'], 'nombre' => $solicitud['acudiente_nombres']],
                'respuesta_clase_particular',
                [
                    'nombre_acudiente'  => $solicitud['acudiente_nombres'] . ' ' . $solicitud['acudiente_apellidos'],
                    'nombre_profesor'   => $solicitud['profesor_nombres'] . ' ' . $solicitud['profesor_apellidos'],
                    'nombre_estudiante' => $solicitud['estudiante_nombres'] . ' ' . $solicitud['estudiante_apellidos'],
                    'estado'            => $solicitud['estado'] === 'aprobada' ? 'Aprobada' : 'Rechazada',
                    'seccion_precio'    => $seccionPrecio ?? '',
                    'respuesta_profesor' => $solicitud['respuesta_profesor'] ?? 'Sin mensaje adicional',
                    'url_ver'           => site_url('acudiente/clases-particulares'),
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email respuesta_clase_particular: ' . $e->getMessage());
        }

        // Notification for acudiente
        $notificacionModel = new NotificacionModel();
        $estadoTexto = $solicitud['estado'] === 'aprobada' ? 'aprobo' : 'rechazo';
        $notificacionModel->crearNotificacion(
            (int) $solicitud['acudiente_usuario_id'],
            'respuesta_clase_particular',
            'Respuesta a solicitud de clase particular',
            'El profesor ' . $solicitud['profesor_nombres'] . ' ' . $estadoTexto . ' tu solicitud.',
            'acudiente/clases-particulares'
        );

        $msgEstado = $solicitud['estado'] === 'aprobada' ? 'aprobada con precio propuesto' : 'rechazada';
        return redirect()->to('profesor/clases-particulares')
            ->with('success', "Solicitud {$msgEstado}. El acudiente sera notificado.");
    }

    private function getProfesorId(): int
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $profesor = $db->table('profesores')
            ->where('usuario_id', $userId)
            ->get()
            ->getRowArray();

        return $profesor['id'] ?? 0;
    }
}
