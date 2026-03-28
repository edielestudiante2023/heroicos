<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlantillasClaseParticular extends Migration
{
    public function up()
    {
        $plantillas = [
            [
                'codigo'     => 'solicitud_clase_particular',
                'nombre'     => 'Solicitud de clase particular al profesor',
                'asunto'     => 'Nueva solicitud de clase particular - Academia Heroicos',
                'cuerpo_html' => '<h2>Nueva Solicitud de Clase Particular</h2><p>Hola <strong>{{nombre_profesor}}</strong>,</p><p>El acudiente <strong>{{nombre_acudiente}}</strong> ha solicitado una clase particular para el estudiante <strong>{{nombre_estudiante}}</strong>.</p><p><strong>Fecha preferida:</strong> {{fecha_preferida}}<br><strong>Hora preferida:</strong> {{hora_preferida}}<br><strong>Duracion:</strong> {{duracion}}<br><strong>Observaciones:</strong> {{observaciones}}</p><p>Ingresa a la plataforma para aceptar o rechazar esta solicitud y proponer el precio:</p><p><a href="{{url_responder}}" style="background-color:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Ver Solicitud</a></p>',
                'variables'  => '["nombre_profesor", "nombre_acudiente", "nombre_estudiante", "fecha_preferida", "hora_preferida", "duracion", "observaciones", "url_responder"]',
                'estado'     => 'activa',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'codigo'     => 'respuesta_clase_particular',
                'nombre'     => 'Respuesta del profesor a solicitud de clase particular',
                'asunto'     => 'Respuesta a tu solicitud de clase particular - Academia Heroicos',
                'cuerpo_html' => '<h2>Respuesta a Solicitud de Clase Particular</h2><p>Hola <strong>{{nombre_acudiente}}</strong>,</p><p>El profesor <strong>{{nombre_profesor}}</strong> ha respondido a tu solicitud de clase particular para <strong>{{nombre_estudiante}}</strong>.</p><p><strong>Estado:</strong> {{estado}}</p>{{seccion_precio}}<p><strong>Mensaje del profesor:</strong> {{respuesta_profesor}}</p><p>Ingresa a la plataforma para ver los detalles:</p><p><a href="{{url_ver}}" style="background-color:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Ver Detalles</a></p>',
                'variables'  => '["nombre_acudiente", "nombre_profesor", "nombre_estudiante", "estado", "seccion_precio", "respuesta_profesor", "url_ver"]',
                'estado'     => 'activa',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'codigo'     => 'precio_aceptado_clase',
                'nombre'     => 'Precio aceptado - clase particular confirmada',
                'asunto'     => 'Clase particular confirmada - Academia Heroicos',
                'cuerpo_html' => '<h2>Clase Particular Confirmada</h2><p>Hola <strong>{{nombre_profesor}}</strong>,</p><p>El acudiente <strong>{{nombre_acudiente}}</strong> ha aceptado el precio propuesto para la clase particular del estudiante <strong>{{nombre_estudiante}}</strong>.</p><p><strong>Fecha:</strong> {{fecha}}<br><strong>Hora:</strong> {{hora}}<br><strong>Precio acordado:</strong> ${{precio}}</p><p>La clase ha sido programada exitosamente.</p>',
                'variables'  => '["nombre_profesor", "nombre_acudiente", "nombre_estudiante", "fecha", "hora", "precio"]',
                'estado'     => 'activa',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($plantillas as $plantilla) {
            $exists = $this->db->table('plantillas_email')
                ->where('codigo', $plantilla['codigo'])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('plantillas_email')->insert($plantilla);
            }
        }
    }

    public function down()
    {
        $codigos = ['solicitud_clase_particular', 'respuesta_clase_particular', 'precio_aceptado_clase'];

        foreach ($codigos as $codigo) {
            $this->db->table('plantillas_email')
                ->where('codigo', $codigo)
                ->delete();
        }
    }
}
