<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlantillasEmail extends Migration
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
                'constraint' => 50,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'asunto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'cuerpo_html' => [
                'type' => 'TEXT',
            ],
            'variables' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => 'Lista de variables disponibles',
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activa', 'inactiva'],
                'default'    => 'activa',
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
        $this->forge->createTable('plantillas_email');

        // Insertar plantillas por defecto
        $plantillas = [
            [
                'codigo' => 'bienvenida',
                'nombre' => 'Bienvenida al club',
                'asunto' => 'Bienvenido a Academia Heroicos',
                'cuerpo_html' => '<h1>¡Bienvenido a Academia Heroicos!</h1><p>Hola {{nombre_acudiente}},</p><p>Tu registro ha sido exitoso. Tus credenciales de acceso son:</p><p><strong>Usuario:</strong> {{email}}<br><strong>Contraseña:</strong> {{password_temporal}}</p><p>Te recomendamos cambiar tu contraseña en el primer inicio de sesión.</p><p>¡Nos vemos en la cancha!</p>',
                'variables' => '["nombre_acudiente", "email", "password_temporal"]',
            ],
            [
                'codigo' => 'nuevo_estudiante',
                'nombre' => 'Nuevo estudiante inscrito',
                'asunto' => 'Nuevo estudiante inscrito: {{nombre_estudiante}}',
                'cuerpo_html' => '<h2>Nuevo Estudiante Inscrito</h2><p><strong>Estudiante:</strong> {{nombre_estudiante}}</p><p><strong>Acudiente:</strong> {{nombre_acudiente}}</p><p><strong>Teléfono:</strong> {{telefono}}</p><p><strong>Fecha:</strong> {{fecha_inscripcion}}</p>',
                'variables' => '["nombre_estudiante", "nombre_acudiente", "telefono", "fecha_inscripcion"]',
            ],
            [
                'codigo' => 'pago_recibido',
                'nombre' => 'Confirmación de pago recibido',
                'asunto' => 'Hemos recibido tu pago - Academia Heroicos',
                'cuerpo_html' => '<h2>Pago Recibido</h2><p>Hola {{nombre_acudiente}},</p><p>Hemos recibido tu comprobante de pago por valor de <strong>${{valor}}</strong>.</p><p>Nuestro equipo administrativo lo revisará y te notificaremos cuando sea aprobado.</p><p><strong>Número de recibo:</strong> {{numero_recibo}}</p>',
                'variables' => '["nombre_acudiente", "valor", "numero_recibo"]',
            ],
            [
                'codigo' => 'pago_aprobado',
                'nombre' => 'Pago aprobado',
                'asunto' => 'Tu pago ha sido aprobado - Academia Heroicos',
                'cuerpo_html' => '<h2>Pago Aprobado</h2><p>Hola {{nombre_acudiente}},</p><p>Tu pago por valor de <strong>${{valor}}</strong> ha sido aprobado exitosamente.</p><p><strong>Número de recibo:</strong> {{numero_recibo}}</p><p>Gracias por tu puntualidad.</p>',
                'variables' => '["nombre_acudiente", "valor", "numero_recibo"]',
            ],
            [
                'codigo' => 'pago_rechazado',
                'nombre' => 'Pago rechazado',
                'asunto' => 'Problema con tu pago - Academia Heroicos',
                'cuerpo_html' => '<h2>Pago Rechazado</h2><p>Hola {{nombre_acudiente}},</p><p>Lamentamos informarte que tu pago no pudo ser verificado.</p><p><strong>Motivo:</strong> {{motivo}}</p><p>Por favor, comunícate con nosotros para resolver esta situación.</p>',
                'variables' => '["nombre_acudiente", "motivo"]',
            ],
            [
                'codigo' => 'paz_y_salvo',
                'nombre' => 'Paz y Salvo',
                'asunto' => 'Paz y Salvo - Academia Heroicos',
                'cuerpo_html' => '<h2>Paz y Salvo</h2><p>Hola {{nombre_acudiente}},</p><p>Adjunto encontrarás el paz y salvo del estudiante <strong>{{nombre_estudiante}}</strong>.</p><p>Gracias por haber sido parte de nuestra academia.</p>',
                'variables' => '["nombre_acudiente", "nombre_estudiante"]',
            ],
            [
                'codigo' => 'torneo_disponible',
                'nombre' => 'Nuevo torneo disponible',
                'asunto' => '¡Nuevo torneo! {{nombre_torneo}} - Academia Heroicos',
                'cuerpo_html' => '<h2>¡Nuevo Torneo Disponible!</h2><p>Hola {{nombre_acudiente}},</p><p>Te informamos que hay un nuevo torneo disponible para inscripción:</p><p><strong>{{nombre_torneo}}</strong></p><p><strong>Fecha:</strong> {{fecha_torneo}}<br><strong>Lugar:</strong> {{lugar}}<br><strong>Costo:</strong> ${{costo}}<br><strong>Cupos disponibles:</strong> {{cupos}}</p><p>¡No te quedes sin tu lugar!</p>',
                'variables' => '["nombre_acudiente", "nombre_torneo", "fecha_torneo", "lugar", "costo", "cupos"]',
            ],
            [
                'codigo' => 'enlace_inscripcion',
                'nombre' => 'Enlace de inscripción',
                'asunto' => 'Completa tu inscripción - Academia Heroicos',
                'cuerpo_html' => '<h2>¡Completa tu Inscripción!</h2><p>Hola {{nombre_acudiente}},</p><p>Has sido invitado a registrarte en la Academia Heroicos.</p><p>Por favor, haz clic en el siguiente enlace para completar tu inscripción:</p><p><a href="{{enlace}}" style="background-color:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Completar Inscripción</a></p><p>Este enlace expira en 48 horas.</p>',
                'variables' => '["nombre_acudiente", "enlace"]',
            ],
        ];

        foreach ($plantillas as $plantilla) {
            $plantilla['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('plantillas_email')->insert($plantilla);
        }
    }

    public function down()
    {
        $this->forge->dropTable('plantillas_email');
    }
}
