<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SesionClaseModel;
use App\Models\GrupoModel;

class AttendanceController extends BaseController
{
    protected SesionClaseModel $sesionModel;
    protected GrupoModel $grupoModel;

    public function __construct()
    {
        $this->sesionModel = new SesionClaseModel();
        $this->grupoModel = new GrupoModel();
    }

    /**
     * Attendance history by group
     */
    public function index(): string
    {
        $grupoId = $this->request->getGet('grupo_id');
        $filters = [
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
            'estado'      => $this->request->getGet('estado'),
        ];

        $sesiones = [];
        if ($grupoId) {
            $sesiones = $this->sesionModel->getByGrupo((int)$grupoId, $filters);
            // Enrich with attendance summary
            foreach ($sesiones as &$s) {
                $detail = $this->sesionModel->getWithDetails($s['id']);
                $s['total_estudiantes'] = $detail['total_estudiantes'];
                $s['total_presentes'] = $detail['total_presentes'];
            }
        }

        $grupos = $this->grupoModel->getAll();

        $data = [
            'title'      => 'Asistencia - Academia Heroicos',
            'pageTitle'  => 'Asistencia',
            'activePage' => 'attendance',
            'grupos'     => $grupos,
            'sesiones'   => $sesiones,
            'grupo_id'   => $grupoId,
            'filters'    => $filters,
        ];

        return view('admin/attendance/index', $data);
    }

    /**
     * Attendance report for a student
     */
    public function report(int $estudianteId): string
    {
        $studentModel = new \App\Models\StudentModel();
        $estudiante = $studentModel->find($estudianteId);

        if (!$estudiante) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Estudiante no encontrado');
        }

        $asistenciaModel = new \App\Models\AsistenciaModel();
        $historial = $asistenciaModel->getByEstudiante($estudianteId);
        $stats = $this->sesionModel->getStatsEstudiante($estudianteId);

        $data = [
            'title'      => 'Reporte Asistencia - ' . $estudiante['nombres'],
            'pageTitle'  => 'Reporte de Asistencia',
            'activePage' => 'attendance',
            'estudiante' => $estudiante,
            'historial'  => $historial,
            'stats'      => $stats,
        ];

        return view('admin/attendance/report', $data);
    }

    /**
     * View a specific session
     */
    public function session(int $id): string
    {
        $sesion = $this->sesionModel->getWithDetails($id);

        if (!$sesion) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Sesión no encontrada');
        }

        $data = [
            'title'      => 'Sesión de Clase',
            'pageTitle'  => 'Detalle de Sesión',
            'activePage' => 'attendance',
            'sesion'     => $sesion,
        ];

        return view('admin/attendance/session', $data);
    }
}
