<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTorneoInscripciones extends Migration
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
            'torneo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
            'fecha_inscripcion' => [
                'type' => 'DATETIME',
            ],
            'cargo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente_pago', 'pagado', 'cancelado'],
                'default'    => 'pendiente_pago',
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
        $this->forge->addUniqueKey(['torneo_id', 'estudiante_id'], 'torneo_estudiante_unique');
        $this->forge->addKey('estado');
        $this->forge->addForeignKey('torneo_id', 'torneos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('estudiante_id', 'estudiantes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('acudiente_id', 'acudientes', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('cargo_id', 'cargos', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('torneo_inscripciones');
    }

    public function down()
    {
        $this->forge->dropTable('torneo_inscripciones');
    }
}
