<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConceptosCobro extends Migration
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
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['unico', 'recurrente'],
                'default'    => 'unico',
            ],
            'aplica_al_inscribir' => [
                'type'    => 'BOOLEAN',
                'default' => false,
                'comment' => 'Si se genera automáticamente al inscribir estudiante',
            ],
            'orden' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activo', 'inactivo'],
                'default'    => 'activo',
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
        $this->forge->createTable('conceptos_cobro');

        // Insertar conceptos por defecto
        $conceptos = [
            ['codigo' => 'MAT', 'nombre' => 'Matrícula', 'tipo' => 'unico', 'aplica_al_inscribir' => true, 'orden' => 1],
            ['codigo' => 'UNIF', 'nombre' => 'Uniforme', 'tipo' => 'unico', 'aplica_al_inscribir' => true, 'orden' => 2],
            ['codigo' => 'MENS', 'nombre' => 'Mensualidad', 'tipo' => 'recurrente', 'aplica_al_inscribir' => true, 'orden' => 3],
            ['codigo' => 'TORN', 'nombre' => 'Torneo', 'tipo' => 'unico', 'aplica_al_inscribir' => false, 'orden' => 4],
            ['codigo' => 'CPAR', 'nombre' => 'Clase Particular', 'tipo' => 'unico', 'aplica_al_inscribir' => false, 'orden' => 5],
            ['codigo' => 'OTRO', 'nombre' => 'Otro concepto', 'tipo' => 'unico', 'aplica_al_inscribir' => false, 'orden' => 99],
        ];

        foreach ($conceptos as $concepto) {
            $concepto['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('conceptos_cobro')->insert($concepto);
        }
    }

    public function down()
    {
        $this->forge->dropTable('conceptos_cobro');
    }
}
