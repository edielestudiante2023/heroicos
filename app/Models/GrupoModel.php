<?php

namespace App\Models;

use CodeIgniter\Model;

class GrupoModel extends Model
{
    protected $table            = 'grupos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'categoria_id',
        'nombre',
        'cupo_maximo',
        'estado',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'categoria_id' => 'required|integer|is_not_unique[categorias.id]',
        'nombre'       => 'required|min_length[3]|max_length[100]',
        'cupo_maximo'  => 'required|integer|greater_than[0]',
        'estado'       => 'required|in_list[activo,inactivo]',
    ];

    protected $validationMessages = [
        'categoria_id' => [
            'required' => 'La categoría es requerida',
            'is_not_unique' => 'La categoría seleccionada no existe',
        ],
        'nombre' => [
            'required' => 'El nombre del grupo es requerido',
            'min_length' => 'El nombre debe tener al menos 3 caracteres',
        ],
    ];

    /**
     * Get all groups with related data
     */
    public function getAll(): array
    {
        return $this->select('grupos.*, categorias.nombre as categoria_nombre')
            ->join('categorias', 'categorias.id = grupos.categoria_id', 'left')
            ->orderBy('categorias.nombre', 'ASC')
            ->orderBy('grupos.nombre', 'ASC')
            ->findAll();
    }

    /**
     * Get groups with filters
     */
    public function getFiltered(array $filters = []): array
    {
        $builder = $this->select('grupos.*, categorias.nombre as categoria_nombre')
            ->join('categorias', 'categorias.id = grupos.categoria_id', 'left');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('grupos.nombre', $filters['search'])
                ->orLike('categorias.nombre', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['categoria_id'])) {
            $builder->where('grupos.categoria_id', $filters['categoria_id']);
        }

        if (!empty($filters['estado'])) {
            $builder->where('grupos.estado', $filters['estado']);
        }

        return $builder->orderBy('categorias.nombre', 'ASC')
            ->orderBy('grupos.nombre', 'ASC')
            ->findAll();
    }

    /**
     * Get single group with full details
     */
    public function getWithDetails(int $id): ?array
    {
        $grupo = $this->select('grupos.*, categorias.nombre as categoria_nombre')
            ->join('categorias', 'categorias.id = grupos.categoria_id', 'left')
            ->find($id);

        if (!$grupo) {
            return null;
        }

        // Get professors assigned to this group
        $grupo['profesores'] = $this->getProfesores($id);

        // Get schedules for this group
        $grupo['horarios'] = $this->getHorarios($id);

        // Get enrolled students count
        $grupo['inscritos'] = $this->getInscritosCount($id);

        return $grupo;
    }

    /**
     * Get professors assigned to group
     */
    public function getProfesores(int $grupoId): array
    {
        $db = \Config\Database::connect();
        return $db->table('profesor_grupos pg')
            ->select('p.id, p.nombres, p.apellidos, pg.es_titular, u.email')
            ->join('profesores p', 'p.id = pg.profesor_id')
            ->join('usuarios u', 'u.id = p.usuario_id')
            ->where('pg.grupo_id', $grupoId)
            ->orderBy('pg.es_titular', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get schedules for group
     */
    public function getHorarios(int $grupoId): array
    {
        $db = \Config\Database::connect();
        $dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        $horarios = $db->table('grupo_horarios gh')
            ->select('h.*, gh.vigente_desde, gh.vigente_hasta')
            ->join('horarios h', 'h.id = gh.horario_id')
            ->where('gh.grupo_id', $grupoId)
            ->where('(gh.vigente_hasta IS NULL OR gh.vigente_hasta >= CURDATE())')
            ->orderBy('h.dia_semana', 'ASC')
            ->orderBy('h.hora_inicio', 'ASC')
            ->get()
            ->getResultArray();

        // Add day name
        foreach ($horarios as &$h) {
            $h['dia_nombre'] = $dias[$h['dia_semana']] ?? '';
        }

        return $horarios;
    }

    /**
     * Get enrolled students count
     */
    public function getInscritosCount(int $grupoId): int
    {
        $db = \Config\Database::connect();
        return $db->table('inscripciones')
            ->where('grupo_id', $grupoId)
            ->where('estado', 'activa')
            ->countAllResults();
    }

    /**
     * Get enrolled students for a group
     */
    public function getInscritos(int $grupoId): array
    {
        $db = \Config\Database::connect();
        return $db->table('inscripciones i')
            ->select('e.*, i.fecha_inscripcion, i.estado as inscripcion_estado')
            ->join('estudiantes e', 'e.id = i.estudiante_id')
            ->where('i.grupo_id', $grupoId)
            ->where('i.estado', 'activa')
            ->orderBy('e.apellidos', 'ASC')
            ->orderBy('e.nombres', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $db = \Config\Database::connect();

        return [
            'activos' => $this->where('estado', 'activo')->countAllResults(),
            'inactivos' => $this->where('estado', 'inactivo')->countAllResults(),
            'total' => $this->countAllResults(),
            'con_horario' => $db->table('grupos g')
                ->join('grupo_horarios gh', 'gh.grupo_id = g.id')
                ->where('g.estado', 'activo')
                ->distinct()
                ->select('g.id')
                ->countAllResults(),
        ];
    }

    /**
     * Assign professor to group
     */
    public function assignProfesor(int $grupoId, int $profesorId, bool $esTitular = false): bool
    {
        $db = \Config\Database::connect();

        // Check if already assigned
        $exists = $db->table('profesor_grupos')
            ->where('grupo_id', $grupoId)
            ->where('profesor_id', $profesorId)
            ->countAllResults();

        if ($exists > 0) {
            return false;
        }

        return $db->table('profesor_grupos')->insert([
            'grupo_id' => $grupoId,
            'profesor_id' => $profesorId,
            'es_titular' => $esTitular ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Remove professor from group
     */
    public function removeProfesor(int $grupoId, int $profesorId): bool
    {
        $db = \Config\Database::connect();
        return $db->table('profesor_grupos')
            ->where('grupo_id', $grupoId)
            ->where('profesor_id', $profesorId)
            ->delete();
    }

    /**
     * Assign schedule to group
     */
    public function assignHorario(int $grupoId, int $horarioId, string $vigente_desde, ?string $vigente_hasta = null): bool
    {
        $db = \Config\Database::connect();

        return $db->table('grupo_horarios')->insert([
            'grupo_id' => $grupoId,
            'horario_id' => $horarioId,
            'vigente_desde' => $vigente_desde,
            'vigente_hasta' => $vigente_hasta,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Check if group has available spots
     */
    public function hasAvailableSpots(int $grupoId): bool
    {
        $grupo = $this->find($grupoId);
        if (!$grupo) {
            return false;
        }

        $inscritos = $this->getInscritosCount($grupoId);
        return $inscritos < $grupo['cupo_maximo'];
    }
}
