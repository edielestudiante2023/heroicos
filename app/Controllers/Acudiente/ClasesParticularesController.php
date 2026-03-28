<?php

namespace App\Controllers\Acudiente;

use App\Controllers\BaseController;
use App\Models\SolicitudClaseParticularModel;
use App\Models\ClaseParticularModel;
use App\Models\NotificacionModel;
use App\Models\CargoModel;
use CodeIgniter\HTTP\ResponseInterface;

class ClasesParticularesController extends BaseController
{
    protected SolicitudClaseParticularModel $solicitudModel;

    public function __construct()
    {
        $this->solicitudModel = new SolicitudClaseParticularModel();
    }

    public function index(): string
    {
        $acudienteId = $this->getAcudienteId();
        $solicitudes = $this->solicitudModel->getByAcudiente($acudienteId);

        $data = [
            'title'       => 'Clases Particulares - Academia Heroicos',
            'pageTitle'   => 'Clases Particulares',
            'activePage'  => 'clases-particulares',
            'solicitudes' => $solicitudes,
        ];

        return view('acudiente/clases_particulares/index', $data);
    }

    public function solicitar(): string
    {
        $acudienteId = $this->getAcudienteId();
        $db = \Config\Database::connect();

        // Get acudiente's active students
        $estudiantes = $db->table('estudiantes')
            ->where('acudiente_id', $acudienteId)
            ->where('estado', 'activo')
            ->orderBy('nombres', 'ASC')
            ->get()
            ->getResultArray();

        // Get active professors
        $profesores = $db->table('profesores p')
            ->select('p.id, p.nombres, p.apellidos, p.especialidad')
            ->join('usuarios u', 'u.id = p.usuario_id')
            ->where('p.estado', 'activo')
            ->where('u.estado', 'activo')
            ->orderBy('p.nombres', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'       => 'Solicitar Clase Particular - Academia Heroicos',
            'pageTitle'   => 'Solicitar Clase Particular',
            'activePage'  => 'clases-particulares',
            'estudiantes' => $estudiantes,
            'profesores'  => $profesores,
        ];

        return view('acudiente/clases_particulares/solicitar', $data);
    }

    public function guardar(): ResponseInterface
    {
        $acudienteId = $this->getAcudienteId();

        $rules = [
            'profesor_id'     => 'required|integer',
            'estudiante_id'   => 'required|integer',
            'fecha_preferida' => 'required|valid_date',
            'hora_preferida'  => 'required',
            'duracion_minutos' => 'required|in_list[30,60,90,120]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Por favor completa todos los campos requeridos.');
        }

        $db = \Config\Database::connect();
        $estudianteId = $this->request->getPost('estudiante_id');

        // Verify student belongs to this acudiente
        $estudiante = $db->table('estudiantes')
            ->where('id', $estudianteId)
            ->where('acudiente_id', $acudienteId)
            ->get()->getRowArray();

        if (!$estudiante) {
            return redirect()->back()->with('error', 'Estudiante no encontrado.');
        }

        $profesorId = $this->request->getPost('profesor_id');

        // Insert solicitud
        $this->solicitudModel->insert([
            'acudiente_id'    => $acudienteId,
            'estudiante_id'   => $estudianteId,
            'profesor_id'     => $profesorId,
            'fecha_preferida' => $this->request->getPost('fecha_preferida'),
            'hora_preferida'  => $this->request->getPost('hora_preferida'),
            'duracion_minutos' => $this->request->getPost('duracion_minutos'),
            'observaciones'   => $this->request->getPost('observaciones'),
            'estado'          => 'pendiente',
        ]);

        $solicitudId = $this->solicitudModel->getInsertID();

        // Get details for email
        $solicitud = $this->solicitudModel->getWithDetails($solicitudId);

        // Send email to profesor
        try {
            $sendgrid = new \App\Libraries\SendGridService();
            $sendgrid->enviar(
                ['email' => $solicitud['profesor_email'], 'nombre' => $solicitud['profesor_nombres']],
                'solicitud_clase_particular',
                [
                    'nombre_profesor'   => $solicitud['profesor_nombres'] . ' ' . $solicitud['profesor_apellidos'],
                    'nombre_acudiente'  => $solicitud['acudiente_nombres'] . ' ' . $solicitud['acudiente_apellidos'],
                    'nombre_estudiante' => $solicitud['estudiante_nombres'] . ' ' . $solicitud['estudiante_apellidos'],
                    'fecha_preferida'   => date('d/m/Y', strtotime($solicitud['fecha_preferida'])),
                    'hora_preferida'    => date('h:i A', strtotime($solicitud['hora_preferida'])),
                    'duracion'          => $solicitud['duracion_minutos'] . ' minutos',
                    'observaciones'     => $solicitud['observaciones'] ?? 'Ninguna',
                    'url_responder'     => site_url("profesor/clases-particulares/{$solicitudId}"),
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email solicitud_clase_particular: ' . $e->getMessage());
        }

        // Create notification for profesor
        $notificacionModel = new NotificacionModel();
        $notificacionModel->crearNotificacion(
            (int) $solicitud['profesor_usuario_id'],
            'solicitud_clase_particular',
            'Nueva solicitud de clase particular',
            $solicitud['acudiente_nombres'] . ' ' . $solicitud['acudiente_apellidos'] . ' solicita clase para ' . $solicitud['estudiante_nombres'],
            "profesor/clases-particulares/{$solicitudId}"
        );

        return redirect()->to('acudiente/clases-particulares')
            ->with('success', 'Solicitud enviada exitosamente. El profesor sera notificado por email.');
    }

    public function aceptarPrecio(int $id): ResponseInterface
    {
        $acudienteId = $this->getAcudienteId();
        $solicitud = $this->solicitudModel->getWithDetails($id);

        if (!$solicitud || (int)$solicitud['acudiente_id'] !== $acudienteId) {
            return redirect()->back()->with('error', 'Solicitud no encontrada.');
        }

        if ($solicitud['estado'] !== 'aprobada') {
            return redirect()->back()->with('error', 'Esta solicitud no esta en estado de aceptar precio.');
        }

        $db = \Config\Database::connect();

        // Update solicitud
        $this->solicitudModel->update($id, [
            'precio_aceptado' => 1,
            'estado'          => 'agendada',
        ]);

        // Find CPAR concepto and active periodo
        $concepto = $db->table('conceptos_cobro')->where('codigo', 'CPAR')->get()->getRowArray();
        $periodo = $db->table('periodos')->where('estado', 'activo')->get()->getRowArray();

        if (!$concepto || !$periodo) {
            return redirect()->back()->with('error', 'No se pudo crear el cargo. Contacte al administrador.');
        }

        // Create cargo
        $cargoModel = new CargoModel();
        $cargoId = $cargoModel->insert([
            'estudiante_id'    => $solicitud['estudiante_id'],
            'concepto_id'      => $concepto['id'],
            'periodo_id'       => $periodo['id'],
            'descripcion'      => 'Clase particular con Prof. ' . $solicitud['profesor_nombres'] . ' ' . $solicitud['profesor_apellidos'],
            'valor_original'   => $solicitud['precio_propuesto'],
            'valor_pagado'     => 0,
            'saldo_pendiente'  => $solicitud['precio_propuesto'],
            'fecha_vencimiento' => $solicitud['fecha_preferida'],
            'estado'           => 'pendiente',
        ]);

        // Create clase_particular
        $claseModel = new ClaseParticularModel();
        $claseModel->createFromSolicitud($solicitud, $cargoId);

        // Send email to profesor
        try {
            $sendgrid = new \App\Libraries\SendGridService();
            $sendgrid->enviar(
                ['email' => $solicitud['profesor_email'], 'nombre' => $solicitud['profesor_nombres']],
                'precio_aceptado_clase',
                [
                    'nombre_profesor'   => $solicitud['profesor_nombres'] . ' ' . $solicitud['profesor_apellidos'],
                    'nombre_acudiente'  => $solicitud['acudiente_nombres'] . ' ' . $solicitud['acudiente_apellidos'],
                    'nombre_estudiante' => $solicitud['estudiante_nombres'] . ' ' . $solicitud['estudiante_apellidos'],
                    'fecha'             => date('d/m/Y', strtotime($solicitud['fecha_preferida'])),
                    'hora'              => date('h:i A', strtotime($solicitud['hora_preferida'])),
                    'precio'            => number_format($solicitud['precio_propuesto'], 0, ',', '.'),
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email precio_aceptado_clase: ' . $e->getMessage());
        }

        // Notification for profesor
        $notificacionModel = new NotificacionModel();
        $notificacionModel->crearNotificacion(
            (int) $solicitud['profesor_usuario_id'],
            'precio_aceptado',
            'Precio aceptado - Clase particular confirmada',
            $solicitud['acudiente_nombres'] . ' acepto el precio de $' . number_format($solicitud['precio_propuesto'], 0, ',', '.'),
            "profesor/clases-particulares/{$id}"
        );

        return redirect()->to('acudiente/clases-particulares')
            ->with('success', 'Precio aceptado. La clase ha sido programada y el cargo generado.');
    }

    public function rechazarPrecio(int $id): ResponseInterface
    {
        $acudienteId = $this->getAcudienteId();
        $solicitud = $this->solicitudModel->getWithDetails($id);

        if (!$solicitud || (int)$solicitud['acudiente_id'] !== $acudienteId) {
            return redirect()->back()->with('error', 'Solicitud no encontrada.');
        }

        if ($solicitud['estado'] !== 'aprobada') {
            return redirect()->back()->with('error', 'Esta solicitud no esta en estado de rechazar precio.');
        }

        $this->solicitudModel->update($id, [
            'precio_aceptado' => 0,
            'estado'          => 'rechazada',
            'motivo_rechazo'  => $this->request->getPost('motivo_rechazo') ?? 'Precio no aceptado',
        ]);

        // Notification for profesor
        $notificacionModel = new NotificacionModel();
        $notificacionModel->crearNotificacion(
            (int) $solicitud['profesor_usuario_id'],
            'precio_rechazado',
            'Precio rechazado - Clase particular',
            $solicitud['acudiente_nombres'] . ' rechazo el precio propuesto.',
            "profesor/clases-particulares/{$id}"
        );

        return redirect()->to('acudiente/clases-particulares')
            ->with('success', 'Precio rechazado. La solicitud ha sido cancelada.');
    }

    private function getAcudienteId(): int
    {
        $acudienteId = session()->get('acudiente_id');
        if ($acudienteId) return (int)$acudienteId;

        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $acudiente = $db->table('acudientes')
            ->where('usuario_id', $userId)
            ->get()->getRowArray();

        return $acudiente['id'] ?? 0;
    }
}
