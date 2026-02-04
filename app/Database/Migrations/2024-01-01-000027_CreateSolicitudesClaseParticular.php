<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSolicitudesClaseParticular extends Migration
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
            'acudiente_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'estudiante_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'fecha_preferida' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'hora_preferida' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'duracion_minutos' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 60,
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'aprobada', 'rechazada', 'agendada'],
                'default'    => 'pendiente',
            ],
            'motivo_rechazo' => [
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
        $this->forge->addKey('acudiente_id');
        $this->forge->addKey('estado');
        $this->forge->addForeignKey('acudiente_id', 'acudientes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('estudiante_id', 'estudiantes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('solicitudes_clase_particular');
    }

    public function down()
    {
        $this->forge->dropTable('solicitudes_clase_particular');
    }
}
