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

        $user = $db->table('users')
                   ->select('users.*, roles.nombre as rol_nombre, roles.slug as rol_slug')
                   ->join('roles', 'roles.id = users.role_id')
                   ->where('users.remember_token', $hashedToken)
                   ->where('users.activo', 1)
                   ->where('users.deleted_at', null)
                   ->get()
                   ->getRowArray();

        if (!$user) {
            delete_cookie('remember_token');
            return false;
        }

        // Set session data
        $sessionData = [
            'user_id'     => $user['id'],
            'email'       => $user['email'],
            'nombre'      => $user['nombre'],
            'apellido'    => $user['apellido'],
            'role_id'     => $user['role_id'],
            'rol_nombre'  => $user['rol_nombre'],
            'rol_slug'    => $user['rol_slug'],
            'isLoggedIn'  => true,
        ];
        session()->set($sessionData);

        // Update last login
        $db->table('users')->where('id', $user['id'])->update([
            'last_login' => date('Y-m-d H:i:s')
        ]);

        return true;
    }
}
