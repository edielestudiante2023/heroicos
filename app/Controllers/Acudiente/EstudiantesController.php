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

        // Validate consent
        if (!$this->request->getPost('consent_datos_personales')
            || !$this->request->getPost('consent_datos_menor')
            || !$this->request->getPost('consent_imagen_menor')) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe aceptar todas las autorizaciones de tratamiento de datos.');
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

        // Validate photo
        $foto = $this->request->getFile('foto');
        if (!$foto || !$foto->isValid() || $foto->hasMoved()) {
            return redirect()->back()->withInput()
                ->with('error', 'La fotografia del estudiante es obligatoria.');
        }
        if ($foto->getSize() > 2 * 1024 * 1024) {
            return redirect()->back()->withInput()
                ->with('error', 'La foto excede el limite de 2MB.');
        }
        if (!in_array($foto->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
            return redirect()->back()->withInput()
                ->with('error', 'La foto debe ser JPG, PNG o WebP.');
        }

        $studentModel = new StudentModel();
        $codigo = $studentModel->generateCode();

        // Upload photo
        $uploadPath = FCPATH . 'uploads/estudiantes';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        $newName = $codigo . '_' . $foto->getRandomName();
        $foto->move($uploadPath, $newName);
        $fotoPath = 'uploads/estudiantes/' . $newName;

        $ipAddress = $this->request->getIPAddress();

        $db->table('estudiantes')->insert([
            'acudiente_id'            => $acudiente['id'],
            'codigo'                  => $codigo,
            'nombres'                 => $this->request->getPost('nombres'),
            'apellidos'               => $this->request->getPost('apellidos'),
            'tipo_documento'          => $this->request->getPost('tipo_documento') ?: 'TI',
            'numero_documento'        => $this->request->getPost('numero_documento') ?: '',
            'fecha_nacimiento'        => $this->request->getPost('fecha_nacimiento'),
            'sexo'                    => $this->request->getPost('sexo'),
            'foto'                    => $fotoPath,
            'talla_camiseta'          => $this->request->getPost('talla_camiseta') ?: null,
            'talla_pantaloneta'       => $this->request->getPost('talla_pantaloneta') ?: null,
            'talla_medias'            => $this->request->getPost('talla_medias') ?: null,
            'posicion'                => $this->request->getPost('posicion') ?: null,
            'pie_dominante'           => $this->request->getPost('pie_dominante') ?: 'derecho',
            'eps'                     => $this->request->getPost('eps') ?: '',
            'grupo_sanguineo'         => $this->request->getPost('grupo_sanguineo') ?: '',
            'alergias'                => $this->request->getPost('alergias'),
            'condiciones_medicas'     => $this->request->getPost('condiciones_medicas'),
            'medicamentos'            => $this->request->getPost('medicamentos'),
            'contacto_emergencia'     => $this->request->getPost('contacto_emergencia'),
            'telefono_emergencia'     => $this->request->getPost('telefono_emergencia'),
            'autorizacion_datos_menor' => 1,
            'fecha_autorizacion'      => date('Y-m-d H:i:s'),
            'ip_autorizacion'         => $ipAddress,
            'estado'                  => 'activo',
            'fecha_ingreso'           => date('Y-m-d'),
            'created_at'              => date('Y-m-d H:i:s'),
            'updated_at'              => date('Y-m-d H:i:s'),
        ]);

        // Store consent records
        $consentVersion = $this->request->getPost('consent_version') ?? '1.0';
        $nombreFirmante = $acudiente['nombres'] . ' ' . $acudiente['apellidos'];
        $docFirmante = $acudiente['numero_documento'];
        $userAgent = $this->request->getUserAgent()->getAgentString();
        $consentTexto = 'Autorizacion tratamiento datos personales Academia Heroicos v' . $consentVersion;

        foreach (['datos_personales', 'datos_menor', 'imagen_menor'] as $tipo) {
            $db->table('consentimientos')->insert([
                'acudiente_id'      => $acudiente['id'],
                'usuario_id'        => $userId,
                'tipo'              => $tipo,
                'version'           => $consentVersion,
                'texto_aceptado'    => $consentTexto,
                'aceptado'          => 1,
                'nombre_firmante'   => $nombreFirmante,
                'documento_firmante' => $docFirmante,
                'ip_address'        => $ipAddress,
                'user_agent'        => $userAgent,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('acudiente/estudiantes')
            ->with('message', 'Estudiante registrado exitosamente.');
    }
}
