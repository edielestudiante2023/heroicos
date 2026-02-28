<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecuperarPasswordPlantilla extends Migration
{
    public function up()
    {
        $this->db->table('plantillas_email')->insert([
            'codigo'     => 'recuperar_password',
            'nombre'     => 'Recuperación de contraseña',
            'asunto'     => 'Recupera tu contraseña - Academia Heroicos',
            'cuerpo_html' => '<h2>Recuperacion de Contraseña</h2><p>Hola {{nombre}},</p><p>Recibimos una solicitud para restablecer tu contraseña en Academia Heroicos.</p><p>Haz clic en el siguiente boton para crear una nueva contraseña:</p><p style="text-align:center; margin:25px 0;"><a href="{{enlace}}" style="background-color:#b720d2; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:600; display:inline-block;">Restablecer Contraseña</a></p><p>Si no solicitaste este cambio, puedes ignorar este correo. Tu contraseña actual seguira funcionando.</p><p style="color:#888; font-size:13px;">Este enlace expira en 1 hora.</p>',
            'variables'  => '["nombre", "enlace"]',
            'estado'     => 'activa',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->db->table('plantillas_email')
                 ->where('codigo', 'recuperar_password')
                 ->delete();
    }
}
