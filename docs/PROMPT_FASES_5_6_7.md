# PROMPT: Completar Emails Pendientes + Flujo de Inscripcion + Paz y Salvo

**Copia y pega TODO el contenido de abajo en un nuevo chat de Claude Code:**

---

## CONTEXTO DEL PROYECTO

Estoy desarrollando el sistema de gestion para la **Academia de Futbol Heroicos** usando **CodeIgniter 4.7.0**. El proyecto ya esta en produccion en `https://heroicos.cycloidtalent.com/`.

### Stack Tecnologico
- **Backend:** CodeIgniter 4.7.0 + PHP 8.2
- **BD:** MySQL 8.0 (local XAMPP + produccion DigitalOcean con SSL)
- **Frontend:** Bootstrap 5.3 + Bootstrap Icons + Vanilla JS
- **Email:** SendGrid PHP SDK v8.1.2 (ya instalado y funcional)
- **Repo:** https://github.com/edielestudiante2023/heroicos.git (rama `main` + rama `cycloid`)

### Credenciales
- **Admin:** admin@heroicos.com / Admin123*
- **BD Local:** root / (vacio) - localhost:3306 - db: heroicos
- **BD Produccion:** `db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com:25060`, user `cycloid_userdb`, BD `defaultdb`, SSL requerido

---

## ESTADO ACTUAL - LO QUE YA ESTA HECHO

### Fases Completadas:
- **FASE 1:** Autenticacion (login, logout, forgot password con email real via SendGrid)
- **FASE 2:** Gestion de Usuarios CRUD
- **FASE 3:** Gestion de Estudiantes CRUD
- **FASE 4:** Grupos y Horarios + Usuarios de prueba
- **FASE 5-7:** Cartera, Pagos, Asistencia, Torneos (ya implementados)
- **PWA:** Service Worker + manifest.json + offline.html
- **SendGrid:** Servicio de envio de emails completamente funcional con 4 emails ya implementados

### Archivos clave del sistema de emails (NO TOCAR, ya funcionan):

**Servicio:**
- `app/Libraries/SendGridService.php` - Servicio principal de envio
- `app/Models/EmailEnviadoModel.php` - Log de emails en tabla `emails_enviados`
- `app/Models/PlantillaEmailModel.php` - Plantillas en tabla `plantillas_email`
- `app/Views/emails/base.php` - Template HTML base con branding Heroicos (purpura, logo)

**Emails ya implementados (NO modificar):**

| Email | Controller | Metodo | Destinatario |
|-------|-----------|--------|-------------|
| `recuperar_password` | `AuthController::sendResetEmail()` | Envia enlace de reset | El usuario que solicito |
| `pago_recibido` | `Admin\PaymentController::store()` -> `notificarPagoRecibido()` | Notifica nuevo pago registrado | Todos los admins activos |
| `pago_aprobado` | `Admin\PaymentController::approve()` -> `notificarPagoAprobado()` | Pago aprobado | Acudiente dueno del pago |
| `pago_rechazado` | `Admin\PaymentController::reject()` -> `notificarPagoRechazado()` | Pago rechazado con motivo | Acudiente dueno del pago |

---

## API DEL SERVICIO SENDGRID (ya funciona, solo usarlo)

```php
$sendgrid = new \App\Libraries\SendGridService();

// Enviar con plantilla de BD
$sendgrid->enviar(
    ['email' => 'destino@email.com', 'nombre' => 'Juan'],
    'codigo_plantilla',
    ['variable1' => 'valor1', 'variable2' => 'valor2']
);

// Enviar con adjunto (para paz y salvo con PDF)
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

### Plantillas YA existentes en BD (tabla `plantillas_email`):

| # | Codigo | Variables | Estado |
|---|--------|-----------|--------|
| 1 | `bienvenida` | `nombre_acudiente`, `email`, `password_temporal` | En BD, sin usar |
| 2 | `nuevo_estudiante` | `nombre_estudiante`, `nombre_acudiente`, `telefono`, `fecha_inscripcion` | En BD, sin usar |
| 3 | `pago_recibido` | `nombre_acudiente`, `valor`, `numero_recibo` | IMPLEMENTADO |
| 4 | `pago_aprobado` | `nombre_acudiente`, `valor`, `numero_recibo` | IMPLEMENTADO |
| 5 | `pago_rechazado` | `nombre_acudiente`, `motivo` | IMPLEMENTADO |
| 6 | `paz_y_salvo` | `nombre_acudiente`, `nombre_estudiante` | En BD, sin usar |
| 7 | `torneo_disponible` | `nombre_acudiente`, `nombre_torneo`, `fecha_torneo`, `lugar`, `costo`, `cupos` | En BD, sin usar |
| 8 | `enlace_inscripcion` | `nombre_acudiente`, `enlace` | En BD, sin usar |
| 9 | `recuperar_password` | `nombre`, `enlace` | IMPLEMENTADO |

---

## PATRON DE ENVIO DE EMAIL (SIEMPRE seguir este patron)

Asi se implementaron los emails en PaymentController. Replica exactamente este patron:

```php
protected function notificarAlgo(array $datos): void
{
    try {
        $db = \Config\Database::connect();
        // ... obtener destinatario(s) con query ...

        $sendgrid = new \App\Libraries\SendGridService();
        $sendgrid->enviar(
            ['email' => $destEmail, 'nombre' => $destNombre],
            'codigo_plantilla',
            ['variable1' => 'valor1']
        );
    } catch (\Exception $e) {
        log_message('error', 'Error enviando email X: ' . $e->getMessage());
    }
}
```

**Reglas:**
- Siempre envolver en try/catch para NO romper el flujo principal
- Siempre loguear errores con `log_message('error', ...)`
- El SendGridService ya registra automaticamente en `emails_enviados`
- Crear el metodo como `protected` en el mismo controlador donde se dispara

---

## TAREA 1: Email `torneo_disponible` (controlador ya existe)

### Archivo: `app/Controllers/Admin/TournamentController.php`
### Metodo a modificar: `changeStatus(int $id)` (linea 275)

Cuando el admin cambia el estado de un torneo a `inscripciones_abiertas`, enviar email a todos los acudientes relevantes.

**Agregar metodo `notificarTorneoDisponible(array $torneo)`:**

1. Solo disparar cuando `$nuevoEstado === 'inscripciones_abiertas'`
2. Obtener el torneo completo: `$torneo = $this->torneoModel->find($id)`
3. Si el torneo tiene `categoria_id`:
   - Buscar estudiantes activos en grupos de esa categoria:
     ```sql
     SELECT DISTINCT a.id, a.nombres, a.apellidos, u.email
     FROM estudiantes e
     JOIN inscripciones i ON i.estudiante_id = e.id AND i.estado = 'activa'
     JOIN grupos g ON g.id = i.grupo_id
     JOIN acudientes a ON a.id = e.acudiente_id
     JOIN usuarios u ON u.id = a.usuario_id
     WHERE e.estado = 'activo'
     AND g.categoria_id = {categoria_id}
     AND u.estado = 'activo'
     ```
4. Si NO tiene `categoria_id` (torneo abierto a todas las categorias):
   - Enviar a TODOS los acudientes con al menos un estudiante activo
5. Enviar a cada acudiente con plantilla `torneo_disponible`:
   - `nombre_acudiente`: nombres + apellidos
   - `nombre_torneo`: torneo['nombre']
   - `fecha_torneo`: torneo['fecha_evento'] formateada
   - `lugar`: torneo['lugar']
   - `costo`: number_format(torneo['costo'], 0, ',', '.')
   - `cupos`: cupo_maximo - inscritos_actuales (calcular con query a `torneo_inscripciones`)

**En `changeStatus()`**, despues de actualizar el estado, agregar:
```php
if ($nuevoEstado === 'inscripciones_abiertas') {
    $torneo = $this->torneoModel->find($id);
    $this->notificarTorneoDisponible($torneo);
}
```

---

## TAREA 2: Email `inscripcion_torneo_confirmada` (NUEVO - crear plantilla + implementar)

### Archivo: `app/Controllers/Admin/TournamentController.php`
### Metodo a modificar: `enrollStudent(int $torneoId)` (linea 221)

Cuando el admin inscribe a un estudiante en un torneo, notificar al acudiente.

**Pasos:**
1. Crear migracion para insertar nueva plantilla en `plantillas_email`:
   - codigo: `inscripcion_torneo_confirmada`
   - asunto: `Inscripcion confirmada en {{nombre_torneo}} - Academia Heroicos`
   - cuerpo_html: Mensaje confirmando que el estudiante fue inscrito en el torneo con detalles (nombre, fecha, lugar, costo)
   - variables: `nombre_acudiente`, `nombre_estudiante`, `nombre_torneo`, `fecha_torneo`, `lugar`, `costo`
2. Agregar metodo `notificarInscripcionTorneo()` al TournamentController
3. Llamarlo despues de `enrollStudent()` exitoso (linea 252, despues del if de exito)
4. Destinatario: El acudiente del estudiante inscrito

---

## TAREA 3: Flujo Completo de Inscripcion por Enlace (CREAR desde cero)

Este flujo usa 3 emails: `enlace_inscripcion`, `bienvenida`, `nuevo_estudiante`.

### 3A. Controller del Profesor: Generar enlace

**Crear:** `app/Controllers/Profesor/InscriptionController.php`

**Rutas a agregar** en `app/Config/Routes.php` dentro del grupo profesor:
```php
$routes->get('inscripcion', 'Profesor\InscriptionController::index');
$routes->post('inscripcion/generar', 'Profesor\InscriptionController::generate');
```

**Funcionalidad de `generate()`:**
1. Validar nombre y email del formulario
2. Verificar que el email no este ya registrado como usuario
3. Generar token: `bin2hex(random_bytes(32))` = 64 chars hex
4. Guardar en tabla `tokens_inscripcion`:
   ```php
   [
       'token' => $token,
       'email' => $email,
       'nombre_acudiente' => $nombre,
       'profesor_id' => session()->get('profesor_id'),
       'usado' => false,
       'expira_at' => date('Y-m-d H:i:s', strtotime('+48 hours')),
   ]
   ```
5. Enviar email con plantilla `enlace_inscripcion`:
   - `nombre_acudiente`: nombre ingresado
   - `enlace`: `base_url('registro/' . $token)`
6. Mostrar flash message de exito

**Tabla `tokens_inscripcion` YA EXISTE en BD** con esta estructura:
- id, token(unique), email, nombre_acudiente, profesor_id(FK->profesores), usado(bool), fecha_uso, acudiente_id, expira_at, created_at

### 3B. Vista del profesor

**Crear:** `app/Views/profesor/inscription/index.php`
- Formulario con campos: nombre del acudiente (text), email (email)
- Boton "Generar y Enviar Enlace"
- Debajo: tabla con enlaces generados previamente por el profesor logueado
  - Columnas: Fecha, Nombre Acudiente, Email, Estado (Pendiente/Usado/Expirado), Acciones
  - Estado: `usado=true` -> "Usado", `expira_at < NOW()` -> "Expirado", else -> "Pendiente"
- Usar layout `layouts/main.php` con sidebar de profesor
- Estilo igual al resto del proyecto (stat-card, table-card, etc.)

### 3C. Controller publico: Registro

**Crear:** `app/Controllers/RegistroController.php`

**Rutas** (publicas, SIN filtro de auth):
```php
$routes->get('registro/(:segment)', 'RegistroController::index/$1');
$routes->post('registro/(:segment)', 'RegistroController::store/$1');
```

**Metodo `index($token)`:**
1. Buscar token en `tokens_inscripcion` donde `usado = false` y `expira_at > NOW()`
2. Si invalido/expirado: mostrar vista de error con mensaje amable
3. Si valido: mostrar formulario de registro con nombre y email prellenados

**Formulario de registro** (vista `app/Views/auth/registro.php`):
- Usar layout `layouts/auth.php` (igual que login, sin sidebar)
- **Datos del acudiente:**
  - nombres, apellidos (prellenados del token)
  - email (prellenado, readonly)
  - tipo_documento (CC, TI, CE, PP), numero_documento
  - telefono, direccion
- **Datos del estudiante:**
  - nombres, apellidos
  - fecha_nacimiento, sexo (M/F)
  - tipo_documento, numero_documento
  - talla_camiseta, talla_pantaloneta, talla_medias
  - posicion, pie_dominante (derecho/izquierdo/ambidiestro)
  - eps, rh (grupo sanguineo)
- Boton "Agregar otro estudiante" (JavaScript para clonar bloque de campos)
- Boton submit "Completar Registro"

**Metodo `store($token)`:**
1. Validar token nuevamente (doble check)
2. Validar todos los campos del formulario
3. **En una transaccion de BD:**
   a. Generar password temporal: `substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'), 0, 8)`
   b. Crear usuario en `usuarios`:
      - `email`, `password` (hash del temporal), `nombre` = nombres, `rol_id` = 3 (acudiente), `estado` = 'activo'
   c. Crear registro en `acudientes`:
      - `usuario_id`, `nombres`, `apellidos`, `tipo_documento`, `numero_documento`, `telefono`, `direccion`
   d. Por cada estudiante del formulario, crear en `estudiantes`:
      - `acudiente_id`, `codigo` (autogenerar con patron EST{YEAR}{SECUENCIAL}), todos los campos
   e. Marcar token: `usado = true`, `fecha_uso = NOW()`, `acudiente_id = nuevo_id`
4. **Enviar email `bienvenida`** al nuevo acudiente:
   - `nombre_acudiente`: nombres + ' ' + apellidos
   - `email`: email de acceso
   - `password_temporal`: password generado (texto plano, antes de hashear)
5. **Enviar email `nuevo_estudiante`** por cada estudiante inscrito:
   - Destinatarios: TODOS los admins (rol_id=1, estado='activo') + el profesor del token (profesor_id)
   - `nombre_estudiante`, `nombre_acudiente`, `telefono`, `fecha_inscripcion`
6. Mostrar vista de exito: "Registro completado. Revisa tu email para tus credenciales de acceso."

**Nota sobre generacion de codigo de estudiante:**
Revisar como lo hace `StudentModel.php` para el campo `codigo` y replicar la misma logica.

---

## TAREA 4: Paz y Salvo con PDF

### 4A. Instalar TCPDF
```bash
composer require tecnickcom/tcpdf
```
Luego en produccion: `cd /www/wwwroot/heroicos && composer install --no-dev`

### 4B. Generador de PDF

**Crear:** `app/Libraries/PazYSalvoPdfGenerator.php`

Clase que genera un PDF con:
- Logo de la academia (usar `FCPATH . 'assets/images/heroicos.png'`)
- Titulo centrado: "CERTIFICADO DE PAZ Y SALVO"
- Numero de certificado: formato `PYS-{YYYY}-{consecutivo}` (ej: PYS-2026-00001)
- Cuerpo: "La Academia Heroicos Futbol Club certifica que el(la) senor(a) **[NOMBRE ACUDIENTE]**, identificado(a) con **[TIPO DOC] No. [NUMERO DOC]**, se encuentra a **PAZ Y SALVO** por concepto de todos los compromisos economicos correspondientes al estudiante **[NOMBRE ESTUDIANTE]** a la fecha **[FECHA CORTE]**."
- Fecha de emision
- Pie: "Documento generado automaticamente - Academia Heroicos Futbol Club"
- Guardar en: `writable/uploads/paz_y_salvos/PYS-{id}.pdf`
- Metodo principal: `generar(array $datos): string` que retorna la ruta del PDF

### 4C. Controller del Acudiente

**Crear:** `app/Controllers/Acudiente/PazYSalvoController.php`

**Rutas** (dentro del grupo acudiente):
```php
$routes->get('paz-y-salvo', 'Acudiente\PazYSalvoController::index');
$routes->post('paz-y-salvo/solicitar', 'Acudiente\PazYSalvoController::solicitar');
$routes->get('paz-y-salvo/descargar/(:num)', 'Acudiente\PazYSalvoController::descargar/$1');
```

**Metodo `index()`:**
1. Obtener acudiente logueado: `session()->get('acudiente_id')` o buscar por `usuario_id`
2. Obtener sus estudiantes activos
3. Por cada estudiante, calcular saldo pendiente: `SUM(saldo_pendiente) FROM cargos WHERE estudiante_id = X AND estado IN ('pendiente', 'parcial')`
4. Verificar si ya tiene paz y salvo vigente (buscar en `paz_y_salvos`)
5. Pasar datos a la vista

**Metodo `solicitar()`:**
1. Recibir `estudiante_id` por POST
2. Verificar que el estudiante pertenece al acudiente logueado (seguridad!)
3. Verificar saldo = 0 del estudiante
4. Generar numero de certificado: `PYS-{YEAR}-{consecutivo}`
5. Generar PDF usando PazYSalvoPdfGenerator
6. Guardar en tabla `paz_y_salvos`:
   ```php
   [
       'estudiante_id' => $estudianteId,
       'acudiente_id' => $acudienteId,
       'numero' => $numeroCertificado,
       'fecha_generacion' => date('Y-m-d H:i:s'),
       'fecha_corte' => date('Y-m-d'),
       'archivo_pdf' => $rutaPdf,
       'enviado_email' => true,
       'motivo' => 'solicitud',
   ]
   ```
7. **Enviar email con plantilla `paz_y_salvo`** usando `enviarConAdjunto()`:
   - `nombre_acudiente`, `nombre_estudiante`
   - Adjunto: el PDF generado
8. Redirect con flash message de exito

**Metodo `descargar($id)`:**
1. Buscar paz_y_salvo por id
2. Verificar que pertenece al acudiente logueado
3. Retornar respuesta de descarga del PDF

### 4D. Vista del Acudiente

**Crear:** `app/Views/acudiente/paz_y_salvo/index.php`
- Extiende `layouts/main.php` con sidebar de acudiente
- Tabla con los estudiantes del acudiente:
  - Columnas: Estudiante, Codigo, Saldo Pendiente, Estado, Accion
  - Saldo = 0: badge verde "Al dia", boton "Solicitar Paz y Salvo" (verde)
  - Saldo > 0: badge rojo "$X.XXX pendiente", boton deshabilitado gris
  - Si ya tiene paz y salvo: boton "Descargar PDF" (azul) + fecha de emision
- Card informativa arriba: "El paz y salvo certifica que no tienes deudas pendientes con la academia..."

### 4E. Modelo PazYSalvoModel

Verificar que `app/Models/PazYSalvoModel.php` existe. Si no, crearlo con:
- tabla: `paz_y_salvos`
- allowedFields: estudiante_id, acudiente_id, numero, fecha_generacion, fecha_corte, archivo_pdf, enviado_email, motivo, observaciones
- Metodo `getByEstudiante($estudianteId)`: obtener ultimo paz y salvo vigente

**Tabla `paz_y_salvos` YA EXISTE** con campos:
- id, estudiante_id(FK), acudiente_id(FK), numero(unique), fecha_generacion, fecha_corte, archivo_pdf, enviado_email(bool), motivo(retiro/solicitud/otro), observaciones

---

## TAREA 5: Emails Adicionales (crear plantillas nuevas + implementar)

Estos emails NO tienen plantilla en BD todavia. Se debe crear una migracion para agregarlas.

### 5A. Migracion de plantillas nuevas

**Crear:** migracion `AddPlantillasAdicionales`

Insertar en `plantillas_email`:

```php
// recordatorio_pago
[
    'codigo' => 'recordatorio_pago',
    'nombre' => 'Recordatorio de pago vencido',
    'asunto' => 'Recordatorio de pago pendiente - Academia Heroicos',
    'cuerpo_html' => '<h2>Recordatorio de Pago</h2><p>Hola {{nombre_acudiente}},</p><p>Te recordamos que tienes un saldo pendiente por valor de <strong>${{valor_pendiente}}</strong> correspondiente al estudiante <strong>{{estudiante}}</strong>.</p><p><strong>Concepto:</strong> {{concepto}}<br><strong>Fecha de vencimiento:</strong> {{fecha_vencimiento}}</p><p>Te invitamos a realizar tu pago lo antes posible para mantener al dia la cuenta de tu hijo(a).</p><p>Si ya realizaste el pago, por favor ignora este mensaje.</p>',
    'variables' => '["nombre_acudiente", "valor_pendiente", "estudiante", "concepto", "fecha_vencimiento"]',
],

// inscripcion_torneo_confirmada
[
    'codigo' => 'inscripcion_torneo_confirmada',
    'nombre' => 'Confirmacion de inscripcion a torneo',
    'asunto' => 'Inscripcion confirmada: {{nombre_torneo}} - Academia Heroicos',
    'cuerpo_html' => '<h2>Inscripcion a Torneo Confirmada</h2><p>Hola {{nombre_acudiente}},</p><p>Te confirmamos que el estudiante <strong>{{nombre_estudiante}}</strong> ha sido inscrito exitosamente en el torneo:</p><p><strong>{{nombre_torneo}}</strong></p><p><strong>Fecha:</strong> {{fecha_torneo}}<br><strong>Lugar:</strong> {{lugar}}<br><strong>Costo:</strong> ${{costo}}</p><p>Recuerda estar pendiente de las indicaciones del profesor para la preparacion.</p>',
    'variables' => '["nombre_acudiente", "nombre_estudiante", "nombre_torneo", "fecha_torneo", "lugar", "costo"]',
],

// asistencia_inasistencia
[
    'codigo' => 'alerta_inasistencia',
    'nombre' => 'Alerta de inasistencias consecutivas',
    'asunto' => 'Alerta de inasistencias - {{nombre_estudiante}} - Academia Heroicos',
    'cuerpo_html' => '<h2>Alerta de Inasistencias</h2><p>Hola {{nombre_acudiente}},</p><p>Te informamos que el estudiante <strong>{{nombre_estudiante}}</strong> ha acumulado <strong>{{cantidad_inasistencias}} inasistencias consecutivas</strong>.</p><p><strong>Ultimas fechas de inasistencia:</strong><br>{{fechas}}</p><p>La asistencia regular es fundamental para el desarrollo deportivo. Si hay alguna situacion especial, por favor comunicate con el profesor o la administracion.</p>',
    'variables' => '["nombre_acudiente", "nombre_estudiante", "cantidad_inasistencias", "fechas"]',
],

// cambio_grupo
[
    'codigo' => 'cambio_grupo',
    'nombre' => 'Notificacion de cambio de grupo',
    'asunto' => 'Cambio de grupo - {{nombre_estudiante}} - Academia Heroicos',
    'cuerpo_html' => '<h2>Cambio de Grupo</h2><p>Hola {{nombre_acudiente}},</p><p>Te informamos que el estudiante <strong>{{nombre_estudiante}}</strong> ha sido trasladado:</p><p><strong>Grupo anterior:</strong> {{grupo_anterior}}<br><strong>Grupo nuevo:</strong> {{grupo_nuevo}}<br><strong>Horario:</strong> {{horario_nuevo}}</p><p>Si tienes alguna pregunta, no dudes en contactarnos.</p>',
    'variables' => '["nombre_acudiente", "nombre_estudiante", "grupo_anterior", "grupo_nuevo", "horario_nuevo"]',
],
```

### 5B. `recordatorio_pago` - Recordatorio de pagos vencidos

**Archivo:** `app/Controllers/Admin/CarteraController.php`
**Agregar metodo:** `enviarRecordatorios()`
**Ruta:** `$routes->post('cartera/recordatorios', 'Admin\CarteraController::enviarRecordatorios');`

**Logica:**
1. Buscar cargos vencidos: `fecha_vencimiento < CURDATE() AND estado IN ('pendiente', 'parcial')`
2. Agrupar por acudiente (para no enviar multiples emails al mismo acudiente)
3. Por cada acudiente, enviar UN email con el resumen de sus cargos vencidos
4. Registrar cuantos emails se enviaron y mostrar flash message

**En la vista de cartera**, agregar boton "Enviar Recordatorios" que haga POST a esta ruta.

### 5C. `inscripcion_torneo_confirmada` (ya cubierto en TAREA 2)

### 5D. `alerta_inasistencia` - Alerta de inasistencias consecutivas

**Archivo:** `app/Controllers/Profesor/AttendanceController.php`
**Cuando:** Despues de guardar asistencia, verificar si algun estudiante con `presente=false` acumula 3+ inasistencias consecutivas
**Logica:**
1. Para cada estudiante marcado como ausente en la sesion recien guardada
2. Contar inasistencias consecutivas recientes (ultimas sesiones del mismo grupo)
3. Si >= 3 consecutivas, enviar email al acudiente con plantilla `alerta_inasistencia`
4. Solo enviar si no se ha enviado una alerta en los ultimos 7 dias para ese estudiante (evitar spam)

### 5E. `cambio_grupo` - Notificacion de cambio de grupo

**Archivo:** `app/Controllers/Admin/GroupController.php`
**Cuando:** Metodo de enroll/unenroll - si se cambia a un estudiante de grupo
**Logica:**
1. Al inscribir estudiante en un grupo nuevo, verificar si tenia inscripcion activa en otro grupo
2. Si es cambio (no primera inscripcion), enviar email al acudiente con plantilla `cambio_grupo`
3. Variables: grupo anterior, grupo nuevo, horario del grupo nuevo

---

## REGLAS IMPORTANTES

### Estructura de archivos
- Controladores: `app/Controllers/{Rol}/NombreController.php`
- Vistas: `app/Views/{rol}/{modulo}/archivo.php`
- Modelos: `app/Models/NombreModel.php`
- Las vistas extienden `layouts/main.php` con `$this->extend('layouts/main')`
- Las vistas publicas (registro) extienden `layouts/auth.php`

### Paleta de colores CSS
```css
--heroicos-primary: #b720d2;   /* Purpura principal */
--heroicos-secondary: #ffd65e; /* Amarillo dorado */
--heroicos-dark: #8a189e;      /* Purpura oscuro */
--heroicos-light: #f8e6fc;     /* Purpura claro */
--heroicos-accent: #d62b23;    /* Rojo */
```

### Base de datos
- **NUNCA ejecutar SQL manualmente en produccion**
- Cambios de BD solo via migraciones de CodeIgniter
- Orden: ejecutar local primero, produccion despues
- Para ejecutar en produccion, crear un script PHP CLI o usar `php spark migrate`

### Git workflow
```bash
# En local:
git add [archivos especificos]
git commit -m "Mensaje descriptivo"
git checkout main
git merge cycloid
git push origin main
git checkout cycloid

# En servidor:
cd /www/wwwroot/heroicos && git pull origin main
```

### Deploy de dependencias nuevas (si se instala algo con composer)
```bash
# En servidor:
cd /www/wwwroot/heroicos && composer install --no-dev
```

---

## ORDEN DE EJECUCION SUGERIDO

1. **TAREA 5A** - Crear migracion con plantillas nuevas y ejecutarla (local + produccion)
2. **TAREA 1** - Email `torneo_disponible` (rapido, controlador ya existe)
3. **TAREA 2** - Email `inscripcion_torneo_confirmada` (rapido, controlador ya existe)
4. **TAREA 3** - Flujo completo de inscripcion por enlace (3 emails + 2 controllers + vistas)
5. **TAREA 4** - Paz y salvo con PDF (controller + PDF generator + vista)
6. **TAREA 5B-5E** - Emails adicionales (menor prioridad)

### Al terminar cada tarea:
- Verificar que no hay errores de PHP (revisar `writable/logs/`)
- Actualizar `docs/SENDGRID_EMAILS.md` cambiando estados de PENDIENTE a IMPLEMENTADO
- Hacer commit con mensaje descriptivo

---

## DOCUMENTACION DE REFERENCIA

Lee estos archivos antes de empezar para entender los patrones exactos:

1. `app/Libraries/SendGridService.php` - Como funciona el servicio de emails
2. `app/Controllers/Admin/PaymentController.php` - Ejemplo perfecto de 3 emails implementados
3. `app/Controllers/AuthController.php` - Ejemplo de email individual
4. `app/Controllers/Admin/TournamentController.php` - Donde agregar torneo_disponible
5. `app/Config/Routes.php` - Rutas existentes
6. `app/Views/layouts/main.php` - Layout principal
7. `app/Views/admin/dashboard.php` - Para entender la estructura del sidebar
8. `docs/SENDGRID_EMAILS.md` - Inventario completo de emails
9. `docs/PLAN_PROYECTO.md` - Plan general del proyecto
