<?php

namespace App\Models;

use CodeIgniter\Model;

class EstudianteHistorialDeportivoModel extends Model
{
    protected $table         = 'estudiante_historial_deportivo';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'estudiante_id', 'academia_anterior', 'periodo',
        'torneos_participados', 'logros', 'posicion_juego',
        'observaciones',
    ];

    public function getByEstudiante(int $estudianteId): array
    {
        return $this->where('estudiante_id', $estudianteId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
