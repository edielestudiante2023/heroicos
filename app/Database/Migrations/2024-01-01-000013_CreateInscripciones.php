<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInscripciones extends Migration
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
            'grupo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'fecha_inscripcion' => [
                'type' => 'DATE',
            ],
            'fecha_fin' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activa', 'finalizada'],
                'default'    => 'activa',
            ],
            'motivo_cambio' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('estudiante_id');
        $this->forge->addKey('grupo_id');
        $this->forge->addKey('estado');
        $this->forge->addForeignKey('estudiante_id', 'estudiantes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('grupo_id', 'grupos', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('inscripciones');
    }

    public function down()
    {
        $this->forge->dropTable('inscripciones');
    }
}
