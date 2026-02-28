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
| 2 | `enlace_inscripcion` | Profesor genera enlace de inscripcion para un acudiente nuevo | **No existe el controlador** | PENDIENTE | Email del acudiente nuevo (lo ingresa el profesor en un formulario) |
| 3 | `bienvenida` | Acudiente completa registro (se le envian credenciales temporales) | **No existe el controlador** | PENDIENTE | Email del acudiente recien registrado |
| 4 | `nuevo_estudiante` | Nuevo estudiante se inscribe en la academia | **No existe el controlador** | PENDIENTE | Todos los admins + el profesor que genero el enlace |
| 5 | `pago_recibido` | Se registra un pago (admin registra pago en el sistema) | `Admin\PaymentController::store()` | IMPLEMENTADO | Todos los admins activos |
| 6 | `pago_aprobado` | Admin aprueba un pago pendiente de revision | `Admin\PaymentController::approve()` | IMPLEMENTADO | Email del acudiente dueno del pago |
| 7 | `pago_rechazado` | Admin rechaza un pago con motivo | `Admin\PaymentController::reject()` | IMPLEMENTADO | Email del acudiente dueno del pago (con motivo del rechazo) |
| 8 | `paz_y_salvo` | Acudiente solicita paz y salvo (saldo = 0) | **No existe el controlador** | PENDIENTE | Email del acudiente que lo solicita (con PDF adjunto) |
| 9 | `torneo_disponible` | Admin abre inscripciones de un torneo | `Admin\TournamentController::changeStatus()` | PENDIENTE | Todos los acudientes con estudiantes activos en la categoria del torneo |

---

## Detalle por Email

### 1. recuperar_password (IMPLEMENTADO)

- **Archivo:** `app/Controllers/AuthController.php` metodo `sendResetEmail()`
- **Trigger:** POST `/forgot-password`
- **Destinatario:** El usuario que solicito la recuperacion
- **Variables:**
  - `nombre` - Nombre del usuario
  - `enlace` - URL con token para restablecer contrasena
- **Notas:** El enlace expira segun la logica de `UserModel::verifyResetToken()`

### 2. enlace_inscripcion (PENDIENTE)

- **Archivo:** Por crear - `app/Controllers/Profesor/InscriptionController.php`
- **Trigger:** Profesor ingresa nombre y email de un acudiente nuevo
- **Destinatario:** Email del acudiente (ingresado por el profesor)
- **Variables:**
  - `nombre_acudiente` - Nombre del acudiente
  - `enlace` - URL `/registro/{token}` (expira en 48h)
- **Notas:** Requiere crear tabla `tokens_inscripcion` y el flujo de inscripcion (FASE 2 del plan)

### 3. bienvenida (PENDIENTE)

- **Archivo:** Por crear - Controller de registro publico
- **Trigger:** Acudiente completa el formulario de inscripcion con todos los datos
- **Destinatario:** Email del acudiente recien registrado
- **Variables:**
  - `nombre_acudiente` - Nombre del acudiente
  - `email` - Email de acceso
  - `password_temporal` - Contrasena generada automaticamente
- **Notas:** Se envia despues de crear usuario + acudiente + estudiante(s) + cargos automaticos

### 4. nuevo_estudiante (PENDIENTE)

- **Archivo:** Por crear - Mismo controller de registro
- **Trigger:** Se completa la inscripcion de un nuevo estudiante
- **Destinatarios:**
  - El profesor que genero el enlace de inscripcion
  - Todos los administradores activos
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
- **Notas:** Cuando se implemente el panel de acudiente (subir comprobante), se debe agregar el mismo email en ese controlador

### 6. pago_aprobado (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Admin/PaymentController.php` metodo `approve()` -> `notificarPagoAprobado()`
- **Trigger:** Admin hace clic en "Aprobar" en la revision de un pago
- **Destinatario:** Email del acudiente (se obtiene via: acudientes -> usuarios.email)
- **Variables:**
  - `nombre_acudiente` - Nombre completo del acudiente
  - `valor` - Valor formateado del pago
  - `numero_recibo` - Numero de recibo

### 7. pago_rechazado (IMPLEMENTADO)

- **Archivo:** `app/Controllers/Admin/PaymentController.php` metodo `reject()` -> `notificarPagoRechazado()`
- **Trigger:** Admin hace clic en "Rechazar" e ingresa motivo
- **Destinatario:** Email del acudiente (se obtiene via: acudientes -> usuarios.email)
- **Variables:**
  - `nombre_acudiente` - Nombre completo del acudiente
  - `motivo` - Motivo de rechazo ingresado por el admin

### 8. paz_y_salvo (PENDIENTE)

- **Archivo:** Por crear - `app/Controllers/Acudiente/PazYSalvoController.php`
- **Trigger:** Acudiente solicita paz y salvo (solo si saldo pendiente = 0)
- **Destinatario:** Email del acudiente
- **Variables:**
  - `nombre_acudiente` - Nombre del acudiente
  - `nombre_estudiante` - Nombre del estudiante
- **Adjunto:** PDF del paz y salvo generado
- **Metodo SendGrid:** `enviarConAdjunto()` en lugar de `enviar()`
- **Notas:** Requiere libreria de generacion de PDF (TCPDF o similar)

### 9. torneo_disponible (PENDIENTE)

- **Archivo:** `app/Controllers/Admin/TournamentController.php` metodo `changeStatus()`
- **Trigger:** Admin cambia estado del torneo a `inscripciones_abiertas`
- **Destinatario:** Todos los acudientes con estudiantes activos en la categoria del torneo
- **Variables:**
  - `nombre_acudiente` - Nombre del acudiente
  - `nombre_torneo` - Nombre del torneo
  - `fecha_torneo` - Fecha del evento
  - `lugar` - Lugar del torneo
  - `costo` - Costo de inscripcion
  - `cupos` - Cupos disponibles
- **Notas:** Es un envio masivo. Considerar envio en background para no bloquear la respuesta HTTP

---

## Resumen de Destinatarios

| Tipo de destinatario | Plantillas |
|---------------------|-----------|
| Un acudiente especifico | recuperar_password, bienvenida, pago_aprobado, pago_rechazado, paz_y_salvo |
| Todos los admins | nuevo_estudiante, pago_recibido |
| Un profesor especifico | nuevo_estudiante (el que genero el enlace) |
| Multiples acudientes (masivo) | torneo_disponible |
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
*Ultima actualizacion: Implementacion de pagos (aprobado, rechazado, recibido)*
