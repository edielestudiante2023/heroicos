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
            $totalPresentes = count($presentes);
            $totalEstudiantes = count($estudianteIds);
            return redirect()->to('profesor/groups/' . $grupoId)
                ->with('success', "Asistencia registrada: {$totalPresentes}/{$totalEstudiantes} presentes");
        }

        return redirect()->back()->with('error', 'Error al registrar asistencia');
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
