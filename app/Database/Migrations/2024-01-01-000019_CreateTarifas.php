<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTarifas extends Migration
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
            'concepto_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'categoria_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL = aplica a todas las categorías',
            ],
            'periodo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'vigente_desde' => [
                'type' => 'DATE',
            ],
            'vigente_hasta' => [
                'type' => 'DATE',
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
        $this->forge->addKey(['concepto_id', 'categoria_id', 'periodo_id']);
        $this->forge->addForeignKey('concepto_id', 'conceptos_cobro', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('categoria_id', 'categorias', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('periodo_id', 'periodos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tarifas');
    }

    public function down()
    {
        $this->forge->dropTable('tarifas');
    }
}
