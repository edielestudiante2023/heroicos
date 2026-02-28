<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\GrupoModel;
use App\Models\InscripcionModel;

class GroupController extends BaseController
{
    protected GrupoModel $grupoModel;

    public function __construct()
    {
        $this->grupoModel = new GrupoModel();
    }

    /**
     * List professor's groups
     */
    public function index(): string
    {
        $profesorId = $this->getProfesorId();
        $db = \Config\Database::connect();

        $grupos = $db->table('profesor_grupos pg')
            ->select('g.*, c.nombre as categoria_nombre, pg.es_titular')
            ->join('grupos g', 'g.id = pg.grupo_id')
            ->join('categorias c', 'c.id = g.categoria_id', 'left')
            ->where('pg.profesor_id', $profesorId)
            ->where('g.estado', 'activo')
            ->orderBy('g.nombre', 'ASC')
            ->get()
            ->getResultArray();

        // Add enrolled count
        foreach ($grupos as &$grupo) {
            $grupo['inscritos'] = $this->grupoModel->getInscritosCount($grupo['id']);
        }

        $data = [
            'title'      => 'Mis Grupos - Academia Heroicos',
            'pageTitle'  => 'Mis Grupos',
            'activePage' => 'groups',
            'grupos'     => $grupos,
        ];

        return view('profesor/groups/index', $data);
    }

    /**
     * Show group details
     */
    public function show(int $id): string
    {
        $grupo = $this->grupoModel->getWithDetails($id);

        if (!$grupo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Grupo no encontrado');
        }

        $inscripcionModel = new InscripcionModel();
        $inscritos = $inscripcionModel->getByGrupo($id);

        foreach ($inscritos as &$est) {
            $est['edad'] = date_diff(date_create($est['fecha_nacimiento']), date_create('today'))->y;
        }

        $data = [
            'title'      => 'Grupo: ' . $grupo['nombre'],
            'pageTitle'  => $grupo['nombre'],
            'activePage' => 'groups',
            'grupo'      => $grupo,
            'inscritos'  => $inscritos,
        ];

        return view('profesor/groups/show', $data);
    }

    private function getProfesorId(): int
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $profesor = $db->table('profesores')
            ->where('usuario_id', $userId)
            ->get()
            ->getRowArray();

        return $profesor['id'] ?? 0;
    }
}
