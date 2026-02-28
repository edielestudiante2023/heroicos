<?php

namespace App\Models;

use CodeIgniter\Model;

class PazYSalvoModel extends Model
{
    protected $table            = 'paz_y_salvos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'estudiante_id',
        'acudiente_id',
        'numero',
        'fecha_generacion',
        'fecha_corte',
        'archivo_pdf',
        'enviado_email',
        'motivo',
        'observaciones',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Generate unique paz y salvo number
     */
    public function generateNumero(): string
    {
        $year = date('Y');
        $db = \Config\Database::connect();

        $last = $db->table('paz_y_salvos')
            ->like('numero', "PYS-{$year}-")
            ->orderBy('numero', 'DESC')
            ->get()
            ->getRowArray();

        if ($last && $last['numero']) {
            $lastNumber = (int)substr($last['numero'], -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "PYS-{$year}-" . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}
