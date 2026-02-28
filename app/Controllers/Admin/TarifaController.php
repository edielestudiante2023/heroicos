<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TarifaModel;
use App\Models\ConceptoCobroModel;
use App\Models\CategoriaModel;
use App\Models\PeriodoModel;
use CodeIgniter\HTTP\ResponseInterface;

class TarifaController extends BaseController
{
    protected TarifaModel $tarifaModel;
    protected ConceptoCobroModel $conceptoModel;
    protected CategoriaModel $categoriaModel;
    protected PeriodoModel $periodoModel;

    public function __construct()
    {
        $this->tarifaModel = new TarifaModel();
        $this->conceptoModel = new ConceptoCobroModel();
        $this->categoriaModel = new CategoriaModel();
        $this->periodoModel = new PeriodoModel();
    }

    /**
     * List all tarifas
     */
    public function index(): string
    {
        $filters = [
            'concepto_id'  => $this->request->getGet('concepto_id'),
            'categoria_id' => $this->request->getGet('categoria_id'),
            'periodo_id'   => $this->request->getGet('periodo_id'),
        ];

        $data = [
            'title'      => 'Tarifas - Academia Heroicos',
            'pageTitle'  => 'Tarifas',
            'activePage' => 'tarifas',
            'tarifas'    => $this->tarifaModel->getFiltered($filters),
            'conceptos'  => $this->conceptoModel->getAll(),
            'categorias' => $this->categoriaModel->getActive(),
            'periodos'   => $this->periodoModel->getAll(),
            'filters'    => $filters,
        ];

        return view('admin/tarifas/index', $data);
    }

    /**
     * Show create form
     */
    public function new(): string
    {
        $data = [
            'title'      => 'Nueva Tarifa',
            'pageTitle'  => 'Nueva Tarifa',
            'activePage' => 'tarifas',
            'action'     => 'create',
            'tarifa'     => [],
            'conceptos'  => $this->conceptoModel->getActive(),
            'categorias' => $this->categoriaModel->getActive(),
            'periodos'   => $this->periodoModel->getAll(),
        ];

        return view('admin/tarifas/form', $data);
    }

    /**
     * Create new tarifa
     */
    public function create(): ResponseInterface
    {
        $rules = [
            'concepto_id'   => 'required|integer',
            'periodo_id'    => 'required|integer',
            'valor'         => 'required|decimal',
            'vigente_desde' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'concepto_id'    => $this->request->getPost('concepto_id'),
            'categoria_id'   => $this->request->getPost('categoria_id') ?: null,
            'periodo_id'     => $this->request->getPost('periodo_id'),
            'valor'          => $this->request->getPost('valor'),
            'vigente_desde'  => $this->request->getPost('vigente_desde'),
            'vigente_hasta'  => $this->request->getPost('vigente_hasta') ?: null,
        ];

        if ($this->tarifaModel->insert($data)) {
            return redirect()->to('admin/tarifas')
                ->with('success', 'Tarifa creada exitosamente');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Error al crear la tarifa');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $tarifa = $this->tarifaModel->getWithDetails($id);

        if (!$tarifa) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tarifa no encontrada');
        }

        $data = [
            'title'      => 'Editar Tarifa',
            'pageTitle'  => 'Editar Tarifa',
            'activePage' => 'tarifas',
            'action'     => 'update',
            'tarifa'     => $tarifa,
            'conceptos'  => $this->conceptoModel->getActive(),
            'categorias' => $this->categoriaModel->getActive(),
            'periodos'   => $this->periodoModel->getAll(),
        ];

        return view('admin/tarifas/form', $data);
    }

    /**
     * Update tarifa
     */
    public function update(int $id): ResponseInterface
    {
        $tarifa = $this->tarifaModel->find($id);

        if (!$tarifa) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tarifa no encontrada');
        }

        $rules = [
            'concepto_id'   => 'required|integer',
            'periodo_id'    => 'required|integer',
            'valor'         => 'required|decimal',
            'vigente_desde' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'concepto_id'    => $this->request->getPost('concepto_id'),
            'categoria_id'   => $this->request->getPost('categoria_id') ?: null,
            'periodo_id'     => $this->request->getPost('periodo_id'),
            'valor'          => $this->request->getPost('valor'),
            'vigente_desde'  => $this->request->getPost('vigente_desde'),
            'vigente_hasta'  => $this->request->getPost('vigente_hasta') ?: null,
        ];

        $this->tarifaModel->update($id, $data);

        return redirect()->to('admin/tarifas')
            ->with('success', 'Tarifa actualizada exitosamente');
    }

    /**
     * Delete tarifa
     */
    public function delete(int $id): ResponseInterface
    {
        $tarifa = $this->tarifaModel->find($id);

        if (!$tarifa) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tarifa no encontrada');
        }

        $this->tarifaModel->delete($id);

        return redirect()->to('admin/tarifas')
            ->with('success', 'Tarifa eliminada exitosamente');
    }
}
