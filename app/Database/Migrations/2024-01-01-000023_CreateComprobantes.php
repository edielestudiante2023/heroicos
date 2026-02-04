<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComprobantes extends Migration
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
            'archivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'archivo_original' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'tipo_archivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'tamano' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Tamaño en bytes',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('pago_id');
        $this->forge->addForeignKey('pago_id', 'pagos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('comprobantes');
    }

    public function down()
    {
        $this->forge->dropTable('comprobantes');
    }
}
