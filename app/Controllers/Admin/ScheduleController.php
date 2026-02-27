<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HorarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class ScheduleController extends BaseController
{
    protected HorarioModel $horarioModel;

    public function __construct()
    {
        $this->horarioModel = new HorarioModel();
    }

    /**
     * List all schedules
     */
    public function index(): string
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'dia_semana' => $this->request->getGet('dia_semana'),
        ];

        $horarios = $this->horarioModel->getFiltered($filters);

        // Add group count for each schedule
        $db = \Config\Database::connect();
        foreach ($horarios as &$h) {
            $h['grupos_count'] = $db->table('grupo_horarios')
                ->where('horario_id', $h['id'])
                ->where('(vigente_hasta IS NULL OR vigente_hasta >= CURDATE())')
                ->countAllResults();
        }

        $data = [
            'title' => 'Gestión de Horarios',
            'horarios' => $horarios,
            'dias' => HorarioModel::$dias,
            'filters' => $filters,
        ];

        return view('admin/schedules/index', $data);
    }

    /**
     * Show create form
     */
    public function new(): string
    {
        $data = [
            'title' => 'Nuevo Horario',
            'action' => 'create',
            'horario' => [],
            'dias' => HorarioModel::$dias,
        ];

        return view('admin/schedules/form', $data);
    }

    /**
     * Create new schedule
     */
    public function create(): ResponseInterface
    {
        $rules = [
            'nombre' => 'required|min_length[3]|max_length[100]',
            'dia_semana' => 'required|integer|greater_than[0]|less_than[8]',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $horaInicio = $this->request->getPost('hora_inicio');
        $horaFin = $this->request->getPost('hora_fin');

        // Validate times
        if ($horaInicio >= $horaFin) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'La hora de fin debe ser posterior a la hora de inicio');
        }

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'dia_semana' => $this->request->getPost('dia_semana'),
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'lugar' => $this->request->getPost('lugar'),
        ];

        $horarioId = $this->horarioModel->insert($data);

        if (!$horarioId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el horario');
        }

        return redirect()->to('admin/schedules')
            ->with('success', 'Horario creado exitosamente');
    }

    /**
     * Show schedule details
     */
    public function show(int $id): string
    {
        $horario = $this->horarioModel->getWithGrupos($id);

        if (!$horario) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Horario no encontrado');
        }

        $data = [
            'title' => 'Detalle de Horario',
            'horario' => $horario,
            'dias' => HorarioModel::$dias,
        ];

        return view('admin/schedules/show', $data);
    }

    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $horario = $this->horarioModel->find($id);

        if (!$horario) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Horario no encontrado');
        }

        $horario['dia_nombre'] = HorarioModel::$dias[$horario['dia_semana']] ?? '';

        $data = [
            'title' => 'Editar Horario',
            'action' => 'update',
            'horario' => $horario,
            'dias' => HorarioModel::$dias,
        ];

        return view('admin/schedules/form', $data);
    }

    /**
     * Update schedule
     */
    public function update(int $id): ResponseInterface
    {
        $horario = $this->horarioModel->find($id);

        if (!$horario) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Horario no encontrado');
        }

        $rules = [
            'nombre' => 'required|min_length[3]|max_length[100]',
            'dia_semana' => 'required|integer|greater_than[0]|less_than[8]',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $horaInicio = $this->request->getPost('hora_inicio');
        $horaFin = $this->request->getPost('hora_fin');

        if ($horaInicio >= $horaFin) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'La hora de fin debe ser posterior a la hora de inicio');
        }

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'dia_semana' => $this->request->getPost('dia_semana'),
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'lugar' => $this->request->getPost('lugar'),
        ];

        $this->horarioModel->update($id, $data);

        return redirect()->to('admin/schedules')
            ->with('success', 'Horario actualizado exitosamente');
    }

    /**
     * Delete schedule
     */
    public function delete(int $id): ResponseInterface
    {
        $horario = $this->horarioModel->find($id);

        if (!$horario) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Horario no encontrado');
        }

        // Check if assigned to groups
        $db = \Config\Database::connect();
        $gruposCount = $db->table('grupo_horarios')
            ->where('horario_id', $id)
            ->where('(vigente_hasta IS NULL OR vigente_hasta >= CURDATE())')
            ->countAllResults();

        if ($gruposCount > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar el horario porque está asignado a ' . $gruposCount . ' grupos activos');
        }

        $this->horarioModel->delete($id);

        return redirect()->to('admin/schedules')
            ->with('success', 'Horario eliminado exitosamente');
    }
}
