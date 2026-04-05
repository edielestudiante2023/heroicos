# HARDENING DE REPOSITORIO — heroicos

**Fecha:** 2026-04-05
**Aplicativo:** heroicos — Sistema de Gestion de Academia de Futbol
**Empresa:** Cycloid Talent
**Preparado para:** Edwin Lopez (consultor de infraestructura)

---

## TABLA DE CONTENIDO

1. Descripcion del aplicativo
2. Mapa de base de datos
3. Inventario de API Keys y servicios externos
4. Documentacion del proyecto (README, CONTRIBUTING, .env.example)
5. Ramas de trabajo
6. Pipelines CI/CD (Gitea)
7. Organizacion del repositorio
8. Hallazgos criticos y acciones pendientes

---

## 1. DESCRIPCION DEL APLICATIVO

### Stack tecnologico

| Componente | Tecnologia |
| --- | --- |
| Backend | PHP 8.2 + CodeIgniter 4.7 |
| Base de datos | MySQL 8.0 (DigitalOcean Managed, SSL required) |
| Servidor web | Apache (XAMPP local) / Nginx (produccion) |
| Email | SendGrid API v8.1 |
| PDF | TCPDF 6.10 (paz y salvos, certificados) |
| Frontend | Bootstrap 5.3 + Vanilla JS |
| PWA | Service Workers + IndexedDB |
| Iconos | Bootstrap Icons |

### Modulos principales (12)

| Modulo | Descripcion |
| --- | --- |
| Autenticacion | Login con roles, remember me, recuperacion de password por email |
| Gestion de usuarios | CRUD de admin, profesores y acudientes con envio de credenciales |
| Estudiantes | Registro, perfil con foto, historial deportivo, consentimientos |
| Grupos y categorias | Categorias por edad, grupos con cupos, inscripcion/desinscripcion |
| Horarios | Definicion de horarios y asignacion a grupos |
| Cartera | Generacion automatica de cargos, seguimiento de pagos, recordatorios |
| Pagos | Carga de comprobantes, revision admin, aprobacion/rechazo |
| Tarifas | Configuracion de montos por concepto, categoria y periodo |
| Asistencia | Registro por sesion de clase, reportes por grupo y fechas |
| Torneos | Creacion, inscripcion con cupo, cobro asociado |
| Clases particulares | Solicitud, negociacion de precio, reasignacion entre profesores |
| Paz y salvos | Solicitud, generacion PDF, envio por email |

### Roles de usuario

| Rol | Acceso |
| --- | --- |
| admin | Todo el sistema + gestion de usuarios + configuracion + auditoria |
| profesor | Grupos asignados + asistencia + enlaces de inscripcion + clases particulares |
| acudiente | Portal de estudiantes + pagos + horarios + paz y salvos + clases particulares |

### Estructura del proyecto

```text
heroicos/
├── app/
│   ├── Config/            # Routes, Database, Filters, App
│   ├── Controllers/
│   │   ├── Admin/         # ~12 controladores
│   │   ├── Profesor/      # 5 controladores
│   │   ├── Acudiente/     # 7 controladores
│   │   └── Api/           # OfflineController (PWA sync)
│   ├── Database/
│   │   ├── Migrations/    # 42 migraciones
│   │   └── Seeds/         # Seeders
│   ├── Filters/           # AuthFilter, RoleFilter, ApiAuthFilter
│   ├── Libraries/         # SendGridService, PazYSalvoPdfGenerator
│   ├── Models/            # ~27 modelos
│   └── Views/             # Vistas organizadas por rol
├── public/                # Punto de entrada web
├── vendor/                # Dependencias Composer
├── writable/              # Logs, cache, sesiones, uploads
├── docs/                  # Documentacion tecnica
├── tests/                 # Tests PHPUnit
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

### Cron jobs

No se detectaron cron jobs programados en el sistema. La aplicacion es event-driven. El deploy se ejecuta via scripts manuales (`app/Database/deploy_migrations.php`).

---

## 2. MAPA DE BASE DE DATOS

**Motor:** MySQL 8.0.45 (DigitalOcean Managed)
**Base de datos:** heroicos
**Tamano total:** 3.59 MB
**SSL:** Required

### Usuarios de base de datos

| Usuario | Permisos | Uso |
| --- | --- | --- |
| cycloid_userdb | Full access | Aplicacion principal (CRUD) |
| cycloid_readonly | SELECT only | Consultas de solo lectura |
| doadmin | Superuser | Administracion DigitalOcean |

### Resumen

- **39 tablas** (BASE TABLE)
- **0 vistas** (VIEW)
- **49 foreign keys** definidas
- **10 tablas vacias** (26%) — modulos pendientes de uso

### Tablas por modulo

**Nucleo (7 tablas):** usuarios (19 reg), roles (2), permisos (16), rol_permisos (16), administradores (1), configuracion (5), auditoria (79)

**Academico (10 tablas):** estudiantes (15 reg), acudientes (14), categorias (6), categoria_anios (0), grupos (4), inscripciones (4), profesores (3), profesor_grupos (3), horarios (10), grupo_horarios (6)

**Financiero (8 tablas):** conceptos_cobro (6), tarifas (4), cargos (11), pagos (4), pago_detalles (4), comprobantes (0), periodos (0), paz_y_salvos (0)

**Comunicaciones (4 tablas):** emails_enviados (160 reg — 1.58 MB, la mas grande), plantillas_email (16), notificaciones (2), tokens_inscripcion (49)

**Torneos (2 tablas):** torneos (0), torneo_inscripciones (0)

**Clases particulares (3 tablas):** solicitudes_clase_particular (0), clases_particulares (0), sesiones_clase (0)

**Otros (5 tablas):** asistencias (0), ci_sessions (63), migrations (0), consentimientos (65), estudiante_historial_deportivo (0)

### Tabla central: usuarios

La tabla `usuarios` es la entidad central. Las tablas `acudientes`, `administradores` y `profesores` dependen de ella via FK `usuario_id`. A su vez, `estudiantes` depende de `acudientes`, y la mayoria de las 39 tablas restantes dependen transitivamente de esta cadena.

### Foreign keys (49 relaciones)

| Tabla origen | Columna | Tabla destino |
| --- | --- | --- |
| acudientes | usuario_id | usuarios |
| administradores | usuario_id | usuarios |
| asistencias | estudiante_id | estudiantes |
| asistencias | registrado_por | usuarios |
| asistencias | sesion_id | sesiones_clase |
| cargos | estudiante_id | estudiantes |
| cargos | concepto_id | conceptos_cobro |
| cargos | periodo_id | periodos |
| categoria_anios | categoria_id | categorias |
| clases_particulares | solicitud_id | solicitudes_clase_particular |
| clases_particulares | estudiante_id | estudiantes |
| clases_particulares | profesor_id | profesores |
| clases_particulares | cargo_id | cargos |
| comprobantes | pago_id | pagos |
| estudiante_historial_deportivo | estudiante_id | estudiantes |
| estudiantes | acudiente_id | acudientes |
| grupo_horarios | grupo_id | grupos |
| grupo_horarios | horario_id | horarios |
| grupos | categoria_id | categorias |
| inscripciones | estudiante_id | estudiantes |
| inscripciones | grupo_id | grupos |
| notificaciones | usuario_id | usuarios |
| pago_detalles | pago_id | pagos |
| pago_detalles | cargo_id | cargos |
| pagos | acudiente_id | acudientes |
| pagos | revisado_por | usuarios |
| paz_y_salvos | estudiante_id | estudiantes |
| paz_y_salvos | acudiente_id | acudientes |
| profesor_grupos | profesor_id | profesores |
| profesor_grupos | grupo_id | grupos |
| profesores | usuario_id | usuarios |
| rol_permisos | rol_id | roles |
| rol_permisos | permiso_id | permisos |
| sesiones_clase | grupo_id | grupos |
| sesiones_clase | profesor_id | profesores |
| sesiones_clase | horario_id | horarios |
| solicitudes_clase_particular | acudiente_id | acudientes |
| solicitudes_clase_particular | estudiante_id | estudiantes |
| solicitudes_clase_particular | profesor_id | profesores |
| tarifas | concepto_id | conceptos_cobro |
| tarifas | categoria_id | categorias |
| tarifas | periodo_id | periodos |
| tokens_inscripcion | profesor_id | profesores |
| torneo_inscripciones | torneo_id | torneos |
| torneo_inscripciones | estudiante_id | estudiantes |
| torneo_inscripciones | acudiente_id | acudientes |
| torneo_inscripciones | cargo_id | cargos |
| torneos | categoria_id | categorias |
| usuarios | rol_id | roles |

### Tablas mas grandes por peso

| Tabla | Registros | Tamano |
| --- | --- | --- |
| emails_enviados | 160 | 1.58 MB |
| cargos | 11 | 96 KB |
| clases_particulares | 0 | 96 KB |
| consentimientos | 65 | 96 KB |
| pagos | 4 | 96 KB |
| torneo_inscripciones | 0 | 96 KB |

### Tablas vacias (10 de 39)

asistencias, categoria_anios, clases_particulares, comprobantes, estudiante_historial_deportivo, migrations, paz_y_salvos, periodos, sesiones_clase, solicitudes_clase_particular, torneos, torneo_inscripciones

### Observaciones

- **emails_enviados** es la tabla mas pesada (1.58 MB) con 160 registros — almacena cuerpo HTML de emails
- **10 tablas vacias** corresponden a modulos construidos pero sin uso en produccion (torneos, clases particulares, asistencia, paz y salvos)
- No existen vistas SQL — todo se resuelve via queries en modelos
- La integridad referencial esta bien definida con 49 FKs

---

## 3. INVENTARIO DE API KEYS Y SERVICIOS EXTERNOS

### Resumen

| Servicio | Variable | Archivos | Estado |
| --- | --- | --- | --- |
| SendGrid | `sendgrid.apiKey` | 1 (centralizado) | Activa |
| Deploy Token | hardcodeado en deploy.php | 1 | Activa |

### SendGrid (email transaccional)

Centralizado en una sola clase: `app/Libraries/SendGridService.php`

**Patron:** `env('sendgrid.apiKey', '')`

**Uso:** Todo el email transaccional del sistema:
- Recuperacion de password
- Envio de credenciales a nuevos usuarios
- Notificaciones de inscripcion
- Paz y salvos por email
- Clases particulares (solicitud, respuesta, reasignacion)

**Variables en .env:**
- `sendgrid.apiKey` — API Key de SendGrid
- `sendgrid.fromEmail` — Email remitente
- `sendgrid.fromName` — Nombre remitente

**Evaluacion:** BUENA — El email esta centralizado en `SendGridService`, no hay instancias directas de `new SendGrid()` dispersas en controladores. La API key se carga desde `.env` via `env()`.

### Deploy Token

**Archivo:** `public/deploy.php:13`
**Problema:** Token hardcodeado: `$secretToken = 'heroicos2024deploy'`
**Mitigacion:** El archivo esta en `.gitignore`, no se commitea. Pero si alguien lo despliega manualmente, queda expuesto.

### Hallazgos de seguridad

| Archivo | Problema | Severidad |
| --- | --- | --- |
| `.env` (local) | Contiene password de BD produccion (formato `AVNS_*`) | INFORMATIVO — no esta en git |
| `public/deploy.php` | Token hardcodeado `heroicos2024deploy` | MEDIA — archivo en .gitignore |

**Nota positiva:** No se encontraron credenciales hardcodeadas en el codigo fuente PHP trackeado. La SendGrid API key se maneja correctamente via `.env`.

---

## 4. DOCUMENTACION DEL PROYECTO

### Archivos creados en el repositorio

| Archivo | Descripcion |
| --- | --- |
| `README.md` | Documentacion principal: stack, modulos, roles, estructura, instalacion, deploy |
| `CONTRIBUTING.md` | Guia de contribucion: flujo de ramas, convencion de commits, reglas, revision |
| `.env.example` | Template con todas las variables de entorno necesarias (sin valores reales) |

### README.md incluye

- Stack tecnologico completo
- 12 modulos con descripcion
- 3 roles de usuario con accesos
- Estructura de carpetas
- Requisitos previos e instrucciones de instalacion
- 9 variables de entorno documentadas
- Instrucciones de deploy
- Links a documentacion adicional

### CONTRIBUTING.md incluye

- Flujo de ramas (main → develop → feature/ → hotfix/)
- Convencion de commits (feat:, fix:, docs:, refactor:, chore:)
- Convencion de nombres de ramas
- 5 reglas (no push directo, no credenciales, no temporales, no destructivos, no force push)
- Proceso de revision con pipeline CI/CD

### .env.example incluye

- Variables de BD local y produccion (sin valores)
- Variables de SendGrid (apiKey, fromEmail, fromName)
- Encryption key y session config
- Comentarios explicativos por seccion

---

## 5. RAMAS DE TRABAJO

### Estructura creada

```text
main          ← Produccion. Solo codigo validado y estable.
develop       ← Integracion. Aqui se unen los cambios antes de ir a main.
feature/xxx   ← Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    ← Correcciones urgentes. Se crean desde main.
```

### Estado actual

| Rama | Estado | Commit actual |
| --- | --- | --- |
| main | Existente, en remoto | Produccion estable |
| develop | Creada desde main, pendiente push a remoto | Mismo commit que main |
| cycloid | Legacy — sera reemplazada por develop | 14 commits adelante de main |

### Proteccion de ramas (pendiente en Gitea)

- **main:** protegida, requiere PR, no push directo
- **develop:** protegida, requiere PR desde feature/

### Flujo de trabajo

- Nueva funcionalidad: `develop` → `feature/nombre` → PR a `develop` → PR a `main`
- Hotfix urgente: `main` → `hotfix/nombre` → PR a `main` + PR a `develop`

### Migracion desde cycloid

La rama `cycloid` tiene 14 commits por delante de `main`. Estos deben mergearse a `main` (o a `develop`) antes de eliminar `cycloid`. Plan sugerido:

1. Merge `cycloid` → `main` (PR)
2. Push `develop` a remoto
3. Eliminar rama `cycloid` una vez confirmado que `develop` la reemplaza

---

## 6. PIPELINES CI/CD

### Plataforma: Gitea con Gitea Runner (act_runner)

### Pipeline 1: Validar y Deploy a Dev/QA

**Archivo:** `.gitea/workflows/validate-and-deploy-qa.yml`
**Trigger:** Push/PR a develop o feature/*

```text
git push → Gitea → Runner → Tests + Trivy + Semgrep → Deploy SSH → LXC (Dev/QA)
```

| Job | Que hace | Bloquea si falla |
| --- | --- | --- |
| test | `php -l` en todos los .php de app/ | Si |
| trivy | Escaneo de vulnerabilidades en dependencias (HIGH/CRITICAL) | Si |
| semgrep | Analisis estatico de seguridad (reglas PHP + secrets + security-audit) | Si |
| secrets-scan | Busca API keys hardcodeadas (SendGrid, DigitalOcean) | Si |
| deploy-qa | SSH al servidor Dev/QA y ejecuta deploy | Solo en push a develop |

### Pipeline 2: Cutover a Produccion

**Archivo:** `.gitea/workflows/cutover-production.yml`
**Trigger:** Push a main (despues de merge de PR desde develop)

```text
PR develop → main → Validacion → Trivy → Semgrep → Deploy SSH → Produccion
                                                                → Verificacion post-deploy
```

| Job | Que hace |
| --- | --- |
| validate | Sintaxis PHP + busqueda de credenciales |
| trivy | Escaneo vulnerabilidades (paralelo con semgrep) |
| semgrep | Analisis estatico seguridad (paralelo con trivy) |
| deploy-production | SSH al servidor + deploy + verificacion HTTP post-deploy |

**Todo por pipeline, nada manual.**

### Secrets necesarios en Gitea

**Para Dev/QA:** QA_HOST, QA_USER, QA_SSH_KEY, QA_PATH
**Para Produccion:** PROD_HOST, PROD_USER, PROD_SSH_KEY, PROD_PATH

### Flujo completo

```text
feature/xxx → push → Validacion → PR a develop → Validacion → merge
                                                                 ↓
                                          Deploy automatico a LXC Dev/QA
                                                                 ↓
                                              Pruebas en QA (manuales o auto)
                                                                 ↓
                                          PR develop → main → Validacion → merge
                                                                             ↓
                                                     Cutover automatico a Produccion
                                                                             ↓
                                                          Verificacion post-deploy
                                                                             ↓
                                                              EN PRODUCCION
```

---

## 7. ORGANIZACION DEL REPOSITORIO

### Estado del repositorio

| Aspecto | Estado actual | Accion |
| --- | --- | --- |
| Visibilidad | PUBLICO en GitHub | Verificar — considerar migrar a Gitea privado |
| .gitignore | Actualizado (excluye .env, uploads, deploy, .claude, basura) | OK |
| .env.example | Creado con todas las variables | OK |
| .env en historial | NUNCA fue commiteado | OK |
| Credenciales en codigo | No se encontraron hardcodeadas en app/ | OK |
| Archivos basura | Solo cookies.txt trackeado | Pendiente limpieza menor |

### Archivos basura trackeados en git (pendiente limpieza)

**Archivos que deberian eliminarse:**

- `cookies.txt` — archivo de cookies sin utilidad en el repo

**Total:** 1 archivo basura. El repo esta significativamente mas limpio que otros proyectos.

### Archivos que SI deben quedarse

- `docs/*.md` — 5 archivos de documentacion tecnica, correctamente en `docs/`
- `tests/README.md` — documentacion de tests
- `public/robots.txt` — necesario para SEO

### .gitignore actualizado incluye

- `.env` — Variables de entorno
- `vendor/` — Dependencias Composer
- `writable/` — Logs, cache, sesiones
- `public/uploads/` — Archivos subidos por usuarios
- `public/deploy.php` — Scripts de deploy
- `.claude/` — Configuracion de Claude Code
- `cookies.txt`, `*.stackdump`, `tmp_*.php`, `composer-setup.php` — Basura

---

## 8. HALLAZGOS CRITICOS Y ACCIONES PENDIENTES

### Prioridad CRITICA

| # | Accion | Responsable |
| --- | --- | --- |
| 1 | Verificar visibilidad del repo en GitHub (si es publico, hacer privado o migrar a Gitea) | Consultor/Cliente |
| 2 | Rotar password de BD produccion (formato `AVNS_*`, visible en .env local) | Cliente |
| 3 | Rotar API Key de SendGrid | Cliente |

### Prioridad ALTA

| # | Accion | Responsable |
| --- | --- | --- |
| 4 | Push de rama develop al remoto | Cliente |
| 5 | Merge de los 14 commits de cycloid a main via PR | Cliente |
| 6 | Configurar proteccion de ramas en Gitea | Consultor |
| 7 | Configurar secrets en Gitea para pipelines (QA_*, PROD_*) | Consultor |

### Prioridad MEDIA

| # | Accion | Responsable |
| --- | --- | --- |
| 8 | Eliminar cookies.txt del repo (commit de limpieza) | Cliente |
| 9 | Eliminar public/deploy.php del servidor de produccion si existe | Cliente |
| 10 | Evaluar las 10 tablas vacias — decidir si son modulos pendientes o se eliminan | Cliente |

### Prioridad BAJA

| # | Accion | Responsable |
| --- | --- | --- |
| 11 | Agregar tests PHPUnit para modulos criticos (auth, pagos, cartera) | Cliente |
| 12 | Considerar agregar vistas SQL para consultas frecuentes del portal | Cliente |

---

*Documento generado el 2026-04-05. Preparado como entregable del proceso de hardening del repositorio heroicos.*
