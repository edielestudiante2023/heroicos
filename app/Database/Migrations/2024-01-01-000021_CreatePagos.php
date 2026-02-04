<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagos extends Migration
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
            'numero_recibo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'fecha_pago' => [
                'type' => 'DATE',
            ],
            'fecha_registro' => [
                'type' => 'DATETIME',
            ],
            'valor_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'metodo_pago' => [
                'type'       => 'ENUM',
                'constraint' => ['transferencia', 'efectivo', 'nequi', 'daviplata', 'otro'],
                'default'    => 'transferencia',
            ],
            'banco' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'referencia_banco' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente_revision', 'aprobado', 'rechazado'],
                'default'    => 'pendiente_revision',
            ],
            'revisado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'fecha_revision' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addUniqueKey('numero_recibo');
        $this->forge->addKey('acudiente_id');
        $this->forge->addKey('estado');
        $this->forge->addKey('fecha_pago');
        $this->forge->addForeignKey('acudiente_id', 'acudientes', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('revisado_por', 'usuarios', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('pagos');
    }

    public function down()
    {
        $this->forge->dropTable('pagos');
    }
}
