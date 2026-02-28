# PROMPT PARA NUEVO CHAT - FASES 5, 6 y 7

**Copia y pega TODO el contenido de abajo en un nuevo chat de Claude Code:**

---

## CONTEXTO DEL PROYECTO

Estoy desarrollando el sistema de gestión para la **Academia de Fútbol Heroicos** usando **CodeIgniter 4.7.0**. El proyecto ya está en producción en `https://heroicos.cycloidtalent.com/`.

### Stack Tecnológico
- **Backend:** CodeIgniter 4.7.0 + PHP 8.2
- **BD:** MySQL 8.0 (local XAMPP + producción DigitalOcean con SSL)
- **Frontend:** Bootstrap 5.3 + Bootstrap Icons + Vanilla JS
- **Servidor:** aaPanel + Nginx en `heroicos.cycloidtalent.com`
- **Repo:** https://github.com/edielestudiante2023/heroicos.git (rama `main` + rama `cycloid`)

### Credenciales
- **Admin:** admin@heroicos.com / Admin123*
- **BD Local:** root / (vacío) - localhost:3306 - db: heroicos
- **BD Producción:** DigitalOcean Aiven con SSL (ver `.env`)

---

## ESTADO ACTUAL - LO QUE YA ESTÁ HECHO

### Fases Completadas:
- **FASE 1:** Autenticación (login, logout, forgot password, sesiones en BD)
- **FASE 2:** Gestión de Usuarios CRUD (admin, profesores, acudientes)
- **FASE 3:** Gestión de Estudiantes CRUD (con código auto-generado EST{AÑO}{NUM})
- **FASE 4:** Grupos y Horarios CRUD (con asignación de profesores, horarios, inscripciones)

### Archivos existentes clave:

**Modelos (app/Models/):**
- `UserModel.php` - tabla `usuarios` (email, password, rol_id, estado)
- `RoleModel.php` - tabla `roles` (admin, profesor, acudiente)
- `StudentModel.php` - tabla `estudiantes` (nombres, apellidos, fecha_nacimiento, sexo, acudiente_id, codigo, etc.)
- `GrupoModel.php` - tabla `grupos` (nombre, categoria_id, cupo_maximo, estado)
- `CategoriaModel.php` - tabla `categorias` (nombre, estado)
- `HorarioModel.php` - tabla `horarios` (dia_semana, hora_inicio, hora_fin, lugar)
- `InscripcionModel.php` - tabla `inscripciones` (estudiante_id, grupo_id, estado)

**Controladores (app/Controllers/):**
- `AuthController.php` - login, logout, reset password
- `DashboardController.php` - redirect según rol
- `Admin/DashboardController.php` - dashboard admin con stats
- `Admin/UserController.php` - CRUD usuarios
- `Admin/StudentController.php` - CRUD estudiantes
- `Admin/GroupController.php` - CRUD grupos + enroll/unenroll
- `Admin/ScheduleController.php` - CRUD horarios
- `Profesor/DashboardController.php` - placeholder
- `Acudiente/DashboardController.php` - placeholder

**Filtros (app/Filters/):**
- `AuthFilter.php` - verificar sesión activa
- `RoleFilter.php` - verificar rol (admin siempre tiene acceso)

**Vistas (app/Views/):**
- `layouts/main.php` - layout principal con sidebar púrpura/amarillo
- `layouts/auth.php` - layout de login
- `auth/login.php`, `auth/forgot_password.php`, `auth/reset_password.php`
- `admin/dashboard.php` - dashboard con stats, pagos pendientes, inscripciones recientes
- `admin/users/index.php`, `form.php`, `show.php`
- `admin/students/index.php`, `form.php`, `show.php`
- `admin/groups/index.php`, `form.php`, `show.php`
- `admin/schedules/index.php`, `form.php`, `show.php`

### Rutas actuales (app/Config/Routes.php):
```php
// Públicas
$routes->get('/', 'AuthController::login');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');
$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password', 'AuthController::sendResetLink');
$routes->get('reset-password/(:segment)', 'AuthController::resetPassword/$1');
$routes->post('reset-password', 'AuthController::updatePassword');
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'auth']);

// Admin (filter: role:admin)
$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    // CRUD: users, students, groups, schedules (GET/POST/PUT/DELETE)
    // groups también tiene enroll/unenroll
});

// Profesor (filter: role:admin,profesor) - solo placeholder dashboard
$routes->group('profesor', ['filter' => 'role:admin,profesor'], function ($routes) {
    $routes->get('dashboard', 'Profesor\DashboardController::index');
});

// Acudiente (filter: role:admin,acudiente) - solo placeholder dashboard
$routes->group('acudiente', ['filter' => 'role:admin,acudiente'], function ($routes) {
    $routes->get('dashboard', 'Acudiente\DashboardController::index');
});
```

### Paleta de colores CSS:
```css
--heroicos-primary: #b720d2;   /* Púrpura principal */
--heroicos-secondary: #ffd65e; /* Amarillo dorado */
--heroicos-dark: #8a189e;      /* Púrpura oscuro */
--heroicos-light: #f8e6fc;     /* Púrpura claro */
--heroicos-accent: #d62b23;    /* Rojo */
```

### Patrón de vistas:
Cada vista extiende `layouts/main`, define sección `sidebar` (navegación) y `content`:
```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('sidebar') ?>
<!-- Nav links con iconos Bootstrap Icons -->
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<!-- Contenido principal -->
<?= $this->endSection() ?>
```

### Datos de prueba existentes en BD:
- 1 admin, 2 profesores, 2 acudientes
- 4 estudiantes activos
- 6 categorías (Sub-8 a Sub-16 + Porteros)
- 10 horarios
- 4 grupos con profesores y horarios asignados
- 4 inscripciones activas
- 1 periodo activo (2026)
- 6 conceptos de cobro (MAT, UNIF, MENS, TORN, CPAR, OTRO)

---

## TABLAS DE BD YA CREADAS (migraciones ejecutadas)

Las 38 tablas ya existen. NO necesitas crear migraciones. Solo crear modelos, controladores y vistas.

### Tablas financieras (FASE 5):

**periodos:** id, nombre, fecha_inicio, fecha_fin, estado(activo/cerrado)

**conceptos_cobro:** id, codigo(unique), nombre, descripcion, tipo(unico/recurrente), aplica_al_inscribir(bool), orden, estado(activo/inactivo)
- Datos: MAT(Matrícula), UNIF(Uniforme), MENS(Mensualidad), TORN(Torneo), CPAR(Clase Particular), OTRO

**tarifas:** id, concepto_id(FK→conceptos_cobro), categoria_id(FK→categorias, nullable), periodo_id(FK→periodos), valor(DECIMAL 12,2), vigente_desde(DATE), vigente_hasta(DATE nullable)

**cargos:** id, estudiante_id(FK→estudiantes), concepto_id(FK→conceptos_cobro), periodo_id(FK→periodos), descripcion, mes(TINYINT 1-12 nullable), anio(YEAR nullable), valor_original(DECIMAL 12,2), valor_pagado(DECIMAL 12,2 default 0), saldo_pendiente(DECIMAL 12,2), fecha_vencimiento(DATE nullable), estado(pendiente/parcial/pagado/anulado), generado_auto(bool)

**pagos:** id, acudiente_id(FK→acudientes), numero_recibo(unique), fecha_pago(DATE), fecha_registro(DATETIME), valor_total(DECIMAL 12,2), metodo_pago(transferencia/efectivo/nequi/daviplata/otro), banco, referencia_banco, observaciones, estado(pendiente_revision/aprobado/rechazado), revisado_por(FK→usuarios nullable), fecha_revision(DATETIME nullable), motivo_rechazo(TEXT nullable)

**pago_detalles:** id, pago_id(FK→pagos CASCADE), cargo_id(FK→cargos), valor_aplicado(DECIMAL 12,2)

**comprobantes:** id, pago_id(FK→pagos CASCADE), archivo, archivo_original, tipo_archivo, tamano(INT bytes)

**paz_y_salvos:** id, estudiante_id(FK→estudiantes), acudiente_id(FK→acudientes), numero(unique), fecha_generacion(DATETIME), fecha_corte(DATE), archivo_pdf, enviado_email(bool), motivo(retiro/solicitud/otro), observaciones

### Tablas de asistencia (FASE 6):

**sesiones_clase:** id, grupo_id(FK→grupos), profesor_id(FK→profesores), horario_id(FK→horarios nullable), fecha(DATE), hora_inicio(TIME), hora_fin(TIME), estado(programada/realizada/cancelada), observaciones

**asistencias:** id, sesion_id(FK→sesiones_clase CASCADE), estudiante_id(FK→estudiantes CASCADE), presente(BOOLEAN default true), justificacion(TEXT nullable), registrado_por(FK→usuarios nullable) + UNIQUE(sesion_id, estudiante_id)

### Tablas de torneos (FASE 7):

**torneos:** id, nombre, descripcion, lugar, fecha_evento(DATE), hora_evento(TIME), fecha_apertura_inscripcion(DATETIME), fecha_cierre_inscripcion(DATETIME), cupo_maximo(INT), costo(DECIMAL 12,2), categoria_id(FK→categorias nullable, NULL=todas), estado(programado/inscripciones_abiertas/inscripciones_cerradas/en_curso/finalizado/cancelado), imagen

**torneo_inscripciones:** id, torneo_id(FK→torneos CASCADE), estudiante_id(FK→estudiantes CASCADE), acudiente_id(FK→acudientes), fecha_inscripcion(DATETIME), cargo_id(FK→cargos nullable), estado(pendiente_pago/pagado/cancelado) + UNIQUE(torneo_id, estudiante_id)

---

## LO QUE NECESITO QUE HAGAS

### FASE 5: Pagos y Cartera (PRIORIDAD CRÍTICA - es el corazón del sistema)

**Crear:**
1. **Modelos:** `CargoModel`, `PagoModel`, `PagoDetalleModel`, `ComprobanteModel`, `TarifaModel`, `PeriodoModel`, `ConceptoCobroModel`, `PazYSalvoModel`
2. **Controladores Admin:**
   - `Admin/CarteraController` - Vista de cartera general, cuenta por estudiante, generación manual de cargos, anulación
   - `Admin/PaymentController` - Lista de pagos pendientes, revisar pago (ver comprobante), aprobar/rechazar
   - `Admin/TarifaController` - CRUD de tarifas por concepto/categoría/periodo
3. **Vistas Admin:**
   - `admin/cartera/index.php` - Dashboard financiero (total por cobrar, recaudo del mes, morosos, gráficas)
   - `admin/cartera/estudiante.php` - Cuenta individual (cargos + pagos + saldo)
   - `admin/payments/index.php` - Pagos pendientes de revisión
   - `admin/payments/review.php` - Revisar pago individual (ver comprobante, aprobar/rechazar)
   - `admin/tarifas/index.php`, `form.php` - CRUD tarifas

**Flujo de pagos:**
1. Admin genera cargos (manual o automático al inscribir)
2. Acudiente ve sus cargos pendientes y sube comprobante de pago
3. Admin revisa comprobante → Aprueba (actualiza saldos) o Rechaza (con motivo)
4. Sistema de abonos: un cargo puede pagarse parcialmente en múltiples pagos

**Lógica clave:**
- Al aprobar pago: actualizar `cargos.valor_pagado += valor_aplicado`, `cargos.saldo_pendiente = valor_original - valor_pagado`
- Si `saldo_pendiente = 0` → `estado = 'pagado'`
- Si `saldo_pendiente > 0 && valor_pagado > 0` → `estado = 'parcial'`
- `numero_recibo` autogenerado: `REC-{AÑO}-{CONSECUTIVO}` (ej: REC-2026-00001)

### FASE 6: Asistencia

**Crear:**
1. **Modelos:** `SesionClaseModel`, `AsistenciaModel`
2. **Controladores:**
   - `Admin/AttendanceController` - Ver historial de asistencia por grupo, reportes
   - `Profesor/AttendanceController` - Tomar asistencia de sus grupos
   - `Profesor/GroupController` - Ver sus grupos y estudiantes
3. **Vistas:**
   - `admin/attendance/index.php` - Historial por grupo con filtros de fecha
   - `admin/attendance/report.php` - Reporte de asistencia por estudiante
   - `profesor/dashboard.php` - Dashboard real del profesor (mis grupos, próximas clases)
   - `profesor/groups/index.php` - Lista de mis grupos
   - `profesor/attendance/take.php` - Tomar asistencia (todos marcados como presente por defecto, solo desmarcar ausentes)

**Lógica clave:**
- Al tomar asistencia se crea una `sesion_clase` y los registros de `asistencias` para cada estudiante del grupo
- Por defecto `presente = true`, el profesor solo desmarca ausentes
- Guardar con un solo clic (submit todo el formulario)

### FASE 7: Torneos

**Crear:**
1. **Modelos:** `TorneoModel`, `TorneoInscripcionModel`
2. **Controladores:**
   - `Admin/TournamentController` - CRUD torneos, ver inscritos, cambiar estados
3. **Vistas:**
   - `admin/tournaments/index.php` - Lista de torneos con estados y cupos
   - `admin/tournaments/form.php` - Crear/editar torneo
   - `admin/tournaments/show.php` - Detalle con inscritos y control de cupos

**Lógica de cupos:**
- `disponibles = cupo_maximo - COUNT(torneo_inscripciones WHERE estado != 'cancelado')`
- Si disponibles <= 0: No permitir inscripción, mostrar "Cupos agotados"
- Al inscribir estudiante se genera cargo automático por el costo del torneo

**Estados del torneo:** programado → inscripciones_abiertas → inscripciones_cerradas → en_curso → finalizado (o cancelado)

---

## INSTRUCCIONES IMPORTANTES

1. **Lee estos archivos primero** para entender los patrones exactos:
   - `app/Config/Routes.php`
   - `app/Views/layouts/main.php`
   - `app/Controllers/Admin/GroupController.php` (ejemplo de CRUD completo)
   - `app/Models/GrupoModel.php` (ejemplo de modelo completo)
   - `app/Views/admin/groups/index.php` (ejemplo de vista index)
   - `app/Views/admin/groups/form.php` (ejemplo de formulario)
   - `app/Views/admin/dashboard.php` (sidebar de navegación actual)

2. **Mantén el mismo estilo visual:** Usa las mismas clases CSS (stat-card, table-card, etc.), mismo sidebar con secciones, mismos colores de la paleta Heroicos.

3. **Actualiza el sidebar** en TODAS las vistas admin existentes (dashboard, users, students, groups, schedules) para agregar los nuevos links (Cartera, Pagos, Tarifas, Torneos, Asistencia).

4. **Actualiza el dashboard admin** para que muestre datos reales de cartera y link a los nuevos módulos.

5. **Base de datos:** Las tablas YA EXISTEN, NO crear migraciones. Solo modelos, controladores y vistas.

6. **Nota sobre producción:** La columna de género en `estudiantes` se llama `sexo` (ENUM: M, F), no `genero`.

7. **Tablas de relación clave:**
   - `acudientes` tiene FK a `usuarios` (usuario_id)
   - `profesores` tiene FK a `usuarios` (usuario_id)
   - `estudiantes` tiene FK a `acudientes` (acudiente_id)
   - `pagos` tiene FK a `acudientes` (acudiente_id)
   - `cargos` tiene FK a `estudiantes` (estudiante_id)

8. **Después de cada FASE**, haz commit, push a GitHub, y dame los comandos para actualizar producción:
   ```bash
   cd /www/wwwroot/heroicos && git pull origin main
   ```

9. **Crea datos de prueba** (tarifas, cargos de ejemplo para los 4 estudiantes existentes) para que se pueda probar el flujo completo.

10. **Empieza por FASE 5** (Pagos/Cartera) porque es el corazón del sistema y las otras fases dependen de ella para los cargos automáticos.

---

## DOCUMENTO COMPLETO DEL PROYECTO

Para referencia detallada de flujos, estados, y especificaciones: `docs/PLAN_PROYECTO.md`
