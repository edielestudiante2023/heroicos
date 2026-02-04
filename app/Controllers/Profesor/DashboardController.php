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

        // Get professor's groups
        $mis_grupos = $db->table('groups')
                         ->where('profesor_id', $userId)
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

        $horarios_hoy = $db->table('schedules')
                           ->select('schedules.*, groups.nombre as grupo_nombre')
                           ->join('groups', 'groups.id = schedules.group_id')
                           ->where('groups.profesor_id', $userId)
                           ->where('schedules.dia', $diaHoy)
                           ->orderBy('schedules.hora_inicio', 'ASC')
                           ->get()
                           ->getResultArray();

        // Count total students in my groups
        $total_estudiantes = 0;
        foreach ($mis_grupos as $grupo) {
            $count = $db->table('student_groups')
                        ->where('group_id', $grupo['id'])
                        ->where('activo', 1)
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
