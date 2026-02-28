# PROMPT PARA NUEVO CHAT - PWA (Progressive Web App)

**Copia y pega TODO el contenido de abajo en un nuevo chat de Claude Code:**

---

## CONTEXTO DEL PROYECTO

Estoy desarrollando el sistema de gestión para la **Academia de Fútbol Heroicos** usando **CodeIgniter 4.7.0**. El proyecto ya está en producción con HTTPS en:

**URL producción:** `https://heroicos.cycloidtalent.com/`
**Repo:** https://github.com/edielestudiante2023/heroicos.git

### Stack actual
- **Backend:** CodeIgniter 4.7.0 + PHP 8.2
- **Frontend:** Bootstrap 5.3.2 (CDN) + Bootstrap Icons 1.11.1 (CDN) + Vanilla JS
- **Servidor:** aaPanel + Nginx con SSL
- **BD:** MySQL 8.0 (DigitalOcean con SSL)

### Paleta de colores (para el tema de la PWA):
```css
--heroicos-primary: #b720d2;   /* Purpura - color principal */
--heroicos-secondary: #ffd65e; /* Amarillo dorado */
--heroicos-dark: #8a189e;      /* Purpura oscuro */
--heroicos-light: #f8e6fc;     /* Purpura claro */
--heroicos-accent: #d62b23;    /* Rojo */
```

---

## OBJETIVO

Necesito que conviertas TODA la aplicación en una **PWA (Progressive Web App)** completa con estas características:

1. **Instalable desde el primer momento:** Cuando un usuario entre desde su celular a `https://heroicos.cycloidtalent.com/`, debe aparecer un **banner/prompt de instalación** invitándolo a instalar la app. El prompt debe ser personalizado (no solo el nativo del navegador) con el diseño Heroicos.

2. **Funcionar como app nativa:** Una vez instalada, debe abrir sin barra del navegador (modo standalone), con splash screen personalizado.

3. **Soporte offline básico:** Mostrar una página offline bonita cuando no hay conexión, y cachear las páginas ya visitadas.

4. **Compatible con:** Android (Chrome), iOS (Safari), y navegadores de escritorio.

---

## ESTRUCTURA ACTUAL DE ARCHIVOS

```
public/
├── .htaccess
├── index.php
├── favicon.ico          ← favicon actual (reemplazar por un icono moderno)
├── robots.txt
├── deploy.php
└── assets/
    └── images/
        └── heroicos.png ← Logo principal (circular, fondo blanco)
```

### Layouts (2 archivos que DEBEN ser modificados):

**1. `app/Views/layouts/auth.php`** - Layout de login/autenticación
```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Academia Heroicos - Escuela de Futbol">
    <title><?= $title ?? 'Academia Heroicos' ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <!-- Bootstrap Icons CDN -->
    <!-- CSS inline con colores Heroicos -->
</head>
<body>
    <!-- Formulario de auth centrado -->
    <!-- Bootstrap JS CDN -->
</body>
</html>
```

**2. `app/Views/layouts/main.php`** - Layout principal (usuarios autenticados)
```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Academia Heroicos - Sistema de Gestion">
    <title><?= $title ?? 'Academia Heroicos' ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <!-- Bootstrap Icons CDN -->
    <!-- CSS inline con sidebar, navbar, cards, etc. -->
</head>
<body>
    <!-- Sidebar fijo izquierdo (260px, gradiente purpura) -->
    <!-- Top navbar blanca -->
    <!-- Content area -->
    <!-- Bootstrap JS CDN -->
    <!-- Script para toggle sidebar en mobile -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>
```

### TODAS las vistas existentes (27 archivos):
```
app/Views/
├── layouts/
│   ├── auth.php         ← Modificar (agregar meta PWA + service worker)
│   └── main.php         ← Modificar (agregar meta PWA + service worker + install banner)
├── auth/
│   ├── login.php
│   ├── forgot_password.php
│   └── reset_password.php
├── admin/
│   ├── dashboard.php
│   ├── users/index.php, form.php
│   ├── students/index.php, form.php, show.php
│   ├── groups/index.php, form.php, show.php
│   └── schedules/index.php, form.php, show.php
├── profesor/
│   └── dashboard.php
├── acudiente/
│   └── dashboard.php
└── errors/
    └── html/error_404.php, error_400.php, etc.
```

---

## LO QUE NECESITO QUE CREES

### 1. ARCHIVOS PWA EN `public/`

**`public/manifest.json`** (Web App Manifest)
- `name`: "Academia Heroicos"
- `short_name`: "Heroicos"
- `description`: "Sistema de Gestion - Academia de Futbol Heroicos"
- `start_url`: "/" (relativo a la raiz de la app)
- `scope`: "/"
- `display`: "standalone"
- `orientation`: "portrait"
- `theme_color`: "#b720d2" (purpura principal)
- `background_color`: "#8a189e" (purpura oscuro - para splash screen)
- `lang`: "es"
- `categories`: ["sports", "education"]
- `icons`: Array de iconos en multiples tamaños (72, 96, 128, 144, 152, 192, 384, 512) tanto en formato PNG como maskable
- `screenshots`: Al menos 1 screenshot para mejorar el install prompt en Android
- `shortcuts`: accesos directos a funciones principales

**`public/sw.js`** (Service Worker)
- **Estrategia de cache:**
  - Cache First para assets estaticos (CSS CDN Bootstrap, JS CDN Bootstrap, iconos CDN, imagenes locales)
  - Network First para paginas HTML (mostrar la version en cache si falla la red)
  - Stale While Revalidate para el logo y assets locales
- **Cache versioning:** usar una variable de version para poder invalidar cache
- **Offline fallback:** Cuando no hay conexion y la pagina no esta en cache, mostrar `public/offline.html`
- **Manejar:** evento `install`, `activate` (limpiar caches viejos), `fetch`
- **Pre-cache:** La pagina de login `/`, `/login`, y los assets de Bootstrap CDN

**`public/offline.html`** - Pagina offline bonita
- Usar el estilo visual Heroicos (gradiente purpura, logo)
- Mensaje amigable: "Sin conexion a internet"
- Boton "Reintentar" que recargue la pagina
- Icono de wifi off
- Todo el CSS debe ser inline (no depender de CDN)
- Incluir el logo como base64 o SVG inline

### 2. ICONOS DE LA APP

Genera los iconos necesarios para PWA a partir del logo existente (`public/assets/images/heroicos.png`):

Crea un script PHP o un enfoque para generar los siguientes iconos y guardarlos en `public/assets/icons/`:
- `icon-72x72.png`
- `icon-96x96.png`
- `icon-128x128.png`
- `icon-144x144.png`
- `icon-152x152.png`
- `icon-192x192.png`
- `icon-384x384.png`
- `icon-512x512.png`
- `icon-maskable-192x192.png` (con padding 20% para maskable)
- `icon-maskable-512x512.png` (con padding 20% para maskable)
- `apple-touch-icon.png` (180x180)

**Si no puedes generar los PNG programaticamente**, al menos crea la estructura de carpetas y haz que el manifest apunte a los archivos correctos, y dejame una nota de que debo generarlos manualmente (o usar un servicio como https://maskable.app/ o https://realfavicongenerator.net/).

### 3. MODIFICACIONES A LOS LAYOUTS

**En AMBOS layouts (`auth.php` y `main.php`)**, agregar al `<head>`:

```html
<!-- PWA Meta Tags -->
<meta name="theme-color" content="#b720d2">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Heroicos">
<meta name="application-name" content="Heroicos">
<meta name="msapplication-TileColor" content="#b720d2">
<meta name="msapplication-TileImage" content="<?= base_url('assets/icons/icon-144x144.png') ?>">

<!-- PWA Manifest -->
<link rel="manifest" href="<?= base_url('manifest.json') ?>">

<!-- Apple Touch Icons -->
<link rel="apple-touch-icon" href="<?= base_url('assets/icons/apple-touch-icon.png') ?>">
<link rel="apple-touch-icon" sizes="152x152" href="<?= base_url('assets/icons/icon-152x152.png') ?>">
<link rel="apple-touch-icon" sizes="192x192" href="<?= base_url('assets/icons/icon-192x192.png') ?>">

<!-- Favicon -->
<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/icons/icon-96x96.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/icons/icon-72x72.png') ?>">
```

**En AMBOS layouts**, agregar antes de `</body>`:

```html
<!-- Service Worker Registration -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(reg => console.log('SW registered:', reg.scope))
            .catch(err => console.log('SW registration failed:', err));
    });
}
</script>
```

### 4. BANNER DE INSTALACION PERSONALIZADO

**Solo en `main.php`** (layout principal), agregar un banner/modal de instalacion personalizado que:

1. Se muestre automaticamente la primera vez que el usuario visita la app en un dispositivo movil
2. Intercepte el evento `beforeinstallprompt` del navegador
3. Muestre un banner atractivo con el diseño Heroicos:
   - Logo de Heroicos
   - Texto: "Instala Heroicos en tu dispositivo"
   - Subtexto: "Accede mas rapido sin abrir el navegador"
   - Boton "Instalar" (purpura, prominente)
   - Boton "Ahora no" (texto gris, discreto)
4. Para **iOS/Safari** (que no soporta `beforeinstallprompt`), mostrar un banner diferente con instrucciones:
   - "Para instalar: toca el boton Compartir y luego 'Agregar a pantalla de inicio'"
   - Con una imagen/icono del boton de compartir de Safari
5. No mostrar el banner nuevamente si el usuario lo cierra (usar `localStorage`)
6. No mostrar si la app ya esta instalada (detectar `display-mode: standalone`)
7. Mostrar el banner flotante en la parte inferior de la pantalla (fixed bottom), sobre el contenido

**Tambien en `auth.php`** (layout de login):
- Agregar un banner mas sutil en la pagina de login que invite a instalar
- Puede ser una linea en la parte superior o inferior: "Instala la app para un acceso mas rapido"

### 5. APPLE SPLASH SCREENS (Opcional pero recomendado)

Para que en iOS se vea bien al abrir la app, agregar meta tags para splash screens. Si es muy complejo generar todas las resoluciones, al menos incluir las mas comunes:
- iPhone SE, iPhone 8 (750x1334)
- iPhone X/XS/11 Pro (1125x2436)
- iPhone XR/11 (828x1792)
- iPhone 12/13/14 (1170x2532)
- iPad (1536x2048)

O usar un enfoque simplificado que funcione bien sin los splash screens especificos.

### 6. CONFIGURACION NGINX

El Service Worker necesita headers especificos. Dame el bloque Nginx que debo agregar:

```nginx
# Service Worker - No cache
location /sw.js {
    add_header Cache-Control "no-cache, no-store, must-revalidate";
    add_header Service-Worker-Allowed "/";
}

# Manifest
location /manifest.json {
    add_header Cache-Control "no-cache";
    types { application/manifest+json json; }
}
```

### 7. VERIFICACION Y TESTING

Despues de implementar todo:

1. Verificar que `manifest.json` es accesible en `https://heroicos.cycloidtalent.com/manifest.json`
2. Verificar que `sw.js` se registra correctamente (ver en DevTools > Application > Service Workers)
3. Verificar que el banner de instalacion aparece en mobile
4. Verificar Lighthouse PWA score (dar instrucciones de como probarlo)
5. Probar la pagina offline desconectando la red

---

## INSTRUCCIONES IMPORTANTES

1. **Lee estos archivos primero** para entender la estructura exacta:
   - `app/Views/layouts/main.php` (layout principal completo)
   - `app/Views/layouts/auth.php` (layout de auth completo)
   - `public/index.php`
   - Lista de archivos en `public/`

2. **NO modifiques la logica de la app**, solo agrega las capas PWA encima. No cambies estilos existentes, rutas, ni controladores.

3. **Usa `base_url()`** de CodeIgniter para todas las URLs en los meta tags. La base URL es `https://heroicos.cycloidtalent.com/`.

4. **El Service Worker debe registrarse desde la raiz** (`/sw.js`), no desde una subcarpeta, para que su scope cubra toda la app.

5. **Los CDN de Bootstrap deben cachearse** en el Service Worker para que funcionen offline en paginas ya visitadas.

6. **Nota sobre Nginx:** La app usa Nginx (no Apache). El `.htaccess` de public/ es para desarrollo local con XAMPP. En produccion la config esta en `/www/server/panel/vhost/nginx/heroicos.cycloidtalent.com.conf`. La rewrite rule principal es:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```
   Necesitaras agregar locations especificos para `sw.js` y `manifest.json` ANTES de este bloque.

7. **Despues de terminar**, haz commit, push a GitHub, y dame los comandos para actualizar produccion:
   ```bash
   cd /www/wwwroot/heroicos && git pull origin main
   ```
   Mas el bloque Nginx que debo agregar al config del sitio.

8. **Importante para iOS:** Safari no soporta el evento `beforeinstallprompt` ni los Web Push notifications. Usa la deteccion de user-agent para mostrar instrucciones especificas de "Agregar a pantalla de inicio" para iOS.

9. **El logo `heroicos.png`** es circular con fondo blanco. Los iconos maskable deben tener un padding extra del 20% con fondo purpura (#b720d2) para que se vean bien en Android con iconos adaptativos.

10. **Prioridad:** Lo mas importante es que funcione el install prompt desde la primera visita en celular. El usuario abre `https://heroicos.cycloidtalent.com/` → ve el login → inmediatamente ve un banner invitandolo a instalar la app → la instala → la proxima vez abre desde su pantalla de inicio como una app nativa.

---

## RESUMEN DE ARCHIVOS A CREAR/MODIFICAR

### Crear:
- `public/manifest.json`
- `public/sw.js`
- `public/offline.html`
- `public/assets/icons/` (carpeta con todos los iconos)

### Modificar:
- `app/Views/layouts/auth.php` (meta PWA + SW register + install hint)
- `app/Views/layouts/main.php` (meta PWA + SW register + install banner)

### No tocar:
- Controladores, modelos, rutas, otras vistas, CSS existente
