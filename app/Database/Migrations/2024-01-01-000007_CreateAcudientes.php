<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAcudientes extends Migration
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
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo_documento' => [
                'type'       => 'ENUM',
                'constraint' => ['CC', 'CE', 'pasaporte'],
                'default'    => 'CC',
            ],
            'numero_documento' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nombres' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'apellidos' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'telefono_alt' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'direccion' => [
                'type' => 'TEXT',
            ],
            'ciudad' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'parentesco' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Padre, Madre, Tío, Abuelo, etc',
            ],
            'ocupacion' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'autorizacion_datos' => [
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
        $this->forge->addUniqueKey('numero_documento');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('acudientes');
    }

    public function down()
    {
        $this->forge->dropTable('acudientes');
    }
}
