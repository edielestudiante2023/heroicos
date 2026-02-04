<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Check if user has required role
     *
     * @param RequestInterface $request
     * @param array|null $arguments Role slugs allowed (e.g., ['admin', 'profesor'])
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Must be logged in first
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')
                           ->with('error', 'Debe iniciar sesión para acceder.');
        }

        // If no roles specified, allow any authenticated user
        if (empty($arguments)) {
            return;
        }

        $userRole = $session->get('rol_slug');

        // Admin always has access
        if ($userRole === 'admin') {
            return;
        }

        // Check if user's role is in the allowed list
        if (!in_array($userRole, $arguments)) {
            return redirect()->to('/dashboard')
                           ->with('error', 'No tiene permiso para acceder a esta sección.');
        }
    }

    /**
     * We don't have anything to do here.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
