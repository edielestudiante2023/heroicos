<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PagoModel;
use App\Models\PagoDetalleModel;
use App\Models\ComprobanteModel;
use App\Models\CargoModel;
use CodeIgniter\HTTP\ResponseInterface;

class PaymentController extends BaseController
{
    protected PagoModel $pagoModel;

    public function __construct()
    {
        $this->pagoModel = new PagoModel();
    }

    /**
     * List payments with filters
     */
    public function index(): string
    {
        $filters = [
            'estado'      => $this->request->getGet('estado'),
            'metodo_pago' => $this->request->getGet('metodo_pago'),
            'search'      => $this->request->getGet('search'),
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
        ];

        $data = [
            'title'      => 'Gestión de Pagos - Academia Heroicos',
            'pageTitle'  => 'Pagos',
            'activePage' => 'payments',
            'pagos'      => $this->pagoModel->getFiltered($filters),
            'stats'      => $this->pagoModel->getStats(),
            'filters'    => $filters,
        ];

        return view('admin/payments/index', $data);
    }

    /**
     * Review a specific payment
     */
    public function review(int $id): string
    {
        $pago = $this->pagoModel->getWithDetails($id);

        if (!$pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pago no encontrado');
        }

        $data = [
            'title'      => 'Revisar Pago ' . $pago['numero_recibo'],
            'pageTitle'  => 'Revisar Pago',
            'activePage' => 'payments',
            'pago'       => $pago,
        ];

        return view('admin/payments/review', $data);
    }

    /**
     * Approve a payment
     */
    public function approve(int $id): ResponseInterface
    {
        $pago = $this->pagoModel->find($id);

        if (!$pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pago no encontrado');
        }

        if ($pago['estado'] !== 'pendiente_revision') {
            return redirect()->back()->with('error', 'Este pago ya fue revisado');
        }

        $userId = session()->get('user_id');

        if ($this->pagoModel->aprobar($id, $userId)) {
            return redirect()->to('admin/payments')
                ->with('success', 'Pago aprobado exitosamente. Recibo: ' . $pago['numero_recibo']);
        }

        return redirect()->back()->with('error', 'Error al aprobar el pago');
    }

    /**
     * Reject a payment
     */
    public function reject(int $id): ResponseInterface
    {
        $pago = $this->pagoModel->find($id);

        if (!$pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pago no encontrado');
        }

        if ($pago['estado'] !== 'pendiente_revision') {
            return redirect()->back()->with('error', 'Este pago ya fue revisado');
        }

        $motivo = $this->request->getPost('motivo_rechazo');
        if (empty($motivo)) {
            return redirect()->back()->with('error', 'Debe indicar un motivo de rechazo');
        }

        $userId = session()->get('user_id');

        if ($this->pagoModel->rechazar($id, $userId, $motivo)) {
            return redirect()->to('admin/payments')
                ->with('success', 'Pago rechazado. Se notificará al acudiente.');
        }

        return redirect()->back()->with('error', 'Error al rechazar el pago');
    }

    /**
     * Register a new payment (admin-side)
     */
    public function create(): string
    {
        $db = \Config\Database::connect();

        // Get acudientes
        $acudientes = $db->table('acudientes a')
            ->select('a.id, a.nombres, a.apellidos, u.email')
            ->join('usuarios u', 'u.id = a.usuario_id')
            ->orderBy('a.apellidos', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'       => 'Registrar Pago',
            'pageTitle'   => 'Registrar Pago',
            'activePage'  => 'payments',
            'acudientes'  => $acudientes,
        ];

        return view('admin/payments/create', $data);
    }

    /**
     * Get pending cargos for an acudiente's students (AJAX)
     */
    public function getCargosPendientes(): ResponseInterface
    {
        $acudienteId = $this->request->getGet('acudiente_id');
        if (!$acudienteId) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();

        // Get students of this acudiente
        $estudiantes = $db->table('estudiantes')
            ->where('acudiente_id', $acudienteId)
            ->where('estado', 'activo')
            ->get()
            ->getResultArray();

        $cargoModel = new CargoModel();
        $cargos = [];

        foreach ($estudiantes as $est) {
            $pendientes = $cargoModel->getPendientesByEstudiante($est['id']);
            foreach ($pendientes as $cargo) {
                $cargo['estudiante_nombre'] = $est['nombres'] . ' ' . $est['apellidos'];
                $cargos[] = $cargo;
            }
        }

        return $this->response->setJSON($cargos);
    }

    /**
     * Store a new payment
     */
    public function store(): ResponseInterface
    {
        $rules = [
            'acudiente_id' => 'required|integer',
            'fecha_pago'   => 'required|valid_date',
            'metodo_pago'  => 'required|in_list[transferencia,efectivo,nequi,daviplata,otro]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Por favor complete todos los campos correctamente');
        }

        $cargosSeleccionados = $this->request->getPost('cargos') ?? [];
        $valores = $this->request->getPost('valores') ?? [];

        if (empty($cargosSeleccionados)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Debe seleccionar al menos un cargo a pagar');
        }

        // Calculate total
        $valorTotal = 0;
        foreach ($cargosSeleccionados as $cargoId) {
            $valorTotal += (float)($valores[$cargoId] ?? 0);
        }

        if ($valorTotal <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El valor total debe ser mayor a cero');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Create payment
        $pagoData = [
            'acudiente_id'    => $this->request->getPost('acudiente_id'),
            'numero_recibo'   => $this->pagoModel->generateNumeroRecibo(),
            'fecha_pago'      => $this->request->getPost('fecha_pago'),
            'fecha_registro'  => date('Y-m-d H:i:s'),
            'valor_total'     => $valorTotal,
            'metodo_pago'     => $this->request->getPost('metodo_pago'),
            'banco'           => $this->request->getPost('banco'),
            'referencia_banco'=> $this->request->getPost('referencia_banco'),
            'observaciones'   => $this->request->getPost('observaciones'),
            'estado'          => 'aprobado',
            'revisado_por'    => session()->get('user_id'),
            'fecha_revision'  => date('Y-m-d H:i:s'),
        ];

        $pagoId = $this->pagoModel->insert($pagoData);

        if (!$pagoId) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Error al crear el pago');
        }

        // Create payment details and apply to cargos
        $pagoDetalleModel = new PagoDetalleModel();
        $cargoModel = new CargoModel();

        foreach ($cargosSeleccionados as $cargoId) {
            $valorAplicado = (float)($valores[$cargoId] ?? 0);
            if ($valorAplicado <= 0) continue;

            $pagoDetalleModel->insert([
                'pago_id'        => $pagoId,
                'cargo_id'       => $cargoId,
                'valor_aplicado' => $valorAplicado,
            ]);

            $cargoModel->aplicarPago((int)$cargoId, $valorAplicado);
        }

        // Handle file upload
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

        if ($db->transStatus()) {
            return redirect()->to('admin/payments')
                ->with('success', 'Pago registrado exitosamente. Recibo: ' . $pagoData['numero_recibo']);
        }

        return redirect()->back()->with('error', 'Error al procesar el pago');
    }
}
