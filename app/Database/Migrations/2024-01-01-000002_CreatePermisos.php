<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermisos extends Migration
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
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'modulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nombre');
        $this->forge->createTable('permisos');

        // Insertar permisos por defecto
        $permisos = [
            // Módulo usuarios
            ['nombre' => 'usuarios.ver', 'modulo' => 'usuarios', 'descripcion' => 'Ver listado de usuarios'],
            ['nombre' => 'usuarios.crear', 'modulo' => 'usuarios', 'descripcion' => 'Crear usuarios'],
            ['nombre' => 'usuarios.editar', 'modulo' => 'usuarios', 'descripcion' => 'Editar usuarios'],
            ['nombre' => 'usuarios.eliminar', 'modulo' => 'usuarios', 'descripcion' => 'Eliminar usuarios'],
            // Módulo estudiantes
            ['nombre' => 'estudiantes.ver', 'modulo' => 'estudiantes', 'descripcion' => 'Ver estudiantes'],
            ['nombre' => 'estudiantes.crear', 'modulo' => 'estudiantes', 'descripcion' => 'Crear estudiantes'],
            ['nombre' => 'estudiantes.editar', 'modulo' => 'estudiantes', 'descripcion' => 'Editar estudiantes'],
            ['nombre' => 'estudiantes.eliminar', 'modulo' => 'estudiantes', 'descripcion' => 'Eliminar estudiantes'],
            // Módulo acudientes
            ['nombre' => 'acudientes.ver', 'modulo' => 'acudientes', 'descripcion' => 'Ver acudientes'],
            ['nombre' => 'acudientes.crear', 'modulo' => 'acudientes', 'descripcion' => 'Crear acudientes'],
            ['nombre' => 'acudientes.editar', 'modulo' => 'acudientes', 'descripcion' => 'Editar acudientes'],
            // Módulo cartera
            ['nombre' => 'cartera.ver', 'modulo' => 'cartera', 'descripcion' => 'Ver cartera'],
            ['nombre' => 'cartera.gestionar', 'modulo' => 'cartera', 'descripcion' => 'Gestionar pagos y cargos'],
            ['nombre' => 'cartera.aprobar_pagos', 'modulo' => 'cartera', 'descripcion' => 'Aprobar/rechazar pagos'],
            // Módulo categorías
            ['nombre' => 'categorias.ver', 'modulo' => 'categorias', 'descripcion' => 'Ver categorías'],
            ['nombre' => 'categorias.gestionar', 'modulo' => 'categorias', 'descripcion' => 'Gestionar categorías y grupos'],
            // Módulo horarios
            ['nombre' => 'horarios.ver', 'modulo' => 'horarios', 'descripcion' => 'Ver horarios'],
            ['nombre' => 'horarios.gestionar', 'modulo' => 'horarios', 'descripcion' => 'Gestionar horarios'],
            // Módulo asistencia
            ['nombre' => 'asistencia.ver', 'modulo' => 'asistencia', 'descripcion' => 'Ver asistencia'],
            ['nombre' => 'asistencia.registrar', 'modulo' => 'asistencia', 'descripcion' => 'Registrar asistencia'],
            // Módulo torneos
            ['nombre' => 'torneos.ver', 'modulo' => 'torneos', 'descripcion' => 'Ver torneos'],
            ['nombre' => 'torneos.gestionar', 'modulo' => 'torneos', 'descripcion' => 'Gestionar torneos'],
            // Módulo clases particulares
            ['nombre' => 'clases_particulares.ver', 'modulo' => 'clases_particulares', 'descripcion' => 'Ver clases particulares'],
            ['nombre' => 'clases_particulares.gestionar', 'modulo' => 'clases_particulares', 'descripcion' => 'Gestionar clases particulares'],
            // Módulo configuración
            ['nombre' => 'configuracion.ver', 'modulo' => 'configuracion', 'descripcion' => 'Ver configuración'],
            ['nombre' => 'configuracion.editar', 'modulo' => 'configuracion', 'descripcion' => 'Editar configuración'],
            // Módulo reportes
            ['nombre' => 'reportes.ver', 'modulo' => 'reportes', 'descripcion' => 'Ver reportes'],
            ['nombre' => 'reportes.exportar', 'modulo' => 'reportes', 'descripcion' => 'Exportar reportes'],
        ];

        foreach ($permisos as $permiso) {
            $permiso['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('permisos')->insert($permiso);
        }
    }

    public function down()
    {
        $this->forge->dropTable('permisos');
    }
}
