<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Check if user is authenticated
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('cookie');
        $session = session();

        // Check if user is logged in
        if (!$session->get('isLoggedIn')) {
            // Check for remember me cookie
            if ($this->checkRememberToken()) {
                return; // User was logged in via remember token
            }

            // Store intended URL for redirect after login
            $session->set('redirect_url', current_url());

            return redirect()->to('/login')
                           ->with('error', 'Debe iniciar sesión para acceder a esta página.');
        }
    }

    /**
     * We don't have anything to do here.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }

    /**
     * Check remember token and log in user if valid
     */
    protected function checkRememberToken(): bool
    {
        $token = get_cookie('remember_token');

        if (!$token) {
            return false;
        }

        $hashedToken = hash('sha256', $token);
        $db = \Config\Database::connect();

        $user = $db->table('usuarios')
                   ->select('usuarios.*, roles.nombre as rol_nombre')
                   ->join('roles', 'roles.id = usuarios.rol_id')
                   ->where('usuarios.token_verificacion', $hashedToken)
                   ->where('usuarios.estado', 'activo')
                   ->get()
                   ->getRowArray();

        if (!$user) {
            delete_cookie('remember_token');
            return false;
        }

        // Get profile name from role-specific table
        $nombre = 'Usuario';
        $apellido = '';
        $userModel = new \App\Models\UserModel();
        $profileData = $userModel->getProfileData($user['id'], $user['rol_nombre']);
        if ($profileData) {
            $nombre = $profileData['nombre'] ?? $profileData['nombres'] ?? 'Usuario';
            $apellido = $profileData['apellido'] ?? $profileData['apellidos'] ?? '';
        }

        // Set session data
        $sessionData = [
            'user_id'     => $user['id'],
            'email'       => $user['email'],
            'nombre'      => $nombre,
            'apellido'    => $apellido,
            'rol_id'      => $user['rol_id'],
            'rol_nombre'  => $user['rol_nombre'],
            'isLoggedIn'  => true,
        ];

        // Store role-specific profile ID
        if ($user['rol_nombre'] === 'profesor') {
            $prof = $db->table('profesores')->where('usuario_id', $user['id'])->get()->getRowArray();
            if ($prof) $sessionData['profesor_id'] = $prof['id'];
        } elseif ($user['rol_nombre'] === 'acudiente') {
            $acud = $db->table('acudientes')->where('usuario_id', $user['id'])->get()->getRowArray();
            if ($acud) $sessionData['acudiente_id'] = $acud['id'];
        }

        session()->set($sessionData);

        // Update last login
        $db->table('usuarios')->where('id', $user['id'])->update([
            'ultimo_acceso' => date('Y-m-d H:i:s')
        ]);

        return true;
    }
}
