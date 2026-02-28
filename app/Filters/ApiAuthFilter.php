<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API Authentication Filter
 *
 * Returns JSON 401 instead of redirecting to /login.
 * Used for API endpoints consumed by the PWA offline sync.
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'valid'   => false,
                    'error'   => 'session_expired',
                    'message' => 'Debe iniciar sesion para realizar esta accion.',
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing
    }
}
