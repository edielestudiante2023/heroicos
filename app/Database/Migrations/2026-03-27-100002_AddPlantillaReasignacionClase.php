<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlantillaReasignacionClase extends Migration
{
    public function up()
    {
        $exists = $this->db->table('plantillas_email')
            ->where('codigo', 'reasignacion_clase_particular')
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('plantillas_email')->insert([
                'codigo'     => 'reasignacion_clase_particular',
                'nombre'     => 'Reasignacion de clase particular a otro profesor',
                'asunto'     => 'Tu solicitud de clase particular ha sido reasignada - Academia Heroicos',
                'cuerpo_html' => '<h2>Solicitud Reasignada</h2><p>Hola <strong>{{nombre_acudiente}}</strong>,</p><p>El profesor <strong>{{nombre_profesor_original}}</strong> no puede tomar tu solicitud de clase particular para <strong>{{nombre_estudiante}}</strong>, pero te sugiere al profesor <strong>{{nombre_profesor_nuevo}}</strong>.</p><p><strong>Mensaje del profesor:</strong> {{mensaje_profesor}}</p><p>Tu solicitud ya fue enviada al nuevo profesor. Te notificaremos cuando responda.</p><p><a href="{{url_ver}}" style="background-color:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Ver mis solicitudes</a></p>',
                'variables'  => '["nombre_acudiente", "nombre_profesor_original", "nombre_profesor_nuevo", "nombre_estudiante", "mensaje_profesor", "url_ver"]',
                'estado'     => 'activa',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $this->db->table('plantillas_email')
            ->where('codigo', 'reasignacion_clase_particular')
            ->delete();
    }
}
