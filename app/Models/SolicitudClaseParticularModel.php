<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudClaseParticularModel extends Model
{
    protected $table         = 'solicitudes_clase_particular';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'acudiente_id', 'estudiante_id', 'profesor_id',
        'fecha_preferida', 'hora_preferida', 'duracion_minutos',
        'observaciones', 'estado', 'motivo_rechazo',
        'precio_propuesto', 'precio_aceptado', 'respuesta_profesor',
    ];

    public function getByAcudiente(int $acudienteId): array
    {
        return $this->db->table('solicitudes_clase_particular s')
            ->select('s.*, e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                      p.nombres as profesor_nombres, p.apellidos as profesor_apellidos')
            ->join('estudiantes e', 'e.id = s.estudiante_id')
            ->join('profesores p', 'p.id = s.profesor_id', 'left')
            ->where('s.acudiente_id', $acudienteId)
            ->orderBy('s.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getByProfesor(int $profesorId): array
    {
        return $this->db->table('solicitudes_clase_particular s')
            ->select('s.*, e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                      a.nombres as acudiente_nombres, a.apellidos as acudiente_apellidos, a.telefono as acudiente_telefono')
            ->join('estudiantes e', 'e.id = s.estudiante_id')
            ->join('acudientes a', 'a.id = s.acudiente_id')
            ->where('s.profesor_id', $profesorId)
            ->orderBy('FIELD(s.estado, "pendiente", "aprobada", "agendada", "rechazada")')
            ->orderBy('s.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getWithDetails(int $id): ?array
    {
        return $this->db->table('solicitudes_clase_particular s')
            ->select('s.*, e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos, e.codigo as estudiante_codigo,
                      a.nombres as acudiente_nombres, a.apellidos as acudiente_apellidos, a.telefono as acudiente_telefono,
                      p.nombres as profesor_nombres, p.apellidos as profesor_apellidos, p.especialidad as profesor_especialidad,
                      ua.email as acudiente_email, up.email as profesor_email,
                      ua.id as acudiente_usuario_id, up.id as profesor_usuario_id')
            ->join('estudiantes e', 'e.id = s.estudiante_id')
            ->join('acudientes a', 'a.id = s.acudiente_id')
            ->join('profesores p', 'p.id = s.profesor_id', 'left')
            ->join('usuarios ua', 'ua.id = a.usuario_id')
            ->join('usuarios up', 'up.id = p.usuario_id', 'left')
            ->where('s.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getPendienteCountByProfesor(int $profesorId): int
    {
        return $this->where('profesor_id', $profesorId)
            ->where('estado', 'pendiente')
            ->countAllResults();
    }
}
