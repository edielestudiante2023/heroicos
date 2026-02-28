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

        // Get professor ID
        $profesor = $db->table('profesores')
                       ->where('usuario_id', $userId)
                       ->get()
                       ->getRowArray();

        $profesorId = $profesor['id'] ?? 0;

        // Get professor's groups via profesor_grupos pivot table
        $mis_grupos = $db->table('profesor_grupos pg')
            ->select('g.*, c.nombre as categoria_nombre, pg.es_titular')
            ->join('grupos g', 'g.id = pg.grupo_id')
            ->join('categorias c', 'c.id = g.categoria_id', 'left')
            ->where('pg.profesor_id', $profesorId)
            ->where('g.estado', 'activo')
            ->orderBy('g.nombre', 'ASC')
            ->get()
            ->getResultArray();

        // Count total students in my groups
        $total_estudiantes = 0;
        foreach ($mis_grupos as &$grupo) {
            $count = $db->table('inscripciones')
                        ->where('grupo_id', $grupo['id'])
                        ->where('estado', 'activa')
                        ->countAllResults();
            $grupo['inscritos'] = $count;
            $total_estudiantes += $count;
        }

        // Get today's schedule via grupo_horarios
        $diaSemana = date('N'); // 1=Monday ... 7=Sunday
        $horarios_hoy = $db->table('grupo_horarios gh')
            ->select('h.*, g.nombre as grupo_nombre, g.id as grupo_id')
            ->join('horarios h', 'h.id = gh.horario_id')
            ->join('grupos g', 'g.id = gh.grupo_id')
            ->join('profesor_grupos pg', 'pg.grupo_id = g.id')
            ->where('pg.profesor_id', $profesorId)
            ->where('h.dia_semana', $diaSemana)
            ->where('g.estado', 'activo')
            ->where('(gh.vigente_hasta IS NULL OR gh.vigente_hasta >= CURDATE())')
            ->orderBy('h.hora_inicio', 'ASC')
            ->get()
            ->getResultArray();

        $dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        return view('profesor/dashboard', [
            'title'             => 'Dashboard Profesor - Academia Heroicos',
            'pageTitle'         => 'Dashboard',
            'activePage'        => 'dashboard',
            'mis_grupos'        => $mis_grupos,
            'horarios_hoy'      => $horarios_hoy,
            'total_estudiantes' => $total_estudiantes,
            'dia_actual'        => $dias[$diaSemana] ?? 'Hoy',
        ]);
    }
}
