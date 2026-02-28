<?php

namespace App\Models;

use CodeIgniter\Model;

class TorneoModel extends Model
{
    protected $table            = 'torneos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'descripcion',
        'lugar',
        'fecha_evento',
        'hora_evento',
        'fecha_apertura_inscripcion',
        'fecha_cierre_inscripcion',
        'cupo_maximo',
        'costo',
        'categoria_id',
        'estado',
        'imagen',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nombre'       => 'required|min_length[3]|max_length[200]',
        'fecha_evento' => 'required|valid_date',
        'cupo_maximo'  => 'required|integer|greater_than[0]',
        'costo'        => 'required|decimal',
    ];

    /**
     * Get all tournaments with filters
     */
    public function getFiltered(array $filters = []): array
    {
        $builder = $this->select('torneos.*, cat.nombre as categoria_nombre')
            ->join('categorias cat', 'cat.id = torneos.categoria_id', 'left');

        if (!empty($filters['estado'])) {
            $builder->where('torneos.estado', $filters['estado']);
        }

        if (!empty($filters['categoria_id'])) {
            $builder->where('torneos.categoria_id', $filters['categoria_id']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('torneos.nombre', $filters['search'])
                ->orLike('torneos.lugar', $filters['search'])
                ->groupEnd();
        }

        $torneos = $builder->orderBy('torneos.fecha_evento', 'DESC')->findAll();

        // Add enrollment count
        foreach ($torneos as &$torneo) {
            $torneo['inscritos'] = $this->getInscritosCount($torneo['id']);
            $torneo['cupos_disponibles'] = max(0, $torneo['cupo_maximo'] - $torneo['inscritos']);
        }

        return $torneos;
    }

    /**
     * Get tournament with full details
     */
    public function getWithDetails(int $id): ?array
    {
        $torneo = $this->select('torneos.*, cat.nombre as categoria_nombre')
            ->join('categorias cat', 'cat.id = torneos.categoria_id', 'left')
            ->find($id);

        if (!$torneo) return null;

        $torneo['inscritos'] = $this->getInscritosCount($id);
        $torneo['cupos_disponibles'] = max(0, $torneo['cupo_maximo'] - $torneo['inscritos']);

        // Get enrolled students
        $db = \Config\Database::connect();
        $torneo['inscripciones'] = $db->table('torneo_inscripciones ti')
            ->select('ti.*, e.nombres, e.apellidos, e.codigo as estudiante_codigo, a.nombres as acudiente_nombres, a.apellidos as acudiente_apellidos')
            ->join('estudiantes e', 'e.id = ti.estudiante_id')
            ->join('acudientes a', 'a.id = ti.acudiente_id')
            ->where('ti.torneo_id', $id)
            ->orderBy('ti.fecha_inscripcion', 'DESC')
            ->get()
            ->getResultArray();

        return $torneo;
    }

    /**
     * Get enrolled students count (excluding cancelled)
     */
    public function getInscritosCount(int $torneoId): int
    {
        $db = \Config\Database::connect();
        return $db->table('torneo_inscripciones')
            ->where('torneo_id', $torneoId)
            ->where('estado !=', 'cancelado')
            ->countAllResults();
    }

    /**
     * Check if spots available
     */
    public function hasCuposDisponibles(int $torneoId): bool
    {
        $torneo = $this->find($torneoId);
        if (!$torneo) return false;

        return $this->getInscritosCount($torneoId) < $torneo['cupo_maximo'];
    }

    /**
     * Get stats
     */
    public function getStats(): array
    {
        return [
            'total'       => $this->countAllResults(),
            'programados' => $this->where('estado', 'programado')->countAllResults(),
            'abiertos'    => $this->where('estado', 'inscripciones_abiertas')->countAllResults(),
            'en_curso'    => $this->where('estado', 'en_curso')->countAllResults(),
            'finalizados' => $this->where('estado', 'finalizado')->countAllResults(),
        ];
    }
}
