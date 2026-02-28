<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TorneoModel;
use App\Models\TorneoInscripcionModel;
use App\Models\CategoriaModel;
use App\Models\StudentModel;
use CodeIgniter\HTTP\ResponseInterface;

class TournamentController extends BaseController
{
    protected TorneoModel $torneoModel;
    protected CategoriaModel $categoriaModel;

    public function __construct()
    {
        $this->torneoModel = new TorneoModel();
        $this->categoriaModel = new CategoriaModel();
    }

    /**
     * List tournaments
     */
    public function index(): string
    {
        $filters = [
            'estado'       => $this->request->getGet('estado'),
            'categoria_id' => $this->request->getGet('categoria_id'),
            'search'       => $this->request->getGet('search'),
        ];

        $data = [
            'title'      => 'Torneos - Academia Heroicos',
            'pageTitle'  => 'Torneos',
            'activePage' => 'tournaments',
            'torneos'    => $this->torneoModel->getFiltered($filters),
            'categorias' => $this->categoriaModel->getActive(),
            'stats'      => $this->torneoModel->getStats(),
            'filters'    => $filters,
        ];

        return view('admin/tournaments/index', $data);
    }

    /**
     * Create form
     */
    public function new(): string
    {
        $data = [
            'title'      => 'Nuevo Torneo',
            'pageTitle'  => 'Nuevo Torneo',
            'activePage' => 'tournaments',
            'action'     => 'create',
            'torneo'     => [],
            'categorias' => $this->categoriaModel->getActive(),
        ];

        return view('admin/tournaments/form', $data);
    }

    /**
     * Create tournament
     */
    public function create(): ResponseInterface
    {
        $rules = [
            'nombre'       => 'required|min_length[3]|max_length[200]',
            'fecha_evento' => 'required|valid_date',
            'cupo_maximo'  => 'required|integer|greater_than[0]',
            'costo'        => 'required|decimal',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre'                     => $this->request->getPost('nombre'),
            'descripcion'                => $this->request->getPost('descripcion'),
            'lugar'                      => $this->request->getPost('lugar'),
            'fecha_evento'               => $this->request->getPost('fecha_evento'),
            'hora_evento'                => $this->request->getPost('hora_evento'),
            'fecha_apertura_inscripcion' => $this->request->getPost('fecha_apertura_inscripcion') ?: null,
            'fecha_cierre_inscripcion'   => $this->request->getPost('fecha_cierre_inscripcion') ?: null,
            'cupo_maximo'                => $this->request->getPost('cupo_maximo'),
            'costo'                      => $this->request->getPost('costo'),
            'categoria_id'               => $this->request->getPost('categoria_id') ?: null,
            'estado'                     => 'programado',
        ];

        $id = $this->torneoModel->insert($data);

        if ($id) {
            return redirect()->to('admin/tournaments/' . $id)
                ->with('success', 'Torneo creado exitosamente');
        }

        return redirect()->back()->withInput()->with('error', 'Error al crear el torneo');
    }

    /**
     * Show tournament details
     */
    public function show(int $id): string
    {
        $torneo = $this->torneoModel->getWithDetails($id);

        if (!$torneo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Torneo no encontrado');
        }

        // Get students available to enroll
        $db = \Config\Database::connect();
        $estudiantesBuilder = $db->table('estudiantes e')
            ->select('e.id, e.nombres, e.apellidos, e.codigo, a.id as acudiente_id, a.nombres as acudiente_nombres, a.apellidos as acudiente_apellidos')
            ->join('acudientes a', 'a.id = e.acudiente_id', 'left')
            ->where('e.estado', 'activo');

        // Filter by category if tournament has one
        if ($torneo['categoria_id']) {
            $estudiantesBuilder->join('inscripciones i', 'i.estudiante_id = e.id AND i.estado = "activa"', 'left')
                ->join('grupos g', 'g.id = i.grupo_id', 'left')
                ->where('g.categoria_id', $torneo['categoria_id']);
        }

        $estudiantes = $estudiantesBuilder->orderBy('e.apellidos', 'ASC')->get()->getResultArray();

        $data = [
            'title'       => 'Torneo: ' . $torneo['nombre'],
            'pageTitle'   => $torneo['nombre'],
            'activePage'  => 'tournaments',
            'torneo'      => $torneo,
            'estudiantes' => $estudiantes,
        ];

        return view('admin/tournaments/show', $data);
    }

    /**
     * Edit form
     */
    public function edit(int $id): string
    {
        $torneo = $this->torneoModel->find($id);

        if (!$torneo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Torneo no encontrado');
        }

        $data = [
            'title'      => 'Editar Torneo',
            'pageTitle'  => 'Editar Torneo',
            'activePage' => 'tournaments',
            'action'     => 'update',
            'torneo'     => $torneo,
            'categorias' => $this->categoriaModel->getActive(),
        ];

        return view('admin/tournaments/form', $data);
    }

    /**
     * Update tournament
     */
    public function update(int $id): ResponseInterface
    {
        $torneo = $this->torneoModel->find($id);
        if (!$torneo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Torneo no encontrado');
        }

        $data = [
            'nombre'                     => $this->request->getPost('nombre'),
            'descripcion'                => $this->request->getPost('descripcion'),
            'lugar'                      => $this->request->getPost('lugar'),
            'fecha_evento'               => $this->request->getPost('fecha_evento'),
            'hora_evento'                => $this->request->getPost('hora_evento'),
            'fecha_apertura_inscripcion' => $this->request->getPost('fecha_apertura_inscripcion') ?: null,
            'fecha_cierre_inscripcion'   => $this->request->getPost('fecha_cierre_inscripcion') ?: null,
            'cupo_maximo'                => $this->request->getPost('cupo_maximo'),
            'costo'                      => $this->request->getPost('costo'),
            'categoria_id'               => $this->request->getPost('categoria_id') ?: null,
        ];

        $this->torneoModel->update($id, $data);

        return redirect()->to('admin/tournaments/' . $id)
            ->with('success', 'Torneo actualizado exitosamente');
    }

    /**
     * Delete tournament
     */
    public function delete(int $id): ResponseInterface
    {
        $torneo = $this->torneoModel->find($id);
        if (!$torneo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Torneo no encontrado');
        }

        $inscritos = $this->torneoModel->getInscritosCount($id);
        if ($inscritos > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar un torneo con inscripciones. Cancélelo en su lugar.');
        }

        $this->torneoModel->delete($id);

        return redirect()->to('admin/tournaments')
            ->with('success', 'Torneo eliminado exitosamente');
    }

    /**
     * Enroll student in tournament
     */
    public function enrollStudent(int $torneoId): ResponseInterface
    {
        $estudianteId = $this->request->getPost('estudiante_id');
        $acudienteId = $this->request->getPost('acudiente_id');

        if (!$estudianteId) {
            return redirect()->back()->with('error', 'Seleccione un estudiante');
        }

        $torneo = $this->torneoModel->find($torneoId);
        if (!$torneo || !in_array($torneo['estado'], ['inscripciones_abiertas', 'programado'])) {
            return redirect()->back()->with('error', 'Las inscripciones no están abiertas para este torneo');
        }

        if (!$this->torneoModel->hasCuposDisponibles($torneoId)) {
            return redirect()->back()->with('error', 'No hay cupos disponibles');
        }

        $inscripcionModel = new TorneoInscripcionModel();

        if ($inscripcionModel->isEnrolled((int)$estudianteId, $torneoId)) {
            return redirect()->back()->with('error', 'El estudiante ya está inscrito en este torneo');
        }

        // Get acudiente_id from student if not provided
        if (!$acudienteId) {
            $student = (new StudentModel())->find($estudianteId);
            $acudienteId = $student['acudiente_id'] ?? 0;
        }

        if ($inscripcionModel->enrollStudent($torneoId, (int)$estudianteId, (int)$acudienteId)) {
            return redirect()->back()->with('success', 'Estudiante inscrito exitosamente en el torneo');
        }

        return redirect()->back()->with('error', 'Error al inscribir estudiante');
    }

    /**
     * Remove student from tournament
     */
    public function unenrollStudent(int $torneoId, int $inscripcionId): ResponseInterface
    {
        $inscripcionModel = new TorneoInscripcionModel();

        if ($inscripcionModel->cancelEnrollment($inscripcionId)) {
            return redirect()->back()->with('success', 'Inscripción cancelada');
        }

        return redirect()->back()->with('error', 'Error al cancelar inscripción');
    }

    /**
     * Change tournament status
     */
    public function changeStatus(int $id): ResponseInterface
    {
        $nuevoEstado = $this->request->getPost('estado');
        $estadosValidos = ['programado', 'inscripciones_abiertas', 'inscripciones_cerradas', 'en_curso', 'finalizado', 'cancelado'];

        if (!in_array($nuevoEstado, $estadosValidos)) {
            return redirect()->back()->with('error', 'Estado no válido');
        }

        $this->torneoModel->update($id, ['estado' => $nuevoEstado]);

        return redirect()->back()->with('success', 'Estado del torneo actualizado a: ' . str_replace('_', ' ', $nuevoEstado));
    }
}
