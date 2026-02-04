<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoria extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'accion' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tabla' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'registro_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'datos_anteriores' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'datos_nuevos' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('accion');
        $this->forge->addKey('tabla');
        $this->forge->addKey('created_at');
        $this->forge->createTable('auditoria');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria');
    }
}
