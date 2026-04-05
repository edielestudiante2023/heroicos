# Guia de contribucion — Heroicos Futbol Club

## Flujo de ramas

```
main          ← Produccion. Solo codigo validado y estable.
develop       ← Integracion. Cambios se unen aqui antes de ir a main.
feature/xxx   ← Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    ← Correcciones urgentes. Se crean desde main.
```

### Nueva funcionalidad

```bash
git checkout develop
git pull origin develop
git checkout -b feature/modulo-descripcion
# ... desarrollar ...
# Crear PR de feature/xxx → develop
# Despues de merge: PR de develop → main
```

### Hotfix urgente

```bash
git checkout main
git pull origin main
git checkout -b hotfix/bug-descripcion
# ... corregir ...
# Crear PR de hotfix/xxx → main
# Crear PR de hotfix/xxx → develop (para sincronizar)
```

---

## Convencion de commits

```
tipo: descripcion corta en minusculas

Tipos permitidos:
  feat:     Nueva funcionalidad
  fix:      Correccion de bug
  docs:     Documentacion
  refactor: Refactorizacion sin cambio funcional
  chore:    Mantenimiento (dependencias, config, CI)
  test:     Tests
  style:    Formato, espacios, puntuacion (sin cambio de logica)
```

**Ejemplos:**
```
feat: sistema de notificaciones con campanita y dropdown
fix: prevenir FK violation en tokens_inscripcion
docs: actualizar README con instrucciones de deploy
refactor: centralizar envio de emails en SendGridService
chore: actualizar dependencias de composer
```

---

## Convencion de nombres de ramas

| Tipo | Patron | Ejemplo |
|------|--------|---------|
| Feature | `feature/modulo-descripcion` | `feature/torneos-inscripcion` |
| Hotfix | `hotfix/bug-descripcion` | `hotfix/login-remember-token` |
| Docs | `docs/descripcion` | `docs/hardening-repositorio` |

---

## Reglas

1. **No push directo a main** — Siempre via PR (Pull Request)
2. **No credenciales en el codigo** — Usar `.env` y `env()` para API keys, passwords, tokens
3. **No archivos temporales** — No commitear tmp_*, pruebas sueltas, stackdumps
4. **No operaciones destructivas en produccion** — No DELETE sin WHERE, no DROP sin backup
5. **No forzar push** — No `git push --force` a ramas compartidas

---

## Proceso de revision

1. Crear PR con descripcion clara del cambio
2. El pipeline CI/CD ejecuta automaticamente:
   - Verificacion de sintaxis PHP (`php -l`)
   - Escaneo de vulnerabilidades (Trivy)
   - Analisis estatico de seguridad (Semgrep)
   - Busqueda de credenciales hardcodeadas
3. Si el pipeline pasa, solicitar revision de codigo
4. Merge solo despues de aprobacion

---

## Estructura de codigo

- **Controladores** en `app/Controllers/{Rol}/` — separados por rol de usuario
- **Modelos** en `app/Models/` — un modelo por tabla principal
- **Vistas** en `app/Views/{rol}/` — organizadas por rol
- **Librerias** en `app/Libraries/` — logica de negocio reutilizable
- **Migraciones** en `app/Database/Migrations/` — esquema versionado
