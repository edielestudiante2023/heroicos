<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterSolicitudesClaseParticular extends Migration
{
    public function up()
    {
        $this->forge->addColumn('solicitudes_clase_particular', [
            'profesor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'estudiante_id',
            ],
            'precio_propuesto' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'estado',
            ],
            'precio_aceptado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'after'      => 'precio_propuesto',
            ],
            'respuesta_profesor' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'precio_aceptado',
            ],
        ]);

        // Add foreign key
        $this->db->query('ALTER TABLE solicitudes_clase_particular ADD CONSTRAINT fk_solicitud_profesor FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE RESTRICT ON UPDATE RESTRICT');
        $this->db->query('ALTER TABLE solicitudes_clase_particular ADD INDEX idx_profesor_id (profesor_id)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE solicitudes_clase_particular DROP FOREIGN KEY fk_solicitud_profesor');
        $this->db->query('ALTER TABLE solicitudes_clase_particular DROP INDEX idx_profesor_id');
        $this->forge->dropColumn('solicitudes_clase_particular', ['profesor_id', 'precio_propuesto', 'precio_aceptado', 'respuesta_profesor']);
    }
}
