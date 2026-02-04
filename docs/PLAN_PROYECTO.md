# Academia Heroicos - Plan de Proyecto Completo

## Índice
1. [Visión General](#1-visión-general)
2. [Stack Tecnológico](#2-stack-tecnológico)
3. [Arquitectura del Sistema](#3-arquitectura-del-sistema)
4. [Actores y Roles](#4-actores-y-roles)
5. [Estructura de Base de Datos](#5-estructura-de-base-de-datos)
6. [Fases de Desarrollo](#6-fases-de-desarrollo)
7. [Especificaciones Funcionales por Módulo](#7-especificaciones-funcionales-por-módulo)
8. [Flujos de Usuario Detallados](#8-flujos-de-usuario-detallados)
9. [API SendGrid](#9-api-sendgrid)
10. [Configuración de Entornos](#10-configuración-de-entornos)
11. [Decisiones Técnicas](#11-decisiones-técnicas)
12. [Guía para Nuevos Chats](#12-guía-para-nuevos-chats)

---

## 1. Visión General

### 1.1 Descripción del Proyecto
Sistema de gestión integral para la **Academia de Fútbol Heroicos**, que permite administrar:
- Inscripción de estudiantes y acudientes
- Control de cartera y pagos (CORAZÓN del sistema)
- Gestión de categorías y grupos por año de nacimiento
- Asistencia a clases
- Torneos con inscripción y cupos
- Clases particulares
- Horarios por grupos
- Comunicación vía email (SendGrid)

### 1.2 Objetivo Principal
Digitalizar completamente la operación de la academia, con énfasis especial en el **control de cartera** que incluye:
- Generación automática de cargos (matrícula, uniforme, mensualidades)
- Sistema de abonos (pagos parciales)
- Subida de comprobantes de pago
- Aprobación manual por administrador
- Generación de paz y salvos

### 1.3 Modalidad de Acceso
- **Web App** responsive (funciona en móvil y escritorio)
- **PWA** (Progressive Web App) - instalable desde navegador sin costo de Google Play
- Acceso vía: `https://[dominio]/public/`

---

## 2. Stack Tecnológico

### 2.1 Backend
| Componente | Tecnología | Versión |
|------------|------------|---------|
| Framework | CodeIgniter | 4.7.0 |
| Lenguaje | PHP | 8.1+ |
| Base de Datos | MySQL | 8.0 |
| ORM | CodeIgniter Model | Nativo |

### 2.2 Frontend (Por implementar)
| Componente | Tecnología | Justificación |
|------------|------------|---------------|
| CSS Framework | Bootstrap | 5.3 - Responsive |
| JavaScript | Vanilla JS + Fetch API | Sin dependencias |
| Iconos | Bootstrap Icons | Consistencia |
| Cámara/Fotos | MediaDevices API | Nativo del navegador |

### 2.3 Servicios Externos
| Servicio | Uso | Estado |
|----------|-----|--------|
| SendGrid | Envío de emails | Pendiente configurar API |
| DigitalOcean | Hosting BD producción | ✅ Configurado |

### 2.4 Entornos
| Entorno | Base de Datos | URL |
|---------|---------------|-----|
| Local | heroicos (XAMPP) | http://localhost/heroicos/public/ |
| Producción | heroicos (DigitalOcean) | Por definir |

---

## 3. Arquitectura del Sistema

### 3.1 Patrón MVC
```
app/
├── Config/          # Configuraciones (Database, Routes, etc.)
├── Controllers/     # Lógica de controladores
├── Models/          # Modelos de datos
├── Views/           # Vistas (Blade-like)
├── Filters/         # Middlewares (auth, permisos)
├── Libraries/       # Clases auxiliares (SendGrid, PDF)
├── Helpers/         # Funciones auxiliares
└── Database/
    ├── Migrations/  # 37 migraciones creadas
    └── Seeds/       # Datos iniciales
```

### 3.2 Estructura de URLs (Routes)
```
// Públicas
GET  /                          → Página de inicio/login
GET  /registro/{token}          → Formulario de inscripción (con token)
POST /registro/guardar          → Procesar inscripción

// Autenticación
GET  /login                     → Formulario login
POST /login/autenticar          → Procesar login
GET  /logout                    → Cerrar sesión
GET  /recuperar-password        → Solicitar recuperación
POST /recuperar-password        → Enviar email recuperación

// Panel Admin
GET  /admin/dashboard           → Dashboard principal
GET  /admin/estudiantes         → Listado estudiantes
GET  /admin/acudientes          → Listado acudientes
GET  /admin/profesores          → Gestión profesores
GET  /admin/categorias          → Gestión categorías/grupos
GET  /admin/horarios            → Gestión horarios
GET  /admin/cartera             → Control de cartera (principal)
GET  /admin/cartera/pagos       → Pagos pendientes de revisión
GET  /admin/cartera/morosos     → Estudiantes con deuda
GET  /admin/torneos             → Gestión torneos
GET  /admin/clases-particulares → Solicitudes y agenda
GET  /admin/configuracion       → Configuración del sistema
GET  /admin/reportes            → Reportes y exportación

// Panel Profesor
GET  /profesor/dashboard        → Dashboard profesor
GET  /profesor/grupos           → Mis grupos
GET  /profesor/asistencia/{id}  → Tomar asistencia de grupo
POST /profesor/asistencia/guardar
GET  /profesor/inscribir        → Generar enlace inscripción

// Panel Acudiente
GET  /acudiente/dashboard       → Dashboard acudiente
GET  /acudiente/perfil          → Mi perfil (editable)
GET  /acudiente/hijos           → Mis hijos (editable)
GET  /acudiente/pagos           → Estado de cuenta
POST /acudiente/pagos/subir     → Subir comprobante
GET  /acudiente/horarios        → Horarios de mis hijos
GET  /acudiente/paz-y-salvo     → Solicitar paz y salvo

// API interna
POST /api/notificaciones/marcar-leida
GET  /api/torneos/{id}/cupos
POST /api/clases-particulares/solicitar
```

### 3.3 Middlewares (Filters)
```php
// app/Config/Filters.php
'auth'      → Verificar sesión activa
'admin'     → Verificar rol administrador
'profesor'  → Verificar rol profesor
'acudiente' → Verificar rol acudiente
'permiso'   → Verificar permiso específico
```

---

## 4. Actores y Roles

### 4.1 Administrador (rol_id = 1)
**Permisos:** TODOS

**Funciones principales:**
- Gestionar usuarios (crear profesores, ver acudientes)
- Gestionar categorías, grupos, horarios
- **Aprobar/rechazar pagos** (función crítica)
- Ver y gestionar cartera completa
- Configurar tarifas y conceptos de cobro
- Gestionar torneos
- Agendar clases particulares
- Ver reportes y estadísticas
- Configuración del sistema

### 4.2 Profesor (rol_id = 2)
**Permisos limitados:**
- Ver estudiantes de sus grupos
- Tomar asistencia
- Ver horarios
- Generar enlaces de inscripción
- Ver torneos

**NO puede ver:**
- Datos de acudientes
- Información de pagos/cartera
- Configuración del sistema

### 4.3 Acudiente (rol_id = 3)
**Permisos:**
- Ver/editar su perfil
- Ver/editar datos de sus hijos
- Ver estado de cuenta (cargos y pagos)
- Subir comprobantes de pago
- Ver horarios de sus hijos
- Inscribirse a torneos
- Solicitar clases particulares
- Solicitar paz y salvo

---

## 5. Estructura de Base de Datos

### 5.1 Resumen de Tablas (38 total)

#### Usuarios y Autenticación
| Tabla | Registros iniciales | Descripción |
|-------|---------------------|-------------|
| roles | 3 | admin, profesor, acudiente |
| permisos | 16+ | Permisos granulares |
| rol_permisos | 16+ | Asignación rol-permiso |
| usuarios | 1 | Login (email + password hash) |
| administradores | 1 | Datos admin |
| profesores | 0 | Datos profesores |
| acudientes | 0 | Datos acudientes |

#### Estudiantes
| Tabla | Descripción |
|-------|-------------|
| estudiantes | Datos completos + foto + salud |
| estudiante_historial_deportivo | Academias anteriores, logros |

#### Estructura Académica
| Tabla | Descripción |
|-------|-------------|
| categorias | Ej: "Categoría 2017-2018" |
| categoria_anios | Años que pertenecen a cada categoría |
| grupos | Grupos dentro de categoría (A, B, C) |
| inscripciones | Estudiante → Grupo (con historial) |
| profesor_grupos | Profesor → Grupo |
| horarios | Definición de horarios |
| grupo_horarios | Asignación horario → grupo |

#### Módulo Financiero (CRÍTICO)
| Tabla | Descripción |
|-------|-------------|
| periodos | Años fiscales (2024, 2025, 2026) |
| conceptos_cobro | MAT, UNIF, MENS, TORN, CPAR, OTRO |
| tarifas | Precios por concepto/categoría/periodo |
| **cargos** | **DEUDAS - Lo que debe cada estudiante** |
| **pagos** | **Registros de pago (pendiente→aprobado)** |
| pago_detalles | Distribución pago entre cargos |
| comprobantes | Imágenes de comprobantes |
| paz_y_salvos | Documentos generados |

#### Torneos
| Tabla | Descripción |
|-------|-------------|
| torneos | Eventos con cupos, fechas, costo |
| torneo_inscripciones | Inscripciones de estudiantes |

#### Clases Particulares
| Tabla | Descripción |
|-------|-------------|
| solicitudes_clase_particular | Solicitudes de acudientes |
| clases_particulares | Clases agendadas |

#### Asistencia
| Tabla | Descripción |
|-------|-------------|
| sesiones_clase | Una sesión de clase |
| asistencias | Registro (default: presente=true) |

#### Sistema
| Tabla | Descripción |
|-------|-------------|
| notificaciones | Alertas en la app |
| emails_enviados | Log de SendGrid |
| plantillas_email | Plantillas HTML |
| configuracion | Parámetros del sistema |
| tokens_inscripcion | Enlaces de inscripción |
| auditoria | Log de cambios |
| ci_sessions | Sesiones de CodeIgniter |
| migrations | Control de migraciones |

### 5.2 Relaciones Clave

```
usuarios (1) ←→ (1) acudientes
usuarios (1) ←→ (1) profesores
usuarios (1) ←→ (1) administradores

acudientes (1) ←→ (N) estudiantes
estudiantes (N) ←→ (N) grupos (via inscripciones)
grupos (N) ←→ (1) categorias
categorias (1) ←→ (N) categoria_anios

estudiantes (1) ←→ (N) cargos
cargos (N) ←→ (N) pagos (via pago_detalles)
pagos (1) ←→ (N) comprobantes
acudientes (1) ←→ (N) pagos
```

### 5.3 Campos Críticos

#### Tabla `cargos` (Deudas)
```sql
valor_original    DECIMAL(12,2)  -- Monto inicial
valor_pagado      DECIMAL(12,2)  -- Suma de abonos
saldo_pendiente   DECIMAL(12,2)  -- Lo que falta (calculado)
estado            ENUM('pendiente', 'parcial', 'pagado', 'anulado')
```

#### Tabla `pagos`
```sql
estado            ENUM('pendiente_revision', 'aprobado', 'rechazado')
revisado_por      INT            -- Admin que revisó
fecha_revision    DATETIME
motivo_rechazo    TEXT           -- Si fue rechazado
```

---

## 6. Fases de Desarrollo

### FASE 1: Autenticación y Base (Prioridad: ALTA)
**Duración estimada: 1-2 sesiones**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 1.1 | Controllers/Auth.php | Login, logout, recuperar password |
| 1.2 | Filters/AuthFilter.php | Middleware de autenticación |
| 1.3 | Filters/RoleFilter.php | Middleware de roles |
| 1.4 | Views/auth/login.php | Vista de login |
| 1.5 | Views/layouts/main.php | Layout principal con navbar |
| 1.6 | Libraries/SendGridService.php | Servicio de emails |
| 1.7 | Models/UsuarioModel.php | Modelo de usuarios |

**Criterios de aceptación:**
- [ ] Login funcional con email/password
- [ ] Redirección según rol (admin→/admin, profesor→/profesor, acudiente→/acudiente)
- [ ] Logout funcional
- [ ] Sesiones persistentes
- [ ] Recuperación de password por email

---

### FASE 2: Flujo de Inscripción (Prioridad: ALTA)
**Duración estimada: 2-3 sesiones**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 2.1 | Controllers/Profesor/Inscripcion.php | Generar token de inscripción |
| 2.2 | Controllers/Registro.php | Procesar registro público |
| 2.3 | Views/registro/paso1_acudiente.php | Formulario acudiente |
| 2.4 | Views/registro/paso2_estudiante.php | Formulario estudiante |
| 2.5 | Views/registro/confirmacion.php | Confirmación final |
| 2.6 | Models/AcudienteModel.php | Modelo acudiente |
| 2.7 | Models/EstudianteModel.php | Modelo estudiante |
| 2.8 | Libraries/CargoService.php | Generar cargos automáticos |

**Flujo detallado:**
1. Profesor captura nombre + email del acudiente
2. Sistema genera token único (expira en 48h)
3. Sistema envía email con enlace
4. Acudiente accede al enlace
5. **Paso 1:** Autorización datos personales (checkbox obligatorio)
6. **Paso 1:** Formulario datos acudiente
7. **Paso 2:** Autorización datos del menor (checkbox obligatorio)
8. **Paso 2:** Formulario datos estudiante (con foto)
9. Opción: "Inscribir otro hijo" o "Finalizar"
10. Sistema crea usuario con password temporal
11. Sistema genera cargos automáticos:
    - Matrícula
    - Uniforme
    - Primera mensualidad
12. Sistema envía emails:
    - Al acudiente: Bienvenida + credenciales
    - Al profesor: Nuevo estudiante
    - A admins: Nuevo estudiante

**Criterios de aceptación:**
- [ ] Flujo completo funcional
- [ ] Fotos: tomar con cámara O cargar archivo
- [ ] Checkboxes de autorización obligatorios
- [ ] Cargos generados automáticamente
- [ ] Emails enviados correctamente

---

### FASE 3: Panel de Administración - Cartera (Prioridad: CRÍTICA)
**Duración estimada: 3-4 sesiones**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 3.1 | Controllers/Admin/Dashboard.php | Dashboard con métricas |
| 3.2 | Controllers/Admin/Cartera.php | Gestión de cartera |
| 3.3 | Controllers/Admin/Pagos.php | Revisión de pagos |
| 3.4 | Views/admin/cartera/index.php | Vista principal cartera |
| 3.5 | Views/admin/cartera/estudiante.php | Cuenta de estudiante |
| 3.6 | Views/admin/pagos/pendientes.php | Pagos por revisar |
| 3.7 | Views/admin/pagos/revisar.php | Detalle de pago |
| 3.8 | Models/CargoModel.php | Modelo de cargos |
| 3.9 | Models/PagoModel.php | Modelo de pagos |
| 3.10 | Libraries/CarteraService.php | Lógica de cartera |

**Funcionalidades:**
- Dashboard con:
  - Total cartera por cobrar
  - Pagos pendientes de revisión
  - Estudiantes morosos
  - Recaudo del mes
- Listado de estudiantes con saldo
- Detalle de cuenta por estudiante:
  - Histórico de cargos
  - Histórico de pagos
  - Saldo actual
- Revisión de pagos:
  - Ver comprobante (imagen)
  - Aprobar → Actualiza saldo de cargos
  - Rechazar → Notifica al acudiente
- Generación manual de cargos
- Anulación de cargos

**Criterios de aceptación:**
- [ ] Dashboard con métricas en tiempo real
- [ ] Flujo aprobar/rechazar funcional
- [ ] Cálculo correcto de saldos
- [ ] Histórico completo por estudiante

---

### FASE 4: Panel de Acudiente (Prioridad: ALTA)
**Duración estimada: 2 sesiones**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 4.1 | Controllers/Acudiente/Dashboard.php | Dashboard |
| 4.2 | Controllers/Acudiente/Perfil.php | Mi perfil |
| 4.3 | Controllers/Acudiente/Hijos.php | Mis hijos |
| 4.4 | Controllers/Acudiente/Pagos.php | Mis pagos |
| 4.5 | Views/acudiente/dashboard.php | Vista dashboard |
| 4.6 | Views/acudiente/pagos/index.php | Estado de cuenta |
| 4.7 | Views/acudiente/pagos/subir.php | Subir comprobante |

**Funcionalidades:**
- Dashboard con:
  - Saldo pendiente total
  - Próximos vencimientos
  - Notificaciones
- Ver/editar perfil
- Ver/editar datos de hijos
- Estado de cuenta:
  - Cargos pendientes (con fecha vencimiento)
  - Historial de pagos
  - Subir nuevo comprobante
- Solicitar paz y salvo (solo si saldo = 0)

**Criterios de aceptación:**
- [ ] Acudiente ve solo sus datos e hijos
- [ ] Subida de comprobante funcional
- [ ] Paz y salvo genera PDF y envía email

---

### FASE 5: Panel de Profesor (Prioridad: MEDIA)
**Duración estimada: 1-2 sesiones**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 5.1 | Controllers/Profesor/Dashboard.php | Dashboard |
| 5.2 | Controllers/Profesor/Grupos.php | Mis grupos |
| 5.3 | Controllers/Profesor/Asistencia.php | Tomar asistencia |
| 5.4 | Views/profesor/asistencia/tomar.php | Vista asistencia |

**Funcionalidades:**
- Dashboard con:
  - Mis grupos
  - Próximas clases
  - Asistencia pendiente
- Ver listado de estudiantes por grupo
- Tomar asistencia:
  - Lista con todos marcados como "presente"
  - Solo desmarcar ausentes
  - Guardar con un clic
- Generar enlace de inscripción

**Criterios de aceptación:**
- [ ] Asistencia simple y rápida
- [ ] Profesor NO ve datos de acudientes ni pagos
- [ ] Enlace de inscripción funcional

---

### FASE 6: Gestión de Categorías, Grupos y Horarios (Prioridad: MEDIA)
**Duración estimada: 1-2 sesiones**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 6.1 | Controllers/Admin/Categorias.php | CRUD categorías |
| 6.2 | Controllers/Admin/Grupos.php | CRUD grupos |
| 6.3 | Controllers/Admin/Horarios.php | CRUD horarios |
| 6.4 | Views/admin/categorias/* | Vistas |
| 6.5 | Views/admin/grupos/* | Vistas |
| 6.6 | Views/admin/horarios/* | Vistas |

**Funcionalidades:**
- Categorías:
  - Crear con uno o varios años (ej: 2017-2018)
  - Activar/desactivar
- Grupos:
  - Crear dentro de categoría
  - Asignar cupo máximo
  - Asignar profesores
- Horarios:
  - Definir día, hora inicio, hora fin, lugar
  - Asignar a grupos

**Criterios de aceptación:**
- [ ] CRUD completo
- [ ] Un estudiante puede cambiar de grupo (con historial)

---

### FASE 7: Módulo de Torneos (Prioridad: MEDIA)
**Duración estimada: 1-2 sesiones**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 7.1 | Controllers/Admin/Torneos.php | CRUD torneos |
| 7.2 | Controllers/Acudiente/Torneos.php | Inscripción |
| 7.3 | Views/admin/torneos/* | Vistas admin |
| 7.4 | Views/acudiente/torneos/* | Vistas acudiente |
| 7.5 | Libraries/TorneoService.php | Lógica de torneos |

**Funcionalidades:**
- Admin:
  - Crear torneo con: nombre, fecha, lugar, cupos, costo, categoría
  - Fechas apertura/cierre inscripción
  - Ver inscritos
  - Cerrar inscripciones manualmente
- Acudiente:
  - Ver torneos disponibles para su categoría
  - Ver cupos disponibles (ej: "3/15 inscritos")
  - Inscribirse (genera cargo automático)
  - Subir comprobante de pago
- Sistema:
  - Enviar email a acudientes cuando se abre inscripción

**Criterios de aceptación:**
- [ ] Control de cupos funcional
- [ ] Cargo automático al inscribir
- [ ] Email de notificación

---

### FASE 8: Clases Particulares (Prioridad: BAJA)
**Duración estimada: 1 sesión**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 8.1 | Controllers/Admin/ClasesParticulares.php | Gestión |
| 8.2 | Controllers/Acudiente/ClasesParticulares.php | Solicitar |
| 8.3 | Views/admin/clases-particulares/* | Vistas |

**Funcionalidades:**
- Acudiente:
  - Solicitar clase (fecha preferida, duración, observaciones)
- Admin:
  - Ver solicitudes pendientes
  - Aprobar/rechazar
  - Agendar (asignar profesor, fecha, hora)
  - Generar cargo
- Sistema:
  - Notificar al acudiente cuando se agenda

**Criterios de aceptación:**
- [ ] Flujo solicitud→aprobación→agendamiento
- [ ] Cargo generado automáticamente

---

### FASE 9: Reportes y Exportación (Prioridad: BAJA)
**Duración estimada: 1 sesión**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 9.1 | Controllers/Admin/Reportes.php | Reportes |
| 9.2 | Libraries/ExportService.php | Exportar a Excel/PDF |

**Reportes:**
- Cartera por cobrar (con filtros)
- Recaudo por período
- Estudiantes por categoría/grupo
- Asistencia por grupo
- Morosos

**Criterios de aceptación:**
- [ ] Exportación a Excel funcional
- [ ] Filtros por fecha, categoría, estado

---

### FASE 10: PWA y Notificaciones Push (Prioridad: BAJA)
**Duración estimada: 1 sesión**

| Tarea | Archivos | Descripción |
|-------|----------|-------------|
| 10.1 | public/manifest.json | Manifest PWA |
| 10.2 | public/sw.js | Service Worker |
| 10.3 | Libraries/PushService.php | Notificaciones push |

**Funcionalidades:**
- Instalable desde navegador
- Cache offline para consultas
- Notificaciones push (Firebase Cloud Messaging - gratis)

---

## 7. Especificaciones Funcionales por Módulo

### 7.1 Módulo de Cartera (Detalle)

#### 7.1.1 Generación Automática de Cargos
```
Al inscribir estudiante:
├── Matrícula (concepto_id = 1, tipo = unico)
├── Uniforme (concepto_id = 2, tipo = unico)
└── Primera mensualidad (concepto_id = 3, tipo = recurrente)

Mensualmente (Job/Cron):
└── Para cada estudiante con estado = 'activo':
    └── Generar cargo de mensualidad del mes siguiente
        └── Fecha vencimiento: día 5 del mes
```

#### 7.1.2 Flujo de Pago
```
1. Acudiente ve cargos pendientes
2. Acudiente sube comprobante:
   - Selecciona cargos a pagar (checkbox)
   - Ingresa valor total del pago
   - Selecciona método (transferencia, Nequi, etc.)
   - Sube imagen del comprobante
   - Sistema valida: valor >= suma de cargos seleccionados
3. Sistema crea registro en `pagos` con estado = 'pendiente_revision'
4. Sistema notifica a admins
5. Admin revisa:
   - Ve imagen del comprobante
   - Ve cargos seleccionados
   - APRUEBA:
     - Actualiza estado pago = 'aprobado'
     - Actualiza `cargos.valor_pagado` += valor aplicado
     - Actualiza `cargos.saldo_pendiente` = valor_original - valor_pagado
     - Si saldo_pendiente = 0: estado = 'pagado'
     - Si saldo_pendiente > 0 y valor_pagado > 0: estado = 'parcial'
     - Notifica al acudiente
   - RECHAZA:
     - Actualiza estado pago = 'rechazado'
     - Registra motivo
     - Notifica al acudiente
```

#### 7.1.3 Sistema de Abonos
```
Cargo: Mensualidad Enero - Valor: $100,000

Pago 1: $50,000 → valor_pagado = 50,000, saldo = 50,000, estado = 'parcial'
Pago 2: $30,000 → valor_pagado = 80,000, saldo = 20,000, estado = 'parcial'
Pago 3: $20,000 → valor_pagado = 100,000, saldo = 0, estado = 'pagado'
```

### 7.2 Módulo de Asistencia (Detalle)

#### 7.2.1 Flujo de Toma de Asistencia
```
1. Profesor accede a "Tomar asistencia"
2. Selecciona grupo
3. Sistema muestra:
   - Fecha actual
   - Lista de estudiantes del grupo
   - Todos con checkbox marcado (presente = true)
4. Profesor desmarca los ausentes
5. Opcional: agrega justificación
6. Clic en "Guardar"
7. Sistema crea/actualiza registros en `asistencias`
```

### 7.3 Módulo de Torneos (Detalle)

#### 7.3.1 Estados del Torneo
```
programado → inscripciones_abiertas → inscripciones_cerradas → en_curso → finalizado
                                   ↘ cancelado
```

#### 7.3.2 Control de Cupos
```
cupo_maximo = 15
inscritos = SELECT COUNT(*) FROM torneo_inscripciones WHERE torneo_id = X AND estado != 'cancelado'
disponibles = cupo_maximo - inscritos

Si disponibles <= 0:
  → No permitir más inscripciones
  → Mostrar "Cupos agotados"
```

---

## 8. Flujos de Usuario Detallados

### 8.1 Flujo de Inscripción Completo

```
┌─────────────────────────────────────────────────────────────────────┐
│                    FLUJO DE INSCRIPCIÓN                             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  PROFESOR                                                           │
│  ────────                                                           │
│  1. Accede a "Inscribir estudiante"                                │
│  2. Ingresa: nombre y email del acudiente                          │
│  3. Clic en "Enviar enlace"                                        │
│  4. Sistema genera token y envía email                             │
│                                                                     │
│  ACUDIENTE (por email)                                             │
│  ────────────────────                                               │
│  5. Recibe email con enlace: /registro/{token}                     │
│  6. Accede al enlace                                               │
│                                                                     │
│  PASO 1 - DATOS ACUDIENTE                                          │
│  ────────────────────────                                           │
│  7. Lee política de tratamiento de datos                           │
│  8. Marca checkbox: "Autorizo el tratamiento de mis datos"         │
│  9. Completa formulario:                                           │
│     - Tipo documento (CC/CE/Pasaporte)                             │
│     - Número documento                                              │
│     - Nombres                                                       │
│     - Apellidos                                                     │
│     - Teléfono principal                                           │
│     - Teléfono alternativo (opcional)                              │
│     - Dirección                                                     │
│     - Ciudad                                                        │
│     - Parentesco (Padre/Madre/Tío/Abuelo/Otro)                    │
│     - Ocupación                                                     │
│  10. Clic en "Guardar y continuar"                                 │
│                                                                     │
│  PASO 2 - DATOS ESTUDIANTE                                         │
│  ─────────────────────────                                          │
│  11. Lee política de tratamiento de datos del menor                │
│  12. Marca checkbox: "Como representante legal, autorizo..."       │
│  13. Completa formulario:                                          │
│      - Foto (tomar con cámara o cargar archivo)                    │
│      - Nombres                                                      │
│      - Apellidos                                                    │
│      - Tipo documento (TI/RC/Pasaporte)                            │
│      - Número documento                                             │
│      - Fecha de nacimiento                                          │
│      - Sexo (M/F)                                                   │
│      - Dirección                                                    │
│      - Teléfono                                                     │
│      - EPS                                                          │
│      - Grupo sanguíneo                                              │
│      - Alergias                                                     │
│      - Condiciones médicas                                          │
│      - Medicamentos                                                 │
│      - Contacto de emergencia                                       │
│      - Teléfono de emergencia                                       │
│  14. SECCIÓN: Historial deportivo (opcional)                       │
│      - Academia anterior                                            │
│      - Período                                                      │
│      - Torneos participados                                         │
│      - Logros                                                       │
│      - Posición de juego preferida                                 │
│  15. Clic en "Inscribir otro hijo" → Repite paso 2                 │
│      O clic en "Finalizar inscripción"                             │
│                                                                     │
│  SISTEMA (automático)                                               │
│  ────────────────────                                               │
│  16. Crea usuario con:                                             │
│      - email del acudiente                                          │
│      - password temporal (aleatorio)                                │
│      - rol = acudiente                                              │
│      - estado = activo                                              │
│  17. Crea registro en `acudientes`                                 │
│  18. Crea registro(s) en `estudiantes`                             │
│  19. Genera código único para cada estudiante (HER-2024-0001)      │
│  20. Genera cargos automáticos:                                    │
│      - Matrícula                                                    │
│      - Uniforme                                                     │
│      - Primera mensualidad                                          │
│  21. Marca token como usado                                        │
│  22. Envía emails:                                                 │
│      - A acudiente: Bienvenida + credenciales + link descarga PWA  │
│      - A profesor que generó el enlace: Notificación               │
│      - A administradores: Nuevo estudiante inscrito                │
│                                                                     │
│  CONFIRMACIÓN                                                       │
│  ────────────                                                       │
│  23. Muestra pantalla de éxito con:                                │
│      - Resumen de estudiantes inscritos                            │
│      - Credenciales de acceso                                       │
│      - Instrucciones para descargar PWA                            │
│      - Botón "Ir al login"                                         │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 8.2 Flujo de Pago

```
┌─────────────────────────────────────────────────────────────────────┐
│                      FLUJO DE PAGO                                  │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ACUDIENTE                                                          │
│  ──────────                                                         │
│  1. Accede a "Mis pagos"                                           │
│  2. Ve listado de cargos pendientes:                               │
│     ┌────────────────────────────────────────────────────────┐     │
│     │ ☐ Matrícula 2024        $150,000    Vence: 15/01/2024 │     │
│     │ ☐ Uniforme              $120,000    Vence: 15/01/2024 │     │
│     │ ☑ Mensualidad Enero     $100,000    Vence: 05/01/2024 │     │
│     │ ☑ Mensualidad Febrero   $100,000    Vence: 05/02/2024 │     │
│     └────────────────────────────────────────────────────────┘     │
│  3. Selecciona cargos a pagar (checkbox)                           │
│  4. Sistema muestra total: $200,000                                │
│  5. Clic en "Registrar pago"                                       │
│  6. Formulario de pago:                                            │
│     - Valor pagado: [200000]                                       │
│     - Método: [Transferencia ▼]                                    │
│     - Banco: [Bancolombia]                                         │
│     - Referencia: [123456789]                                      │
│     - Fecha del pago: [04/02/2024]                                 │
│     - Comprobante: [Seleccionar archivo / Tomar foto]              │
│  7. Clic en "Enviar pago"                                          │
│  8. Sistema muestra: "Pago enviado. Será revisado en 24-48 horas"  │
│                                                                     │
│  SISTEMA                                                            │
│  ───────                                                            │
│  9. Crea registro en `pagos`:                                      │
│     - estado = 'pendiente_revision'                                │
│     - numero_recibo = 'REC-2024-00001' (autogenerado)              │
│  10. Crea registros en `pago_detalles`:                            │
│      - pago_id, cargo_id, valor_aplicado                           │
│  11. Guarda imagen en `comprobantes`                               │
│  12. Crea notificación para admins                                 │
│  13. Envía email a admins: "Nuevo pago pendiente de revisión"      │
│                                                                     │
│  ADMIN                                                              │
│  ─────                                                              │
│  14. Accede a "Pagos pendientes"                                   │
│  15. Ve listado de pagos por revisar                               │
│  16. Clic en un pago                                               │
│  17. Ve detalle:                                                   │
│      - Datos del acudiente                                          │
│      - Estudiante(s) relacionado(s)                                │
│      - Cargos incluidos                                             │
│      - Imagen del comprobante (zoom disponible)                    │
│      - Valor declarado vs valor de cargos                          │
│  18. Opciones:                                                     │
│      [APROBAR] → Continúa en paso 19                               │
│      [RECHAZAR] → Continúa en paso 23                              │
│                                                                     │
│  APROBAR                                                            │
│  ────────                                                           │
│  19. Admin clic en "Aprobar"                                       │
│  20. Sistema actualiza:                                            │
│      - pagos.estado = 'aprobado'                                   │
│      - pagos.revisado_por = admin_id                               │
│      - pagos.fecha_revision = NOW()                                │
│      - Para cada cargo en pago_detalles:                           │
│        - cargos.valor_pagado += valor_aplicado                     │
│        - cargos.saldo_pendiente = valor_original - valor_pagado    │
│        - Si saldo_pendiente = 0: estado = 'pagado'                 │
│        - Si saldo_pendiente > 0: estado = 'parcial'                │
│  21. Sistema crea notificación para acudiente                      │
│  22. Sistema envía email: "Tu pago ha sido aprobado"               │
│                                                                     │
│  RECHAZAR                                                           │
│  ─────────                                                          │
│  23. Admin clic en "Rechazar"                                      │
│  24. Admin ingresa motivo: "Comprobante ilegible"                  │
│  25. Sistema actualiza:                                            │
│      - pagos.estado = 'rechazado'                                  │
│      - pagos.revisado_por = admin_id                               │
│      - pagos.fecha_revision = NOW()                                │
│      - pagos.motivo_rechazo = "Comprobante ilegible"               │
│  26. Sistema crea notificación para acudiente                      │
│  27. Sistema envía email: "Problema con tu pago" + motivo          │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 9. API SendGrid

### 9.1 Configuración
El cliente tiene acceso a SendGrid. Debe proporcionar:
- API Key
- Email remitente verificado (ej: noreply@heroicos.com)

### 9.2 Plantillas de Email Definidas

| Código | Uso | Variables |
|--------|-----|-----------|
| bienvenida | Al completar inscripción | nombre_acudiente, email, password_temporal |
| nuevo_estudiante | Notificar a admin/profesor | nombre_estudiante, nombre_acudiente, telefono |
| pago_recibido | Confirmar recepción de pago | nombre_acudiente, valor, numero_recibo |
| pago_aprobado | Pago verificado | nombre_acudiente, valor, numero_recibo |
| pago_rechazado | Pago con problemas | nombre_acudiente, motivo |
| paz_y_salvo | Adjuntar PDF | nombre_acudiente, nombre_estudiante |
| torneo_disponible | Nuevo torneo | nombre_torneo, fecha, cupos, costo |
| enlace_inscripcion | Token de registro | nombre_acudiente, enlace |
| recuperar_password | Reset password | nombre, enlace |

### 9.3 Implementación
```php
// app/Libraries/SendGridService.php
class SendGridService {
    public function enviar($destinatario, $plantilla, $variables): bool
    public function enviarConAdjunto($destinatario, $plantilla, $variables, $archivo): bool
}
```

---

## 10. Configuración de Entornos

### 10.1 Archivo .env (NO subir a Git)
```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'http://localhost/heroicos/public/'

#--------------------------------------------------------------------
# DATABASE - LOCAL
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = heroicos
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306

#--------------------------------------------------------------------
# DATABASE - PRODUCCIÓN
#--------------------------------------------------------------------
database.production.hostname = [VER_ARCHIVO_ENV_LOCAL]
database.production.database = heroicos
database.production.username = [VER_ARCHIVO_ENV_LOCAL]
database.production.password = [VER_ARCHIVO_ENV_LOCAL]
database.production.port = 25060

#--------------------------------------------------------------------
# SENDGRID
#--------------------------------------------------------------------
sendgrid.apiKey = 'SG.XXXXX'
sendgrid.fromEmail = 'noreply@heroicos.com'
sendgrid.fromName = 'Academia Heroicos'
```

### 10.2 Credenciales Importantes

| Servicio | Usuario | Contraseña | Notas |
|----------|---------|------------|-------|
| Admin inicial | admin@heroicos.com | Admin123* | Cambiar en producción |
| BD Local | root | (vacío) | XAMPP default |
| BD Producción | [VER .env LOCAL] | [VER .env LOCAL] | DigitalOcean Aiven |

---

## 11. Decisiones Técnicas

### 11.1 ¿Por qué CodeIgniter 4?
- Ligero y rápido
- Curva de aprendizaje baja
- Buena documentación en español
- No requiere Node.js ni compilación de assets
- Funciona bien en hosting compartido

### 11.2 ¿Por qué NO SPA (React/Vue)?
- Mayor complejidad
- El cliente necesita algo funcional rápido
- SEO no es prioridad
- No hay API externa que consumir

### 11.3 ¿Por qué PWA en lugar de App Nativa?
- Costo $0 (no requiere cuenta de desarrollador)
- Un solo código para web y móvil
- Instalable desde navegador
- Acceso a cámara para fotos
- Puede funcionar offline (para consultas)

### 11.4 Estructura de Archivos de Uploads
```
writable/uploads/
├── fotos/
│   └── estudiantes/
│       └── {estudiante_id}/
│           └── foto.jpg
├── comprobantes/
│   └── {año}/
│       └── {mes}/
│           └── {pago_id}_{timestamp}.jpg
├── torneos/
│   └── {torneo_id}/
│       └── imagen.jpg
└── paz_y_salvos/
    └── {año}/
        └── {numero}.pdf
```

---

## 12. Guía para Nuevos Chats

### 12.1 Contexto Inicial para Copiar/Pegar
```
Estoy desarrollando un sistema de gestión para la Academia de Fútbol Heroicos
usando CodeIgniter 4. El proyecto ya tiene:

- 37 migraciones ejecutadas (local y producción)
- Base de datos con 38 tablas
- Configuración de dos entornos (local XAMPP y producción DigitalOcean)
- Usuario admin creado: admin@heroicos.com / Admin123*

El documento completo del proyecto está en: docs/PLAN_PROYECTO.md

Repositorio: https://github.com/edielestudiante2023/heroicos

Actualmente necesito trabajar en: [ESPECIFICAR FASE]
```

### 12.2 Archivos Clave para Leer
Cuando inicies un nuevo chat, pide leer estos archivos:
1. `docs/PLAN_PROYECTO.md` - Este documento
2. `app/Config/Routes.php` - Rutas definidas
3. `app/Config/Database.php` - Configuración BD
4. `.env` - Variables de entorno (si existe)

### 12.3 Comandos Útiles
```bash
# Ejecutar migraciones
php spark migrate

# Crear controlador
php spark make:controller NombreController

# Crear modelo
php spark make:model NombreModel

# Iniciar servidor de desarrollo
php spark serve

# Ver rutas definidas
php spark routes
```

### 12.4 Estado Actual del Proyecto
- [x] FASE 0: Setup inicial y migraciones
- [ ] FASE 1: Autenticación y Base
- [ ] FASE 2: Flujo de Inscripción
- [ ] FASE 3: Panel Admin - Cartera
- [ ] FASE 4: Panel Acudiente
- [ ] FASE 5: Panel Profesor
- [ ] FASE 6: Categorías, Grupos, Horarios
- [ ] FASE 7: Torneos
- [ ] FASE 8: Clases Particulares
- [ ] FASE 9: Reportes
- [ ] FASE 10: PWA

---

## Anexos

### A. Conceptos de Cobro Predefinidos
| Código | Nombre | Tipo | Auto-inscribir |
|--------|--------|------|----------------|
| MAT | Matrícula | unico | Sí |
| UNIF | Uniforme | unico | Sí |
| MENS | Mensualidad | recurrente | Sí |
| TORN | Torneo | unico | No |
| CPAR | Clase Particular | unico | No |
| OTRO | Otro concepto | unico | No |

### B. Estados de Entidades

**Estudiante:** activo, inactivo, retirado
**Usuario:** activo, inactivo, pendiente
**Cargo:** pendiente, parcial, pagado, anulado
**Pago:** pendiente_revision, aprobado, rechazado
**Torneo:** programado, inscripciones_abiertas, inscripciones_cerradas, en_curso, finalizado, cancelado
**Clase Particular:** programada, realizada, cancelada, no_asistio
**Solicitud Clase:** pendiente, aprobada, rechazada, agendada

### C. Roles y Permisos
```
ADMIN (rol_id=1): Todos los permisos
PROFESOR (rol_id=2): estudiantes.ver, categorias.ver, horarios.ver, asistencia.*, torneos.ver
ACUDIENTE (rol_id=3): Solo sus datos y los de sus hijos
```

---

*Documento generado el 4 de febrero de 2026*
*Versión: 1.0*
