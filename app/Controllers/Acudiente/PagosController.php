<?php

namespace App\Controllers\Acudiente;

use App\Controllers\BaseController;

class PagosController extends BaseController
{
    protected function getAcudienteId(): int
    {
        $db = \Config\Database::connect();
        $acudiente = $db->table('acudientes')
            ->where('usuario_id', session()->get('user_id'))
            ->get()->getRowArray();
        return $acudiente['id'] ?? 0;
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $acudienteId = $this->getAcudienteId();

        // Get all students for this acudiente
        $estudiantes = $db->table('estudiantes')
            ->where('acudiente_id', $acudienteId)
            ->where('estado', 'activo')
            ->get()->getResultArray();

        // Get pending cargos per student
        $cargos_pendientes = [];
        $saldo_total = 0;

        foreach ($estudiantes as $est) {
            $cargos = $db->table('cargos ca')
                ->select('ca.*, cc.nombre as concepto_nombre')
                ->join('conceptos_cobro cc', 'cc.id = ca.concepto_id')
                ->where('ca.estudiante_id', $est['id'])
                ->whereIn('ca.estado', ['pendiente', 'parcial'])
                ->orderBy('ca.fecha_vencimiento', 'ASC')
                ->get()->getResultArray();

            foreach ($cargos as $cargo) {
                $cargo['estudiante_nombre'] = $est['nombres'] . ' ' . $est['apellidos'];
                $cargos_pendientes[] = $cargo;
                $saldo_total += (float) $cargo['saldo_pendiente'];
            }
        }

        // Get payment history
        $pagos = $db->table('pagos')
            ->where('acudiente_id', $acudienteId)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        return view('acudiente/pagos/index', [
            'title'            => 'Mis Pagos - Academia Heroicos',
            'pageTitle'        => 'Estado de Cuenta',
            'cargos_pendientes' => $cargos_pendientes,
            'pagos'            => $pagos,
            'saldo_total'      => $saldo_total,
            'estudiantes'      => $estudiantes,
        ]);
    }

    public function subir()
    {
        $db = \Config\Database::connect();
        $acudienteId = $this->getAcudienteId();

        // Get students with pending cargos
        $estudiantes = $db->table('estudiantes')
            ->where('acudiente_id', $acudienteId)
            ->where('estado', 'activo')
            ->get()->getResultArray();

        $cargos_pendientes = [];
        foreach ($estudiantes as $est) {
            $cargos = $db->table('cargos ca')
                ->select('ca.*, cc.nombre as concepto_nombre')
                ->join('conceptos_cobro cc', 'cc.id = ca.concepto_id')
                ->where('ca.estudiante_id', $est['id'])
                ->whereIn('ca.estado', ['pendiente', 'parcial'])
                ->orderBy('ca.fecha_vencimiento', 'ASC')
                ->get()->getResultArray();

            foreach ($cargos as $cargo) {
                $cargo['estudiante_nombre'] = $est['nombres'] . ' ' . $est['apellidos'];
                $cargos_pendientes[] = $cargo;
            }
        }

        return view('acudiente/pagos/subir', [
            'title'            => 'Registrar Pago - Academia Heroicos',
            'pageTitle'        => 'Registrar Pago',
            'cargos_pendientes' => $cargos_pendientes,
        ]);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $acudienteId = $this->getAcudienteId();

        if (!$acudienteId) {
            return redirect()->to('acudiente/pagos')
                ->with('error', 'Perfil de acudiente no encontrado.');
        }

        $rules = [
            'valor_total'  => 'required|numeric|greater_than[0]',
            'metodo_pago'  => 'required|in_list[transferencia,efectivo,nequi,daviplata,otro]',
            'fecha_pago'   => 'required|valid_date',
            'comprobante'  => 'uploaded[comprobante]|max_size[comprobante,5120]|is_image[comprobante]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', 'Verifique los campos. El comprobante debe ser una imagen de máximo 5MB.');
        }

        $cargosSeleccionados = $this->request->getPost('cargos') ?? [];
        if (empty($cargosSeleccionados)) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe seleccionar al menos un cargo a pagar.');
        }

        // Verify selected cargos belong to this acudiente's students
        $estudianteIds = $db->table('estudiantes')
            ->select('id')
            ->where('acudiente_id', $acudienteId)
            ->get()->getResultArray();
        $estudianteIds = array_column($estudianteIds, 'id');

        if (empty($estudianteIds)) {
            return redirect()->to('acudiente/pagos')
                ->with('error', 'No tiene estudiantes registrados.');
        }

        $cargosValidos = $db->table('cargos')
            ->whereIn('id', $cargosSeleccionados)
            ->whereIn('estudiante_id', $estudianteIds)
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->get()->getResultArray();

        if (count($cargosValidos) !== count($cargosSeleccionados)) {
            return redirect()->back()->withInput()
                ->with('error', 'Algunos cargos seleccionados no son válidos.');
        }

        $db->transStart();

        try {
            // Generate receipt number
            $pagoModel = new \App\Models\PagoModel();
            $numeroRecibo = $pagoModel->generateNumeroRecibo();

            // Create payment
            $db->table('pagos')->insert([
                'acudiente_id'   => $acudienteId,
                'numero_recibo'  => $numeroRecibo,
                'fecha_pago'     => $this->request->getPost('fecha_pago'),
                'fecha_registro' => date('Y-m-d H:i:s'),
                'valor_total'    => $this->request->getPost('valor_total'),
                'metodo_pago'    => $this->request->getPost('metodo_pago'),
                'banco'          => $this->request->getPost('banco'),
                'referencia_banco' => $this->request->getPost('referencia_banco'),
                'observaciones'  => $this->request->getPost('observaciones'),
                'estado'         => 'pendiente_revision',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            $pagoId = $db->insertID();

            // Create pago_detalles
            $valorTotal = (float) $this->request->getPost('valor_total');
            $valorRestante = $valorTotal;

            foreach ($cargosValidos as $cargo) {
                $aplicar = min($valorRestante, (float) $cargo['saldo_pendiente']);
                if ($aplicar <= 0) break;

                $db->table('pago_detalles')->insert([
                    'pago_id'       => $pagoId,
                    'cargo_id'      => $cargo['id'],
                    'valor_aplicado' => $aplicar,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);

                $valorRestante -= $aplicar;
            }

            // Save comprobante
            $file = $this->request->getFile('comprobante');
            if ($file && $file->isValid()) {
                $year = date('Y');
                $month = date('m');
                $uploadPath = WRITEPATH . "uploads/comprobantes/{$year}/{$month}";

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $newName = $pagoId . '_' . time() . '.' . $file->getExtension();
                $file->move($uploadPath, $newName);

                $db->table('comprobantes')->insert([
                    'pago_id'          => $pagoId,
                    'archivo'          => "comprobantes/{$year}/{$month}/{$newName}",
                    'archivo_original' => $file->getClientName(),
                    'tipo_archivo'     => $file->getClientMimeType(),
                    'tamano'           => $file->getSize(),
                    'created_at'       => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();

            if (!$db->transStatus()) {
                return redirect()->back()->withInput()
                    ->with('error', 'Error al registrar el pago. Intente nuevamente.');
            }

            return redirect()->to('acudiente/pagos')
                ->with('message', "Pago registrado exitosamente (Recibo: {$numeroRecibo}). Será revisado por un administrador.");

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error registrando pago: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Error inesperado al registrar el pago.');
        }
    }
}
