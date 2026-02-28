<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SesionClaseModel;
use App\Models\AsistenciaModel;
use App\Models\GrupoModel;
use App\Models\InscripcionModel;
use App\Models\CargoModel;
use App\Models\PagoModel;
use App\Models\PagoDetalleModel;
use App\Models\ComprobanteModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Offline API Controller
 *
 * JSON endpoints for PWA offline sync.
 * All endpoints require authentication via ApiAuthFilter.
 */
class OfflineController extends BaseController
{
    // =========================================================================
    // SESSION CHECK
    // =========================================================================

    /**
     * GET /api/offline/session-check
     * Validate that the user session is still active.
     */
    public function sessionCheck(): ResponseInterface
    {
        $session = session();
        $userId = $session->get('user_id');
        $rolNombre = $session->get('rol_nombre');

        $data = [
            'valid'      => true,
            'user_id'    => (int) $userId,
            'rol_nombre' => $rolNombre,
        ];

        // Add role-specific ID
        $db = \Config\Database::connect();

        if ($rolNombre === 'profesor') {
            $prof = $db->table('profesores')->where('usuario_id', $userId)->get()->getRowArray();
            $data['profesor_id'] = $prof ? (int) $prof['id'] : null;
        } elseif ($rolNombre === 'acudiente') {
            $acud = $db->table('acudientes')->where('usuario_id', $userId)->get()->getRowArray();
            $data['acudiente_id'] = $acud ? (int) $acud['id'] : null;
        }

        return $this->response->setJSON($data);
    }

    // =========================================================================
    // PROFESOR: DATA PREFETCH
    // =========================================================================

    /**
     * GET /api/offline/profesor/grupos
     * Returns groups assigned to the current professor.
     */
    public function profesorGrupos(): ResponseInterface
    {
        $profesorId = $this->getProfesorId();
        if (!$profesorId) {
            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'No es profesor o no tiene perfil asignado.'
            ]);
        }

        $db = \Config\Database::connect();

        // Get groups where this professor is assigned
        $grupos = $db->table('profesor_grupos pg')
            ->select('g.id, g.nombre, g.cupo_maximo, c.nombre as categoria_nombre')
            ->join('grupos g', 'g.id = pg.grupo_id')
            ->join('categorias c', 'c.id = g.categoria_id', 'left')
            ->where('pg.profesor_id', $profesorId)
            ->where('g.estado', 'activo')
            ->get()
            ->getResultArray();

        // Add student count and horarios for each group
        $inscripcionModel = new InscripcionModel();
        foreach ($grupos as &$grupo) {
            $grupo['id'] = (int) $grupo['id'];
            $grupo['inscritos'] = count($inscripcionModel->getByGrupo($grupo['id']));

            // Get horarios
            $horarios = $db->table('grupo_horarios gh')
                ->select('h.dia_semana, h.hora_inicio, h.hora_fin, h.lugar, h.nombre as horario_nombre')
                ->join('horarios h', 'h.id = gh.horario_id')
                ->where('gh.grupo_id', $grupo['id'])
                ->get()
                ->getResultArray();

            $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo'];
            foreach ($horarios as &$h) {
                $h['dia_nombre'] = $dias[(int) $h['dia_semana']] ?? '';
            }
            $grupo['horarios'] = $horarios;
        }

        return $this->response->setJSON([
            'grupos'      => $grupos,
            'profesor_id' => $profesorId,
            'cached_at'   => date('c'),
        ]);
    }

    /**
     * GET /api/offline/profesor/grupo/{id}/estudiantes
     * Returns student list for a group.
     */
    public function grupoEstudiantes(int $grupoId): ResponseInterface
    {
        $profesorId = $this->getProfesorId();
        $rolNombre = session()->get('rol_nombre');

        // Verify access: must be assigned professor or admin
        if ($rolNombre !== 'admin') {
            $db = \Config\Database::connect();
            $assigned = $db->table('profesor_grupos')
                ->where('profesor_id', $profesorId)
                ->where('grupo_id', $grupoId)
                ->countAllResults();

            if ($assigned === 0) {
                return $this->response->setStatusCode(403)->setJSON([
                    'error' => 'No tiene acceso a este grupo.'
                ]);
            }
        }

        $grupoModel = new GrupoModel();
        $grupo = $grupoModel->find($grupoId);

        if (!$grupo) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Grupo no encontrado.'
            ]);
        }

        $inscripcionModel = new InscripcionModel();
        $inscritos = $inscripcionModel->getByGrupo($grupoId);

        $estudiantes = [];
        foreach ($inscritos as $est) {
            $estudiantes[] = [
                'estudiante_id' => (int) $est['estudiante_id'],
                'codigo'        => $est['codigo'] ?? '',
                'nombres'       => $est['nombres'],
                'apellidos'     => $est['apellidos'],
            ];
        }

        return $this->response->setJSON([
            'grupo_id'     => (int) $grupoId,
            'grupo_nombre' => $grupo['nombre'],
            'estudiantes'  => $estudiantes,
            'cached_at'    => date('c'),
        ]);
    }

    // =========================================================================
    // ACUDIENTE: DATA PREFETCH
    // =========================================================================

    /**
     * GET /api/offline/acudiente/cargos
     * Returns pending charges for the acudiente's students.
     */
    public function acudienteCargos(): ResponseInterface
    {
        $acudienteId = $this->getAcudienteId();
        if (!$acudienteId) {
            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'No es acudiente o no tiene perfil asignado.'
            ]);
        }

        $db = \Config\Database::connect();
        $cargoModel = new CargoModel();

        $estudiantes = $db->table('estudiantes')
            ->select('id, nombres, apellidos, codigo')
            ->where('acudiente_id', $acudienteId)
            ->where('estado', 'activo')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($estudiantes as $est) {
            $cargos = $cargoModel->getPendientesByEstudiante((int) $est['id']);
            $cargosList = [];
            foreach ($cargos as $cargo) {
                $cargosList[] = [
                    'id'                => (int) $cargo['id'],
                    'concepto_nombre'   => $cargo['concepto_nombre'] ?? $cargo['descripcion'],
                    'concepto_codigo'   => $cargo['concepto_codigo'] ?? '',
                    'descripcion'       => $cargo['descripcion'],
                    'saldo_pendiente'   => (float) $cargo['saldo_pendiente'],
                    'valor_original'    => (float) $cargo['valor_original'],
                    'fecha_vencimiento' => $cargo['fecha_vencimiento'],
                    'estado'            => $cargo['estado'],
                ];
            }

            $result[] = [
                'id'      => (int) $est['id'],
                'nombre'  => $est['nombres'] . ' ' . $est['apellidos'],
                'codigo'  => $est['codigo'],
                'cargos'  => $cargosList,
            ];
        }

        return $this->response->setJSON([
            'acudiente_id' => $acudienteId,
            'estudiantes'  => $result,
            'cached_at'    => date('c'),
        ]);
    }

    /**
     * GET /api/offline/acudiente/dashboard
     * Returns summary data for the acudiente dashboard.
     */
    public function acudienteDashboard(): ResponseInterface
    {
        $acudienteId = $this->getAcudienteId();
        if (!$acudienteId) {
            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'No es acudiente o no tiene perfil asignado.'
            ]);
        }

        $db = \Config\Database::connect();

        // Total pending
        $totalPendiente = $db->table('cargos c')
            ->selectSum('c.saldo_pendiente', 'total')
            ->join('estudiantes e', 'e.id = c.estudiante_id')
            ->where('e.acudiente_id', $acudienteId)
            ->whereIn('c.estado', ['pendiente', 'parcial'])
            ->get()
            ->getRowArray();

        // Student count
        $numEstudiantes = $db->table('estudiantes')
            ->where('acudiente_id', $acudienteId)
            ->where('estado', 'activo')
            ->countAllResults();

        // Pending payments
        $pagosPendientes = $db->table('pagos')
            ->where('acudiente_id', $acudienteId)
            ->where('estado', 'pendiente_revision')
            ->countAllResults();

        return $this->response->setJSON([
            'acudiente_id'    => $acudienteId,
            'total_pendiente' => (float) ($totalPendiente['total'] ?? 0),
            'num_estudiantes' => (int) $numEstudiantes,
            'pagos_pendientes'=> (int) $pagosPendientes,
            'cached_at'       => date('c'),
        ]);
    }

    // =========================================================================
    // SYNC: ATTENDANCE
    // =========================================================================

    /**
     * POST /api/offline/attendance
     * Sync an offline attendance record.
     */
    public function syncAttendance(): ResponseInterface
    {
        $json = $this->request->getJSON(true);
        if (!$json) {
            $json = $this->request->getPost();
        }

        $grupoId      = (int) ($json['grupo_id'] ?? 0);
        $profesorId   = (int) ($json['profesor_id'] ?? 0);
        $fecha        = $json['fecha'] ?? date('Y-m-d');
        $horaInicio   = $json['hora_inicio'] ?? date('H:i:s');
        $horaFin      = $json['hora_fin'] ?? date('H:i:s', strtotime('+1 hour'));
        $observaciones = $json['observaciones'] ?? '';
        $estudianteIds = $json['estudiante_ids'] ?? [];
        $presentes    = $json['presentes'] ?? [];
        $offlineId    = $json['offline_id'] ?? '';

        if (!$grupoId || empty($estudianteIds)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success'    => false,
                'offline_id' => $offlineId,
                'message'    => 'Datos incompletos: grupo_id y estudiante_ids son requeridos.',
            ]);
        }

        // Check for conflict: existing session on same grupo + fecha
        $sesionModel = new SesionClaseModel();
        $existing = $sesionModel->where('grupo_id', $grupoId)
            ->where('fecha', $fecha)
            ->where('estado', 'realizada')
            ->first();

        if ($existing) {
            // Get professor name for the existing session
            $db = \Config\Database::connect();
            $profData = $db->table('profesores')
                ->select('nombres, apellidos')
                ->where('id', $existing['profesor_id'])
                ->get()
                ->getRowArray();

            $profName = $profData ? ($profData['nombres'] . ' ' . $profData['apellidos']) : 'Otro profesor';

            // Count attendance
            $asistenciaModel = new AsistenciaModel();
            $asistencias = $asistenciaModel->getBySesion((int) $existing['id']);
            $totalPresentes = 0;
            $totalAusentes = 0;
            foreach ($asistencias as $a) {
                if ($a['presente']) $totalPresentes++;
                else $totalAusentes++;
            }

            return $this->response->setStatusCode(409)->setJSON([
                'success'       => false,
                'offline_id'    => $offlineId,
                'error'         => 'conflict',
                'conflict_type' => 'duplicate_session',
                'existing_session' => [
                    'id'               => (int) $existing['id'],
                    'profesor_nombre'  => $profName,
                    'created_at'       => $existing['created_at'] ?? $fecha,
                    'total_presentes'  => $totalPresentes,
                    'total_ausentes'   => $totalAusentes,
                ],
                'message' => 'Ya existe asistencia para este grupo en esta fecha. Registrada por ' . $profName . '.',
            ]);
        }

        // Create the session and attendance records
        $sesionId = $sesionModel->insert([
            'grupo_id'      => $grupoId,
            'profesor_id'   => $profesorId,
            'fecha'         => $fecha,
            'hora_inicio'   => $horaInicio,
            'hora_fin'      => $horaFin,
            'estado'        => 'realizada',
            'observaciones' => $observaciones,
        ]);

        if (!$sesionId) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'offline_id' => $offlineId,
                'message'    => 'Error al crear la sesion de clase.',
            ]);
        }

        $asistenciaModel = new AsistenciaModel();
        $userId = session()->get('user_id');
        $asistenciaModel->registrarAsistencia($sesionId, $presentes, $estudianteIds, $userId);

        $totalPresentes = count($presentes);
        $totalEstudiantes = count($estudianteIds);

        return $this->response->setJSON([
            'success'    => true,
            'offline_id' => $offlineId,
            'sesion_id'  => (int) $sesionId,
            'message'    => "Asistencia registrada: {$totalPresentes}/{$totalEstudiantes} presentes.",
        ]);
    }

    // =========================================================================
    // SYNC: PAYMENTS
    // =========================================================================

    /**
     * POST /api/offline/payments
     * Sync an offline payment record. Supports multipart/form-data for file upload.
     */
    public function syncPayment(): ResponseInterface
    {
        $offlineId    = $this->request->getPost('offline_id') ?? '';
        $acudienteId  = (int) ($this->request->getPost('acudiente_id') ?? 0);
        $fechaPago    = $this->request->getPost('fecha_pago') ?? date('Y-m-d');
        $metodoPago   = $this->request->getPost('metodo_pago') ?? '';
        $banco        = $this->request->getPost('banco') ?? '';
        $referencia   = $this->request->getPost('referencia_banco') ?? '';
        $observaciones = $this->request->getPost('observaciones') ?? '';
        $valorTotal   = (float) ($this->request->getPost('valor_total') ?? 0);
        $cargosData   = $this->request->getPost('cargos') ?? [];

        if (!$acudienteId || empty($cargosData)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success'    => false,
                'offline_id' => $offlineId,
                'message'    => 'Datos incompletos: acudiente_id y cargos son requeridos.',
            ]);
        }

        // Validate cargos: check saldo_pendiente for each
        $cargoModel = new CargoModel();
        $conflicts = [];

        foreach ($cargosData as $cd) {
            $cargoId = (int) ($cd['cargo_id'] ?? 0);
            $valorAplicado = (float) ($cd['valor_aplicado'] ?? 0);
            $cargo = $cargoModel->find($cargoId);

            if (!$cargo) {
                $conflicts[] = [
                    'cargo_id' => $cargoId,
                    'message'  => 'Cargo no encontrado.',
                ];
                continue;
            }

            if ((float) $cargo['saldo_pendiente'] < $valorAplicado) {
                $conflicts[] = [
                    'cargo_id'      => $cargoId,
                    'descripcion'   => $cargo['descripcion'],
                    'saldo_actual'  => (float) $cargo['saldo_pendiente'],
                    'valor_enviado' => $valorAplicado,
                    'message'       => 'El saldo actual ($' . number_format($cargo['saldo_pendiente'], 0, ',', '.') . ') es menor al valor enviado.',
                ];
            }
        }

        if (!empty($conflicts)) {
            return $this->response->setStatusCode(409)->setJSON([
                'success'       => false,
                'offline_id'    => $offlineId,
                'error'         => 'conflict',
                'conflict_type' => 'cargo_saldo_changed',
                'details'       => $conflicts,
                'message'       => 'Uno o mas cargos ya fueron pagados o su saldo cambio.',
            ]);
        }

        // Process payment
        $db = \Config\Database::connect();
        $db->transStart();

        $pagoModel = new PagoModel();
        $numRecibo = $pagoModel->generateNumeroRecibo();

        $pagoId = $pagoModel->insert([
            'acudiente_id'     => $acudienteId,
            'numero_recibo'    => $numRecibo,
            'fecha_pago'       => $fechaPago,
            'fecha_registro'   => date('Y-m-d H:i:s'),
            'valor_total'      => $valorTotal,
            'metodo_pago'      => $metodoPago,
            'banco'            => $banco,
            'referencia_banco' => $referencia,
            'observaciones'    => $observaciones . ($offlineId ? ' [Offline: ' . substr($offlineId, 0, 8) . ']' : ''),
            'estado'           => 'pendiente_revision',
        ]);

        if (!$pagoId) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'offline_id' => $offlineId,
                'message'    => 'Error al crear el registro de pago.',
            ]);
        }

        // Create pago_detalles
        $pagoDetalleModel = new PagoDetalleModel();
        foreach ($cargosData as $cd) {
            $pagoDetalleModel->insert([
                'pago_id'        => $pagoId,
                'cargo_id'       => (int) $cd['cargo_id'],
                'valor_aplicado' => (float) $cd['valor_aplicado'],
            ]);
        }

        // Handle comprobante file upload
        $comprobante = $this->request->getFile('comprobante');
        if ($comprobante && $comprobante->isValid() && !$comprobante->hasMoved()) {
            $newName = $comprobante->getRandomName();
            $comprobante->move(WRITEPATH . 'uploads/comprobantes', $newName);

            $comprobanteModel = new ComprobanteModel();
            $comprobanteModel->insert([
                'pago_id'          => $pagoId,
                'archivo'          => $newName,
                'archivo_original' => $comprobante->getClientName(),
                'tipo_archivo'     => $comprobante->getClientMimeType(),
                'tamano'           => $comprobante->getSize(),
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'offline_id' => $offlineId,
                'message'    => 'Error al procesar el pago.',
            ]);
        }

        return $this->response->setJSON([
            'success'        => true,
            'offline_id'     => $offlineId,
            'pago_id'        => (int) $pagoId,
            'numero_recibo'  => $numRecibo,
            'message'        => 'Pago registrado exitosamente. Recibo: ' . $numRecibo,
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function getProfesorId(): ?int
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');
        $prof = $db->table('profesores')->where('usuario_id', $userId)->get()->getRowArray();
        return $prof ? (int) $prof['id'] : null;
    }

    private function getAcudienteId(): ?int
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');
        $acud = $db->table('acudientes')->where('usuario_id', $userId)->get()->getRowArray();
        return $acud ? (int) $acud['id'] : null;
    }
}
