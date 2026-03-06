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
                              ->where('estado', 'activo')
                              ->get()
                              ->getResultArray();

        // Pending payments for this acudiente
        $pagos_pendientes = [];
        $saldo_total = 0;

        if ($acudienteId) {
            $pagos_pendientes = $db->table('pagos')
                                   ->where('acudiente_id', $acudienteId)
                                   ->where('estado', 'pendiente_revision')
                                   ->get()
                                   ->getResultArray();

            foreach ($pagos_pendientes as $pago) {
                $saldo_total += (float) $pago['valor_total'];
            }
        }

        // Recent payments for this acudiente
        $pagos_recientes = [];

        if ($acudienteId) {
            $pagos_recientes = $db->table('pagos')
                                  ->where('acudiente_id', $acudienteId)
                                  ->orderBy('created_at', 'DESC')
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
