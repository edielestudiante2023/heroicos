<?php

namespace App\Controllers\Acudiente;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * Acudiente (Parent) Dashboard
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        // Get acudiente ID from acudientes table
        $acudiente = $db->table('acudientes')
                        ->where('usuario_id', $userId)
                        ->get()
                        ->getRowArray();

        $acudienteId = $acudiente['id'] ?? 0;

        // Get parent's students
        $mis_estudiantes = $db->table('estudiantes')
                              ->where('acudiente_id', $acudienteId)
                              ->where('activo', 1)
                              ->get()
                              ->getResultArray();

        // Get pending payments from cartera
        $pagos_pendientes = [];
        $saldo_total = 0;

        foreach ($mis_estudiantes as $estudiante) {
            $deudas = $db->table('cartera')
                         ->where('estudiante_id', $estudiante['id'])
                         ->where('saldo >', 0)
                         ->get()
                         ->getResultArray();

            foreach ($deudas as $deuda) {
                $deuda['estudiante_nombre'] = $estudiante['nombres'] . ' ' . $estudiante['apellidos'];
                $pagos_pendientes[] = $deuda;
                $saldo_total += $deuda['saldo'];
            }
        }

        // Get recent payments
        $estudianteIds = array_column($mis_estudiantes, 'id');
        $pagos_recientes = [];

        if (!empty($estudianteIds)) {
            $pagos_recientes = $db->table('pagos')
                                  ->select('pagos.*, estudiantes.nombres as nombre, estudiantes.apellidos as apellido')
                                  ->join('estudiantes', 'estudiantes.id = pagos.estudiante_id')
                                  ->whereIn('pagos.estudiante_id', $estudianteIds)
                                  ->orderBy('pagos.created_at', 'DESC')
                                  ->limit(5)
                                  ->get()
                                  ->getResultArray();
        }

        return view('acudiente/dashboard', [
            'title'            => 'Dashboard Acudiente - Academia Heroicos',
            'pageTitle'        => 'Dashboard',
            'mis_estudiantes'  => $mis_estudiantes,
            'pagos_pendientes' => $pagos_pendientes,
            'pagos_recientes'  => $pagos_recientes,
            'saldo_total'      => $saldo_total,
        ]);
    }
}
