<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEstudiantes extends Migration
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
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nombres' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'apellidos' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tipo_documento' => [
                'type'       => 'ENUM',
                'constraint' => ['TI', 'RC', 'pasaporte', 'otro'],
                'default'    => 'TI',
            ],
            'numero_documento' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'fecha_nacimiento' => [
                'type' => 'DATE',
            ],
            'sexo' => [
                'type'       => 'ENUM',
                'constraint' => ['M', 'F'],
            ],
            'direccion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'eps' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'grupo_sanguineo' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'alergias' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'condiciones_medicas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'medicamentos' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'contacto_emergencia' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'telefono_emergencia' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'autorizacion_datos_menor' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'fecha_autorizacion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'ip_autorizacion' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activo', 'inactivo', 'retirado'],
                'default'    => 'activo',
            ],
            'fecha_ingreso' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'fecha_retiro' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'motivo_retiro' => [
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
        $this->forge->addUniqueKey('codigo');
        $this->forge->addKey('acudiente_id');
        $this->forge->addKey('estado');
        $this->forge->addKey('fecha_nacimiento');
        $this->forge->addForeignKey('acudiente_id', 'acudientes', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('estudiantes');
    }

    public function down()
    {
        $this->forge->dropTable('estudiantes');
    }
}
