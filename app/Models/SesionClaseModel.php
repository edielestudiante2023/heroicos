<?php

namespace App\Models;

use CodeIgniter\Model;

class SesionClaseModel extends Model
{
    protected $table            = 'sesiones_clase';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'grupo_id',
        'profesor_id',
        'horario_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'observaciones',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get sessions by group with filters
     */
    public function getByGrupo(int $grupoId, array $filters = []): array
    {
        $builder = $this->select('sesiones_clase.*, p.nombres as profesor_nombres, p.apellidos as profesor_apellidos')
            ->join('profesores p', 'p.id = sesiones_clase.profesor_id')
            ->where('sesiones_clase.grupo_id', $grupoId);

        if (!empty($filters['fecha_desde'])) {
            $builder->where('sesiones_clase.fecha >=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $builder->where('sesiones_clase.fecha <=', $filters['fecha_hasta']);
        }

        if (!empty($filters['estado'])) {
            $builder->where('sesiones_clase.estado', $filters['estado']);
        }

        return $builder->orderBy('sesiones_clase.fecha', 'DESC')
            ->orderBy('sesiones_clase.hora_inicio', 'DESC')
            ->findAll();
    }

    /**
     * Get sessions by professor
     */
    public function getByProfesor(int $profesorId, array $filters = []): array
    {
        $builder = $this->select('sesiones_clase.*, g.nombre as grupo_nombre')
            ->join('grupos g', 'g.id = sesiones_clase.grupo_id')
            ->where('sesiones_clase.profesor_id', $profesorId);

        if (!empty($filters['fecha'])) {
            $builder->where('sesiones_clase.fecha', $filters['fecha']);
        }

        return $builder->orderBy('sesiones_clase.fecha', 'DESC')
            ->orderBy('sesiones_clase.hora_inicio', 'ASC')
            ->findAll();
    }

    /**
     * Get session with full details
     */
    public function getWithDetails(int $id): ?array
    {
        $sesion = $this->select('sesiones_clase.*, g.nombre as grupo_nombre, p.nombres as profesor_nombres, p.apellidos as profesor_apellidos')
            ->join('grupos g', 'g.id = sesiones_clase.grupo_id')
            ->join('profesores p', 'p.id = sesiones_clase.profesor_id')
            ->find($id);

        if (!$sesion) return null;

        // Get attendance records
        $db = \Config\Database::connect();
        $sesion['asistencias'] = $db->table('asistencias a')
            ->select('a.*, e.nombres, e.apellidos, e.codigo')
            ->join('estudiantes e', 'e.id = a.estudiante_id')
            ->where('a.sesion_id', $id)
            ->orderBy('e.apellidos', 'ASC')
            ->get()
            ->getResultArray();

        $sesion['total_estudiantes'] = count($sesion['asistencias']);
        $sesion['total_presentes'] = count(array_filter($sesion['asistencias'], fn($a) => $a['presente']));
        $sesion['total_ausentes'] = $sesion['total_estudiantes'] - $sesion['total_presentes'];

        return $sesion;
    }

    /**
     * Get attendance stats for a student
     */
    public function getStatsEstudiante(int $estudianteId, ?int $grupoId = null): array
    {
        $db = \Config\Database::connect();

        $builder = $db->table('asistencias a')
            ->join('sesiones_clase sc', 'sc.id = a.sesion_id')
            ->where('a.estudiante_id', $estudianteId)
            ->where('sc.estado', 'realizada');

        if ($grupoId) {
            $builder->where('sc.grupo_id', $grupoId);
        }

        $total = (clone $builder)->countAllResults();
        $presentes = (clone $builder)->where('a.presente', 1)->countAllResults();

        return [
            'total_sesiones' => $total,
            'presentes'      => $presentes,
            'ausentes'       => $total - $presentes,
            'porcentaje'     => $total > 0 ? round(($presentes / $total) * 100, 1) : 0,
        ];
    }
}
