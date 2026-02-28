<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlantillasAdicionales extends Migration
{
    public function up()
    {
        $plantillas = [
            [
                'codigo'     => 'recordatorio_pago',
                'nombre'     => 'Recordatorio de pago vencido',
                'asunto'     => 'Recordatorio de pago pendiente - Academia Heroicos',
                'cuerpo_html' => '<h2>Recordatorio de Pago</h2><p>Hola {{nombre_acudiente}},</p><p>Te recordamos que tienes un saldo pendiente por valor de <strong>${{valor_pendiente}}</strong> correspondiente al estudiante <strong>{{estudiante}}</strong>.</p><p><strong>Concepto:</strong> {{concepto}}<br><strong>Fecha de vencimiento:</strong> {{fecha_vencimiento}}</p><p>Te invitamos a realizar tu pago lo antes posible para mantener al dia la cuenta de tu hijo(a).</p><p>Si ya realizaste el pago, por favor ignora este mensaje.</p>',
                'variables'  => '["nombre_acudiente", "valor_pendiente", "estudiante", "concepto", "fecha_vencimiento"]',
                'estado'     => 'activa',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'codigo'     => 'inscripcion_torneo_confirmada',
                'nombre'     => 'Confirmacion de inscripcion a torneo',
                'asunto'     => 'Inscripcion confirmada: {{nombre_torneo}} - Academia Heroicos',
                'cuerpo_html' => '<h2>Inscripcion a Torneo Confirmada</h2><p>Hola {{nombre_acudiente}},</p><p>Te confirmamos que el estudiante <strong>{{nombre_estudiante}}</strong> ha sido inscrito exitosamente en el torneo:</p><p><strong>{{nombre_torneo}}</strong></p><p><strong>Fecha:</strong> {{fecha_torneo}}<br><strong>Lugar:</strong> {{lugar}}<br><strong>Costo:</strong> ${{costo}}</p><p>Recuerda estar pendiente de las indicaciones del profesor para la preparacion.</p>',
                'variables'  => '["nombre_acudiente", "nombre_estudiante", "nombre_torneo", "fecha_torneo", "lugar", "costo"]',
                'estado'     => 'activa',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'codigo'     => 'alerta_inasistencia',
                'nombre'     => 'Alerta de inasistencias consecutivas',
                'asunto'     => 'Alerta de inasistencias - {{nombre_estudiante}} - Academia Heroicos',
                'cuerpo_html' => '<h2>Alerta de Inasistencias</h2><p>Hola {{nombre_acudiente}},</p><p>Te informamos que el estudiante <strong>{{nombre_estudiante}}</strong> ha acumulado <strong>{{cantidad_inasistencias}} inasistencias consecutivas</strong>.</p><p><strong>Ultimas fechas de inasistencia:</strong><br>{{fechas}}</p><p>La asistencia regular es fundamental para el desarrollo deportivo. Si hay alguna situacion especial, por favor comunicate con el profesor o la administracion.</p>',
                'variables'  => '["nombre_acudiente", "nombre_estudiante", "cantidad_inasistencias", "fechas"]',
                'estado'     => 'activa',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'codigo'     => 'cambio_grupo',
                'nombre'     => 'Notificacion de cambio de grupo',
                'asunto'     => 'Cambio de grupo - {{nombre_estudiante}} - Academia Heroicos',
                'cuerpo_html' => '<h2>Cambio de Grupo</h2><p>Hola {{nombre_acudiente}},</p><p>Te informamos que el estudiante <strong>{{nombre_estudiante}}</strong> ha sido trasladado:</p><p><strong>Grupo anterior:</strong> {{grupo_anterior}}<br><strong>Grupo nuevo:</strong> {{grupo_nuevo}}<br><strong>Horario:</strong> {{horario_nuevo}}</p><p>Si tienes alguna pregunta, no dudes en contactarnos.</p>',
                'variables'  => '["nombre_acudiente", "nombre_estudiante", "grupo_anterior", "grupo_nuevo", "horario_nuevo"]',
                'estado'     => 'activa',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($plantillas as $plantilla) {
            // Check if already exists to avoid duplicates
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
        $codigos = ['recordatorio_pago', 'inscripcion_torneo_confirmada', 'alerta_inasistencia', 'cambio_grupo'];

        foreach ($codigos as $codigo) {
            $this->db->table('plantillas_email')
                ->where('codigo', $codigo)
                ->delete();
        }
    }
}
