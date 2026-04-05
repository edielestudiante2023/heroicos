# Heroicos Futbol Club — Sistema de Gestion de Academia

Plataforma digital para la gestion integral de una academia de futbol. Administra estudiantes, acudientes, grupos, horarios, pagos, asistencia, torneos, clases particulares y comunicaciones por email.

**Empresa:** Cycloid Talent
**Repositorio:** [github.com/edielestudiante2023/heroicos](https://github.com/edielestudiante2023/heroicos)

---

## Stack tecnologico

| Componente | Tecnologia |
|------------|-----------|
| Backend | PHP 8.2 + CodeIgniter 4.7 |
| Base de datos | MySQL 8.0 (DigitalOcean Managed, SSL required) |
| Servidor web | Apache (XAMPP local) / Nginx (produccion) |
| Email | SendGrid API v8.1 |
| PDF | TCPDF 6.10 (paz y salvos, certificados) |
| Frontend | Bootstrap 5.3 + Vanilla JS |
| PWA | Service Workers + IndexedDB (asistencia y pagos offline) |
| Iconos | Bootstrap Icons |

---

## Modulos principales (12)

| Modulo | Descripcion |
|--------|-------------|
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

---

## Roles de usuario

| Rol | Acceso |
|-----|--------|
| admin | Todo el sistema + gestion de usuarios + configuracion + auditoria |
| profesor | Grupos asignados + asistencia + enlaces de inscripcion + clases particulares |
| acudiente | Portal de estudiantes + pagos + horarios + paz y salvos + clases particulares |

---

## Estructura del proyecto

```
heroicos/
├── app/
│   ├── Config/            # Routes, Database, Filters, App
│   ├── Controllers/
│   │   ├── Admin/         # ~12 controladores (Users, Students, Groups, Payments, etc.)
│   │   ├── Profesor/      # 5 controladores (Dashboard, Attendance, Groups, etc.)
│   │   ├── Acudiente/     # 7 controladores (Estudiantes, Pagos, Horarios, etc.)
│   │   └── Api/           # OfflineController (PWA sync)
│   ├── Database/
│   │   ├── Migrations/    # 42 migraciones
│   │   └── Seeds/         # Seeders
│   ├── Filters/           # AuthFilter, RoleFilter, ApiAuthFilter
│   ├── Helpers/           # Funciones auxiliares
│   ├── Libraries/         # SendGridService, PazYSalvoPdfGenerator
│   ├── Models/            # ~27 modelos
│   └── Views/             # Vistas organizadas por rol
├── public/
│   ├── assets/            # JS (PWA), iconos, CSS
│   ├── sw.js              # Service Worker
│   ├── manifest.json      # PWA manifest
│   └── index.php          # Punto de entrada
├── vendor/                # Dependencias Composer
├── writable/              # Logs, cache, sesiones, uploads
├── docs/                  # Documentacion tecnica
├── tests/                 # Tests PHPUnit
├── .env                   # Variables de entorno (NO commitear)
├── .env.example           # Template de variables (SI commitear)
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

---

## Requisitos previos

- PHP 8.2+ con extensiones: intl, mbstring, mysqlnd, curl, json
- MySQL 8.0+
- Composer 2.x
- Apache con mod_rewrite o Nginx
- XAMPP (para desarrollo local)

---

## Instalacion local

```bash
# 1. Clonar repositorio
git clone https://github.com/edielestudiante2023/heroicos.git
cd heroicos

# 2. Instalar dependencias
composer install

# 3. Configurar entorno
cp .env.example .env
# Editar .env con credenciales locales

# 4. Ejecutar migraciones
php spark migrate

# 5. Ejecutar seeders (datos iniciales)
php spark db:seed

# 6. Iniciar servidor de desarrollo
php spark serve
# O acceder via XAMPP: http://localhost/heroicos/public/
```

**Credenciales por defecto:** `admin@heroicos.com` / `Admin123*` (cambiar despues del primer login)

---

## Variables de entorno

| Variable | Descripcion |
|----------|-------------|
| `CI_ENVIRONMENT` | development / production |
| `app.baseURL` | URL base de la aplicacion |
| `database.default.*` | Credenciales BD local |
| `database.production.*` | Credenciales BD produccion (DigitalOcean) |
| `encryption.key` | Clave de encriptacion (hex2bin) |
| `session.driver` | Driver de sesiones (DatabaseHandler) |
| `sendgrid.apiKey` | API Key de SendGrid |
| `sendgrid.fromEmail` | Email remitente |
| `sendgrid.fromName` | Nombre remitente |

---

## Deploy

**Servidor de produccion:**
- IP: `66.29.154.174` (server1.cycloidtalent.com)
- OS: Ubuntu 24.04 LTS
- Ruta: `/www/wwwroot/heroicos`
- Acceso: SSH con llave ed25519

**Proceso de deploy:**
1. Desarrollar en rama `cycloid`
2. Merge a `main`
3. Push a remoto
4. Volver a rama `cycloid`

---

## Documentacion adicional

- [docs/HARDENING-heroicos.md](docs/HARDENING-heroicos.md) — Documento de hardening del repositorio
- [docs/database_design.md](docs/database_design.md) — Diseno de base de datos
- [docs/PLAN_PROYECTO.md](docs/PLAN_PROYECTO.md) — Plan completo del proyecto
