<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ConfiguracionController extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();

        $configs = $db->table('configuracion')
            ->orderBy('grupo', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        // Group by grupo
        $grupos = [];
        foreach ($configs as $config) {
            $grupos[$config['grupo']][] = $config;
        }

        $grupoLabels = [
            'academia'       => 'Informacion de la Academia',
            'financiero'     => 'Configuracion Financiera',
            'notificaciones' => 'Notificaciones',
            'legal'          => 'Legal y Politicas',
            'sistema'        => 'Sistema',
        ];

        $grupoIcons = [
            'academia'       => 'bi-building',
            'financiero'     => 'bi-cash-coin',
            'notificaciones' => 'bi-bell',
            'legal'          => 'bi-file-earmark-text',
            'sistema'        => 'bi-gear',
        ];

        $data = [
            'title'       => 'Configuracion - Academia Heroicos',
            'pageTitle'   => 'Configuracion del Sistema',
            'activePage'  => 'configuracion',
            'grupos'      => $grupos,
            'grupoLabels' => $grupoLabels,
            'grupoIcons'  => $grupoIcons,
        ];

        return view('admin/configuracion/index', $data);
    }

    public function guardar(): ResponseInterface
    {
        $db = \Config\Database::connect();

        $configs = $db->table('configuracion')
            ->where('editable', true)
            ->get()
            ->getResultArray();

        foreach ($configs as $config) {
            $newValue = $this->request->getPost('config_' . $config['id']);

            // For bool type, checkbox sends '1' or nothing
            if ($config['tipo'] === 'bool') {
                $newValue = $newValue ? '1' : '0';
            }

            if ($newValue !== null && $newValue !== $config['valor']) {
                $db->table('configuracion')
                    ->where('id', $config['id'])
                    ->update([
                        'valor'      => $newValue,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }
        }

        // Log action
        $db->table('auditoria')->insert([
            'usuario_id'  => session()->get('user_id'),
            'accion'      => 'UPDATE_CONFIGURACION',
            'tabla'       => 'configuracion',
            'ip'          => $this->request->getIPAddress(),
            'user_agent'  => $this->request->getUserAgent()->getAgentString(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('admin/configuracion')
            ->with('success', 'Configuracion guardada exitosamente.');
    }
}
