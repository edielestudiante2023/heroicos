# Diseño de Base de Datos - Academia Heroicos

## Diagrama de Relaciones (Resumido)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              NÚCLEO DE USUARIOS                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│   ┌──────────┐      ┌──────────────┐      ┌─────────────┐                       │
│   │  roles   │─────▶│   usuarios   │◀─────│  permisos   │                       │
│   └──────────┘      └──────┬───────┘      └─────────────┘                       │
│                            │                                                     │
│         ┌──────────────────┼──────────────────┐                                 │
│         ▼                  ▼                  ▼                                 │
│   ┌───────────┐     ┌─────────────┐    ┌──────────────┐                         │
│   │ profesores│     │ acudientes  │    │administradores│                        │
│   └─────┬─────┘     └──────┬──────┘    └──────────────┘                         │
│         │                  │                                                     │
└─────────┼──────────────────┼────────────────────────────────────────────────────┘
          │                  │
          │                  │ 1:N
          │           ┌──────▼───────┐
          │           │ estudiantes  │──────┐
          │           └──────┬───────┘      │
          │                  │              │
┌─────────┼──────────────────┼──────────────┼─────────────────────────────────────┐
│         │    ESTRUCTURA    │  ACADÉMICA   │                                     │
├─────────┼──────────────────┼──────────────┼─────────────────────────────────────┤
│         │                  │              │                                     │
│         │           ┌──────▼───────┐      │                                     │
│         │           │ inscripciones│      │                                     │
│         │           └──────┬───────┘      │                                     │
│         │                  │              │                                     │
│         │           ┌──────▼───────┐      │     ┌────────────────┐              │
│         └──────────▶│    grupos    │◀─────┼─────│categoria_anios │              │
│                     └──────┬───────┘      │     └───────┬────────┘              │
│                            │              │             │                       │
│                     ┌──────▼───────┐      │     ┌───────▼────────┐              │
│                     │  categorias  │◀─────┴─────│  anios_validos │              │
│                     └──────────────┘            └────────────────┘              │
│                                                                                  │
│   ┌──────────────┐      ┌──────────────┐                                        │
│   │   horarios   │─────▶│grupo_horarios│                                        │
│   └──────────────┘      └──────────────┘                                        │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                           MÓDULO FINANCIERO                                      │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│   ┌────────────────┐     ┌─────────────┐     ┌──────────────┐                   │
│   │conceptos_cobro │────▶│   tarifas   │     │   periodos   │                   │
│   └────────────────┘     └─────────────┘     └──────┬───────┘                   │
│          │                                          │                           │
│          │              ┌───────────────────────────┘                           │
│          ▼              ▼                                                       │
│   ┌─────────────────────────┐      ┌──────────────┐      ┌────────────────┐    │
│   │   cargos (deudas)       │◀────▶│    pagos     │◀────▶│  comprobantes  │    │
│   │   - estudiante_id       │      │  - abono     │      │  - imagen      │    │
│   │   - concepto_id         │      │  - estado    │      │  - estado      │    │
│   │   - monto               │      └──────────────┘      └────────────────┘    │
│   │   - saldo_pendiente     │                                                   │
│   └─────────────────────────┘                                                   │
│                                                                                  │
│   ┌─────────────────────────┐                                                   │
│   │      paz_y_salvos       │                                                   │
│   └─────────────────────────┘                                                   │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              MÓDULO TORNEOS                                      │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│   ┌──────────────┐      ┌─────────────────────┐                                 │
│   │   torneos    │─────▶│ torneo_inscripciones│                                 │
│   │  - cupos     │      │   - estudiante_id   │                                 │
│   │  - costo     │      │   - estado_pago     │                                 │
│   │  - fechas    │      └─────────────────────┘                                 │
│   └──────────────┘                                                              │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                          MÓDULO CLASES PARTICULARES                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│   ┌────────────────────────┐      ┌─────────────────────┐                       │
│   │solicitudes_clase_part  │─────▶│  clases_particulares│                       │
│   │  - acudiente solicita  │      │  - admin agenda     │                       │
│   │  - estado: pendiente   │      │  - profesor asignado│                       │
│   └────────────────────────┘      └─────────────────────┘                       │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                            MÓDULO ASISTENCIA                                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│   ┌──────────────┐      ┌─────────────────────┐                                 │
│   │   sesiones   │─────▶│     asistencias     │  Por defecto: presente = true   │
│   │  (una clase) │      │  - estudiante_id    │  Solo se desmarca el ausente    │
│   └──────────────┘      │  - presente (bool)  │                                 │
│                         └─────────────────────┘                                 │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Detalle de Tablas

### 1. USUARIOS Y AUTENTICACIÓN

#### `roles`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| nombre | VARCHAR(50) | admin, profesor, acudiente |
| descripcion | VARCHAR(255) | |
| created_at | TIMESTAMP | |

#### `usuarios`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| rol_id | INT FK | Referencia a roles |
| email | VARCHAR(255) UNIQUE | Login |
| password | VARCHAR(255) | Hasheado |
| estado | ENUM | activo, inactivo, pendiente |
| email_verificado | BOOLEAN | |
| token_verificacion | VARCHAR(255) | Para activar cuenta |
| ultimo_acceso | TIMESTAMP | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `permisos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| nombre | VARCHAR(100) | ej: gestionar_pagos |
| modulo | VARCHAR(50) | ej: cartera |

#### `rol_permisos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| rol_id | INT FK | |
| permiso_id | INT FK | |

---

### 2. PERSONAS

#### `acudientes`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| usuario_id | INT FK | Referencia a usuarios |
| tipo_documento | ENUM | CC, CE, pasaporte |
| numero_documento | VARCHAR(20) | |
| nombres | VARCHAR(100) | |
| apellidos | VARCHAR(100) | |
| telefono | VARCHAR(20) | |
| telefono_alt | VARCHAR(20) | Opcional |
| direccion | TEXT | |
| ciudad | VARCHAR(100) | |
| parentesco | VARCHAR(50) | Padre, Madre, Tío, etc |
| ocupacion | VARCHAR(100) | |
| autorizacion_datos | BOOLEAN | Acepta política |
| fecha_autorizacion | TIMESTAMP | |
| ip_autorizacion | VARCHAR(45) | Para registro legal |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `estudiantes`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| acudiente_id | INT FK | Acudiente principal |
| codigo | VARCHAR(20) UNIQUE | Código interno (auto) |
| foto | VARCHAR(255) | Ruta de imagen |
| nombres | VARCHAR(100) | |
| apellidos | VARCHAR(100) | |
| tipo_documento | ENUM | TI, RC, pasaporte |
| numero_documento | VARCHAR(20) | |
| fecha_nacimiento | DATE | |
| sexo | ENUM | M, F |
| direccion | TEXT | |
| telefono | VARCHAR(20) | |
| eps | VARCHAR(100) | Servicio de salud |
| grupo_sanguineo | VARCHAR(10) | |
| alergias | TEXT | |
| condiciones_medicas | TEXT | |
| medicamentos | TEXT | |
| contacto_emergencia | VARCHAR(100) | |
| telefono_emergencia | VARCHAR(20) | |
| autorizacion_datos_menor | BOOLEAN | |
| fecha_autorizacion | TIMESTAMP | |
| ip_autorizacion | VARCHAR(45) | |
| estado | ENUM | activo, inactivo, retirado |
| fecha_ingreso | DATE | |
| fecha_retiro | DATE | Nullable |
| motivo_retiro | TEXT | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `estudiante_historial_deportivo`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| estudiante_id | INT FK | |
| academia_anterior | VARCHAR(200) | |
| periodo | VARCHAR(50) | ej: 2022-2023 |
| torneos_participados | TEXT | |
| logros | TEXT | |
| observaciones | TEXT | |

#### `profesores`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| usuario_id | INT FK | |
| tipo_documento | ENUM | CC, CE |
| numero_documento | VARCHAR(20) | |
| nombres | VARCHAR(100) | |
| apellidos | VARCHAR(100) | |
| telefono | VARCHAR(20) | |
| direccion | TEXT | |
| especialidad | VARCHAR(100) | |
| foto | VARCHAR(255) | |
| estado | ENUM | activo, inactivo |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `administradores`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| usuario_id | INT FK | |
| nombres | VARCHAR(100) | |
| apellidos | VARCHAR(100) | |
| telefono | VARCHAR(20) | |
| es_superadmin | BOOLEAN | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### 3. ESTRUCTURA ACADÉMICA

#### `categorias`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| nombre | VARCHAR(100) | ej: "Categoría 2017" |
| descripcion | TEXT | |
| estado | ENUM | activa, inactiva |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `categoria_anios`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| categoria_id | INT FK | |
| anio | YEAR | 2017, 2018, etc |

> Permite categorías con uno o varios años (ej: 2017-2018)

#### `grupos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| categoria_id | INT FK | |
| nombre | VARCHAR(100) | ej: "Grupo A" |
| cupo_maximo | INT | |
| estado | ENUM | activo, inactivo |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `inscripciones`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| estudiante_id | INT FK | |
| grupo_id | INT FK | |
| fecha_inscripcion | DATE | |
| fecha_fin | DATE | Nullable (si cambia de grupo) |
| estado | ENUM | activa, finalizada |
| motivo_cambio | TEXT | Si fue movido de grupo |
| created_at | TIMESTAMP | |

> Mantiene historial de todos los grupos donde ha estado el estudiante

#### `profesor_grupos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| profesor_id | INT FK | |
| grupo_id | INT FK | |
| es_titular | BOOLEAN | Profesor principal |
| created_at | TIMESTAMP | |

---

### 4. HORARIOS

#### `horarios`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| nombre | VARCHAR(100) | ej: "Lunes PM" |
| dia_semana | TINYINT | 1=Lun, 7=Dom |
| hora_inicio | TIME | |
| hora_fin | TIME | |
| lugar | VARCHAR(200) | Cancha, sede, etc |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `grupo_horarios`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| grupo_id | INT FK | |
| horario_id | INT FK | |
| vigente_desde | DATE | |
| vigente_hasta | DATE | Nullable |

---

### 5. MÓDULO FINANCIERO

#### `periodos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| nombre | VARCHAR(50) | ej: "2025" |
| fecha_inicio | DATE | |
| fecha_fin | DATE | |
| estado | ENUM | activo, cerrado |
| created_at | TIMESTAMP | |

#### `conceptos_cobro`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| codigo | VARCHAR(20) | MAT, MENS, UNIF, etc |
| nombre | VARCHAR(100) | Matrícula, Mensualidad... |
| descripcion | TEXT | |
| tipo | ENUM | unico, recurrente |
| aplica_al_inscribir | BOOLEAN | Auto-generar al inscribir |
| estado | ENUM | activo, inactivo |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `tarifas`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| concepto_id | INT FK | |
| categoria_id | INT FK | Nullable (si aplica a todas) |
| periodo_id | INT FK | |
| valor | DECIMAL(12,2) | |
| vigente_desde | DATE | |
| vigente_hasta | DATE | |
| created_at | TIMESTAMP | |

#### `cargos`
> La deuda que tiene cada estudiante

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| estudiante_id | INT FK | |
| concepto_id | INT FK | |
| periodo_id | INT FK | |
| descripcion | VARCHAR(255) | Detalle adicional |
| mes | TINYINT | Para mensualidades (1-12) |
| anio | YEAR | |
| valor_original | DECIMAL(12,2) | Monto inicial |
| valor_pagado | DECIMAL(12,2) | Acumulado de abonos |
| saldo_pendiente | DECIMAL(12,2) | Lo que falta |
| fecha_vencimiento | DATE | |
| estado | ENUM | pendiente, parcial, pagado, anulado |
| generado_auto | BOOLEAN | Si fue auto-generado |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `pagos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| acudiente_id | INT FK | Quien paga |
| numero_recibo | VARCHAR(50) | Auto-generado |
| fecha_pago | DATE | Fecha del banco |
| fecha_registro | TIMESTAMP | Fecha que subió |
| valor_total | DECIMAL(12,2) | Total del pago |
| metodo_pago | ENUM | transferencia, efectivo, otro |
| banco | VARCHAR(100) | |
| referencia_banco | VARCHAR(100) | Nro de transacción |
| observaciones | TEXT | |
| estado | ENUM | pendiente_revision, aprobado, rechazado |
| revisado_por | INT FK | Admin que revisó |
| fecha_revision | TIMESTAMP | |
| motivo_rechazo | TEXT | Si fue rechazado |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `pago_detalles`
> Distribución del pago entre varios cargos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| pago_id | INT FK | |
| cargo_id | INT FK | |
| valor_aplicado | DECIMAL(12,2) | Cuánto se abona a este cargo |

#### `comprobantes`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| pago_id | INT FK | |
| archivo | VARCHAR(255) | Ruta de imagen |
| tipo_archivo | VARCHAR(50) | jpg, png, pdf |
| created_at | TIMESTAMP | |

#### `paz_y_salvos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| estudiante_id | INT FK | |
| acudiente_id | INT FK | Quien lo solicitó |
| numero | VARCHAR(50) | Consecutivo |
| fecha_generacion | TIMESTAMP | |
| fecha_corte | DATE | Fecha hasta donde está al día |
| archivo_pdf | VARCHAR(255) | Ruta del PDF |
| enviado_email | BOOLEAN | |
| created_at | TIMESTAMP | |

---

### 6. MÓDULO TORNEOS

#### `torneos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| nombre | VARCHAR(200) | |
| descripcion | TEXT | |
| lugar | VARCHAR(200) | |
| fecha_evento | DATE | |
| hora_evento | TIME | |
| fecha_apertura_inscripcion | DATETIME | |
| fecha_cierre_inscripcion | DATETIME | |
| cupo_maximo | INT | |
| costo | DECIMAL(12,2) | |
| categoria_id | INT FK | A qué categoría aplica |
| estado | ENUM | programado, inscripciones_abiertas, inscripciones_cerradas, en_curso, finalizado, cancelado |
| imagen | VARCHAR(255) | Flyer/afiche |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `torneo_inscripciones`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| torneo_id | INT FK | |
| estudiante_id | INT FK | |
| acudiente_id | INT FK | Quien inscribió |
| fecha_inscripcion | TIMESTAMP | |
| cargo_id | INT FK | Referencia al cargo generado |
| estado | ENUM | pendiente_pago, pagado, cancelado |
| created_at | TIMESTAMP | |

---

### 7. MÓDULO CLASES PARTICULARES

#### `solicitudes_clase_particular`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| acudiente_id | INT FK | Quien solicita |
| estudiante_id | INT FK | |
| fecha_preferida | DATE | |
| hora_preferida | TIME | |
| duracion_minutos | INT | 60, 90, 120 |
| observaciones | TEXT | |
| estado | ENUM | pendiente, aprobada, rechazada, agendada |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

#### `clases_particulares`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| solicitud_id | INT FK | Nullable (puede ser sin solicitud) |
| estudiante_id | INT FK | |
| profesor_id | INT FK | |
| fecha | DATE | |
| hora_inicio | TIME | |
| hora_fin | TIME | |
| lugar | VARCHAR(200) | |
| cargo_id | INT FK | Cargo generado |
| estado | ENUM | programada, realizada, cancelada, no_asistio |
| observaciones | TEXT | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### 8. MÓDULO ASISTENCIA

#### `sesiones_clase`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| grupo_id | INT FK | |
| profesor_id | INT FK | Quien dicta |
| horario_id | INT FK | |
| fecha | DATE | |
| estado | ENUM | programada, realizada, cancelada |
| observaciones | TEXT | |
| created_at | TIMESTAMP | |

#### `asistencias`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| sesion_id | INT FK | |
| estudiante_id | INT FK | |
| presente | BOOLEAN | DEFAULT true |
| justificacion | TEXT | Si no asistió |
| registrado_por | INT FK | Usuario que registró |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### 9. NOTIFICACIONES

#### `notificaciones`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| usuario_id | INT FK | Destinatario |
| tipo | VARCHAR(50) | nuevo_estudiante, pago_recibido, etc |
| titulo | VARCHAR(200) | |
| mensaje | TEXT | |
| leida | BOOLEAN | |
| fecha_lectura | TIMESTAMP | |
| data_json | JSON | Datos adicionales |
| created_at | TIMESTAMP | |

#### `emails_enviados`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| destinatario_email | VARCHAR(255) | |
| destinatario_nombre | VARCHAR(200) | |
| asunto | VARCHAR(255) | |
| cuerpo | TEXT | |
| plantilla | VARCHAR(100) | |
| estado | ENUM | enviado, fallido, pendiente |
| sendgrid_id | VARCHAR(255) | ID de SendGrid |
| error | TEXT | Si falló |
| created_at | TIMESTAMP | |

#### `plantillas_email`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| codigo | VARCHAR(50) | bienvenida, nuevo_pago, etc |
| nombre | VARCHAR(100) | |
| asunto | VARCHAR(255) | |
| cuerpo_html | TEXT | Con placeholders |
| variables | JSON | Lista de variables disponibles |
| estado | ENUM | activa, inactiva |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

### 10. CONFIGURACIÓN Y SISTEMA

#### `configuracion`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| clave | VARCHAR(100) UNIQUE | |
| valor | TEXT | |
| tipo | VARCHAR(20) | string, int, bool, json |
| descripcion | VARCHAR(255) | |
| updated_at | TIMESTAMP | |

Valores iniciales sugeridos:
- `academia_nombre` = "Heroicos"
- `academia_direccion`
- `academia_telefono`
- `academia_email`
- `academia_logo`
- `dias_vencimiento_mensualidad` = 5
- `mensaje_bienvenida`
- `politica_datos` (texto completo)

#### `tokens_inscripcion`
> Para el enlace que envía el profesor

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT PK | |
| token | VARCHAR(255) UNIQUE | |
| email | VARCHAR(255) | Email del acudiente |
| nombre_acudiente | VARCHAR(200) | |
| profesor_id | INT FK | Quien generó |
| usado | BOOLEAN | |
| fecha_uso | TIMESTAMP | |
| expira_at | TIMESTAMP | |
| created_at | TIMESTAMP | |

#### `auditoria`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT PK | |
| usuario_id | INT FK | |
| accion | VARCHAR(100) | crear, editar, eliminar, login, etc |
| tabla | VARCHAR(100) | |
| registro_id | INT | |
| datos_anteriores | JSON | |
| datos_nuevos | JSON | |
| ip | VARCHAR(45) | |
| user_agent | VARCHAR(255) | |
| created_at | TIMESTAMP | |

---

## Índices Recomendados

```sql
-- Búsquedas frecuentes
CREATE INDEX idx_estudiantes_acudiente ON estudiantes(acudiente_id);
CREATE INDEX idx_estudiantes_estado ON estudiantes(estado);
CREATE INDEX idx_cargos_estudiante ON cargos(estudiante_id);
CREATE INDEX idx_cargos_estado ON cargos(estado);
CREATE INDEX idx_pagos_estado ON pagos(estado);
CREATE INDEX idx_pagos_acudiente ON pagos(acudiente_id);
CREATE INDEX idx_inscripciones_estudiante ON inscripciones(estudiante_id);
CREATE INDEX idx_inscripciones_grupo ON inscripciones(grupo_id);
CREATE INDEX idx_asistencias_sesion ON asistencias(sesion_id);
CREATE INDEX idx_notificaciones_usuario ON notificaciones(usuario_id, leida);
```

---

## Notas Importantes

### Protección de Datos (Ley 1581 de 2012 - Colombia)
1. Campos `autorizacion_datos` + `fecha_autorizacion` + `ip_autorizacion` en acudientes y estudiantes
2. Tabla `auditoria` para trazabilidad
3. Los datos sensibles de salud deben mostrarse solo a personal autorizado

### Lógica de Negocio Importante
1. Al inscribir estudiante → Crear cargos automáticos (matrícula, uniforme, primera mensualidad)
2. Mensualidades → Job mensual que genera cargos para estudiantes activos
3. Pagos → Requieren aprobación manual del admin
4. Paz y salvo → Solo si `saldo_pendiente = 0` en todos los cargos

### PWA - App Móvil
- Service Worker para cache offline
- Manifest.json para instalación
- Notificaciones push con Firebase Cloud Messaging (gratis)
