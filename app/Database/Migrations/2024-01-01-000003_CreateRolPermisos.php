<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolPermisos extends Migration
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
            'rol_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'permiso_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['rol_id', 'permiso_id'], 'rol_permiso_unique');
        $this->forge->addForeignKey('rol_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permiso_id', 'permisos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rol_permisos');

        // Asignar todos los permisos al admin
        $permisos = $this->db->table('permisos')->get()->getResult();
        foreach ($permisos as $permiso) {
            $this->db->table('rol_permisos')->insert([
                'rol_id' => 1, // admin
                'permiso_id' => $permiso->id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Permisos para profesor
        $permisosProfesor = ['estudiantes.ver', 'categorias.ver', 'horarios.ver', 'asistencia.ver', 'asistencia.registrar', 'torneos.ver'];
        foreach ($permisosProfesor as $nombrePermiso) {
            $permiso = $this->db->table('permisos')->where('nombre', $nombrePermiso)->get()->getRow();
            if ($permiso) {
                $this->db->table('rol_permisos')->insert([
                    'rol_id' => 2, // profesor
                    'permiso_id' => $permiso->id,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('rol_permisos');
    }
}
