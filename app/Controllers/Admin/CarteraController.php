<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CargoModel;
use App\Models\ConceptoCobroModel;
use App\Models\PeriodoModel;
use App\Models\TarifaModel;
use App\Models\StudentModel;
use App\Models\PagoDetalleModel;
use CodeIgniter\HTTP\ResponseInterface;

class CarteraController extends BaseController
{
    protected CargoModel $cargoModel;
    protected ConceptoCobroModel $conceptoModel;
    protected PeriodoModel $periodoModel;

    public function __construct()
    {
        $this->cargoModel = new CargoModel();
        $this->conceptoModel = new ConceptoCobroModel();
        $this->periodoModel = new PeriodoModel();
    }

    /**
     * Dashboard financiero - cartera general
     */
    public function index(): string
    {
        $filters = [
            'estado'      => $this->request->getGet('estado'),
            'concepto_id' => $this->request->getGet('concepto_id'),
            'search'      => $this->request->getGet('search'),
        ];

        $data = [
            'title'      => 'Cartera - Academia Heroicos',
            'pageTitle'  => 'Cartera',
            'activePage' => 'cartera',
            'stats'      => $this->cargoModel->getStats(),
            'cargos'     => $this->cargoModel->getCarteraGeneral($filters),
            'conceptos'  => $this->conceptoModel->getActive(),
            'morosos'    => $this->cargoModel->getMorosos(),
            'filters'    => $filters,
        ];

        return view('admin/cartera/index', $data);
    }

    /**
     * Cuenta individual del estudiante
     */
    public function estudiante(int $id): string
    {
        $studentModel = new StudentModel();
        $estudiante = $studentModel->getWithAcudiente($id);

        if (!$estudiante) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Estudiante no encontrado');
        }

        $cuenta = $this->cargoModel->getCuentaEstudiante($id);

        // Get payment history for each cargo
        $pagoDetalleModel = new PagoDetalleModel();
        foreach ($cuenta['cargos'] as &$cargo) {
            $cargo['pagos'] = $pagoDetalleModel->getByCargo($cargo['id']);
        }

        $data = [
            'title'      => 'Cuenta de ' . $estudiante['nombres'] . ' ' . $estudiante['apellidos'],
            'pageTitle'  => 'Cuenta Estudiante',
            'activePage' => 'cartera',
            'estudiante' => $estudiante,
            'cuenta'     => $cuenta,
            'conceptos'  => $this->conceptoModel->getActive(),
            'periodos'   => $this->periodoModel->getAll(),
        ];

        return view('admin/cartera/estudiante', $data);
    }

    /**
     * Generar cargo manual
     */
    public function generarCargo(): ResponseInterface
    {
        $rules = [
            'estudiante_id' => 'required|integer',
            'concepto_id'   => 'required|integer',
            'periodo_id'    => 'required|integer',
            'valor'         => 'required|decimal',
            'descripcion'   => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor complete todos los campos correctamente');
        }

        $valor = (float)$this->request->getPost('valor');

        $data = [
            'estudiante_id'    => $this->request->getPost('estudiante_id'),
            'concepto_id'      => $this->request->getPost('concepto_id'),
            'periodo_id'       => $this->request->getPost('periodo_id'),
            'descripcion'      => $this->request->getPost('descripcion'),
            'mes'              => $this->request->getPost('mes') ?: null,
            'anio'             => $this->request->getPost('anio') ?: date('Y'),
            'valor_original'   => $valor,
            'valor_pagado'     => 0,
            'saldo_pendiente'  => $valor,
            'fecha_vencimiento'=> $this->request->getPost('fecha_vencimiento') ?: null,
            'estado'           => 'pendiente',
            'generado_auto'    => 0,
        ];

        if ($this->cargoModel->insert($data)) {
            return redirect()->to('admin/cartera/estudiante/' . $data['estudiante_id'])
                ->with('success', 'Cargo generado exitosamente');
        }

        return redirect()->back()->with('error', 'Error al generar el cargo');
    }

    /**
     * Send payment reminders for overdue charges
     */
    public function enviarRecordatorios(): ResponseInterface
    {
        $db = \Config\Database::connect();

        // Get overdue charges grouped by acudiente
        $cargosVencidos = $db->query("
            SELECT
                c.id as cargo_id,
                c.descripcion,
                c.saldo_pendiente,
                c.fecha_vencimiento,
                e.id as estudiante_id,
                e.nombres as est_nombres,
                e.apellidos as est_apellidos,
                a.id as acudiente_id,
                a.nombres as acud_nombres,
                a.apellidos as acud_apellidos,
                u.email
            FROM cargos c
            JOIN estudiantes e ON e.id = c.estudiante_id
            JOIN acudientes a ON a.id = e.acudiente_id
            JOIN usuarios u ON u.id = a.usuario_id
            WHERE c.fecha_vencimiento < CURDATE()
            AND c.estado IN ('pendiente', 'parcial')
            AND u.estado = 'activo'
            ORDER BY a.id, c.fecha_vencimiento ASC
        ")->getResultArray();

        if (empty($cargosVencidos)) {
            return redirect()->back()->with('message', 'No hay cargos vencidos para enviar recordatorios.');
        }

        // Group by acudiente
        $porAcudiente = [];
        foreach ($cargosVencidos as $cargo) {
            $acudId = $cargo['acudiente_id'];
            if (!isset($porAcudiente[$acudId])) {
                $porAcudiente[$acudId] = [
                    'email'    => $cargo['email'],
                    'nombre'   => $cargo['acud_nombres'] . ' ' . $cargo['acud_apellidos'],
                    'cargos'   => [],
                ];
            }
            $porAcudiente[$acudId]['cargos'][] = $cargo;
        }

        $enviados = 0;
        $sendgrid = new \App\Libraries\SendGridService();

        foreach ($porAcudiente as $acudiente) {
            // Use the first overdue charge for the template variables
            $primerCargo = $acudiente['cargos'][0];
            $totalPendiente = array_sum(array_column($acudiente['cargos'], 'saldo_pendiente'));

            try {
                $sendgrid->enviar(
                    ['email' => $acudiente['email'], 'nombre' => $acudiente['nombre']],
                    'recordatorio_pago',
                    [
                        'nombre_acudiente'  => $acudiente['nombre'],
                        'valor_pendiente'   => number_format($totalPendiente, 0, ',', '.'),
                        'estudiante'        => $primerCargo['est_nombres'] . ' ' . $primerCargo['est_apellidos'],
                        'concepto'          => $primerCargo['descripcion'],
                        'fecha_vencimiento' => date('d/m/Y', strtotime($primerCargo['fecha_vencimiento'])),
                    ]
                );
                $enviados++;
            } catch (\Exception $e) {
                log_message('error', 'Error enviando recordatorio a ' . $acudiente['email'] . ': ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', "Se enviaron {$enviados} recordatorios de pago exitosamente.");
    }

    /**
     * Anular cargo
     */
    public function anularCargo(int $id): ResponseInterface
    {
        $cargo = $this->cargoModel->find($id);

        if (!$cargo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Cargo no encontrado');
        }

        if ($cargo['valor_pagado'] > 0) {
            return redirect()->back()->with('error', 'No se puede anular un cargo que ya tiene pagos aplicados');
        }

        $this->cargoModel->update($id, ['estado' => 'anulado']);

        return redirect()->back()->with('success', 'Cargo anulado exitosamente');
    }
}
