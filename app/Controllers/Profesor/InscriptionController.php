<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\TokenInscripcionModel;
use CodeIgniter\HTTP\ResponseInterface;

class InscriptionController extends BaseController
{
    protected TokenInscripcionModel $tokenModel;

    public function __construct()
    {
        $this->tokenModel = new TokenInscripcionModel();
    }

    /**
     * Show inscription link management page
     */
    public function index(): string
    {
        $profesorId = $this->getProfesorId();
        $tokens = $this->tokenModel->getByProfesor($profesorId);

        $data = [
            'title'      => 'Enlaces de Inscripcion - Academia Heroicos',
            'pageTitle'   => 'Enlaces de Inscripcion',
            'activePage'  => 'inscripcion',
            'tokens'      => $tokens,
        ];

        return view('profesor/inscription/index', $data);
    }

    /**
     * Generate inscription link and send email
     */
    public function generate(): ResponseInterface
    {
        $rules = [
            'nombre' => 'required|min_length[3]|max_length[200]',
            'email'  => 'required|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor complete todos los campos correctamente.');
        }

        $email  = $this->request->getPost('email');
        $nombre = $this->request->getPost('nombre');

        // Verify email is not already registered
        $db = \Config\Database::connect();
        $existingUser = $db->table('usuarios')
            ->where('email', $email)
            ->get()->getRowArray();

        if ($existingUser) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Este email ya esta registrado en el sistema.');
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        $profesorId = $this->getProfesorId();

        $this->tokenModel->insert([
            'token'              => $token,
            'email'              => $email,
            'nombre_acudiente'   => $nombre,
            'profesor_id'        => $profesorId,
            'usado'              => 0,
            'expira_at'          => date('Y-m-d H:i:s', strtotime('+48 hours')),
        ]);

        // Send email with inscription link
        try {
            $sendgrid = new \App\Libraries\SendGridService();
            $sendgrid->enviar(
                ['email' => $email, 'nombre' => $nombre],
                'enlace_inscripcion',
                [
                    'nombre_acudiente' => $nombre,
                    'enlace'           => base_url('registro/' . $token),
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email enlace_inscripcion: ' . $e->getMessage());
        }

        return redirect()->to('profesor/inscripcion')
            ->with('success', 'Enlace de inscripcion enviado exitosamente a ' . $email);
    }

    /**
     * Get professor ID from session
     */
    private function getProfesorId(): int
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $profesor = $db->table('profesores')
            ->where('usuario_id', $userId)
            ->get()->getRowArray();

        return $profesor['id'] ?? 0;
    }
}
