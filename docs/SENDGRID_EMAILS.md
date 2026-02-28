# SendGrid - Mapa de Emails del Sistema

## Configuracion

| Variable | Valor |
|----------|-------|
| API Key | Configurada en `.env` (sendgrid.apiKey) |
| Remitente email | notificacion.cycloidtalent@cycloidtalent.com |
| Remitente nombre | Heroicos Futbol Club |
| Servicio | `app/Libraries/SendGridService.php` |
| Log de envios | Tabla `emails_enviados` |
| Plantillas | Tabla `plantillas_email` |
| Template HTML base | `app/Views/emails/base.php` |

---

## Inventario de Emails

| # | Plantilla | Accion que lo dispara | Controlador / Metodo | Estado | Destinatario (email) |
|---|-----------|----------------------|---------------------|--------|---------------------|
| 1 | `recuperar_password` | Usuario solicita "Olvido su contrasena" | `AuthController::sendResetEmail()` | IMPLEMENTADO | El email del usuario que lo solicito |
| 2 | `enlace_inscripcion` | Profesor genera enlace de inscripcion para un acudiente nuevo | `Profesor\InscriptionController::generate()` | IMPLEMENTADO | Email del acudiente nuevo (lo ingresa el profesor en un formulario) |
| 3 | `bienvenida` | Acudiente completa registro (se le envian credenciales temporales) | `RegistroController::store()` -> `enviarEmailBienvenida()` | IMPLEMENTADO | Email del acudiente recien registrado |
| 4 | `nuevo_estudiante` | Nuevo estudiante se inscribe en la academia | `RegistroController::store()` -> `enviarEmailNuevoEstudiante()` | IMPLEMENTADO | Todos los admins + el profesor que genero el enlace |
| 5 | `pago_recibido` | Se registra un pago (admin registra pago en el sistema) | `Admin\PaymentController::store()` -> `notificarPagoRecibido()` | IMPLEMENTADO | Todos los admins activos |
| 6 | `pago_aprobado` | Admin aprueba un pago pendiente de revision | `Admin\PaymentController::approve()` -> `notificarPagoAprobado()` | IMPLEMENTADO | Email del acudiente dueno del pago |
| 7 | `pago_rechazado` | Admin rechaza un pago con motivo | `Admin\PaymentController::reject()` -> `notificarPagoRechazado()` | IMPLEMENTADO | Email del acudiente dueno del pago (con motivo del rechazo) |
| 8 | `paz_y_salvo` | Acudiente solicita paz y salvo (saldo = 0) | `Acudiente\PazYSalvoController::solicitar()` | IMPLEMENTADO | Email del acudiente que lo solicita (con PDF adjunto) |
| 9 | `torneo_disponible` | Admin abre inscripciones de un torneo | `Admin\TournamentController::changeStatus()` -> `notificarTorneoDisponible()` | IMPLEMENTADO | Todos los acudientes con estudiantes activos en la categoria del torneo |
| 10 | `inscripcion_torneo_confirmada` | Admin inscribe estudiante en torneo | `Admin\TournamentController::enrollStudent()` -> `notificarInscripcionTorneo()` | IMPLEMENTADO | Email del acudiente del estudiante inscrito |
| 11 | `recordatorio_pago` | Admin envia recordatorios masivos | `Admin\CarteraController::enviarRecordatorios()` | IMPLEMENTADO | Acudientes con cargos vencidos |
| 12 | `alerta_inasistencia` | Profesor guarda asistencia (3+ inasistencias consecutivas) | `Profesor\AttendanceController::save()` -> `verificarInasistenciasConsecutivas()` | IMPLEMENTADO | Email del acudiente del estudiante ausente |
| 13 | `cambio_grupo` | Admin inscribe estudiante en grupo nuevo (teniendo otro activo) | `Admin\GroupController::enrollStudent()` -> `notificarCambioGrupo()` | IMPLEMENTADO | Email del acudiente del estudiante |

---

## Detalle por Email

### 1. recuperar_password (IMPLEMENTADO)

- **Archivo:** `app/Controllers/AuthController.php` metodo `sendResetEmail()`
- **Trigger:** POST `/forgot-password`
- **Destinatario:** El usuario que solicito la recuperacion
- **Variables:**
  - `nombre` - Nombre del usuario
  - `enlace` - URL con token para restablecer contrasena

### 2. enlace_inscripcion (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Profesor/InscriptionController.php` metodo `generate()`
- **Trigger:** POST `profesor/inscripcion/generar`
- **Destinatario:** Email del acudiente (ingresado por el profesor)
- **Variables:**
  - `nombre_acudiente` - Nombre del acudiente
  - `enlace` - URL `/registro/{token}` (expira en 48h)

### 3. bienvenida (IMPLEMENTADO)

- **Archivo:** `app/Controllers/RegistroController.php` metodo `store()` -> `enviarEmailBienvenida()`
- **Trigger:** Acudiente completa el formulario de registro publico
- **Destinatario:** Email del acudiente recien registrado
- **Variables:**
  - `nombre_acudiente` - Nombre completo del acudiente
  - `email` - Email de acceso
  - `password_temporal` - Contrasena generada automaticamente

### 4. nuevo_estudiante (IMPLEMENTADO)

- **Archivo:** `app/Controllers/RegistroController.php` metodo `store()` -> `enviarEmailNuevoEstudiante()`
- **Trigger:** Se completa la inscripcion de un nuevo estudiante
- **Destinatarios:** Todos los admins activos + el profesor que genero el enlace
- **Variables:**
  - `nombre_estudiante` - Nombre completo del estudiante
  - `nombre_acudiente` - Nombre del acudiente
  - `telefono` - Telefono del acudiente
  - `fecha_inscripcion` - Fecha de inscripcion

### 5. pago_recibido (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Admin/PaymentController.php` metodo `store()` -> `notificarPagoRecibido()`
- **Trigger:** Admin registra un pago en el sistema
- **Destinatario:** Todos los usuarios con rol admin (rol_id = 1) y estado activo
- **Variables:**
  - `nombre_acudiente` - Nombre del acudiente
  - `valor` - Valor formateado del pago
  - `numero_recibo` - Numero de recibo generado

### 6. pago_aprobado (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Admin/PaymentController.php` metodo `approve()` -> `notificarPagoAprobado()`
- **Trigger:** Admin hace clic en "Aprobar" en la revision de un pago
- **Destinatario:** Email del acudiente
- **Variables:**
  - `nombre_acudiente` - Nombre completo del acudiente
  - `valor` - Valor formateado del pago
  - `numero_recibo` - Numero de recibo

### 7. pago_rechazado (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Admin/PaymentController.php` metodo `reject()` -> `notificarPagoRechazado()`
- **Trigger:** Admin hace clic en "Rechazar" e ingresa motivo
- **Destinatario:** Email del acudiente
- **Variables:**
  - `nombre_acudiente` - Nombre completo del acudiente
  - `motivo` - Motivo de rechazo ingresado por el admin

### 8. paz_y_salvo (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Acudiente/PazYSalvoController.php` metodo `solicitar()`
- **Trigger:** Acudiente solicita paz y salvo (saldo = 0)
- **Destinatario:** Email del acudiente
- **Variables:**
  - `nombre_acudiente` - Nombre del acudiente
  - `nombre_estudiante` - Nombre del estudiante
- **Adjunto:** PDF del paz y salvo generado con TCPDF
- **Metodo SendGrid:** `enviarConAdjunto()`

### 9. torneo_disponible (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Admin/TournamentController.php` metodo `changeStatus()` -> `notificarTorneoDisponible()`
- **Trigger:** Admin cambia estado del torneo a `inscripciones_abiertas`
- **Destinatario:** Acudientes con estudiantes activos en la categoria del torneo (o todos si no tiene categoria)
- **Variables:**
  - `nombre_acudiente`, `nombre_torneo`, `fecha_torneo`, `lugar`, `costo`, `cupos`

### 10. inscripcion_torneo_confirmada (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Admin/TournamentController.php` metodo `enrollStudent()` -> `notificarInscripcionTorneo()`
- **Trigger:** Admin inscribe un estudiante en un torneo
- **Destinatario:** Email del acudiente del estudiante inscrito
- **Variables:**
  - `nombre_acudiente`, `nombre_estudiante`, `nombre_torneo`, `fecha_torneo`, `lugar`, `costo`

### 11. recordatorio_pago (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Admin/CarteraController.php` metodo `enviarRecordatorios()`
- **Trigger:** Admin hace clic en "Enviar Recordatorios" en la vista de cartera
- **Destinatario:** Acudientes con cargos vencidos (agrupados, un email por acudiente)
- **Variables:**
  - `nombre_acudiente`, `valor_pendiente`, `estudiante`, `concepto`, `fecha_vencimiento`

### 12. alerta_inasistencia (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Profesor/AttendanceController.php` metodo `save()` -> `verificarInasistenciasConsecutivas()`
- **Trigger:** Despues de guardar asistencia, si un estudiante tiene 3+ inasistencias consecutivas
- **Destinatario:** Email del acudiente del estudiante ausente
- **Variables:**
  - `nombre_acudiente`, `nombre_estudiante`, `cantidad_inasistencias`, `fechas`
- **Notas:** Solo se envia si no se ha enviado alerta en los ultimos 7 dias para ese estudiante

### 13. cambio_grupo (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Admin/GroupController.php` metodo `enrollStudent()` -> `notificarCambioGrupo()`
- **Trigger:** Al inscribir un estudiante en un grupo nuevo, si tenia inscripcion activa en otro grupo
- **Destinatario:** Email del acudiente del estudiante
- **Variables:**
  - `nombre_acudiente`, `nombre_estudiante`, `grupo_anterior`, `grupo_nuevo`, `horario_nuevo`

---

## Resumen de Destinatarios

| Tipo de destinatario | Plantillas |
|---------------------|-----------|
| Un acudiente especifico | recuperar_password, bienvenida, pago_aprobado, pago_rechazado, paz_y_salvo, inscripcion_torneo_confirmada, alerta_inasistencia, cambio_grupo |
| Todos los admins | nuevo_estudiante, pago_recibido |
| Un profesor especifico | nuevo_estudiante (el que genero el enlace) |
| Multiples acudientes (masivo) | torneo_disponible, recordatorio_pago |
| Email nuevo (no registrado aun) | enlace_inscripcion |

---

## Como usar el servicio SendGrid

```php
// Importar
$sendgrid = new \App\Libraries\SendGridService();

// Enviar con plantilla de BD
$sendgrid->enviar(
    ['email' => 'destino@email.com', 'nombre' => 'Juan'],
    'codigo_plantilla',
    ['variable1' => 'valor1', 'variable2' => 'valor2']
);

// Enviar con adjunto
$sendgrid->enviarConAdjunto(
    ['email' => 'destino@email.com', 'nombre' => 'Juan'],
    'paz_y_salvo',
    ['nombre_acudiente' => 'Juan', 'nombre_estudiante' => 'Pedro'],
    '/ruta/al/archivo.pdf',
    'PazYSalvo.pdf'
);

// Enviar directo sin plantilla
$sendgrid->enviarDirecto(
    'destino@email.com',
    'Asunto del correo',
    '<h1>Contenido HTML</h1>'
);
```

---

## Verificacion y Debug

- Todos los emails se registran en la tabla `emails_enviados`
- Campos utiles para debug: `estado` (enviado/fallido), `error`, `sendgrid_id`
- Logs de PHP en `writable/logs/log-YYYY-MM-DD.log` con prefijo `SendGrid:`

---

*Documento generado el 28 de febrero de 2026*
*Ultima actualizacion: Implementacion completa de todos los emails del sistema (13 plantillas)*
