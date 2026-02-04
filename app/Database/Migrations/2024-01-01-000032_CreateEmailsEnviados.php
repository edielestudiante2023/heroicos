<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailsEnviados extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'destinatario_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'destinatario_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'asunto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'cuerpo' => [
                'type' => 'TEXT',
            ],
            'plantilla' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'enviado', 'fallido'],
                'default'    => 'pendiente',
            ],
            'sendgrid_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'intentos' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('estado');
        $this->forge->addKey('created_at');
        $this->forge->createTable('emails_enviados');
    }

    public function down()
    {
        $this->forge->dropTable('emails_enviados');
    }
}
