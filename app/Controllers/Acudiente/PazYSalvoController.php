<?php

namespace App\Controllers\Acudiente;

use App\Controllers\BaseController;
use App\Models\PazYSalvoModel;
use App\Models\StudentModel;
use App\Libraries\PazYSalvoPdfGenerator;
use CodeIgniter\HTTP\ResponseInterface;

class PazYSalvoController extends BaseController
{
    protected PazYSalvoModel $pazYSalvoModel;

    public function __construct()
    {
        $this->pazYSalvoModel = new PazYSalvoModel();
    }

    /**
     * Show paz y salvo page with students and their balances
     */
    public function index(): string
    {
        $acudienteId = $this->getAcudienteId();
        $db = \Config\Database::connect();

        // Get acudiente data
        $acudiente = $db->table('acudientes a')
            ->select('a.*, u.email')
            ->join('usuarios u', 'u.id = a.usuario_id')
            ->where('a.id', $acudienteId)
            ->get()->getRowArray();

        // Get active students
        $studentModel = new StudentModel();
        $estudiantes = $studentModel->getByAcudiente($acudienteId);

        // Calculate balance for each student and check for existing paz y salvo
        foreach ($estudiantes as &$est) {
            $saldo = $db->table('cargos')
                ->selectSum('saldo_pendiente')
                ->where('estudiante_id', $est['id'])
                ->whereIn('estado', ['pendiente', 'parcial'])
                ->get()->getRowArray();

            $est['saldo_pendiente'] = (float)($saldo['saldo_pendiente'] ?? 0);

            // Get latest paz y salvo
            $est['paz_y_salvo'] = $db->table('paz_y_salvos')
                ->where('estudiante_id', $est['id'])
                ->where('acudiente_id', $acudienteId)
                ->orderBy('fecha_generacion', 'DESC')
                ->get()->getRowArray();
        }

        $data = [
            'title'       => 'Paz y Salvo - Academia Heroicos',
            'pageTitle'   => 'Paz y Salvo',
            'activePage'  => 'paz-y-salvo',
            'acudiente'   => $acudiente,
            'estudiantes' => $estudiantes,
        ];

        return view('acudiente/paz_y_salvo/index', $data);
    }

    /**
     * Request paz y salvo for a student
     */
    public function solicitar(): ResponseInterface
    {
        $estudianteId = $this->request->getPost('estudiante_id');
        $acudienteId = $this->getAcudienteId();
        $db = \Config\Database::connect();

        // Verify student belongs to this acudiente
        $estudiante = $db->table('estudiantes')
            ->where('id', $estudianteId)
            ->where('acudiente_id', $acudienteId)
            ->get()->getRowArray();

        if (!$estudiante) {
            return redirect()->back()->with('error', 'Estudiante no encontrado.');
        }

        // Verify balance is zero
        $saldo = $db->table('cargos')
            ->selectSum('saldo_pendiente')
            ->where('estudiante_id', $estudianteId)
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->get()->getRowArray();

        if ((float)($saldo['saldo_pendiente'] ?? 0) > 0) {
            return redirect()->back()->with('error', 'El estudiante tiene saldo pendiente. No se puede generar el paz y salvo.');
        }

        // Get acudiente info
        $acudiente = $db->table('acudientes a')
            ->select('a.*, u.email')
            ->join('usuarios u', 'u.id = a.usuario_id')
            ->where('a.id', $acudienteId)
            ->get()->getRowArray();

        // Generate certificate number
        $numeroCertificado = $this->pazYSalvoModel->generateNumero();

        // Generate PDF
        $pdfGenerator = new PazYSalvoPdfGenerator();
        $rutaPdf = $pdfGenerator->generar([
            'numero'           => $numeroCertificado,
            'nombre_acudiente' => $acudiente['nombres'] . ' ' . $acudiente['apellidos'],
            'tipo_documento'   => $acudiente['tipo_documento'] ?? 'C.C.',
            'numero_documento' => $acudiente['numero_documento'] ?? '',
            'nombre_estudiante' => $estudiante['nombres'] . ' ' . $estudiante['apellidos'],
            'fecha_corte'      => date('Y-m-d'),
        ]);

        // Save record
        $this->pazYSalvoModel->insert([
            'estudiante_id'    => $estudianteId,
            'acudiente_id'     => $acudienteId,
            'numero'           => $numeroCertificado,
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'fecha_corte'      => date('Y-m-d'),
            'archivo_pdf'      => $rutaPdf,
            'enviado_email'    => 1,
            'motivo'           => 'solicitud',
        ]);

        // Send email with PDF attached
        try {
            $sendgrid = new \App\Libraries\SendGridService();
            $sendgrid->enviarConAdjunto(
                ['email' => $acudiente['email'], 'nombre' => $acudiente['nombres']],
                'paz_y_salvo',
                [
                    'nombre_acudiente'  => $acudiente['nombres'] . ' ' . $acudiente['apellidos'],
                    'nombre_estudiante' => $estudiante['nombres'] . ' ' . $estudiante['apellidos'],
                ],
                $rutaPdf,
                'PazYSalvo_' . $numeroCertificado . '.pdf'
            );
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email paz_y_salvo: ' . $e->getMessage());
        }

        return redirect()->to('acudiente/paz-y-salvo')
            ->with('success', 'Paz y salvo generado exitosamente. Revisa tu email.');
    }

    /**
     * Download paz y salvo PDF
     */
    public function descargar(int $id): ResponseInterface
    {
        $acudienteId = $this->getAcudienteId();

        $pazYSalvo = $this->pazYSalvoModel
            ->where('id', $id)
            ->where('acudiente_id', $acudienteId)
            ->first();

        if (!$pazYSalvo || !file_exists($pazYSalvo['archivo_pdf'])) {
            return redirect()->back()->with('error', 'Archivo no encontrado.');
        }

        return $this->response->download($pazYSalvo['archivo_pdf'], null)
            ->setFileName('PazYSalvo_' . $pazYSalvo['numero'] . '.pdf');
    }

    /**
     * Get acudiente ID from session
     */
    private function getAcudienteId(): int
    {
        $acudienteId = session()->get('acudiente_id');
        if ($acudienteId) return (int)$acudienteId;

        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $acudiente = $db->table('acudientes')
            ->where('usuario_id', $userId)
            ->get()->getRowArray();

        return $acudiente['id'] ?? 0;
    }
}
