<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTorneos extends Migration
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
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'lugar' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'fecha_evento' => [
                'type' => 'DATE',
            ],
            'hora_evento' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'fecha_apertura_inscripcion' => [
                'type' => 'DATETIME',
            ],
            'fecha_cierre_inscripcion' => [
                'type' => 'DATETIME',
            ],
            'cupo_maximo' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'costo' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'categoria_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL = todas las categorías',
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['programado', 'inscripciones_abiertas', 'inscripciones_cerradas', 'en_curso', 'finalizado', 'cancelado'],
                'default'    => 'programado',
            ],
            'imagen' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey('estado');
        $this->forge->addKey('fecha_evento');
        $this->forge->addForeignKey('categoria_id', 'categorias', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('torneos');
    }

    public function down()
    {
        $this->forge->dropTable('torneos');
    }
}
