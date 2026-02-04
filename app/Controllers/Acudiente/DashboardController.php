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

        // Get parent's students
        $mis_estudiantes = $db->table('students')
                              ->where('acudiente_id', $userId)
                              ->where('activo', 1)
                              ->get()
                              ->getResultArray();

        // Get pending payments
        $pagos_pendientes = [];
        $saldo_total = 0;

        foreach ($mis_estudiantes as $estudiante) {
            $pagos = $db->table('portfolio')
                        ->where('student_id', $estudiante['id'])
                        ->where('saldo >', 0)
                        ->get()
                        ->getResultArray();

            foreach ($pagos as $pago) {
                $pago['estudiante_nombre'] = $estudiante['nombre'] . ' ' . $estudiante['apellido'];
                $pagos_pendientes[] = $pago;
                $saldo_total += $pago['saldo'];
            }
        }

        // Get recent payments
        $pagos_recientes = $db->table('payments')
                              ->select('payments.*, students.nombre, students.apellido')
                              ->join('students', 'students.id = payments.student_id')
                              ->whereIn('payments.student_id', array_column($mis_estudiantes, 'id') ?: [0])
                              ->orderBy('payments.created_at', 'DESC')
                              ->limit(5)
                              ->get()
                              ->getResultArray();

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
