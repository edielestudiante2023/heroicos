<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdministradores extends Migration
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
                'null'       => true,
            ],
            'es_superadmin' => [
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
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('administradores');

        // Crear usuario admin por defecto
        $this->db->table('usuarios')->insert([
            'rol_id' => 1,
            'email' => 'admin@heroicos.com',
            'password' => password_hash('Admin123*', PASSWORD_DEFAULT),
            'estado' => 'activo',
            'email_verificado' => true,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $userId = $this->db->insertID();

        $this->db->table('administradores')->insert([
            'usuario_id' => $userId,
            'nombres' => 'Administrador',
            'apellidos' => 'Principal',
            'es_superadmin' => true,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('administradores');
    }
}
