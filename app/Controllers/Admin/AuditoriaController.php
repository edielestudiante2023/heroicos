<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AuditoriaController extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();

        // Build query with filters
        $builder = $db->table('auditoria a')
            ->select('a.*, u.email as usuario_email')
            ->join('usuarios u', 'u.id = a.usuario_id', 'left')
            ->orderBy('a.created_at', 'DESC');

        // Apply filters
        $accion = $this->request->getGet('accion');
        $tabla = $this->request->getGet('tabla');
        $usuario = $this->request->getGet('usuario');
        $fechaDesde = $this->request->getGet('fecha_desde');
        $fechaHasta = $this->request->getGet('fecha_hasta');

        if ($accion) {
            $builder->like('a.accion', $accion);
        }
        if ($tabla) {
            $builder->where('a.tabla', $tabla);
        }
        if ($usuario) {
            $builder->like('u.email', $usuario);
        }
        if ($fechaDesde) {
            $builder->where('a.created_at >=', $fechaDesde . ' 00:00:00');
        }
        if ($fechaHasta) {
            $builder->where('a.created_at <=', $fechaHasta . ' 23:59:59');
        }

        $logs = $builder->limit(500)->get()->getResultArray();

        // Get distinct actions and tables for filter dropdowns
        $acciones = $db->table('auditoria')
            ->select('accion')
            ->distinct()
            ->orderBy('accion', 'ASC')
            ->get()->getResultArray();

        $tablas = $db->table('auditoria')
            ->select('tabla')
            ->distinct()
            ->where('tabla IS NOT NULL')
            ->orderBy('tabla', 'ASC')
            ->get()->getResultArray();

        $data = [
            'title'      => 'Auditoria - Academia Heroicos',
            'pageTitle'  => 'Auditoria del Sistema',
            'activePage' => 'auditoria',
            'logs'       => $logs,
            'acciones'   => array_column($acciones, 'accion'),
            'tablas'     => array_column($tablas, 'tabla'),
            'filters'    => [
                'accion'      => $accion,
                'tabla'       => $tabla,
                'usuario'     => $usuario,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ],
        ];

        return view('admin/auditoria/index', $data);
    }

    public function detalle(int $id): string
    {
        $db = \Config\Database::connect();

        $log = $db->table('auditoria a')
            ->select('a.*, u.email as usuario_email')
            ->join('usuarios u', 'u.id = a.usuario_id', 'left')
            ->where('a.id', $id)
            ->get()->getRowArray();

        if (!$log) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Registro no encontrado');
        }

        $data = [
            'title'      => 'Detalle Auditoria - Academia Heroicos',
            'pageTitle'  => 'Detalle de Auditoria',
            'activePage' => 'auditoria',
            'log'        => $log,
        ];

        return view('admin/auditoria/detalle', $data);
    }
}
