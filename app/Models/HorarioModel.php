<?php

namespace App\Models;

use CodeIgniter\Model;

class HorarioModel extends Model
{
    protected $table            = 'horarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'lugar',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nombre'      => 'required|min_length[3]|max_length[100]',
        'dia_semana'  => 'required|integer|greater_than[0]|less_than[8]',
        'hora_inicio' => 'required',
        'hora_fin'    => 'required',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre del horario es requerido',
        ],
        'dia_semana' => [
            'required' => 'El día de la semana es requerido',
            'greater_than' => 'Seleccione un día válido',
            'less_than' => 'Seleccione un día válido',
        ],
    ];

    // Days of week
    public static array $dias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    /**
     * Get all schedules with day names
     */
    public function getAll(): array
    {
        $horarios = $this->orderBy('dia_semana', 'ASC')
            ->orderBy('hora_inicio', 'ASC')
            ->findAll();

        foreach ($horarios as &$h) {
            $h['dia_nombre'] = self::$dias[$h['dia_semana']] ?? '';
        }

        return $horarios;
    }

    /**
     * Get schedules filtered
     */
    public function getFiltered(array $filters = []): array
    {
        $builder = $this;

        if (!empty($filters['search'])) {
            $builder = $builder->groupStart()
                ->like('nombre', $filters['search'])
                ->orLike('lugar', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['dia_semana'])) {
            $builder = $builder->where('dia_semana', $filters['dia_semana']);
        }

        $horarios = $builder->orderBy('dia_semana', 'ASC')
            ->orderBy('hora_inicio', 'ASC')
            ->findAll();

        foreach ($horarios as &$h) {
            $h['dia_nombre'] = self::$dias[$h['dia_semana']] ?? '';
        }

        return $horarios;
    }

    /**
     * Get schedule with groups assigned
     */
    public function getWithGrupos(int $id): ?array
    {
        $horario = $this->find($id);
        if (!$horario) {
            return null;
        }

        $horario['dia_nombre'] = self::$dias[$horario['dia_semana']] ?? '';

        // Get groups using this schedule
        $db = \Config\Database::connect();
        $horario['grupos'] = $db->table('grupo_horarios gh')
            ->select('g.id, g.nombre, c.nombre as categoria_nombre, gh.vigente_desde, gh.vigente_hasta')
            ->join('grupos g', 'g.id = gh.grupo_id')
            ->join('categorias c', 'c.id = g.categoria_id', 'left')
            ->where('gh.horario_id', $id)
            ->where('(gh.vigente_hasta IS NULL OR gh.vigente_hasta >= CURDATE())')
            ->get()
            ->getResultArray();

        return $horario;
    }

    /**
     * Format time range
     */
    public function formatTimeRange(string $inicio, string $fin): string
    {
        return substr($inicio, 0, 5) . ' - ' . substr($fin, 0, 5);
    }

    /**
     * Get schedules available for a group (not already assigned)
     */
    public function getAvailableForGrupo(int $grupoId): array
    {
        $db = \Config\Database::connect();

        // Get currently assigned schedules
        $assigned = $db->table('grupo_horarios')
            ->select('horario_id')
            ->where('grupo_id', $grupoId)
            ->where('(vigente_hasta IS NULL OR vigente_hasta >= CURDATE())')
            ->get()
            ->getResultArray();

        $assignedIds = array_column($assigned, 'horario_id');

        $builder = $this;
        if (!empty($assignedIds)) {
            $builder = $builder->whereNotIn('id', $assignedIds);
        }

        $horarios = $builder->orderBy('dia_semana', 'ASC')
            ->orderBy('hora_inicio', 'ASC')
            ->findAll();

        foreach ($horarios as &$h) {
            $h['dia_nombre'] = self::$dias[$h['dia_semana']] ?? '';
        }

        return $horarios;
    }

    /**
     * Get schedule summary (for display)
     */
    public function getSummary(int $id): string
    {
        $h = $this->find($id);
        if (!$h) {
            return '';
        }

        $dia = self::$dias[$h['dia_semana']] ?? '';
        return sprintf('%s: %s - %s',
            $dia,
            substr($h['hora_inicio'], 0, 5),
            substr($h['hora_fin'], 0, 5)
        );
    }

    /**
     * Check for schedule conflicts
     */
    public function hasConflict(int $diaSemana, string $horaInicio, string $horaFin, ?int $excludeId = null): bool
    {
        $builder = $this->where('dia_semana', $diaSemana)
            ->groupStart()
                // New schedule starts during existing
                ->groupStart()
                    ->where('hora_inicio <=', $horaInicio)
                    ->where('hora_fin >', $horaInicio)
                ->groupEnd()
                // New schedule ends during existing
                ->orGroupStart()
                    ->where('hora_inicio <', $horaFin)
                    ->where('hora_fin >=', $horaFin)
                ->groupEnd()
                // New schedule contains existing
                ->orGroupStart()
                    ->where('hora_inicio >=', $horaInicio)
                    ->where('hora_fin <=', $horaFin)
                ->groupEnd()
            ->groupEnd();

        if ($excludeId) {
            $builder = $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }
}
