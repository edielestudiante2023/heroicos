<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * Profesor Dashboard
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        // Get professor ID from profesores table
        $profesor = $db->table('profesores')
                       ->where('usuario_id', $userId)
                       ->get()
                       ->getRowArray();

        $profesorId = $profesor['id'] ?? 0;

        // Get professor's groups
        $mis_grupos = $db->table('grupos')
                         ->where('profesor_id', $profesorId)
                         ->where('activo', 1)
                         ->get()
                         ->getResultArray();

        // Get today's schedule
        $hoy = strtolower(date('l'));
        $dias = [
            'monday'    => 'lunes',
            'tuesday'   => 'martes',
            'wednesday' => 'miercoles',
            'thursday'  => 'jueves',
            'friday'    => 'viernes',
            'saturday'  => 'sabado',
            'sunday'    => 'domingo',
        ];
        $diaHoy = $dias[$hoy] ?? 'lunes';

        $horarios_hoy = $db->table('horarios')
                           ->select('horarios.*, grupos.nombre as grupo_nombre')
                           ->join('grupos', 'grupos.id = horarios.grupo_id')
                           ->where('grupos.profesor_id', $profesorId)
                           ->where('horarios.dia', $diaHoy)
                           ->orderBy('horarios.hora_inicio', 'ASC')
                           ->get()
                           ->getResultArray();

        // Count total students in my groups
        $total_estudiantes = 0;
        foreach ($mis_grupos as $grupo) {
            $count = $db->table('inscripciones')
                        ->where('grupo_id', $grupo['id'])
                        ->where('estado', 'completada')
                        ->countAllResults();
            $total_estudiantes += $count;
        }

        return view('profesor/dashboard', [
            'title'            => 'Dashboard Profesor - Academia Heroicos',
            'pageTitle'        => 'Dashboard',
            'mis_grupos'       => $mis_grupos,
            'horarios_hoy'     => $horarios_hoy,
            'total_estudiantes'=> $total_estudiantes,
            'dia_actual'       => ucfirst($diaHoy),
        ]);
    }
}
