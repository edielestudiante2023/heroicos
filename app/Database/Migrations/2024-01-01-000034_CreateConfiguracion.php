<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConfiguracion extends Migration
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
            'clave' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'valor' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'string',
                'comment'    => 'string, int, bool, json, text',
            ],
            'grupo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'general',
            ],
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'editable' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('clave');
        $this->forge->addKey('grupo');
        $this->forge->createTable('configuracion');

        // Insertar configuraciones por defecto
        $configs = [
            // Información de la academia
            ['clave' => 'academia_nombre', 'valor' => 'Academia Heroicos', 'tipo' => 'string', 'grupo' => 'academia', 'descripcion' => 'Nombre de la academia'],
            ['clave' => 'academia_slogan', 'valor' => 'Formando campeones del mañana', 'tipo' => 'string', 'grupo' => 'academia', 'descripcion' => 'Slogan de la academia'],
            ['clave' => 'academia_direccion', 'valor' => '', 'tipo' => 'string', 'grupo' => 'academia', 'descripcion' => 'Dirección física'],
            ['clave' => 'academia_telefono', 'valor' => '', 'tipo' => 'string', 'grupo' => 'academia', 'descripcion' => 'Teléfono de contacto'],
            ['clave' => 'academia_email', 'valor' => 'info@heroicos.com', 'tipo' => 'string', 'grupo' => 'academia', 'descripcion' => 'Email de contacto'],
            ['clave' => 'academia_logo', 'valor' => '', 'tipo' => 'string', 'grupo' => 'academia', 'descripcion' => 'Ruta del logo'],
            ['clave' => 'academia_nit', 'valor' => '', 'tipo' => 'string', 'grupo' => 'academia', 'descripcion' => 'NIT de la academia'],

            // Configuración financiera
            ['clave' => 'dia_vencimiento_mensualidad', 'valor' => '5', 'tipo' => 'int', 'grupo' => 'financiero', 'descripcion' => 'Día del mes en que vence la mensualidad'],
            ['clave' => 'dias_gracia', 'valor' => '5', 'tipo' => 'int', 'grupo' => 'financiero', 'descripcion' => 'Días de gracia después del vencimiento'],
            ['clave' => 'banco_nombre', 'valor' => '', 'tipo' => 'string', 'grupo' => 'financiero', 'descripcion' => 'Nombre del banco para pagos'],
            ['clave' => 'banco_tipo_cuenta', 'valor' => 'Ahorros', 'tipo' => 'string', 'grupo' => 'financiero', 'descripcion' => 'Tipo de cuenta bancaria'],
            ['clave' => 'banco_numero_cuenta', 'valor' => '', 'tipo' => 'string', 'grupo' => 'financiero', 'descripcion' => 'Número de cuenta bancaria'],
            ['clave' => 'banco_titular', 'valor' => '', 'tipo' => 'string', 'grupo' => 'financiero', 'descripcion' => 'Titular de la cuenta'],
            ['clave' => 'nequi_numero', 'valor' => '', 'tipo' => 'string', 'grupo' => 'financiero', 'descripcion' => 'Número Nequi para pagos'],
            ['clave' => 'daviplata_numero', 'valor' => '', 'tipo' => 'string', 'grupo' => 'financiero', 'descripcion' => 'Número Daviplata para pagos'],

            // Configuración de notificaciones
            ['clave' => 'notificar_nuevo_estudiante', 'valor' => '1', 'tipo' => 'bool', 'grupo' => 'notificaciones', 'descripcion' => 'Notificar cuando se inscribe nuevo estudiante'],
            ['clave' => 'notificar_pago_recibido', 'valor' => '1', 'tipo' => 'bool', 'grupo' => 'notificaciones', 'descripcion' => 'Notificar cuando se recibe un pago'],
            ['clave' => 'emails_admin', 'valor' => '[]', 'tipo' => 'json', 'grupo' => 'notificaciones', 'descripcion' => 'Emails de administradores para notificaciones'],

            // Política de datos
            ['clave' => 'politica_datos', 'valor' => 'En cumplimiento de la Ley 1581 de 2012 y el Decreto 1377 de 2013, la Academia Heroicos como responsable del tratamiento de datos personales, solicita su autorización para recolectar, almacenar, usar, circular, suprimir, y en general, tratar sus datos personales de acuerdo con la política de tratamiento de datos personales.', 'tipo' => 'text', 'grupo' => 'legal', 'descripcion' => 'Política de tratamiento de datos personales'],
            ['clave' => 'terminos_condiciones', 'valor' => '', 'tipo' => 'text', 'grupo' => 'legal', 'descripcion' => 'Términos y condiciones'],

            // Configuración del sistema
            ['clave' => 'mantenimiento', 'valor' => '0', 'tipo' => 'bool', 'grupo' => 'sistema', 'descripcion' => 'Modo mantenimiento activo'],
            ['clave' => 'registro_abierto', 'valor' => '1', 'tipo' => 'bool', 'grupo' => 'sistema', 'descripcion' => 'Permitir nuevos registros'],
        ];

        foreach ($configs as $config) {
            $config['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('configuracion')->insert($config);
        }
    }

    public function down()
    {
        $this->forge->dropTable('configuracion');
    }
}
