<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * Admin Dashboard
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Get statistics - using try/catch to handle missing tables gracefully
        try {
            $totalEstudiantes = $db->table('estudiantes')->where('estado', 'activo')->countAllResults();
        } catch (\Exception $e) {
            $totalEstudiantes = 0;
        }

        try {
            $totalProfesores = $db->table('usuarios')
                                  ->join('roles', 'roles.id = usuarios.rol_id')
                                  ->where('roles.nombre', 'profesor')
                                  ->where('usuarios.estado', 'activo')
                                  ->countAllResults();
        } catch (\Exception $e) {
            $totalProfesores = 0;
        }

        try {
            $totalGrupos = $db->table('grupos')->where('activo', 1)->countAllResults();
        } catch (\Exception $e) {
            $totalGrupos = 0;
        }

        try {
            $pagosPendientes = $db->table('pagos')->where('estado', 'pendiente')->countAllResults();
        } catch (\Exception $e) {
            $pagosPendientes = 0;
        }

        $stats = [
            'total_estudiantes' => $totalEstudiantes,
            'total_profesores'  => $totalProfesores,
            'total_grupos'      => $totalGrupos,
            'pagos_pendientes'  => $pagosPendientes,
        ];

        // Recent payments pending approval
        try {
            $pagos_recientes = $db->table('pagos')
                                  ->select('pagos.*, estudiantes.nombres as estudiante_nombre, estudiantes.apellidos as estudiante_apellido')
                                  ->join('estudiantes', 'estudiantes.id = pagos.estudiante_id')
                                  ->where('pagos.estado', 'pendiente')
                                  ->orderBy('pagos.created_at', 'DESC')
                                  ->limit(5)
                                  ->get()
                                  ->getResultArray();
        } catch (\Exception $e) {
            $pagos_recientes = [];
        }

        // Recent enrollments
        try {
            $inscripciones_recientes = $db->table('inscripciones')
                                          ->select('inscripciones.*, estudiantes.nombres, estudiantes.apellidos')
                                          ->join('estudiantes', 'estudiantes.id = inscripciones.estudiante_id')
                                          ->orderBy('inscripciones.created_at', 'DESC')
                                          ->limit(5)
                                          ->get()
                                          ->getResultArray();
        } catch (\Exception $e) {
            $inscripciones_recientes = [];
        }

        return view('admin/dashboard', [
            'title'     => 'Dashboard Admin - Academia Heroicos',
            'pageTitle' => 'Dashboard',
            'stats'     => $stats,
            'pagos_recientes' => $pagos_recientes,
            'inscripciones_recientes' => $inscripciones_recientes,
        ]);
    }
}
