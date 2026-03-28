<?php

namespace App\Models;

use CodeIgniter\Model;

class ClaseParticularModel extends Model
{
    protected $table         = 'clases_particulares';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'solicitud_id', 'estudiante_id', 'profesor_id',
        'fecha', 'hora_inicio', 'hora_fin', 'lugar',
        'cargo_id', 'estado', 'observaciones',
    ];

    public function createFromSolicitud(array $solicitud, int $cargoId): int|false
    {
        $horaInicio = $solicitud['hora_preferida'];
        $horaFin = date('H:i:s', strtotime($horaInicio) + ($solicitud['duracion_minutos'] * 60));

        return $this->insert([
            'solicitud_id'  => $solicitud['id'],
            'estudiante_id' => $solicitud['estudiante_id'],
            'profesor_id'   => $solicitud['profesor_id'],
            'fecha'         => $solicitud['fecha_preferida'],
            'hora_inicio'   => $horaInicio,
            'hora_fin'      => $horaFin,
            'cargo_id'      => $cargoId,
            'estado'        => 'programada',
        ]);
    }
}
