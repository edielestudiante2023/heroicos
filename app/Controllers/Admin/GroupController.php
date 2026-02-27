<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GrupoModel;
use App\Models\CategoriaModel;
use App\Models\HorarioModel;
use App\Models\InscripcionModel;
use CodeIgniter\HTTP\ResponseInterface;

class GroupController extends BaseController
{
    protected GrupoModel $grupoModel;
    protected CategoriaModel $categoriaModel;
    protected HorarioModel $horarioModel;

    public function __construct()
    {
        $this->grupoModel = new GrupoModel();
        $this->categoriaModel = new CategoriaModel();
        $this->horarioModel = new HorarioModel();
    }

    /**
     * List all groups
     */
    public function index(): string
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'categoria_id' => $this->request->getGet('categoria_id'),
            'estado' => $this->request->getGet('estado'),
        ];

        $grupos = $this->grupoModel->getFiltered($filters);

        // Add additional data for each group
        foreach ($grupos as &$grupo) {
            $grupo['inscritos'] = $this->grupoModel->getInscritosCount($grupo['id']);
            $grupo['profesores'] = $this->grupoModel->getProfesores($grupo['id']);
        }

        $data = [
            'title' => 'Gestión de Grupos',
            'grupos' => $grupos,
            'categorias' => $this->categoriaModel->getActive(),
            'stats' => $this->grupoModel->getStats(),
            'filters' => $filters,
        ];

        return view('admin/groups/index', $data);
    }

    /**
     * Show create form
     */
    public function new(): string
    {
        $data = [
            'title' => 'Nuevo Grupo',
            'action' => 'create',
            'grupo' => [],
            'categorias' => $this->categoriaModel->getActive(),
            'profesores' => $this->getProfesoresDisponibles(),
            'horarios' => $this->horarioModel->getAll(),
        ];

        return view('admin/groups/form', $data);
    }

    /**
     * Create new group
     */
    public function create(): ResponseInterface
    {
        $rules = [
            'nombre' => 'required|min_length[3]|max_length[100]',
            'categoria_id' => 'required|integer',
            'cupo_maximo' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'categoria_id' => $this->request->getPost('categoria_id'),
            'cupo_maximo' => $this->request->getPost('cupo_maximo'),
            'estado' => 'activo',
        ];

        $grupoId = $this->grupoModel->insert($data);

        if (!$grupoId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el grupo');
        }

        // Assign professors if selected
        $profesores = $this->request->getPost('profesores') ?? [];
        $titular = $this->request->getPost('profesor_titular');

        foreach ($profesores as $profesorId) {
            $this->grupoModel->assignProfesor($grupoId, $profesorId, $profesorId == $titular);
        }

        // Assign schedules if selected
        $horarios = $this->request->getPost('horarios') ?? [];
        foreach ($horarios as $horarioId) {
            $this->grupoModel->assignHorario($grupoId, $horarioId, date('Y-m-d'));
        }

        return redirect()->to('admin/groups/' . $grupoId)
            ->with('success', 'Grupo creado exitosamente');
    }

    /**
     * Show group details
     */
    public function show(int $id): string
    {
        $grupo = $this->grupoModel->getWithDetails($id);

        if (!$grupo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Grupo no encontrado');
        }

        // Get enrolled students
        $inscripcionModel = new InscripcionModel();
        $inscritos = $inscripcionModel->getByGrupo($id);

        // Calculate ages
        foreach ($inscritos as &$est) {
            $est['edad'] = date_diff(date_create($est['fecha_nacimiento']), date_create('today'))->y;
        }

        $data = [
            'title' => 'Detalle de Grupo',
            'grupo' => $grupo,
            'inscritos' => $inscritos,
        ];

        return view('admin/groups/show', $data);
    }

    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $grupo = $this->grupoModel->getWithDetails($id);

        if (!$grupo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Grupo no encontrado');
        }

        $data = [
            'title' => 'Editar Grupo',
            'action' => 'update',
            'grupo' => $grupo,
            'categorias' => $this->categoriaModel->getActive(),
            'profesores' => $this->getProfesoresDisponibles(),
            'horarios' => $this->horarioModel->getAll(),
            'profesores_asignados' => array_column($grupo['profesores'], 'id'),
            'horarios_asignados' => array_column($grupo['horarios'], 'id'),
        ];

        return view('admin/groups/form', $data);
    }

    /**
     * Update group
     */
    public function update(int $id): ResponseInterface
    {
        $grupo = $this->grupoModel->find($id);

        if (!$grupo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Grupo no encontrado');
        }

        $rules = [
            'nombre' => 'required|min_length[3]|max_length[100]',
            'categoria_id' => 'required|integer',
            'cupo_maximo' => 'required|integer|greater_than[0]',
            'estado' => 'required|in_list[activo,inactivo]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'categoria_id' => $this->request->getPost('categoria_id'),
            'cupo_maximo' => $this->request->getPost('cupo_maximo'),
            'estado' => $this->request->getPost('estado'),
        ];

        $this->grupoModel->update($id, $data);

        // Update professor assignments
        $this->updateProfesorAssignments($id);

        // Update schedule assignments
        $this->updateHorarioAssignments($id);

        return redirect()->to('admin/groups/' . $id)
            ->with('success', 'Grupo actualizado exitosamente');
    }

    /**
     * Delete (deactivate) group
     */
    public function delete(int $id): ResponseInterface
    {
        $grupo = $this->grupoModel->find($id);

        if (!$grupo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Grupo no encontrado');
        }

        // Check if has active enrollments
        $inscritos = $this->grupoModel->getInscritosCount($id);
        if ($inscritos > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar el grupo porque tiene ' . $inscritos . ' estudiantes inscritos');
        }

        $this->grupoModel->update($id, ['estado' => 'inactivo']);

        return redirect()->to('admin/groups')
            ->with('success', 'Grupo desactivado exitosamente');
    }

    /**
     * Enroll student in group
     */
    public function enrollStudent(int $grupoId): ResponseInterface
    {
        $estudianteId = $this->request->getPost('estudiante_id');

        if (!$estudianteId) {
            return redirect()->back()->with('error', 'Seleccione un estudiante');
        }

        // Check if group has space
        if (!$this->grupoModel->hasAvailableSpots($grupoId)) {
            return redirect()->back()->with('error', 'El grupo no tiene cupos disponibles');
        }

        $inscripcionModel = new InscripcionModel();

        // Check if already enrolled
        if ($inscripcionModel->isEnrolled($estudianteId, $grupoId)) {
            return redirect()->back()->with('error', 'El estudiante ya está inscrito en este grupo');
        }

        if ($inscripcionModel->enroll($estudianteId, $grupoId)) {
            return redirect()->back()->with('success', 'Estudiante inscrito exitosamente');
        }

        return redirect()->back()->with('error', 'Error al inscribir estudiante');
    }

    /**
     * Remove student from group
     */
    public function unenrollStudent(int $grupoId, int $inscripcionId): ResponseInterface
    {
        $inscripcionModel = new InscripcionModel();
        $motivo = $this->request->getPost('motivo') ?? 'Retirado del grupo';

        if ($inscripcionModel->endEnrollment($inscripcionId, $motivo)) {
            return redirect()->back()->with('success', 'Estudiante retirado del grupo');
        }

        return redirect()->back()->with('error', 'Error al retirar estudiante');
    }

    /**
     * Get available professors
     */
    private function getProfesoresDisponibles(): array
    {
        $db = \Config\Database::connect();
        return $db->table('profesores p')
            ->select('p.id, p.nombres, p.apellidos, u.email')
            ->join('usuarios u', 'u.id = p.usuario_id')
            ->where('p.estado', 'activo')
            ->orderBy('p.apellidos', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Update professor assignments for group
     */
    private function updateProfesorAssignments(int $grupoId): void
    {
        $db = \Config\Database::connect();

        // Get current assignments
        $current = $db->table('profesor_grupos')
            ->where('grupo_id', $grupoId)
            ->get()
            ->getResultArray();
        $currentIds = array_column($current, 'profesor_id');

        // Get new assignments
        $newProfesores = $this->request->getPost('profesores') ?? [];
        $titular = $this->request->getPost('profesor_titular');

        // Remove unselected
        foreach ($currentIds as $profId) {
            if (!in_array($profId, $newProfesores)) {
                $this->grupoModel->removeProfesor($grupoId, $profId);
            }
        }

        // Add new ones
        foreach ($newProfesores as $profId) {
            if (!in_array($profId, $currentIds)) {
                $this->grupoModel->assignProfesor($grupoId, $profId, $profId == $titular);
            } else {
                // Update titular status
                $db->table('profesor_grupos')
                    ->where('grupo_id', $grupoId)
                    ->where('profesor_id', $profId)
                    ->update(['es_titular' => $profId == $titular ? 1 : 0]);
            }
        }
    }

    /**
     * Update schedule assignments for group
     */
    private function updateHorarioAssignments(int $grupoId): void
    {
        $db = \Config\Database::connect();

        // Get current active assignments
        $current = $db->table('grupo_horarios')
            ->where('grupo_id', $grupoId)
            ->where('(vigente_hasta IS NULL OR vigente_hasta >= CURDATE())')
            ->get()
            ->getResultArray();
        $currentIds = array_column($current, 'horario_id');

        // Get new assignments
        $newHorarios = $this->request->getPost('horarios') ?? [];

        // End removed schedules
        foreach ($current as $gh) {
            if (!in_array($gh['horario_id'], $newHorarios)) {
                $db->table('grupo_horarios')
                    ->where('id', $gh['id'])
                    ->update(['vigente_hasta' => date('Y-m-d')]);
            }
        }

        // Add new ones
        foreach ($newHorarios as $horarioId) {
            if (!in_array($horarioId, $currentIds)) {
                $this->grupoModel->assignHorario($grupoId, $horarioId, date('Y-m-d'));
            }
        }
    }
}
