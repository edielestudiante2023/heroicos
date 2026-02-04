<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAsistencias extends Migration
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
            'sesion_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'estudiante_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'presente' => [
                'type'    => 'BOOLEAN',
                'default' => true,
                'comment' => 'Por defecto todos presentes, solo desmarcar ausentes',
            ],
            'justificacion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'registrado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addUniqueKey(['sesion_id', 'estudiante_id'], 'sesion_estudiante_unique');
        $this->forge->addKey('presente');
        $this->forge->addForeignKey('sesion_id', 'sesiones_clase', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('estudiante_id', 'estudiantes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('registrado_por', 'usuarios', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('asistencias');
    }

    public function down()
    {
        $this->forge->dropTable('asistencias');
    }
}
