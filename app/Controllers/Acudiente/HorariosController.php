<?php

namespace App\Controllers\Acudiente;

use App\Controllers\BaseController;

class HorariosController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $acudiente = $db->table('acudientes')
                        ->where('usuario_id', $userId)
                        ->get()->getRowArray();

        $acudienteId = $acudiente['id'] ?? 0;

        $estudiantes = $db->table('estudiantes')
            ->where('acudiente_id', $acudienteId)
            ->where('estado', 'activo')
            ->get()->getResultArray();

        $dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        $horariosEstudiantes = [];
        foreach ($estudiantes as $est) {
            $inscripcion = $db->table('inscripciones')
                ->where('estudiante_id', $est['id'])
                ->where('estado', 'activa')
                ->get()->getRowArray();

            if (!$inscripcion) continue;

            $horarios = $db->table('grupo_horarios gh')
                ->select('h.*, g.nombre as grupo_nombre')
                ->join('horarios h', 'h.id = gh.horario_id')
                ->join('grupos g', 'g.id = gh.grupo_id')
                ->where('gh.grupo_id', $inscripcion['grupo_id'])
                ->where('gh.vigente_hasta IS NULL OR gh.vigente_hasta >=', date('Y-m-d'))
                ->orderBy('h.dia_semana', 'ASC')
                ->orderBy('h.hora_inicio', 'ASC')
                ->get()->getResultArray();

            if (!empty($horarios)) {
                $horariosEstudiantes[] = [
                    'estudiante' => $est,
                    'grupo'      => $horarios[0]['grupo_nombre'] ?? '',
                    'horarios'   => $horarios,
                ];
            }
        }

        return view('acudiente/horarios/index', [
            'title'       => 'Horarios - Academia Heroicos',
            'pageTitle'   => 'Horarios',
            'horariosEstudiantes' => $horariosEstudiantes,
            'dias'        => $dias,
        ]);
    }
}
