<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StudentModel;
use App\Models\EstudianteHistorialDeportivoModel;

class StudentController extends BaseController
{
    protected StudentModel $studentModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
    }

    /**
     * List all students with filters
     */
    public function index()
    {
        // Get filter parameters
        $filters = [
            'search'    => $this->request->getGet('search'),
            'estado'    => $this->request->getGet('estado'),
            'categoria' => $this->request->getGet('categoria'),
        ];

        $estudiantes = $this->studentModel->getFiltered($filters);

        // Calculate age for each student
        foreach ($estudiantes as &$est) {
            $est['edad'] = $this->studentModel->getAge($est['fecha_nacimiento']);
            $est['categoria'] = date('Y', strtotime($est['fecha_nacimiento']));
        }

        // Get available categories for filter
        $categorias = $this->studentModel->getAvailableCategories();

        return view('admin/students/index', [
            'title'       => 'Gestión de Estudiantes - Academia Heroicos',
            'pageTitle'   => 'Estudiantes',
            'estudiantes' => $estudiantes,
            'categorias'  => $categorias,
            'filters'     => $filters,
            'stats'       => $this->studentModel->getStats(),
        ]);
    }

    /**
     * Show create student form
     */
    public function new()
    {
        // Get acudientes for dropdown
        $db = \Config\Database::connect();
        $acudientes = $db->table('acudientes')
                         ->select('acudientes.id, acudientes.nombres, acudientes.apellidos, usuarios.email')
                         ->join('usuarios', 'usuarios.id = acudientes.usuario_id')
                         ->orderBy('acudientes.apellidos', 'ASC')
                         ->get()
                         ->getResultArray();

        return view('admin/students/form', [
            'title'      => 'Nuevo Estudiante - Academia Heroicos',
            'pageTitle'  => 'Nuevo Estudiante',
            'estudiante' => null,
            'acudientes' => $acudientes,
            'action'     => 'create',
        ]);
    }

    /**
     * Create new student
     */
    public function create()
    {
        // Validate input
        $rules = [
            'nombres'          => 'required|min_length[2]|max_length[100]',
            'apellidos'        => 'required|min_length[2]|max_length[100]',
            'fecha_nacimiento' => 'required|valid_date',
            'tipo_documento'   => 'required|in_list[TI,RC,pasaporte,otro]',
            'numero_documento' => 'required|max_length[20]',
            'sexo'             => 'required|in_list[M,F]',
            'acudiente_id'     => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Verificar documento duplicado
        $db = \Config\Database::connect();
        $existe = $db->table('estudiantes')
                     ->where('tipo_documento', $this->request->getPost('tipo_documento'))
                     ->where('numero_documento', $this->request->getPost('numero_documento'))
                     ->countAllResults();
        if ($existe > 0) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Ya existe un estudiante registrado con ese tipo y número de documento.');
        }

        try {
            $data = [
                'codigo'              => $this->studentModel->generateCode(),
                'acudiente_id'        => $this->request->getPost('acudiente_id') ?: null,
                'nombres'             => $this->request->getPost('nombres'),
                'apellidos'           => $this->request->getPost('apellidos'),
                'tipo_documento'      => $this->request->getPost('tipo_documento'),
                'numero_documento'    => $this->request->getPost('numero_documento'),
                'fecha_nacimiento'    => $this->request->getPost('fecha_nacimiento'),
                'sexo'                => $this->request->getPost('sexo'),
                'direccion'           => $this->request->getPost('direccion'),
                'telefono'            => $this->request->getPost('telefono'),
                'talla_camiseta'      => $this->request->getPost('talla_camiseta') ?: null,
                'talla_pantaloneta'   => $this->request->getPost('talla_pantaloneta') ?: null,
                'talla_medias'        => $this->request->getPost('talla_medias') ?: null,
                'posicion'            => $this->request->getPost('posicion') ?: null,
                'pie_dominante'       => $this->request->getPost('pie_dominante') ?: 'derecho',
                'eps'                 => $this->request->getPost('eps'),
                'grupo_sanguineo'     => $this->request->getPost('grupo_sanguineo'),
                'alergias'            => $this->request->getPost('alergias'),
                'condiciones_medicas' => $this->request->getPost('condiciones_medicas'),
                'medicamentos'        => $this->request->getPost('medicamentos'),
                'contacto_emergencia' => $this->request->getPost('contacto_emergencia'),
                'telefono_emergencia' => $this->request->getPost('telefono_emergencia'),
                'estado'              => 'activo',
                'fecha_ingreso'       => date('Y-m-d'),
                'created_at'          => date('Y-m-d H:i:s'),
            ];

            // Handle photo upload
            $foto = $this->request->getFile('foto');
            if ($foto && $foto->isValid() && !$foto->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/estudiantes';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $newName = $data['codigo'] . '_' . $foto->getRandomName();
                $foto->move($uploadPath, $newName);
                $data['foto'] = 'uploads/estudiantes/' . $newName;
            }

            $this->studentModel->insert($data);
            $studentId = $this->studentModel->getInsertID();

            // Log action
            $this->logAction('CREATE', 'estudiantes', $studentId);

            return redirect()->to('/admin/students')
                           ->with('message', 'Estudiante creado exitosamente. Código: ' . $data['codigo']);

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al crear estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Show student details
     */
    public function show($id)
    {
        $estudiante = $this->studentModel->getWithAcudiente($id);

        if (!$estudiante) {
            return redirect()->to('/admin/students')
                           ->with('error', 'Estudiante no encontrado.');
        }

        $estudiante['edad'] = $this->studentModel->getAge($estudiante['fecha_nacimiento']);

        // Get student's groups
        $db = \Config\Database::connect();
        $grupos = $db->table('inscripciones')
                     ->select('grupos.*, inscripciones.estado as inscripcion_estado')
                     ->join('grupos', 'grupos.id = inscripciones.grupo_id')
                     ->where('inscripciones.estudiante_id', $id)
                     ->get()
                     ->getResultArray();

        // Get student's sports history
        $historialModel = new EstudianteHistorialDeportivoModel();
        $historial = $historialModel->getByEstudiante($id);

        return view('admin/students/show', [
            'title'      => 'Detalle Estudiante - Academia Heroicos',
            'pageTitle'  => 'Detalle de Estudiante',
            'estudiante' => $estudiante,
            'grupos'     => $grupos,
            'historial'  => $historial,
        ]);
    }

    /**
     * Show edit student form
     */
    public function edit($id)
    {
        $estudiante = $this->studentModel->find($id);

        if (!$estudiante) {
            return redirect()->to('/admin/students')
                           ->with('error', 'Estudiante no encontrado.');
        }

        // Get acudientes for dropdown
        $db = \Config\Database::connect();
        $acudientes = $db->table('acudientes')
                         ->select('acudientes.id, acudientes.nombres, acudientes.apellidos, usuarios.email')
                         ->join('usuarios', 'usuarios.id = acudientes.usuario_id')
                         ->orderBy('acudientes.apellidos', 'ASC')
                         ->get()
                         ->getResultArray();

        return view('admin/students/form', [
            'title'      => 'Editar Estudiante - Academia Heroicos',
            'pageTitle'  => 'Editar Estudiante',
            'estudiante' => $estudiante,
            'acudientes' => $acudientes,
            'action'     => 'update',
        ]);
    }

    /**
     * Update student
     */
    public function update($id)
    {
        $estudiante = $this->studentModel->find($id);

        if (!$estudiante) {
            return redirect()->to('/admin/students')
                           ->with('error', 'Estudiante no encontrado.');
        }

        // Validate input
        $rules = [
            'nombres'          => 'required|min_length[2]|max_length[100]',
            'apellidos'        => 'required|min_length[2]|max_length[100]',
            'fecha_nacimiento' => 'required|valid_date',
            'tipo_documento'   => 'required|in_list[TI,RC,pasaporte,otro]',
            'numero_documento' => 'required|max_length[20]',
            'sexo'             => 'required|in_list[M,F]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'acudiente_id'        => $this->request->getPost('acudiente_id') ?: null,
                'nombres'             => $this->request->getPost('nombres'),
                'apellidos'           => $this->request->getPost('apellidos'),
                'tipo_documento'      => $this->request->getPost('tipo_documento'),
                'numero_documento'    => $this->request->getPost('numero_documento'),
                'fecha_nacimiento'    => $this->request->getPost('fecha_nacimiento'),
                'sexo'                => $this->request->getPost('sexo'),
                'direccion'           => $this->request->getPost('direccion'),
                'telefono'            => $this->request->getPost('telefono'),
                'talla_camiseta'      => $this->request->getPost('talla_camiseta') ?: null,
                'talla_pantaloneta'   => $this->request->getPost('talla_pantaloneta') ?: null,
                'talla_medias'        => $this->request->getPost('talla_medias') ?: null,
                'posicion'            => $this->request->getPost('posicion') ?: null,
                'pie_dominante'       => $this->request->getPost('pie_dominante') ?: 'derecho',
                'eps'                 => $this->request->getPost('eps'),
                'grupo_sanguineo'     => $this->request->getPost('grupo_sanguineo'),
                'alergias'            => $this->request->getPost('alergias'),
                'condiciones_medicas' => $this->request->getPost('condiciones_medicas'),
                'medicamentos'        => $this->request->getPost('medicamentos'),
                'contacto_emergencia' => $this->request->getPost('contacto_emergencia'),
                'telefono_emergencia' => $this->request->getPost('telefono_emergencia'),
                'estado'              => $this->request->getPost('estado'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ];

            // Handle photo upload
            $foto = $this->request->getFile('foto');
            if ($foto && $foto->isValid() && !$foto->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/estudiantes';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $newName = $estudiante['codigo'] . '_' . $foto->getRandomName();
                $foto->move($uploadPath, $newName);
                $data['foto'] = 'uploads/estudiantes/' . $newName;
            }

            // Handle retirement
            if ($data['estado'] === 'retirado' && $estudiante['estado'] !== 'retirado') {
                $data['fecha_retiro'] = date('Y-m-d');
                $data['motivo_retiro'] = $this->request->getPost('motivo_retiro');
            }

            $this->studentModel->update($id, $data);

            // Log action
            $this->logAction('UPDATE', 'estudiantes', $id);

            return redirect()->to('/admin/students')
                           ->with('message', 'Estudiante actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al actualizar estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Delete student
     */
    public function delete($id)
    {
        $estudiante = $this->studentModel->find($id);

        if (!$estudiante) {
            return redirect()->to('/admin/students')
                           ->with('error', 'Estudiante no encontrado.');
        }

        try {
            // Soft delete - just mark as retired
            $this->studentModel->update($id, [
                'estado'        => 'retirado',
                'fecha_retiro'  => date('Y-m-d'),
                'motivo_retiro' => 'Eliminado por administrador',
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            // Log action
            $this->logAction('DELETE', 'estudiantes', $id);

            return redirect()->to('/admin/students')
                           ->with('message', 'Estudiante retirado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->to('/admin/students')
                           ->with('error', 'Error al retirar estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Hard delete student (superadmin only)
     */
    public function hardDelete($id)
    {
        if (session()->get('rol_nombre') !== 'admin') {
            return redirect()->to('/admin/students')
                           ->with('error', 'No tiene permisos para eliminar estudiantes permanentemente.');
        }

        $estudiante = $this->studentModel->find($id);

        if (!$estudiante) {
            return redirect()->to('/admin/students')
                           ->with('error', 'Estudiante no encontrado.');
        }

        $db = \Config\Database::connect();

        // Check related records
        $relaciones = [
            'inscripciones'              => 'Inscripciones a grupos',
            'cargos'                     => 'Cargos de pago',
            'asistencias'                => 'Registros de asistencia',
            'torneo_inscripciones'       => 'Inscripciones a torneos',
            'paz_y_salvos'               => 'Paz y salvos',
            'solicitudes_clase_particular' => 'Clases particulares',
            'estudiante_historial_deportivo' => 'Historial deportivo',
        ];

        $conDatos = [];
        foreach ($relaciones as $tabla => $etiqueta) {
            $count = $db->table($tabla)->where('estudiante_id', $id)->countAllResults();
            if ($count > 0) {
                $conDatos[] = "$etiqueta ($count)";
            }
        }

        if (!empty($conDatos)) {
            return redirect()->to('/admin/students')
                           ->with('error', 'No se puede eliminar al estudiante porque tiene registros asociados: ' . implode(', ', $conDatos) . '.');
        }

        try {
            $this->logAction('HARD_DELETE', 'estudiantes', $id);
            $this->studentModel->delete($id, true);

            return redirect()->to('/admin/students')
                           ->with('message', 'Estudiante eliminado permanentemente.');

        } catch (\Exception $e) {
            return redirect()->to('/admin/students')
                           ->with('error', 'Error al eliminar estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Store a sports history entry for a student
     */
    public function guardarHistorial(int $estudianteId)
    {
        $estudiante = $this->studentModel->find($estudianteId);
        if (!$estudiante) {
            return redirect()->back()->with('error', 'Estudiante no encontrado.');
        }

        $historialModel = new EstudianteHistorialDeportivoModel();

        $historialModel->insert([
            'estudiante_id'        => $estudianteId,
            'academia_anterior'    => $this->request->getPost('academia_anterior'),
            'periodo'              => $this->request->getPost('periodo'),
            'torneos_participados' => $this->request->getPost('torneos_participados'),
            'logros'               => $this->request->getPost('logros'),
            'posicion_juego'       => $this->request->getPost('posicion_juego'),
            'observaciones'        => $this->request->getPost('observaciones'),
        ]);

        $this->logAction('CREATE_HISTORIAL', 'estudiante_historial_deportivo', $estudianteId);

        return redirect()->to("admin/students/{$estudianteId}")
            ->with('message', 'Historial deportivo agregado exitosamente.');
    }

    /**
     * Delete a sports history entry
     */
    public function eliminarHistorial(int $estudianteId, int $historialId)
    {
        $historialModel = new EstudianteHistorialDeportivoModel();
        $registro = $historialModel->where('id', $historialId)
            ->where('estudiante_id', $estudianteId)
            ->first();

        if (!$registro) {
            return redirect()->back()->with('error', 'Registro no encontrado.');
        }

        $historialModel->delete($historialId);
        $this->logAction('DELETE_HISTORIAL', 'estudiante_historial_deportivo', $historialId);

        return redirect()->to("admin/students/{$estudianteId}")
            ->with('message', 'Registro de historial eliminado.');
    }

    /**
     * Log admin action
     */
    protected function logAction(string $action, string $tabla, int $registroId): void
    {
        try {
            $db = \Config\Database::connect();
            $db->table('auditoria')->insert([
                'usuario_id'  => session()->get('user_id'),
                'accion'      => $action,
                'tabla'       => $tabla,
                'registro_id' => $registroId,
                'ip'          => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent()->getAgentString(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error logging action: ' . $e->getMessage());
        }
    }
}
