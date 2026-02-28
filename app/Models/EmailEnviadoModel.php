<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailEnviadoModel extends Model
{
    protected $table            = 'emails_enviados';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'destinatario_email',
        'destinatario_nombre',
        'asunto',
        'cuerpo',
        'plantilla',
        'estado',
        'sendgrid_id',
        'error',
        'intentos',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
