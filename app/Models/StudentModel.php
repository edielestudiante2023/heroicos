<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table            = 'estudiantes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'acudiente_id',
        'codigo',
        'foto',
        'nombres',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'telefono',
        'eps',
        'grupo_sanguineo',
        'talla_camiseta',
        'talla_pantaloneta',
        'talla_medias',
        'posicion',
        'pie_dominante',
        'alergias',
        'condiciones_medicas',
        'medicamentos',
        'contacto_emergencia',
        'telefono_emergencia',
        'autorizacion_datos_menor',
        'fecha_autorizacion',
        'ip_autorizacion',
        'estado',
        'fecha_ingreso',
        'fecha_retiro',
        'motivo_retiro'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nombres'          => 'required|min_length[2]|max_length[100]',
        'apellidos'        => 'required|min_length[2]|max_length[100]',
        'fecha_nacimiento' => 'required|valid_date',
        'tipo_documento'   => 'required|in_list[TI,RC,pasaporte,otro]',
        'numero_documento' => 'required|max_length[20]',
        'sexo'             => 'required|in_list[M,F]',
    ];

    protected $validationMessages = [
        'nombres' => [
            'required'   => 'El nombre es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 2 caracteres.',
        ],
        'apellidos' => [
            'required'   => 'El apellido es obligatorio.',
            'min_length' => 'El apellido debe tener al menos 2 caracteres.',
        ],
        'fecha_nacimiento' => [
            'required'   => 'La fecha de nacimiento es obligatoria.',
            'valid_date' => 'La fecha de nacimiento no es válida.',
        ],
    ];

    /**
     * Get student with acudiente info
     */
    public function getWithAcudiente(int $id): ?array
    {
        $student = $this->find($id);

        if (!$student) {
            return null;
        }

        if ($student['acudiente_id']) {
            $db = \Config\Database::connect();
            $acudiente = $db->table('acudientes')
                            ->select('acudientes.*, usuarios.email')
                            ->join('usuarios', 'usuarios.id = acudientes.usuario_id')
                            ->where('acudientes.id', $student['acudiente_id'])
                            ->get()
                            ->getRowArray();

            if ($acudiente) {
                $student['acudiente'] = $acudiente;
            }
        }

        return $student;
    }

    /**
     * Get all students with filters
     */
    public function getFiltered(array $filters = []): array
    {
        $builder = $this->builder();
        $builder->select('estudiantes.*, acudientes.nombres as acudiente_nombres, acudientes.apellidos as acudiente_apellidos');
        $builder->join('acudientes', 'acudientes.id = estudiantes.acudiente_id', 'left');

        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('estudiantes.nombres', $filters['search'])
                    ->orLike('estudiantes.apellidos', $filters['search'])
                    ->orLike('estudiantes.numero_documento', $filters['search'])
                    ->groupEnd();
        }

        if (!empty($filters['estado'])) {
            $builder->where('estudiantes.estado', $filters['estado']);
        }

        if (!empty($filters['categoria'])) {
            // Filter by birth year (category)
            $builder->where('YEAR(estudiantes.fecha_nacimiento)', $filters['categoria']);
        }

        if (!empty($filters['acudiente_id'])) {
            $builder->where('estudiantes.acudiente_id', $filters['acudiente_id']);
        }

        return $builder->orderBy('estudiantes.apellidos', 'ASC')
                       ->orderBy('estudiantes.nombres', 'ASC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Get students by acudiente
     */
    public function getByAcudiente(int $acudienteId): array
    {
        return $this->where('acudiente_id', $acudienteId)
                    ->where('estado', 'activo')
                    ->orderBy('nombres', 'ASC')
                    ->findAll();
    }

    /**
     * Calculate category based on birth year
     */
    public function getCategoria(string $fechaNacimiento): string
    {
        $birthYear = date('Y', strtotime($fechaNacimiento));
        return $birthYear;
    }

    /**
     * Get available categories (birth years)
     */
    public function getAvailableCategories(): array
    {
        $db = \Config\Database::connect();
        $result = $db->table('estudiantes')
                     ->select('YEAR(fecha_nacimiento) as year')
                     ->where('estado', 'activo')
                     ->groupBy('YEAR(fecha_nacimiento)')
                     ->orderBy('year', 'DESC')
                     ->get()
                     ->getResultArray();

        return array_column($result, 'year');
    }

    /**
     * Get full name
     */
    public function getFullName(array $student): string
    {
        return trim($student['nombres'] . ' ' . $student['apellidos']);
    }

    /**
     * Calculate age
     */
    public function getAge(string $fechaNacimiento): int
    {
        $birthDate = new \DateTime($fechaNacimiento);
        $today = new \DateTime();
        return $birthDate->diff($today)->y;
    }

    /**
     * Generate unique student code
     */
    public function generateCode(): string
    {
        $year = date('Y');
        $db = \Config\Database::connect();

        // Get last code for this year
        $lastStudent = $db->table('estudiantes')
                          ->like('codigo', "EST{$year}")
                          ->orderBy('codigo', 'DESC')
                          ->get()
                          ->getRowArray();

        if ($lastStudent && $lastStudent['codigo']) {
            $lastNumber = (int)substr($lastStudent['codigo'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "EST{$year}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $db = \Config\Database::connect();

        return [
            'total'    => $this->where('estado', 'activo')->countAllResults(),
            'activos'  => $this->where('estado', 'activo')->countAllResults(),
            'inactivos'=> $this->where('estado', 'inactivo')->countAllResults(),
            'retirados'=> $this->where('estado', 'retirado')->countAllResults(),
        ];
    }
}
