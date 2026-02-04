<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    /**
     * Redirect to role-specific dashboard
     */
    public function index()
    {
        $role = session()->get('rol_slug');

        switch ($role) {
            case 'admin':
                return redirect()->to('/admin/dashboard');
            case 'profesor':
                return redirect()->to('/profesor/dashboard');
            case 'acudiente':
                return redirect()->to('/acudiente/dashboard');
            default:
                return redirect()->to('/login')
                               ->with('error', 'Rol no reconocido.');
        }
    }
}
