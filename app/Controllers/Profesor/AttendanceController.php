<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\SesionClaseModel;
use App\Models\AsistenciaModel;
use App\Models\GrupoModel;
use App\Models\InscripcionModel;
use CodeIgniter\HTTP\ResponseInterface;

class AttendanceController extends BaseController
{
    protected SesionClaseModel $sesionModel;
    protected AsistenciaModel $asistenciaModel;

    public function __construct()
    {
        $this->sesionModel = new SesionClaseModel();
        $this->asistenciaModel = new AsistenciaModel();
    }

    /**
     * List my attendance sessions
     */
    public function index(): string
    {
        $profesorId = $this->getProfesorId();
        $sesiones = $this->sesionModel->getByProfesor($profesorId);

        $data = [
            'title'      => 'Mi Asistencia - Academia Heroicos',
            'pageTitle'  => 'Asistencia',
            'activePage' => 'attendance',
            'sesiones'   => $sesiones,
        ];

        return view('profesor/attendance/index', $data);
    }

    /**
     * Take attendance for a group
     */
    public function take(int $grupoId): string
    {
        $profesorId = $this->getProfesorId();
        $grupoModel = new GrupoModel();
        $grupo = $grupoModel->getWithDetails($grupoId);

        if (!$grupo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Grupo no encontrado');
        }

        // Verify professor is assigned to this group
        $profesorAsignado = false;
        foreach ($grupo['profesores'] as $prof) {
            if ($prof['id'] == $profesorId) {
                $profesorAsignado = true;
                break;
            }
        }

        // Admin can also access
        $rolNombre = session()->get('rol_nombre');
        if (!$profesorAsignado && $rolNombre !== 'admin') {
            return redirect()->to('profesor/dashboard')
                ->with('error', 'No tienes permiso para tomar asistencia en este grupo');
        }

        // Get enrolled students
        $inscripcionModel = new InscripcionModel();
        $estudiantes = $inscripcionModel->getByGrupo($grupoId);

        $data = [
            'title'       => 'Tomar Asistencia - ' . $grupo['nombre'],
            'pageTitle'   => 'Tomar Asistencia',
            'activePage'  => 'attendance',
            'grupo'       => $grupo,
            'estudiantes' => $estudiantes,
            'fecha'       => date('Y-m-d'),
            'profesorId'  => $profesorId,
        ];

        return view('profesor/attendance/take', $data);
    }

    /**
     * Save attendance
     */
    public function save(): ResponseInterface
    {
        $grupoId = $this->request->getPost('grupo_id');
        $profesorId = $this->request->getPost('profesor_id') ?: $this->getProfesorId();
        $fecha = $this->request->getPost('fecha') ?: date('Y-m-d');
        $horaInicio = $this->request->getPost('hora_inicio') ?: date('H:i:s');
        $horaFin = $this->request->getPost('hora_fin') ?: date('H:i:s', strtotime('+1 hour'));
        $presentes = $this->request->getPost('presentes') ?? [];
        $estudianteIds = $this->request->getPost('estudiante_ids') ?? [];
        $observaciones = $this->request->getPost('observaciones') ?? '';

        if (empty($grupoId) || empty($estudianteIds)) {
            return redirect()->back()->with('error', 'Datos incompletos para registrar asistencia');
        }

        // Create session
        $sesionId = $this->sesionModel->insert([
            'grupo_id'      => $grupoId,
            'profesor_id'   => $profesorId,
            'fecha'         => $fecha,
            'hora_inicio'   => $horaInicio,
            'hora_fin'      => $horaFin,
            'estado'        => 'realizada',
            'observaciones' => $observaciones,
        ]);

        if (!$sesionId) {
            return redirect()->back()->with('error', 'Error al crear la sesión de clase');
        }

        // Register attendance
        $userId = session()->get('user_id');
        $result = $this->asistenciaModel->registrarAsistencia(
            $sesionId,
            $presentes,
            $estudianteIds,
            $userId
        );

        if ($result) {
            // Check for consecutive absences and send alerts
            $ausentes = array_diff($estudianteIds, $presentes);
            if (!empty($ausentes)) {
                $this->verificarInasistenciasConsecutivas($ausentes, $grupoId);
            }

            $totalPresentes = count($presentes);
            $totalEstudiantes = count($estudianteIds);
            return redirect()->to('profesor/groups/' . $grupoId)
                ->with('success', "Asistencia registrada: {$totalPresentes}/{$totalEstudiantes} presentes");
        }

        return redirect()->back()->with('error', 'Error al registrar asistencia');
    }

    /**
     * Check if absent students have 3+ consecutive absences and send alert
     */
    protected function verificarInasistenciasConsecutivas(array $estudianteIds, int $grupoId): void
    {
        try {
            $db = \Config\Database::connect();

            foreach ($estudianteIds as $estudianteId) {
                // Count recent consecutive absences for this student in this group
                $inasistencias = $db->query("
                    SELECT a.id, sc.fecha
                    FROM asistencias a
                    JOIN sesiones_clase sc ON sc.id = a.sesion_id
                    WHERE a.estudiante_id = ?
                    AND sc.grupo_id = ?
                    AND a.presente = 0
                    ORDER BY sc.fecha DESC
                    LIMIT 10
                ", [$estudianteId, $grupoId])->getResultArray();

                if (count($inasistencias) < 3) continue;

                // Verify they are truly consecutive (no attendance in between)
                $ultimasSesiones = $db->query("
                    SELECT a.presente, sc.fecha
                    FROM asistencias a
                    JOIN sesiones_clase sc ON sc.id = a.sesion_id
                    WHERE a.estudiante_id = ?
                    AND sc.grupo_id = ?
                    ORDER BY sc.fecha DESC
                    LIMIT 5
                ", [$estudianteId, $grupoId])->getResultArray();

                $consecutivas = 0;
                $fechasInasistencia = [];
                foreach ($ultimasSesiones as $sesion) {
                    if (!$sesion['presente']) {
                        $consecutivas++;
                        $fechasInasistencia[] = date('d/m/Y', strtotime($sesion['fecha']));
                    } else {
                        break;
                    }
                }

                if ($consecutivas < 3) continue;

                // Check if alert was already sent in last 7 days
                $alertaReciente = $db->table('emails_enviados')
                    ->where('plantilla', 'alerta_inasistencia')
                    ->like('cuerpo', "estudiante_id={$estudianteId}")
                    ->where('created_at >', date('Y-m-d H:i:s', strtotime('-7 days')))
                    ->countAllResults();

                if ($alertaReciente > 0) continue;

                // Get student + acudiente info
                $estudiante = $db->table('estudiantes e')
                    ->select('e.nombres, e.apellidos, a.nombres as acud_nombres, a.apellidos as acud_apellidos, u.email')
                    ->join('acudientes a', 'a.id = e.acudiente_id')
                    ->join('usuarios u', 'u.id = a.usuario_id')
                    ->where('e.id', $estudianteId)
                    ->get()->getRowArray();

                if (!$estudiante) continue;

                $sendgrid = new \App\Libraries\SendGridService();
                $sendgrid->enviar(
                    ['email' => $estudiante['email'], 'nombre' => $estudiante['acud_nombres']],
                    'alerta_inasistencia',
                    [
                        'nombre_acudiente'       => $estudiante['acud_nombres'] . ' ' . $estudiante['acud_apellidos'],
                        'nombre_estudiante'      => $estudiante['nombres'] . ' ' . $estudiante['apellidos'],
                        'cantidad_inasistencias' => (string)$consecutivas,
                        'fechas'                 => implode('<br>', $fechasInasistencia),
                    ]
                );

                // Log with student ID marker for de-duplication
                log_message('info', "Alerta inasistencia enviada: estudiante_id={$estudianteId}, consecutivas={$consecutivas}");
            }
        } catch (\Exception $e) {
            log_message('error', 'Error verificando inasistencias: ' . $e->getMessage());
        }
    }

    /**
     * Get professor ID from current session
     */
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
