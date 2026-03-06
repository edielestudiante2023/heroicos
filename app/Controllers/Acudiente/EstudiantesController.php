<?php

namespace App\Controllers\Acudiente;

use App\Controllers\BaseController;

class EstudiantesController extends BaseController
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
                          ->orderBy('nombres', 'ASC')
                          ->get()->getResultArray();

        // Get inscription/group info for each student
        foreach ($estudiantes as &$est) {
            $inscripcion = $db->table('inscripciones i')
                ->select('g.nombre as grupo_nombre, c.nombre as categoria_nombre')
                ->join('grupos g', 'g.id = i.grupo_id')
                ->join('categorias c', 'c.id = g.categoria_id')
                ->where('i.estudiante_id', $est['id'])
                ->where('i.estado', 'activa')
                ->get()->getRowArray();

            $est['grupo'] = $inscripcion['grupo_nombre'] ?? null;
            $est['categoria'] = $inscripcion['categoria_nombre'] ?? null;

            // Get pending balance from cargos
            $saldo = $db->table('cargos')
                ->selectSum('saldo_pendiente')
                ->where('estudiante_id', $est['id'])
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->get()->getRowArray();

            $est['saldo_pendiente'] = (float) ($saldo['saldo_pendiente'] ?? 0);
        }

        return view('acudiente/estudiantes/index', [
            'title'       => 'Mis Estudiantes - Academia Heroicos',
            'pageTitle'   => 'Mis Estudiantes',
            'estudiantes' => $estudiantes,
        ]);
    }

    public function detalle(int $id)
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $acudiente = $db->table('acudientes')
                        ->where('usuario_id', $userId)
                        ->get()->getRowArray();

        $acudienteId = $acudiente['id'] ?? 0;

        // Verify student belongs to this acudiente
        $estudiante = $db->table('estudiantes')
                         ->where('id', $id)
                         ->where('acudiente_id', $acudienteId)
                         ->get()->getRowArray();

        if (!$estudiante) {
            return redirect()->to('acudiente/estudiantes')
                ->with('error', 'Estudiante no encontrado.');
        }

        // Get active inscription
        $inscripcion = $db->table('inscripciones i')
            ->select('g.nombre as grupo_nombre, c.nombre as categoria_nombre')
            ->join('grupos g', 'g.id = i.grupo_id')
            ->join('categorias c', 'c.id = g.categoria_id')
            ->where('i.estudiante_id', $id)
            ->where('i.estado', 'activa')
            ->get()->getRowArray();

        $estudiante['grupo'] = $inscripcion['grupo_nombre'] ?? null;
        $estudiante['categoria'] = $inscripcion['categoria_nombre'] ?? null;

        // Get cargos
        $cargos = $db->table('cargos ca')
            ->select('ca.*, cc.nombre as concepto_nombre')
            ->join('conceptos_cobro cc', 'cc.id = ca.concepto_id')
            ->where('ca.estudiante_id', $id)
            ->orderBy('ca.created_at', 'DESC')
            ->get()->getResultArray();

        // Get horarios through inscription
        $horarios = [];
        if ($inscripcion) {
            $grupoId = $db->table('inscripciones')
                ->select('grupo_id')
                ->where('estudiante_id', $id)
                ->where('estado', 'activa')
                ->get()->getRowArray();

            if ($grupoId) {
                $horarios = $db->table('grupo_horarios gh')
                    ->select('h.*')
                    ->join('horarios h', 'h.id = gh.horario_id')
                    ->where('gh.grupo_id', $grupoId['grupo_id'])
                    ->where('gh.vigente_hasta IS NULL OR gh.vigente_hasta >=', date('Y-m-d'))
                    ->orderBy('h.dia_semana', 'ASC')
                    ->get()->getResultArray();
            }
        }

        return view('acudiente/estudiantes/detalle', [
            'title'      => $estudiante['nombres'] . ' - Academia Heroicos',
            'pageTitle'  => $estudiante['nombres'] . ' ' . $estudiante['apellidos'],
            'estudiante' => $estudiante,
            'cargos'     => $cargos,
            'horarios'   => $horarios,
        ]);
    }
}
