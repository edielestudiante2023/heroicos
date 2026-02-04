<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePazYSalvos extends Migration
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
            'estudiante_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'acudiente_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'numero' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'fecha_generacion' => [
                'type' => 'DATETIME',
            ],
            'fecha_corte' => [
                'type' => 'DATE',
            ],
            'archivo_pdf' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'enviado_email' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'motivo' => [
                'type'       => 'ENUM',
                'constraint' => ['retiro', 'solicitud', 'otro'],
                'default'    => 'solicitud',
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('numero');
        $this->forge->addKey('estudiante_id');
        $this->forge->addForeignKey('estudiante_id', 'estudiantes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('acudiente_id', 'acudientes', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('paz_y_salvos');
    }

    public function down()
    {
        $this->forge->dropTable('paz_y_salvos');
    }
}
