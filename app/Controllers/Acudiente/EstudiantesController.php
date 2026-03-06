<?php

namespace App\Controllers\Acudiente;

use App\Controllers\BaseController;
use App\Models\StudentModel;

class EstudiantesController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $acudiente = $db->table('acudientes')
                        ->where('usuario_id', $userId)
                        ->get()->getRowArray();

        $acudienteId = $acudiente['id'] ?? 0;

        $estudiantes = $db->table('estudiantes')
                          ->where('acudiente_id', $acudienteId)
                          ->orderBy('nombres', 'ASC')
                          ->get()->getResultArray();

        // Get inscription/group info for each student
        foreach ($estudiantes as &$est) {
            $inscripcion = $db->table('inscripciones i')
                ->select('g.nombre as grupo_nombre, c.nombre as categoria_nombre')
                ->join('grupos g', 'g.id = i.grupo_id')
                ->join('categorias c', 'c.id = g.categoria_id')
                ->where('i.estudiante_id', $est['id'])
                ->where('i.estado', 'activa')
                ->get()->getRowArray();

            $est['grupo'] = $inscripcion['grupo_nombre'] ?? null;
            $est['categoria'] = $inscripcion['categoria_nombre'] ?? null;

            // Get pending balance from cargos
            $saldo = $db->table('cargos')
                ->selectSum('saldo_pendiente')
                ->where('estudiante_id', $est['id'])
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->get()->getRowArray();

            $est['saldo_pendiente'] = (float) ($saldo['saldo_pendiente'] ?? 0);
        }

        return view('acudiente/estudiantes/index', [
            'title'       => 'Mis Estudiantes - Academia Heroicos',
            'pageTitle'   => 'Mis Estudiantes',
            'estudiantes' => $estudiantes,
        ]);
    }

    public function detalle(int $id)
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $acudiente = $db->table('acudientes')
                        ->where('usuario_id', $userId)
                        ->get()->getRowArray();

        $acudienteId = $acudiente['id'] ?? 0;

        // Verify student belongs to this acudiente
        $estudiante = $db->table('estudiantes')
                         ->where('id', $id)
                         ->where('acudiente_id', $acudienteId)
                         ->get()->getRowArray();

        if (!$estudiante) {
            return redirect()->to('acudiente/estudiantes')
                ->with('error', 'Estudiante no encontrado.');
        }

        // Get active inscription
        $inscripcion = $db->table('inscripciones i')
            ->select('g.nombre as grupo_nombre, c.nombre as categoria_nombre, i.grupo_id')
            ->join('grupos g', 'g.id = i.grupo_id')
            ->join('categorias c', 'c.id = g.categoria_id')
            ->where('i.estudiante_id', $id)
            ->where('i.estado', 'activa')
            ->get()->getRowArray();

        $estudiante['grupo'] = $inscripcion['grupo_nombre'] ?? null;
        $estudiante['categoria'] = $inscripcion['categoria_nombre'] ?? null;

        // Get cargos
        $cargos = $db->table('cargos ca')
            ->select('ca.*, cc.nombre as concepto_nombre')
            ->join('conceptos_cobro cc', 'cc.id = ca.concepto_id')
            ->where('ca.estudiante_id', $id)
            ->orderBy('ca.created_at', 'DESC')
            ->get()->getResultArray();

        // Get horarios through inscription
        $horarios = [];
        if ($inscripcion) {
            $horarios = $db->table('grupo_horarios gh')
                ->select('h.*')
                ->join('horarios h', 'h.id = gh.horario_id')
                ->where('gh.grupo_id', $inscripcion['grupo_id'])
                ->groupStart()
                    ->where('gh.vigente_hasta IS NULL', null, false)
                    ->orWhere('gh.vigente_hasta >=', date('Y-m-d'))
                ->groupEnd()
                ->orderBy('h.dia_semana', 'ASC')
                ->get()->getResultArray();
        }

        return view('acudiente/estudiantes/detalle', [
            'title'      => $estudiante['nombres'] . ' - Academia Heroicos',
            'pageTitle'  => $estudiante['nombres'] . ' ' . $estudiante['apellidos'],
            'estudiante' => $estudiante,
            'cargos'     => $cargos,
            'horarios'   => $horarios,
        ]);
    }

    public function crear()
    {
        return view('acudiente/estudiantes/crear', [
            'title'     => 'Nuevo Estudiante - Academia Heroicos',
            'pageTitle' => 'Registrar Estudiante',
        ]);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $acudiente = $db->table('acudientes')
                        ->where('usuario_id', $userId)
                        ->get()->getRowArray();

        if (!$acudiente) {
            return redirect()->to('acudiente/estudiantes')
                ->with('error', 'Perfil de acudiente no encontrado.');
        }

        $rules = [
            'nombres'          => 'required|min_length[2]|max_length[100]',
            'apellidos'        => 'required|min_length[2]|max_length[100]',
            'fecha_nacimiento' => 'required|valid_date',
            'sexo'             => 'required|in_list[M,F]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', 'Por favor complete los campos obligatorios correctamente.');
        }

        $studentModel = new StudentModel();
        $codigo = $studentModel->generateCode();

        $db->table('estudiantes')->insert([
            'acudiente_id'       => $acudiente['id'],
            'codigo'             => $codigo,
            'nombres'            => $this->request->getPost('nombres'),
            'apellidos'          => $this->request->getPost('apellidos'),
            'tipo_documento'     => $this->request->getPost('tipo_documento') ?: 'TI',
            'numero_documento'   => $this->request->getPost('numero_documento') ?: '',
            'fecha_nacimiento'   => $this->request->getPost('fecha_nacimiento'),
            'sexo'               => $this->request->getPost('sexo'),
            'talla_camiseta'     => $this->request->getPost('talla_camiseta') ?: null,
            'talla_pantaloneta'  => $this->request->getPost('talla_pantaloneta') ?: null,
            'talla_medias'       => $this->request->getPost('talla_medias') ?: null,
            'posicion'           => $this->request->getPost('posicion') ?: null,
            'pie_dominante'      => $this->request->getPost('pie_dominante') ?: 'derecho',
            'eps'                => $this->request->getPost('eps') ?: '',
            'grupo_sanguineo'    => $this->request->getPost('grupo_sanguineo') ?: '',
            'alergias'           => $this->request->getPost('alergias'),
            'condiciones_medicas' => $this->request->getPost('condiciones_medicas'),
            'medicamentos'       => $this->request->getPost('medicamentos'),
            'contacto_emergencia' => $this->request->getPost('contacto_emergencia'),
            'telefono_emergencia' => $this->request->getPost('telefono_emergencia'),
            'estado'             => 'activo',
            'fecha_ingreso'      => date('Y-m-d'),
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('acudiente/estudiantes')
            ->with('message', 'Estudiante registrado exitosamente.');
    }
}
