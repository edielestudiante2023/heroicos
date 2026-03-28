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

        $db = \Config\Database::connect();

        // Get other active professors (exclude current one) for reassignment
        $otrosProfesores = $db->table('profesores p')
            ->select('p.id, p.nombres, p.apellidos, p.especialidad')
            ->join('usuarios u', 'u.id = p.usuario_id')
            ->where('p.estado', 'activo')
            ->where('u.estado', 'activo')
            ->where('p.id !=', $profesorId)
            ->orderBy('p.nombres', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'            => 'Detalle Solicitud - Academia Heroicos',
            'pageTitle'        => 'Detalle de Solicitud',
            'activePage'       => 'clases_particulares',
            'solicitud'        => $solicitud,
            'otrosProfesores'  => $otrosProfesores,
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

        } elseif ($accion === 'reasignar') {
            $nuevoProfesorId = $this->request->getPost('nuevo_profesor_id');
            if (empty($nuevoProfesorId)) {
                return redirect()->back()->withInput()->with('error', 'Debes seleccionar un profesor.');
            }

            $db = \Config\Database::connect();

            // Get new professor data
            $nuevoProfesor = $db->table('profesores p')
                ->select('p.*, u.email as profesor_email, u.id as usuario_id')
                ->join('usuarios u', 'u.id = p.usuario_id')
                ->where('p.id', $nuevoProfesorId)
                ->get()->getRowArray();

            if (!$nuevoProfesor) {
                return redirect()->back()->with('error', 'Profesor no encontrado.');
            }

            $mensajeProfesor = $this->request->getPost('respuesta_profesor') ?? '';

            // Reassign solicitud to new professor, reset to pendiente
            $this->solicitudModel->update($id, [
                'profesor_id'        => $nuevoProfesorId,
                'estado'             => 'pendiente',
                'respuesta_profesor' => null,
                'precio_propuesto'   => null,
                'precio_aceptado'    => null,
                'motivo_rechazo'     => null,
            ]);

            $solicitud = $this->solicitudModel->getWithDetails($id);
            $profesorActualNombre = $solicitud['profesor_nombres'] . ' ' . $solicitud['profesor_apellidos'];

            // Notify acudiente about reassignment
            try {
                $sendgrid = new \App\Libraries\SendGridService();
                $sendgrid->enviar(
                    ['email' => $solicitud['acudiente_email'], 'nombre' => $solicitud['acudiente_nombres']],
                    'reasignacion_clase_particular',
                    [
                        'nombre_acudiente'        => $solicitud['acudiente_nombres'] . ' ' . $solicitud['acudiente_apellidos'],
                        'nombre_profesor_original' => session()->get('user_name') ?? 'El profesor',
                        'nombre_profesor_nuevo'    => $nuevoProfesor['nombres'] . ' ' . $nuevoProfesor['apellidos'],
                        'nombre_estudiante'        => $solicitud['estudiante_nombres'] . ' ' . $solicitud['estudiante_apellidos'],
                        'mensaje_profesor'         => $mensajeProfesor ?: 'Sin mensaje adicional',
                        'url_ver'                  => site_url('acudiente/clases-particulares'),
                    ]
                );
            } catch (\Exception $e) {
                log_message('error', 'Error enviando email reasignacion_clase_particular: ' . $e->getMessage());
            }

            // Notify new professor about the solicitud
            try {
                $sendgrid = new \App\Libraries\SendGridService();
                $sendgrid->enviar(
                    ['email' => $nuevoProfesor['profesor_email'], 'nombre' => $nuevoProfesor['nombres']],
                    'solicitud_clase_particular',
                    [
                        'nombre_profesor'   => $nuevoProfesor['nombres'] . ' ' . $nuevoProfesor['apellidos'],
                        'nombre_acudiente'  => $solicitud['acudiente_nombres'] . ' ' . $solicitud['acudiente_apellidos'],
                        'nombre_estudiante' => $solicitud['estudiante_nombres'] . ' ' . $solicitud['estudiante_apellidos'],
                        'fecha_preferida'   => date('d/m/Y', strtotime($solicitud['fecha_preferida'])),
                        'hora_preferida'    => date('h:i A', strtotime($solicitud['hora_preferida'])),
                        'duracion'          => $solicitud['duracion_minutos'] . ' minutos',
                        'observaciones'     => $solicitud['observaciones'] ?? 'Ninguna',
                        'url_responder'     => site_url("profesor/clases-particulares/{$id}"),
                    ]
                );
            } catch (\Exception $e) {
                log_message('error', 'Error enviando email solicitud al nuevo profesor: ' . $e->getMessage());
            }

            // Notifications
            $notificacionModel = new NotificacionModel();
            $notificacionModel->crearNotificacion(
                (int) $solicitud['acudiente_usuario_id'],
                'reasignacion_clase_particular',
                'Tu solicitud fue reasignada a otro profesor',
                'El profesor te sugiere tomar la clase con ' . $nuevoProfesor['nombres'] . ' ' . $nuevoProfesor['apellidos'],
                'acudiente/clases-particulares'
            );
            $notificacionModel->crearNotificacion(
                (int) $nuevoProfesor['usuario_id'],
                'solicitud_clase_particular',
                'Nueva solicitud de clase particular (reasignada)',
                $solicitud['acudiente_nombres'] . ' solicita clase para ' . $solicitud['estudiante_nombres'],
                "profesor/clases-particulares/{$id}"
            );

            return redirect()->to('profesor/clases-particulares')
                ->with('success', 'Solicitud reasignada al profesor ' . $nuevoProfesor['nombres'] . ' ' . $nuevoProfesor['apellidos'] . '. Ambos seran notificados.');
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
