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

        // Get statistics
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
            $totalGrupos = $db->table('grupos')->where('estado', 'activo')->countAllResults();
        } catch (\Exception $e) {
            $totalGrupos = 0;
        }

        try {
            $pagosPendientes = $db->table('pagos')->where('estado', 'pendiente_revision')->countAllResults();
        } catch (\Exception $e) {
            $pagosPendientes = 0;
        }

        // Cartera stats
        try {
            $totalPorCobrar = $db->table('cargos')
                ->selectSum('saldo_pendiente')
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->get()->getRowArray();
            $totalPorCobrar = (float)($totalPorCobrar['saldo_pendiente'] ?? 0);
        } catch (\Exception $e) {
            $totalPorCobrar = 0;
        }

        $stats = [
            'total_estudiantes' => $totalEstudiantes,
            'total_profesores'  => $totalProfesores,
            'total_grupos'      => $totalGrupos,
            'pagos_pendientes'  => $pagosPendientes,
            'total_por_cobrar'  => $totalPorCobrar,
        ];

        // Recent payments pending approval
        try {
            $pagos_recientes = $db->table('pagos')
                ->select('pagos.*, a.nombres as acudiente_nombres, a.apellidos as acudiente_apellidos')
                ->join('acudientes a', 'a.id = pagos.acudiente_id')
                ->where('pagos.estado', 'pendiente_revision')
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
            'activePage'=> 'dashboard',
            'stats'     => $stats,
            'pagos_recientes' => $pagos_recientes,
            'inscripciones_recientes' => $inscripciones_recientes,
        ]);
    }
}
