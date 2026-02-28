<?php

namespace App\Models;

use CodeIgniter\Model;

class AsistenciaModel extends Model
{
    protected $table            = 'asistencias';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sesion_id',
        'estudiante_id',
        'presente',
        'justificacion',
        'registrado_por',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get attendance for a session
     */
    public function getBySesion(int $sesionId): array
    {
        return $this->select('asistencias.*, e.nombres, e.apellidos, e.codigo')
            ->join('estudiantes e', 'e.id = asistencias.estudiante_id')
            ->where('asistencias.sesion_id', $sesionId)
            ->orderBy('e.apellidos', 'ASC')
            ->findAll();
    }

    /**
     * Get attendance history for a student
     */
    public function getByEstudiante(int $estudianteId, ?int $grupoId = null): array
    {
        $builder = $this->select('asistencias.*, sc.fecha, sc.hora_inicio, sc.hora_fin, g.nombre as grupo_nombre')
            ->join('sesiones_clase sc', 'sc.id = asistencias.sesion_id')
            ->join('grupos g', 'g.id = sc.grupo_id')
            ->where('asistencias.estudiante_id', $estudianteId);

        if ($grupoId) {
            $builder->where('sc.grupo_id', $grupoId);
        }

        return $builder->orderBy('sc.fecha', 'DESC')
            ->findAll();
    }

    /**
     * Batch create attendance records for a session
     */
    public function registrarAsistencia(int $sesionId, array $presentes, array $estudianteIds, int $registradoPor): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($estudianteIds as $estId) {
            $this->insert([
                'sesion_id'      => $sesionId,
                'estudiante_id'  => $estId,
                'presente'       => in_array($estId, $presentes) ? 1 : 0,
                'registrado_por' => $registradoPor,
            ]);
        }

        $db->transComplete();
        return $db->transStatus();
    }
}
