<?php

namespace App\Models;

use CodeIgniter\Model;

class InscripcionModel extends Model
{
    protected $table            = 'inscripciones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'estudiante_id',
        'grupo_id',
        'fecha_inscripcion',
        'fecha_fin',
        'estado',
        'motivo_cambio',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'estudiante_id'     => 'required|integer|is_not_unique[estudiantes.id]',
        'grupo_id'          => 'required|integer|is_not_unique[grupos.id]',
        'fecha_inscripcion' => 'required|valid_date',
        'estado'            => 'required|in_list[activa,finalizada]',
    ];

    /**
     * Get student's active enrollments
     */
    public function getByEstudiante(int $estudianteId): array
    {
        return $this->select('inscripciones.*, g.nombre as grupo_nombre, c.nombre as categoria_nombre')
            ->join('grupos g', 'g.id = inscripciones.grupo_id')
            ->join('categorias c', 'c.id = g.categoria_id', 'left')
            ->where('inscripciones.estudiante_id', $estudianteId)
            ->orderBy('inscripciones.estado', 'ASC')
            ->orderBy('inscripciones.fecha_inscripcion', 'DESC')
            ->findAll();
    }

    /**
     * Get group's active enrollments
     */
    public function getByGrupo(int $grupoId): array
    {
        return $this->select('inscripciones.*, e.codigo, e.nombres, e.apellidos, e.fecha_nacimiento, e.foto, e.sexo')
            ->join('estudiantes e', 'e.id = inscripciones.estudiante_id')
            ->where('inscripciones.grupo_id', $grupoId)
            ->where('inscripciones.estado', 'activa')
            ->orderBy('e.apellidos', 'ASC')
            ->orderBy('e.nombres', 'ASC')
            ->findAll();
    }

    /**
     * Check if student is already enrolled in group
     */
    public function isEnrolled(int $estudianteId, int $grupoId): bool
    {
        return $this->where('estudiante_id', $estudianteId)
            ->where('grupo_id', $grupoId)
            ->where('estado', 'activa')
            ->countAllResults() > 0;
    }

    /**
     * Enroll student in group
     */
    public function enroll(int $estudianteId, int $grupoId): bool
    {
        // Check if already enrolled
        if ($this->isEnrolled($estudianteId, $grupoId)) {
            return false;
        }

        return $this->insert([
            'estudiante_id' => $estudianteId,
            'grupo_id' => $grupoId,
            'fecha_inscripcion' => date('Y-m-d'),
            'estado' => 'activa',
        ]);
    }

    /**
     * End enrollment
     */
    public function endEnrollment(int $id, string $motivo = ''): bool
    {
        return $this->update($id, [
            'estado' => 'finalizada',
            'fecha_fin' => date('Y-m-d'),
            'motivo_cambio' => $motivo,
        ]);
    }

    /**
     * Transfer student to another group
     */
    public function transferir(int $estudianteId, int $grupoActualId, int $grupoNuevoId, string $motivo = ''): bool
    {
        // End current enrollment
        $inscripcionActual = $this->where('estudiante_id', $estudianteId)
            ->where('grupo_id', $grupoActualId)
            ->where('estado', 'activa')
            ->first();

        if ($inscripcionActual) {
            $this->endEnrollment($inscripcionActual['id'], $motivo ?: 'Transferido a otro grupo');
        }

        // Create new enrollment
        return $this->enroll($estudianteId, $grupoNuevoId);
    }
}
