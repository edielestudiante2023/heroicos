<?php

namespace App\Controllers;

use App\Models\TokenInscripcionModel;
use App\Models\StudentModel;
use CodeIgniter\HTTP\ResponseInterface;

class RegistroController extends BaseController
{
    protected TokenInscripcionModel $tokenModel;

    public function __construct()
    {
        $this->tokenModel = new TokenInscripcionModel();
    }

    /**
     * Show registration form
     */
    public function index(string $token): string
    {
        $tokenData = $this->tokenModel->findValidToken($token);

        if (!$tokenData) {
            return view('auth/registro_error', [
                'title' => 'Enlace Invalido - Academia Heroicos',
            ]);
        }

        return view('auth/registro', [
            'title' => 'Registro - Academia Heroicos',
            'token' => $tokenData,
        ]);
    }

    /**
     * Process registration
     */
    public function store(string $token): ResponseInterface
    {
        // Double-check token validity
        $tokenData = $this->tokenModel->findValidToken($token);

        if (!$tokenData) {
            return redirect()->to('login')
                ->with('error', 'El enlace de inscripcion es invalido o ha expirado.');
        }

        // Validate acudiente fields
        $rules = [
            'acud_nombres'          => 'required|min_length[2]|max_length[100]',
            'acud_apellidos'        => 'required|min_length[2]|max_length[100]',
            'acud_tipo_documento'   => 'required|in_list[CC,TI,CE,PP]',
            'acud_numero_documento' => 'required|max_length[20]',
            'acud_telefono'         => 'required|max_length[20]',
            'acud_direccion'        => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor complete todos los campos del acudiente correctamente.');
        }

        // Validate at least one student
        $estNombres = $this->request->getPost('est_nombres');
        if (empty($estNombres) || !is_array($estNombres) || empty($estNombres[0])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Debe registrar al menos un estudiante.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Generate temporary password
            $passwordTemporal = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'), 0, 8);

            // Create user (tabla usuarios no tiene columna 'nombre')
            $userId = $db->table('usuarios')->insert([
                'email'      => $tokenData['email'],
                'password'   => password_hash($passwordTemporal, PASSWORD_DEFAULT),
                'rol_id'     => 3, // acudiente
                'estado'     => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $newUserId = $db->insertID();

            // Create acudiente
            $db->table('acudientes')->insert([
                'usuario_id'       => $newUserId,
                'nombres'          => $this->request->getPost('acud_nombres'),
                'apellidos'        => $this->request->getPost('acud_apellidos'),
                'tipo_documento'   => $this->request->getPost('acud_tipo_documento'),
                'numero_documento' => $this->request->getPost('acud_numero_documento'),
                'telefono'         => $this->request->getPost('acud_telefono'),
                'direccion'        => $this->request->getPost('acud_direccion'),
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);
            $acudienteId = $db->insertID();

            // Create students
            $studentModel = new StudentModel();
            $estNombres       = $this->request->getPost('est_nombres') ?? [];
            $estApellidos     = $this->request->getPost('est_apellidos') ?? [];
            $estFechaNac      = $this->request->getPost('est_fecha_nacimiento') ?? [];
            $estSexo          = $this->request->getPost('est_sexo') ?? [];
            $estTipoDoc       = $this->request->getPost('est_tipo_documento') ?? [];
            $estNumDoc        = $this->request->getPost('est_numero_documento') ?? [];
            $estTallaCamiseta = $this->request->getPost('est_talla_camiseta') ?? [];
            $estTallaPant     = $this->request->getPost('est_talla_pantaloneta') ?? [];
            $estTallaMedias   = $this->request->getPost('est_talla_medias') ?? [];
            $estPosicion      = $this->request->getPost('est_posicion') ?? [];
            $estPieDom        = $this->request->getPost('est_pie_dominante') ?? [];
            $estEps           = $this->request->getPost('est_eps') ?? [];
            $estRh            = $this->request->getPost('est_rh') ?? [];

            $estudiantesCreados = [];

            for ($i = 0; $i < count($estNombres); $i++) {
                if (empty($estNombres[$i]) || empty($estApellidos[$i])) continue;

                $codigo = $studentModel->generateCode();

                $db->table('estudiantes')->insert([
                    'acudiente_id'     => $acudienteId,
                    'codigo'           => $codigo,
                    'nombres'          => $estNombres[$i],
                    'apellidos'        => $estApellidos[$i],
                    'fecha_nacimiento' => $estFechaNac[$i] ?? null,
                    'sexo'             => $estSexo[$i] ?? 'M',
                    'tipo_documento'   => $estTipoDoc[$i] ?? 'TI',
                    'numero_documento' => $estNumDoc[$i] ?? '',
                    'eps'              => $estEps[$i] ?? '',
                    'grupo_sanguineo'  => $estRh[$i] ?? '',
                    'estado'           => 'activo',
                    'fecha_ingreso'    => date('Y-m-d'),
                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s'),
                ]);

                $estudiantesCreados[] = [
                    'nombres'  => $estNombres[$i],
                    'apellidos' => $estApellidos[$i],
                ];
            }

            // Mark token as used
            $this->tokenModel->markAsUsed($tokenData['id'], $acudienteId);

            $db->transComplete();

            if (!$db->transStatus()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ocurrio un error al procesar el registro. Intente nuevamente.');
            }

            // Send welcome email to acudiente
            $this->enviarEmailBienvenida(
                $tokenData['email'],
                $this->request->getPost('acud_nombres') . ' ' . $this->request->getPost('acud_apellidos'),
                $passwordTemporal
            );

            // Send new student email to admins + professor
            foreach ($estudiantesCreados as $est) {
                $this->enviarEmailNuevoEstudiante(
                    $est['nombres'] . ' ' . $est['apellidos'],
                    $this->request->getPost('acud_nombres') . ' ' . $this->request->getPost('acud_apellidos'),
                    $this->request->getPost('acud_telefono'),
                    $tokenData['profesor_id']
                );
            }

            return $this->response->setBody(view('auth/registro_exito', [
                'title' => 'Registro Exitoso - Academia Heroicos',
            ]));

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en registro: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrio un error inesperado. Intente nuevamente.');
        }
    }

    /**
     * Send welcome email with credentials
     */
    protected function enviarEmailBienvenida(string $email, string $nombreCompleto, string $password): void
    {
        try {
            $sendgrid = new \App\Libraries\SendGridService();
            $sendgrid->enviar(
                ['email' => $email, 'nombre' => $nombreCompleto],
                'bienvenida',
                [
                    'nombre'            => $nombreCompleto,
                    'email'             => $email,
                    'password_temporal' => $password,
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email bienvenida: ' . $e->getMessage());
        }
    }

    /**
     * Send new student notification to admins and professor
     */
    protected function enviarEmailNuevoEstudiante(string $nombreEstudiante, string $nombreAcudiente, string $telefono, ?int $profesorId): void
    {
        try {
            $db = \Config\Database::connect();

            // Get all active admins
            $admins = $db->table('usuarios')
                ->select('email')
                ->where('rol_id', 1)
                ->where('estado', 'activo')
                ->get()->getResultArray();

            // Get professor email if available
            $destinatarios = [];
            foreach ($admins as $admin) {
                $destinatarios[] = ['email' => $admin['email'], 'nombre' => 'Admin'];
            }

            if ($profesorId) {
                $profesor = $db->table('profesores p')
                    ->select('u.email, p.nombres')
                    ->join('usuarios u', 'u.id = p.usuario_id')
                    ->where('p.id', $profesorId)
                    ->get()->getRowArray();

                if ($profesor) {
                    $destinatarios[] = ['email' => $profesor['email'], 'nombre' => $profesor['nombres']];
                }
            }

            $sendgrid = new \App\Libraries\SendGridService();

            foreach ($destinatarios as $dest) {
                $sendgrid->enviar(
                    $dest,
                    'nuevo_estudiante',
                    [
                        'nombre_estudiante'  => $nombreEstudiante,
                        'nombre_acudiente'   => $nombreAcudiente,
                        'telefono'           => $telefono,
                        'fecha_inscripcion'  => date('d/m/Y'),
                    ]
                );
            }
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email nuevo_estudiante: ' . $e->getMessage());
        }
    }
}
