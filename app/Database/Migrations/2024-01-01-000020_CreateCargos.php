<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCargos extends Migration
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
            'concepto_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'periodo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'mes' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'null'       => true,
                'comment'    => 'Para mensualidades: 1-12',
            ],
            'anio' => [
                'type' => 'YEAR',
                'null' => true,
            ],
            'valor_original' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'valor_pagado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'saldo_pendiente' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'fecha_vencimiento' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'parcial', 'pagado', 'anulado'],
                'default'    => 'pendiente',
            ],
            'generado_auto' => [
                'type'    => 'BOOLEAN',
                'default' => false,
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
        $this->forge->addKey('estado');
        $this->forge->addKey(['anio', 'mes']);
        $this->forge->addForeignKey('estudiante_id', 'estudiantes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('concepto_id', 'conceptos_cobro', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('periodo_id', 'periodos', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('cargos');
    }

    public function down()
    {
        $this->forge->dropTable('cargos');
    }
}
