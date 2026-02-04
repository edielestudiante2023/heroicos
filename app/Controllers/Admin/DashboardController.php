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
        $stats = [
            'total_estudiantes' => $db->table('students')->where('activo', 1)->countAllResults(),
            'total_profesores'  => $db->table('users')
                                      ->join('roles', 'roles.id = users.role_id')
                                      ->where('roles.slug', 'profesor')
                                      ->where('users.activo', 1)
                                      ->countAllResults(),
            'total_grupos'      => $db->table('groups')->where('activo', 1)->countAllResults(),
            'pagos_pendientes'  => $db->table('payments')
                                      ->where('estado', 'pendiente')
                                      ->countAllResults(),
        ];

        // Recent payments pending approval
        $pagos_recientes = $db->table('payments')
                              ->select('payments.*, students.nombre as estudiante_nombre, students.apellido as estudiante_apellido')
                              ->join('students', 'students.id = payments.student_id')
                              ->where('payments.estado', 'pendiente')
                              ->orderBy('payments.created_at', 'DESC')
                              ->limit(5)
                              ->get()
                              ->getResultArray();

        // Recent enrollments
        $inscripciones_recientes = $db->table('enrollments')
                                      ->select('enrollments.*, students.nombre, students.apellido')
                                      ->join('students', 'students.id = enrollments.student_id')
                                      ->orderBy('enrollments.created_at', 'DESC')
                                      ->limit(5)
                                      ->get()
                                      ->getResultArray();

        return view('admin/dashboard', [
            'title'     => 'Dashboard Admin - Academia Heroicos',
            'pageTitle' => 'Dashboard',
            'stats'     => $stats,
            'pagos_recientes' => $pagos_recientes,
            'inscripciones_recientes' => $inscripciones_recientes,
        ]);
    }
}
