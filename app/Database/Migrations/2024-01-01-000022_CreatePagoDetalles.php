<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagoDetalles extends Migration
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
            'pago_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'cargo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'valor_aplicado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['pago_id', 'cargo_id']);
        $this->forge->addForeignKey('pago_id', 'pagos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('cargo_id', 'cargos', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('pago_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('pago_detalles');
    }
}
