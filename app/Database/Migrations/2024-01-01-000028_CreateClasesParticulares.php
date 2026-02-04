<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClasesParticulares extends Migration
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
            'solicitud_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'estudiante_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'profesor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'fecha' => [
                'type' => 'DATE',
            ],
            'hora_inicio' => [
                'type' => 'TIME',
            ],
            'hora_fin' => [
                'type' => 'TIME',
            ],
            'lugar' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'cargo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['programada', 'realizada', 'cancelada', 'no_asistio'],
                'default'    => 'programada',
            ],
            'observaciones' => [
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
        $this->forge->addKey('profesor_id');
        $this->forge->addKey('fecha');
        $this->forge->addForeignKey('solicitud_id', 'solicitudes_clase_particular', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('estudiante_id', 'estudiantes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('profesor_id', 'profesores', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('cargo_id', 'cargos', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('clases_particulares');
    }

    public function down()
    {
        $this->forge->dropTable('clases_particulares');
    }
}
