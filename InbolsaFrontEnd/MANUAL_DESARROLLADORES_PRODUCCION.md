# 🚀 Manual de Desarrolladores - Inbolsa (Producción)

**Versión**: 3.0
**Última actualización**: Noviembre 20, 2025
**Stack**: Astro 5.x + PHP 8.x + MySQL 8.0 + Tailwind CSS
**Enfoque**: Producción y Deployment

---

## 📑 Tabla de Contenidos

1. [Stack Tecnológico](#1-stack-tecnológico)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Setup del Entorno de Desarrollo](#3-setup-del-entorno-de-desarrollo)
4. [Estructura del Proyecto](#4-estructura-del-proyecto)
5. [Frontend - Astro](#5-frontend---astro)
6. [Backend - PHP API](#6-backend---php-api)
7. [Base de Datos - MySQL](#7-base-de-datos---mysql)
8. [Sistema de Autenticación](#8-sistema-de-autenticación)
9. [Sistema de QR Completo](#9-sistema-de-qr-completo)
10. [Gestión de Media (Imágenes, Logos, Favicon)](#10-gestión-de-media)
11. [Diseño y Branding](#11-diseño-y-branding)
12. [Google Analytics](#12-google-analytics)
13. [JavaScript Libraries](#13-javascript-libraries)
14. [Build y Deploy para Producción](#14-build-y-deploy-para-producción)
15. [Performance y Optimización](#15-performance-y-optimización)
16. [Seguridad](#16-seguridad)
17. [Troubleshooting](#17-troubleshooting)
18. [API Reference Completa](#18-api-reference-completa)
19. [Mejores Prácticas](#19-mejores-prácticas)

---

## 1. Stack Tecnológico

### 1.1 Frontend Stack

```javascript
{
  "framework": "Astro 5.13.9",
  "css": "Tailwind CSS 3.4.18",
  "javascript": "Vanilla JS (ES2022)",
  "icons": "Heroicons + Custom PNG/SVG",
  "fonts": "System UI Stack (sin Google Fonts)",
  "imageOptimization": "Astro Built-in + astro-compress + Sharp",
  "buildTool": "Vite 6.x",
  "outputMode": "static (SSG)",
  "analytics": "Google Analytics 4 (gtag.js)"
}
```

**Dependencias principales:**
```json
{
  "@astrojs/tailwind": "^5.1.5",
  "astro": "^5.13.9",
  "astro-compress": "^2.3.8",
  "qrcode": "^1.5.4",
  "sharp": "^0.34.4",
  "tailwindcss": "^3.4.18"
}
```

### 1.2 Backend Stack

```php
<?php
// Stack Backend
[
  'language' => 'PHP 8.0+',
  'server' => 'Apache 2.4+ / Nginx',
  'database' => 'MySQL 8.0+',
  'architecture' => 'REST API',
  'auth' => 'Session-based',
  'cors' => 'Configurado',
  'routing' => 'Custom Router con .htaccess',
]
```

**Extensiones PHP requeridas:**
```ini
extension=pdo_mysql
extension=openssl
extension=json
extension=session
extension=hash
```

### 1.3 Base de Datos

```sql
-- MySQL 8.0+
Database: inbolsa_production
Charset: utf8mb4_unicode_ci
Collation: utf8mb4_0900_ai_ci
Engine: InnoDB
```

### 1.4 Hosting Recomendado

**Frontend (Archivos Estáticos):**
- ✅ iPage
- ✅ Netlify
- ✅ Vercel
- ✅ Cloudflare Pages
- ✅ AWS S3 + CloudFront

**Backend (PHP + MySQL):**
- ✅ VPS con Apache/Nginx
- ✅ DigitalOcean Droplet
- ✅ AWS EC2 + RDS
- ✅ Hostinger Business
- ✅ cPanel Hosting

---

## 2. Arquitectura del Sistema

### 2.1 Diagrama de Arquitectura (Producción)

```
┌─────────────────────────────────────────────────────────────┐
│                    USUARIO FINAL                            │
│                  (Navegador Web)                            │
└──────────────┬──────────────────────────────────────────────┘
               │
               ↓
┌──────────────────────────────────────────────────────────────┐
│                   FRONTEND (Astro SSG)                       │
│  Hosting: iPage / Netlify / Vercel                          │
│  ─────────────────────────────────────────────────────────  │
│  • Páginas públicas (/, /industrias, /contacto, etc.)      │
│  • Páginas privadas (/productos - requiere QR)             │
│  • Panel admin (/app/panel - requiere login)               │
│  • Assets optimizados (WebP, compresión Gzip/Brotli)       │
└──────────────┬───────────────────────────────────────────────┘
               │
               │ API Calls (fetch)
               ↓
┌──────────────────────────────────────────────────────────────┐
│              BACKEND API (PHP REST)                          │
│  Hosting: VPS / cPanel / DigitalOcean                       │
│  URL: https://api.tudominio.com/inbolsa-api/               │
│  ─────────────────────────────────────────────────────────  │
│  • /api/auth/* (login, logout, me)                         │
│  • /api/qr/* (create, list, validate, revoke)              │
│  • /api/access/payload (verificación de acceso)            │
│  • /api/health (health check)                              │
└──────────────┬───────────────────────────────────────────────┘
               │
               │ MySQL Connection
               ↓
┌──────────────────────────────────────────────────────────────┐
│              BASE DE DATOS (MySQL 8.0+)                      │
│  Hosting: AWS RDS / DigitalOcean DB / VPS                   │
│  ─────────────────────────────────────────────────────────  │
│  • admins (usuarios admin)                                  │
│  • qr_codes (códigos QR generados)                          │
│  • qr_access_log (registro de accesos)                      │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│              SERVICIOS EXTERNOS                              │
│  ─────────────────────────────────────────────────────────  │
│  • Google Analytics 4 (Tracking: G-SW9NY8MGLR)             │
│  • WhatsApp Business API (Chat widget)                      │
└──────────────────────────────────────────────────────────────┘
```

### 2.2 Flujo de Datos

#### Flujo 1: Visitante Público
```
Usuario → Frontend (/)
       → Ve contenido público
       → Google Analytics registra visita
       → Chat widget disponible (WhatsApp)
```

#### Flujo 2: Acceso por QR (Cliente Privado)
```
1. Usuario escanea QR → /qr?code=ABC123&m=120&p=product1,product2
2. Frontend valida con API → GET /api/qr/validate?code=ABC123
3. Backend verifica en DB → SELECT * FROM qr_codes WHERE code=...
4. Si válido: habilita acceso privado 120 min (localStorage + cookies)
5. Redirige a /privado → Video institucional
6. Usuario accede a /productos → Solo ve productos permitidos
7. Verificación periódica cada 30s → GET /api/access/payload
8. Si QR revocado → Expulsa usuario y limpia sesión
```

#### Flujo 3: Panel Administrativo
```
1. Admin accede a /app/login
2. Submit credenciales → POST /api/auth/login
3. Backend valida → SELECT * FROM admins WHERE email=...
4. Crea sesión PHP → session_start()
5. Redirige a /app/panel
6. Admin genera QR → POST /api/qr/create
7. Sistema crea código + URL + imagen QR
8. Admin puede compartir por WhatsApp
9. Ver listado QRs → GET /api/qr/list
10. Revocar QR → POST /api/qr/revoke
11. Google Analytics registra evento 'qr_created'
```

---

## 3. Setup del Entorno de Desarrollo

### 3.1 Requisitos Previos

```bash
# Node.js (v18+ recomendado)
node --version  # v18.17.0 o superior

# npm
npm --version   # 9.0.0 o superior

# Git
git --version   # 2.30.0 o superior

# PHP (para desarrollo local del backend)
php --version   # 8.0.0 o superior

# MySQL (para desarrollo local)
mysql --version # 8.0.0 o superior
```

### 3.2 Instalación del Proyecto

#### Paso 1: Clonar el Repositorio

```bash
git clone <url-repositorio> inbolsa-project
cd inbolsa-project
```

#### Paso 2: Instalar Dependencias Frontend

```bash
npm install
```

Esto instalará:
- Astro 5.13.9
- Tailwind CSS 3.4.18
- astro-compress 2.3.8
- sharp 0.34.4
- qrcode 1.5.4

#### Paso 3: Configurar Variables de Entorno

Crear archivo `.env` en la raíz del proyecto:

```bash
# .env
PUBLIC_API_BASE=/inbolsa-api/api
PUBLIC_SITE_URL=https://tudominio.com
```

#### Paso 4: Configurar Backend (Local)

Si estás usando un servidor local (WAMP, MAMP, etc.):

```bash
# 1. Copiar backend a tu servidor web
# Ejemplo: C:\wamp64\www\inbolsa-api\ (Windows)
# Ejemplo: /var/www/html/inbolsa-api/ (Linux)

# 2. Configurar config.php
```

Editar `inbolsa-api/config.php`:

```php
<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inbolsa_dev');
define('DB_USER', 'root');
define('DB_PASS', '');  // Tu password de MySQL

// URLs
define('FRONTEND_URL', 'http://localhost:4321');
define('API_URL', 'http://localhost/inbolsa-api');
```

#### Paso 5: Importar Base de Datos

```bash
# Crear base de datos
mysql -u root -p -e "CREATE DATABASE inbolsa_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar schema
mysql -u root -p inbolsa_dev < inbolsa-api/inbolsa_db_setup.sql
```

#### Paso 6: Crear Usuario Admin

```bash
mysql -u root -p inbolsa_dev
```

```sql
-- Crear admin de prueba
INSERT INTO admins (email, password_hash)
VALUES ('admin@inbolsa.net', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Password: password
```

### 3.3 Iniciar Servidor de Desarrollo

```bash
# Frontend (Astro)
npm run dev

# → Abre http://localhost:4321
```

**Verificar que funcione:**

1. Frontend: `http://localhost:4321`
2. API Health: `http://localhost/inbolsa-api/api/health`
3. Panel Admin: `http://localhost:4321/app/login`

---

## 4. Estructura del Proyecto

### 4.1 Estructura de Carpetas (Frontend)

```
c:\pruebadeploy\                          # Raíz del proyecto
│
├── src/                                  # Código fuente
│   ├── pages/                           # Páginas (rutas automáticas)
│   │   ├── index.astro                 # Home (/)
│   │   ├── valores.astro               # /valores
│   │   ├── historia.astro              # /historia
│   │   ├── industrias.astro            # /industrias
│   │   ├── soluciones.astro            # /soluciones (catálogo público)
│   │   ├── contacto.astro              # /contacto
│   │   ├── privado.astro               # /privado (landing privada)
│   │   ├── productos.astro             # /productos (requiere QR)
│   │   ├── qr.astro                    # /qr (validador de QR)
│   │   ├── login.astro                 # /login (redirección)
│   │   └── app/                        # Rutas protegidas admin
│   │       ├── index.astro            # /app (dashboard)
│   │       ├── login.astro            # /app/login
│   │       └── panel.astro            # /app/panel (generador QR)
│   │
│   ├── components/                      # Componentes reutilizables
│   │   ├── Header.astro                # Navegación principal
│   │   ├── Footer.astro                # Pie de página
│   │   ├── HomeHero.astro              # Hero de inicio
│   │   ├── AnimatedCard.astro          # Card con animaciones
│   │   ├── InteractiveHoverButton.astro # Botón interactivo
│   │   ├── ChatWidget.astro            # Widget WhatsApp
│   │   └── ProductosGrid.astro         # Grilla de productos
│   │
│   ├── layouts/                         # Layouts
│   │   ├── Base.astro                  # Layout público base
│   │   └── AppLayout.astro             # Layout app admin
│   │
│   └── styles/                          # Estilos globales
│       └── tailwind.css                # Importación Tailwind
│
├── public/                              # Assets estáticos (se copian tal cual)
│   ├── favicon1.png                    # Favicon 512x512
│   ├── logo.webp                       # Logo principal (WebP)
│   ├── logonavidad1.webp               # Logo navideño (opcional)
│   ├── logooea.webp                    # Logo OEA
│   ├── banderas.webp                   # Banderas Bolivia
│   ├── videolanding.mp4                # Video hero privado
│   │
│   ├── img/                            # Imágenes organizadas
│   │   ├── landing1.jpg                # Hero público
│   │   ├── landing2.jpg                # Secciones
│   │   ├── story1.jpg                  # Historia
│   │   ├── story2.webp                 # Historia
│   │   ├── story3.JPG                  # Historia
│   │   │
│   │   ├── industrias/                 # Imágenes de industrias
│   │   │   └── hilos.webp
│   │   │
│   │   ├── productos/                  # Imágenes de productos
│   │   │   ├── sacos-pp/
│   │   │   ├── big-bag/
│   │   │   ├── hilos/
│   │   │   └── telas/
│   │   │
│   │   ├── colors/                     # Galería B/N → Color (70+ imgs)
│   │   │   ├── 1.webp
│   │   │   ├── 2.webp
│   │   │   └── ...
│   │   │
│   │   ├── blackandwhite/              # Galería B/N
│   │   │   └── ...
│   │   │
│   │   └── icons/                      # Iconos y logos
│   │       ├── Productos/             # Iconos productos
│   │       └── Usos/                  # Iconos usos
│   │
│   └── lib/                            # Scripts compilados (no editar)
│       ├── api.js                     # Cliente API (build output)
│       └── privado.js                 # Sistema privado (build output)
│
├── lib/                                 # Librerías TypeScript (source)
│   ├── api.ts                          # Cliente API REST
│   └── privado.ts                      # Sistema acceso privado
│
├── dist/                                # Build output (generado, no versionar)
│   ├── index.html
│   ├── _astro/                        # Assets compilados
│   ├── lib/                           # Scripts compilados
│   └── img/                           # Imágenes optimizadas
│
├── astro.config.mjs                     # Configuración Astro
├── tailwind.config.js                   # Configuración Tailwind
├── tsconfig.json                        # TypeScript config
├── package.json                         # Dependencias
├── .env                                 # Variables entorno (no versionar)
├── .gitignore                          # Ignorar archivos
│
├── build.bat                            # Script build Windows (CMD)
├── build-deploy.ps1                     # Script build Windows (PowerShell)
│
└── DOCUMENTACION/
    ├── MANUAL_DESARROLLADORES_PRODUCCION.md  # Este archivo
    ├── GUIA_DISENO_BRANDING.md               # Guía de diseño
    ├── MANUAL_CORPORATIVO.md                 # Manual corporativo
    └── README.md                             # Readme del proyecto
```

### 4.2 Estructura del Backend

```
inbolsa-api/                              # Backend PHP
│
├── api/                                  # Endpoints
│   ├── auth.php                         # Autenticación
│   ├── qr.php                           # Sistema QR
│   ├── access.php                       # Verificación acceso
│   ├── health.php                       # Health check
│   └── test.php                         # Testing
│
├── config.php                            # Configuración DB y constantes
├── db.php                                # Conexión MySQL y helpers
├── middleware.php                        # Verificación de sesión
├── index.php                             # Router principal
├── _uri_bootstrap.php                    # Parsing de URIs
├── .htaccess                            # Rewrite rules Apache
│
├── storage/                              # Datos persistentes
│
├── sql/                                  # Scripts SQL
│   ├── schema.sql                       # Estructura inicial
│   ├── schema - add.sql                 # Migraciones
│   └── inbolsa_db_setup.sql            # Setup completo
│
└── diagnostics.php                       # Info del servidor
```

---

## 5. Frontend - Astro

### 5.1 Configuración de Astro

**Archivo:** `astro.config.mjs`

```javascript
import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';
import compress from 'astro-compress';

export default defineConfig({
  output: 'static',  // SSG (Static Site Generation)
  base: '/',

  integrations: [
    tailwind(),
    compress({
      CSS: true,
      HTML: {
        removeAttributeQuotes: false,
        collapseWhitespace: true,
        conservativeCollapse: true,
      },
      Image: true,
      JavaScript: true,
      SVG: true,
    }),
  ],

  server: {
    port: 4321,
    host: true,
  },

  vite: {
    server: {
      proxy: {
        '/api': {
          target: 'http://localhost/inbolsa-api/api',
          changeOrigin: true,
        },
      },
    },
    build: {
      target: ['es2020', 'edge88', 'firefox78', 'chrome87', 'safari14'],
    },
  },
});
```

### 5.2 Páginas Principales

#### 5.2.1 Home (`src/pages/index.astro`)

**Ruta:** `/`

**Características:**
- Hero con datos empresariales (50+ años, 52k m², 300+ empleados)
- Sección de características
- Cards destacadas (Valores, Industrias, Historia)
- Call to actions
- Animaciones fade-in on scroll
- Google Analytics pageview

**Componentes usados:**
- `<Header />`
- `<HomeHero />`
- `<AnimatedCard />`
- `<Footer />`
- `<ChatWidget />`

#### 5.2.2 Valores (`src/pages/valores.astro`)

**Ruta:** `/valores`

**Características:**
- Galería transformación B/N → Color (70+ imágenes)
- Misión y Visión
- 6 valores de negocio (Tecnología, Calidad, Servicio, Integridad, Sostenibilidad, Respeto)
- Política de seguridad
- Presencia nacional (La Paz, Santa Cruz)
- Galería de plantas en acción

#### 5.2.3 Historia (`src/pages/historia.astro`)

**Ruta:** `/historia`

**Características:**
- Timeline interactivo 1974-2024 (50 años)
- Slider de imágenes históricas
- Hitos principales
- Navegación por años

#### 5.2.4 Industrias (`src/pages/industrias.astro`)

**Ruta:** `/industrias`

**Características:**
- 6 sectores industriales con grillas animadas:
  1. Agroindustria
  2. Minería y Químicos
  3. Construcción
  4. Ganadería
  5. Industria Textil y Costura
  6. Industria Alimenticia

#### 5.2.5 Soluciones (`src/pages/soluciones.astro`)

**Ruta:** `/soluciones`

**Características:**
- Catálogo público completo
- Sistema de tabs:
  - Tab 0: Sacos PP (6 tipos)
  - Tab 1: Big Bag (5 tipos)
  - Tab 2: Hilos y Sogas (5 tipos)
  - Tab 3: Telas (2 tipos)
- Tarjetas de productos con especificaciones

#### 5.2.6 Productos Privados (`src/pages/productos.astro`)

**Ruta:** `/productos`

**Requiere:** Acceso por QR válido

**Características:**
- Verificación de acceso al cargar (`isPrivateEnabled()`)
- Filtrado de productos según grant list
- Verificación periódica cada 30s
- Auto-expulsión si QR revocado

#### 5.2.7 Contacto (`src/pages/contacto.astro`)

**Ruta:** `/contacto`

**Características:**
- Formulario de contacto (nombre, email, teléfono, empresa, ciudad, mensaje)
- 2 oficinas (La Paz, Santa Cruz)
- Mapas Google embebidos
- WhatsApp 24/7
- Horarios de atención

#### 5.2.8 Panel Admin (`src/pages/app/panel.astro`)

**Ruta:** `/app/panel`

**Requiere:** Autenticación de admin

**Características:**
- Generador de códigos QR
- Selector de productos
- Configuración de expiración y límite de usos
- Listado de QRs (tabla con estado, usos, fecha)
- Revocación de QRs
- Estadísticas (total, activos, usos, revocados)
- Compartir por WhatsApp
- Modal de información de QR
- Integración con Google Analytics (eventos 'qr_created', 'qr_shared')

---

## 6. Backend - PHP API

### 6.1 Endpoints Disponibles

#### 6.1.1 Health Check

```
GET /api/health
```

**Respuesta:**
```json
{
  "ok": true,
  "message": "API operativa",
  "timestamp": 1700000000
}
```

#### 6.1.2 Autenticación

**Login:**
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@inbolsa.net",
  "password": "tu_password"
}
```

**Respuesta éxito:**
```json
{
  "ok": true,
  "admin": {
    "id": 1,
    "email": "admin@inbolsa.net"
  }
}
```

**Logout:**
```
POST /api/auth/logout
```

**Me (verificar sesión):**
```
GET /api/auth/me
```

#### 6.1.3 Sistema QR

**Crear QR:**
```
POST /api/qr/create
Content-Type: application/json
Credentials: include

{
  "type": "productos",
  "recipient": "Nombre Cliente",
  "products": ["sacos-convencionales", "big-bag-tubular"],
  "allow": "include",
  "expires_at": "2025-12-31 23:59:59",
  "usage_limit": 10
}
```

**Listar QRs:**
```
GET /api/qr/list
Credentials: include
```

**Validar QR:**
```
GET /api/qr/validate?code=ABC123XYZ
```

**Revocar QR:**
```
POST /api/qr/revoke
Content-Type: application/json
Credentials: include

{
  "code": "ABC123XYZ"
}
```

#### 6.1.4 Access Payload

```
GET /api/access/payload
Credentials: include
```

**Respuesta si válido:**
```json
{
  "ok": true,
  "payload": {
    "section": "productos",
    "allow": "include",
    "products": ["sacos-convencionales", "big-bag-tubular"],
    "exp": 1735689599,
    "recipient": "Cliente Ejemplo"
  }
}
```

---

## 7. Base de Datos - MySQL

### 7.1 Tablas

#### Tabla `admins`

```sql
CREATE TABLE admins (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Tabla `qr_codes`

```sql
CREATE TABLE qr_codes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(255) UNIQUE NOT NULL,
  payload JSON NOT NULL,
  status ENUM('active', 'revoked', 'expired') DEFAULT 'active',
  expires_at TIMESTAMP NULL,
  usage_limit INT DEFAULT NULL,
  used_count INT DEFAULT 0,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL,
  INDEX idx_code (code),
  INDEX idx_status (status),
  INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Tabla `qr_access_log`

```sql
CREATE TABLE qr_access_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  qr_code_id INT,
  ip VARCHAR(45),
  user_agent TEXT,
  accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (qr_code_id) REFERENCES qr_codes(id) ON DELETE CASCADE,
  INDEX idx_qr_code_id (qr_code_id),
  INDEX idx_accessed_at (accessed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 7.2 Estructura del Payload QR

```json
{
  "section": "productos",
  "allow": "include",
  "products": [
    "sacos-convencionales",
    "sacos-red",
    "sacos-laminados",
    "big-bag-tubular"
  ],
  "exp": 1735689599,
  "recipient": "Nombre del Cliente S.A."
}
```

**Campos:**
- `section`: Sección a la que da acceso (`"productos"`)
- `allow`: Modo de filtro (`"include"` = solo productos listados, `"all"` = todos)
- `products`: Array de IDs de productos permitidos
- `exp`: Timestamp de expiración (UNIX timestamp)
- `recipient`: Nombre del destinatario

---

## 8. Sistema de Autenticación

### 8.1 Flujo de Login

```
1. Usuario accede a /app/login
2. Ingresa email y password
3. Submit form → POST /api/auth/login
4. Backend:
   a. Busca admin en DB: SELECT * FROM admins WHERE email = ?
   b. Verifica password: password_verify($input, $hash)
   c. Si válido: crea sesión PHP (session_start())
   d. Guarda datos: $_SESSION['admin_id'] = $admin['id']
   e. Retorna: { ok: true, admin: {...} }
5. Frontend:
   a. Guarda respuesta
   b. Redirige a /app/panel
6. En /app/panel:
   a. Verifica sesión al cargar: GET /api/auth/me
   b. Si no válido: redirige a /app/login
```

### 8.2 Middleware de Protección

**Backend (`middleware.php`):**

```php
<?php
function requireAuth() {
  session_start();
  if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'not_authenticated']);
    exit;
  }
}
```

**Frontend (JavaScript):**

```javascript
// Verificar sesión al cargar página protegida
import { api } from '/lib/api.js';

async function checkAuth() {
  try {
    const user = await api.me();
    console.log('Usuario autenticado:', user);
  } catch (error) {
    console.error('No autenticado:', error);
    location.href = '/app/login';
  }
}

// Llamar al cargar
checkAuth();
```

---

## 9. Sistema de QR Completo

### 9.1 Generación de QR

**Panel Admin (`/app/panel`):**

1. Admin selecciona productos
2. Ingresa nombre de destinatario
3. Configura expiración (opcional)
4. Configura límite de usos (opcional)
5. Click "Generar QR"

**JavaScript:**

```javascript
async function generarQR() {
  const selectedProducts = Array.from(
    document.querySelectorAll('input[name="products"]:checked')
  ).map(cb => cb.value);

  const recipient = document.getElementById('recipient').value.trim();

  const payload = {
    type: 'productos',
    recipient: recipient || 'Cliente',
    products: selectedProducts,
    allow: selectedProducts.length === 0 ? 'all' : 'include',
    expires_at: null,  // O fecha específica
    usage_limit: null  // O número específico
  };

  try {
    const response = await fetch('/inbolsa-api/api/qr/create', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify(payload)
    });

    const data = await response.json();

    if (data.ok) {
      console.log('QR generado:', data.code);
      console.log('URL:', data.url);

      // Registrar evento en Google Analytics
      if (window.gtag) {
        gtag('event', 'qr_created', {
          event_category: 'panel',
          event_label: recipient,
          value: selectedProducts.length
        });
      }

      // Actualizar lista
      listarQRs();
    }
  } catch (error) {
    console.error('Error generando QR:', error);
  }
}
```

### 9.2 Validación de QR

**Flujo:**

```
1. Usuario escanea QR → URL: /qr?code=ABC123&m=120&p=prod1,prod2
2. Página /qr.astro carga
3. JavaScript:
   a. Lee parámetros URL (code, m, p)
   b. Llama API: GET /api/qr/validate?code=ABC123
4. Backend:
   a. SELECT * FROM qr_codes WHERE code = 'ABC123'
   b. Verifica status != 'revoked'
   c. Verifica expires_at > NOW()
   d. Verifica used_count < usage_limit (si aplica)
   e. Incrementa used_count
   f. Inserta en qr_access_log (IP, user_agent)
   g. Retorna payload
5. Frontend:
   a. Recibe payload
   b. Llama enablePrivate(120) → guarda en localStorage
   c. Llama setGrantProducts(['prod1', 'prod2'])
   d. Redirige a /privado
```

### 9.3 Verificación Periódica

**En `/productos`:**

```javascript
// Verificar cada 30 segundos
setInterval(async () => {
  try {
    const response = await fetch('/inbolsa-api/api/access/payload', {
      credentials: 'include'
    });

    const data = await response.json();

    if (!data.ok) {
      // QR revocado o expirado
      console.log('Acceso ya no válido');
      disablePrivate();
      location.href = '/';
    }
  } catch (error) {
    console.error('Error verificando acceso:', error);
  }
}, 30000);
```

---

## 10. Gestión de Media

### 10.1 Estructura de Carpeta `public/`

Todos los archivos en `public/` se copian directamente al build output (`dist/`) sin procesamiento.

```
public/
├── favicon1.png              # 512x512px (se usa en <head>)
├── logo.webp                 # Logo principal (formato WebP)
├── logonavidad1.webp         # Logo navideño (opcional)
├── logonavidad2.webp         # Logo navideño alternativo
├── logooea.webp              # Logo OEA (certificación)
├── banderas.webp             # Banderas de Bolivia
├── videolanding.mp4          # Video hero (/privado)
│
└── img/
    ├── landing1.jpg          # Hero principal
    ├── landing2.jpg          # Secciones
    ├── story1.jpg            # Historia
    ├── story2.webp
    ├── story3.JPG
    │
    ├── industrias/           # Imágenes de industrias
    ├── productos/            # Imágenes de productos
    ├── colors/               # Galería B/N → Color (70+ imágenes)
    ├── blackandwhite/        # Galería B/N
    └── icons/                # Iconos SVG/PNG
        ├── Productos/
        └── Usos/
```

### 10.2 Cómo Cambiar el Logo

#### Opción 1: Reemplazar archivo existente

**Ubicación:** `public/logo.webp`

1. Prepara tu nuevo logo en formato **WebP** (recomendado) o PNG
2. Dimensiones recomendadas: **500x150px** (ancho x alto)
3. Optimiza la imagen (compresión WebP 80-90%)
4. Renombra tu archivo a `logo.webp`
5. Reemplaza el archivo en `public/logo.webp`
6. Rebuild:

```bash
npm run build
```

#### Opción 2: Usar un nuevo archivo

1. Agrega tu logo en `public/` (ejemplo: `public/logo-nuevo.webp`)
2. Edita `src/components/Header.astro`:

```astro
<!-- Línea ~50 -->
<img
  id="siteLogo"
  src="/logo-nuevo.webp"  <!-- Cambiar aquí -->
  alt="Inbolsa"
  class="h-12 md:h-16 w-auto"
/>
```

3. Rebuild.

#### Logos Especiales (Navidad, Eventos)

**Ubicación:** `public/logonavidad1.webp`, `public/logonavidad2.webp`

Para activar un logo temporal:

1. Edita `src/components/Header.astro`
2. Cambia la ruta en el `<img>`:

```astro
<img
  id="siteLogo"
  src="/logonavidad1.webp"  <!-- Logo temporal -->
  alt="Inbolsa"
  class="h-12 md:h-16 w-auto"
/>
```

3. Rebuild y deploy.

**Revertir:** Vuelve a cambiar a `/logo.webp`.

### 10.3 Cómo Cambiar el Favicon

**Ubicación:** `public/favicon1.png`

1. Prepara tu favicon en formato PNG
2. Dimensiones: **512x512px** (cuadrado)
3. Renombra a `favicon1.png`
4. Reemplaza `public/favicon1.png`
5. Edita `src/layouts/Base.astro` si cambias el nombre:

```astro
<!-- Línea ~19 -->
<link rel="icon" type="image/png" href="/favicon1.png" />
```

6. Rebuild.

**Formatos soportados:**
- `.png` (recomendado para favicon)
- `.ico` (legacy)
- `.svg` (moderno pero menos compatible)

### 10.4 Optimización de Imágenes

#### Herramientas Recomendadas

**1. WebP Converter (Online)**
- https://squoosh.app/ (Google)
- Calidad recomendada: 80-90%

**2. TinyPNG**
- https://tinypng.com/
- Comprime PNG/JPG sin pérdida visible

**3. Sharp (CLI - ya instalado en el proyecto)**

```bash
# Convertir JPG/PNG a WebP
npx sharp -i input.jpg -o output.webp --webp

# Redimensionar
npx sharp -i input.jpg -o output.jpg --resize 1920 1080
```

#### Mejores Prácticas

| Tipo de Imagen | Formato Recomendado | Tamaño Máximo | Calidad |
|----------------|---------------------|---------------|---------|
| Logo | WebP o PNG | 500x150px | 90% |
| Favicon | PNG | 512x512px | 100% |
| Hero/Landing | WebP o JPG | 1920x1080px | 80% |
| Productos | WebP | 800x600px | 85% |
| Íconos | PNG o SVG | 128x128px | 100% |
| Galería | WebP | 1200x800px | 80% |

### 10.5 Cómo Agregar Nuevas Imágenes

#### Paso 1: Optimizar la Imagen

```bash
# Ejemplo: convertir y optimizar
npx sharp -i producto-nuevo.jpg -o producto-nuevo.webp --webp quality=85
```

#### Paso 2: Colocar en `public/img/`

```bash
# Estructura sugerida
public/img/productos/sacos-pp/producto-nuevo.webp
```

#### Paso 3: Usar en el Código

**En componente Astro:**

```astro
<img
  src="/img/productos/sacos-pp/producto-nuevo.webp"
  alt="Producto Nuevo"
  class="w-full h-auto rounded-xl"
  loading="lazy"
/>
```

**Atributos importantes:**
- `loading="lazy"` → Carga diferida (mejora performance)
- `alt="..."` → Accesibilidad y SEO
- `width` y `height` → Evita layout shift

#### Paso 4: Rebuild

```bash
npm run build
```

### 10.6 Cambiar Imágenes de Productos

**Ubicación:** `public/img/productos/`

**Estructura:**

```
public/img/productos/
├── sacos-pp/
│   ├── sacos-convencionales.webp
│   ├── sacos-red.webp
│   ├── sacos-laminados.webp
│   └── ...
├── big-bag/
│   ├── big-bag-tubular.webp
│   ├── big-bag-upanel.webp
│   └── ...
├── hilos/
│   └── hilo-plano.webp
└── telas/
    └── tela-plana.webp
```

**Para reemplazar:**

1. Optimiza la nueva imagen (WebP, 800x600px, 85%)
2. Renombra con el **mismo nombre** del archivo anterior
3. Reemplaza en `public/img/productos/categoria/`
4. Rebuild:

```bash
npm run build
```

**Para agregar nuevo producto:**

1. Agrega imagen en la carpeta correspondiente
2. Edita `src/pages/soluciones.astro` o `src/pages/productos.astro`
3. Agrega nueva tarjeta de producto:

```astro
<article class="group rounded-xl bg-white p-6 shadow-lg" data-product-id="producto-nuevo-id">
  <img
    src="/img/productos/categoria/producto-nuevo.webp"
    alt="Producto Nuevo"
    class="w-full h-48 object-cover rounded-lg mb-4"
  />
  <h3 class="text-xl font-bold">Producto Nuevo</h3>
  <p class="text-slate-600 mt-2">Descripción del producto...</p>
</article>
```

### 10.7 Cambiar Video del Landing Privado

**Ubicación:** `public/videolanding.mp4`

**Especificaciones actuales:**
- Formato: MP4 (H.264)
- Dimensiones: ~1920x1080px
- Duración: ~30-60 segundos
- Tamaño: <10 MB (recomendado)

**Para reemplazar:**

1. Prepara tu video en formato MP4
2. Optimiza con HandBrake o similar (H.264, CRF 23)
3. Renombra a `videolanding.mp4`
4. Reemplaza `public/videolanding.mp4`
5. Rebuild

**Optimización de video:**

```bash
# Usando FFmpeg (opcional)
ffmpeg -i input.mov -c:v libx264 -crf 23 -preset medium -c:a aac -b:a 128k videolanding.mp4
```

**Usar en código (`src/pages/privado.astro`):**

```astro
<video
  class="w-full max-w-4xl rounded-2xl shadow-2xl"
  controls
  poster="/img/video-poster.jpg"
>
  <source src="/videolanding.mp4" type="video/mp4" />
  Tu navegador no soporta video HTML5.
</video>
```

---

## 11. Diseño y Branding

Para la guía completa de diseño, consulta: **[GUIA_DISENO_BRANDING.md](GUIA_DISENO_BRANDING.md)**

### 11.1 Resumen Rápido

#### Tipografía

**Sistema de Fuentes Nativas** (no usa Google Fonts)

- macOS/iOS: **San Francisco**
- Windows: **Segoe UI**
- Android: **Roboto**
- Linux: **Noto Sans**

**Pesos usados:**
- `font-light` (300) - Textos descriptivos
- `font-medium` (500) - Labels
- `font-semibold` (600) - Subtítulos, botones
- `font-bold` (700) - Títulos
- `font-black` (900) - Hero titles

#### Color de Marca

**Brand Blue:** `#3E7DD2`

**Configuración en `tailwind.config.js`:**

```javascript
colors: {
  brand: {
    50:  '#eef6ff',
    500: '#3e7dd2',  // ⭐ COLOR PRINCIPAL
    600: '#2f65b0',  // Hover
    900: '#213e63'
  }
}
```

**Para cambiar el color de marca:**

1. Genera paleta en https://uicolors.app/create
2. Edita `tailwind.config.js` (líneas 6-11)
3. Actualiza `src/layouts/Base.astro`:
   - Línea 15: `<meta name="theme-color" content="#TU_COLOR" />`
   - Línea 61: `outline: 3px solid #TU_COLOR;`
4. Rebuild

---

## 12. Google Analytics

### 12.1 Configuración Actual

**Tracking ID:** `G-SW9NY8MGLR`

**Ubicación:** `src/layouts/Base.astro` (líneas 22-28)

```html
<!-- Google tag (gtag.js) -->
<script is:inline async src="https://www.googletagmanager.com/gtag/js?id=G-SW9NY8MGLR"></script>
<script is:inline>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-SW9NY8MGLR');
</script>
```

### 12.2 Eventos Personalizados

#### Evento: QR Creado

**Ubicación:** `src/pages/app/panel.astro` (línea ~257)

```javascript
if (window.gtag) {
  gtag('event', 'qr_created', {
    event_category: 'panel',
    event_label: nombreDestinatario,
    value: cantidadProductos
  });
}
```

#### Evento: QR Compartido

**Ubicación:** `src/pages/app/panel.astro` (línea ~382)

```javascript
if (window.gtag) {
  gtag('event', 'qr_shared', {
    event_category: 'panel',
    method: navigator.share ? 'native' : 'whatsapp'
  });
}
```

### 12.3 Cambiar ID de Google Analytics

**Para usar tu propio Tracking ID:**

1. Crea una propiedad en Google Analytics 4
2. Copia tu ID (formato: `G-XXXXXXXXXX`)
3. Edita `src/layouts/Base.astro`:

```astro
<!-- Línea 23 -->
<script is:inline async src="https://www.googletagmanager.com/gtag/js?id=G-TU-NUEVO-ID"></script>
<script is:inline>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-TU-NUEVO-ID');  <!-- Cambiar aquí -->
</script>
```

4. Rebuild y deploy

### 12.4 Verificar que Google Analytics Funciona

1. Abre tu sitio en un navegador
2. Abre DevTools (F12) → Console
3. Ejecuta:

```javascript
console.log(window.gtag);  // Debe existir
console.log(window.dataLayer);  // Debe tener datos
```

4. Ve a Google Analytics → Tiempo Real → debe aparecer tu visita

### 12.5 Eventos Personalizados Adicionales (Ejemplos)

#### Rastrear Clicks en Botones

```javascript
// En cualquier página .astro
document.getElementById('btn-contacto').addEventListener('click', () => {
  if (window.gtag) {
    gtag('event', 'click', {
      event_category: 'button',
      event_label: 'contacto_hero'
    });
  }
});
```

#### Rastrear Envío de Formulario

```javascript
document.getElementById('form-contacto').addEventListener('submit', (e) => {
  if (window.gtag) {
    gtag('event', 'form_submit', {
      event_category: 'contact',
      event_label: 'contacto_page'
    });
  }
});
```

#### Rastrear Scroll Profundo

```javascript
let scrollTracked = false;
window.addEventListener('scroll', () => {
  if (!scrollTracked && window.scrollY > document.documentElement.scrollHeight * 0.75) {
    scrollTracked = true;
    if (window.gtag) {
      gtag('event', 'scroll', {
        event_category: 'engagement',
        event_label: '75_percent'
      });
    }
  }
});
```

### 12.6 Deshabilitar Google Analytics (GDPR)

Para cumplir con GDPR/CCPA, puedes hacer condicional el tracking:

**Opción 1: Variable de Entorno**

`.env`:
```
PUBLIC_ANALYTICS_ENABLED=true
```

`src/layouts/Base.astro`:
```astro
{import.meta.env.PUBLIC_ANALYTICS_ENABLED === 'true' && (
  <>
    <script is:inline async src="https://www.googletagmanager.com/gtag/js?id=G-SW9NY8MGLR"></script>
    <script is:inline>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-SW9NY8MGLR');
    </script>
  </>
)}
```

**Opción 2: Consent Mode**

```javascript
// Configurar con consentimiento
gtag('consent', 'default', {
  'analytics_storage': 'denied'
});

// Cuando usuario acepta cookies:
gtag('consent', 'update', {
  'analytics_storage': 'granted'
});
```

---

## 13. JavaScript Libraries

### 13.1 API Client (`lib/api.ts`)

**Ubicación:** `lib/api.ts`
**Build output:** `public/lib/api.js`

**Endpoints disponibles:**

```typescript
import { api } from '/lib/api.js';

// Health check
await api.health();

// Auth
await api.login('email@example.com', 'password');
await api.logout();
await api.me();

// QR
await api.qrCreate({ type: 'productos', products: [...] });
await api.qrList();
await api.qrValidate('CODE123');
await api.qrRevoke('CODE123');

// Access
await api.accessPayload();
await api.accessPayload('TOKEN123');
```

### 13.2 Sistema de Acceso Privado (`lib/privado.ts`)

**Ubicación:** `lib/privado.ts`
**Build output:** `public/lib/privado.js`

**Funciones disponibles:**

```typescript
import {
  enablePrivate,
  disablePrivate,
  isPrivateEnabled,
  setGrantProducts,
  getGrantProducts,
  checkAccessValid,
  startRevocationCheck
} from '/lib/privado.js';

// Habilitar acceso privado por 120 minutos
enablePrivate(120);

// Verificar si está habilitado
if (isPrivateEnabled()) {
  console.log('Acceso privado activo');
}

// Guardar productos permitidos
setGrantProducts(['sacos-convencionales', 'big-bag-tubular']);

// Obtener productos permitidos
const products = getGrantProducts();  // ['sacos-convencionales', 'big-bag-tubular']

// Verificar validez con backend
const valid = await checkAccessValid();

// Deshabilitar y limpiar todo
disablePrivate();
```

---

## 14. Build y Deploy para Producción

### 14.1 Build del Frontend

```bash
# Limpiar build anterior
rm -rf dist/

# Build optimizado para producción
npm run build
```

**Output:** `dist/`

**Optimizaciones aplicadas:**
- Compresión HTML (minificado)
- Compresión CSS (minificado + tree-shaking)
- Compresión JavaScript (minificado + tree-shaking)
- Optimización de imágenes (WebP + Sharp)
- Gzip/Brotli ready

### 14.2 Deployment Frontend (Hosting Estático)

#### Opción 1: iPage (cPanel)

```bash
# 1. Build
npm run build

# 2. Subir vía FTP/SFTP
# Host: ftp.tudominio.com
# Usuario: tu_usuario
# Carpeta destino: public_html/

# 3. Subir todo el contenido de dist/ a public_html/
```

**Usando FileZilla:**
1. Conectar a FTP
2. Navegar a `public_html/`
3. Arrastrar todo el contenido de `dist/` (no la carpeta dist misma)
4. Esperar a que termine la transferencia

#### Opción 2: Netlify

```bash
# 1. Instalar Netlify CLI
npm install -g netlify-cli

# 2. Login
netlify login

# 3. Deploy
netlify deploy --prod --dir=dist
```

**O configurar deploy automático:**

1. Conectar repositorio en Netlify
2. Configurar:
   - Build command: `npm run build`
   - Publish directory: `dist`
3. Cada push a `main` → deploy automático

#### Opción 3: Vercel

```bash
# 1. Instalar Vercel CLI
npm install -g vercel

# 2. Login
vercel login

# 3. Deploy
vercel --prod
```

#### Opción 4: Cloudflare Pages

1. Ve a Cloudflare Pages
2. Conecta repositorio
3. Configurar:
   - Build command: `npm run build`
   - Build output directory: `dist`
4. Deploy

### 14.3 Deployment Backend (PHP + MySQL)

#### Requisitos del Servidor

```
- PHP 8.0+
- MySQL 8.0+ (o MariaDB 10.5+)
- Apache 2.4+ con mod_rewrite
- HTTPS (SSL Certificate)
```

#### Paso 1: Subir archivos Backend

```bash
# Via FTP/SFTP
# Carpeta destino: /var/www/html/inbolsa-api/
# O: public_html/inbolsa-api/

# Archivos a subir:
inbolsa-api/
├── api/
├── config.php
├── db.php
├── index.php
├── middleware.php
├── .htaccess
└── storage/
```

#### Paso 2: Configurar `config.php`

```php
<?php
// config.php - PRODUCCIÓN

// Base de datos
define('DB_HOST', 'localhost');  // O IP del servidor MySQL
define('DB_NAME', 'inbolsa_production');
define('DB_USER', 'inbolsa_user');
define('DB_PASS', 'PASSWORD_SEGURO_AQUI');  // ⚠️ CAMBIAR

// URLs
define('FRONTEND_URL', 'https://tudominio.com');
define('API_URL', 'https://tudominio.com/inbolsa-api');

// Seguridad
define('SESSION_SECURE', true);  // Solo HTTPS
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// Modo debug
define('DEBUG_MODE', false);  // ⚠️ SIEMPRE false en producción
```

#### Paso 3: Crear Base de Datos en Producción

```bash
# Conectar a MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE inbolsa_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Crear usuario
CREATE USER 'inbolsa_user'@'localhost' IDENTIFIED BY 'PASSWORD_SEGURO';

# Dar permisos
GRANT ALL PRIVILEGES ON inbolsa_production.* TO 'inbolsa_user'@'localhost';
FLUSH PRIVILEGES;

# Salir
EXIT;
```

#### Paso 4: Importar Schema

```bash
# Importar estructura
mysql -u inbolsa_user -p inbolsa_production < inbolsa-api/sql/inbolsa_db_setup.sql
```

#### Paso 5: Crear Usuario Admin

```bash
mysql -u inbolsa_user -p inbolsa_production
```

```sql
-- Crear admin
INSERT INTO admins (email, password_hash)
VALUES (
  'admin@tudominio.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);
-- Password por defecto: "password"
-- ⚠️ CAMBIAR INMEDIATAMENTE después del primer login
```

#### Paso 6: Verificar `.htaccess`

**Ubicación:** `inbolsa-api/.htaccess`

```apache
# .htaccess
RewriteEngine On

# Redirigir todo a index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Headers de seguridad
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"

# CORS (si frontend está en dominio diferente)
Header set Access-Control-Allow-Origin "https://tudominio.com"
Header set Access-Control-Allow-Credentials "true"
Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"

# Comprimir respuestas
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE application/json
  AddOutputFilterByType DEFLATE text/plain
</IfModule>
```

#### Paso 7: Verificar Funcionamiento

```bash
# Health check
curl https://tudominio.com/inbolsa-api/api/health

# Debe retornar:
# {"ok":true,"message":"API operativa","timestamp":1700000000}
```

### 14.4 Configuración de DNS

Si el frontend y backend están en dominios diferentes:

**Frontend:** `https://inbolsa.com` (Netlify/Vercel)
**Backend:** `https://api.inbolsa.com` (VPS)

**Configuración DNS:**

```
# Registro A para backend
api.inbolsa.com  →  A  →  123.45.67.89 (IP del VPS)

# Registro CNAME para frontend
www.inbolsa.com  →  CNAME  →  tudominio.netlify.app
inbolsa.com      →  A      →  IP de Netlify
```

**Actualizar `.env` en frontend:**

```bash
PUBLIC_API_BASE=https://api.inbolsa.com/api
```

**Actualizar CORS en backend:**

```php
// config.php
define('ALLOWED_ORIGINS', [
  'https://inbolsa.com',
  'https://www.inbolsa.com'
]);
```

### 14.5 HTTPS / SSL

**Obligatorio en producción.**

#### Opción 1: Let's Encrypt (Gratis)

```bash
# En el servidor
sudo apt install certbot python3-certbot-apache

# Obtener certificado
sudo certbot --apache -d tudominio.com -d www.tudominio.com

# Auto-renovación
sudo certbot renew --dry-run
```

#### Opción 2: Cloudflare (Gratis)

1. Agregar sitio a Cloudflare
2. Cambiar nameservers del dominio
3. SSL/TLS → Full (strict)
4. Certificado generado automáticamente

### 14.6 Scripts de Deploy Automático

**Para Windows (PowerShell):**

`build-deploy-production.ps1`:

```powershell
# Build
Write-Host "Building..." -ForegroundColor Yellow
npm run build

# Subir a servidor vía SFTP (requiere WinSCP o similar)
Write-Host "Deploying to server..." -ForegroundColor Yellow
# ... configurar WinSCP script ...

Write-Host "Deploy complete!" -ForegroundColor Green
```

**Para Linux/macOS:**

`build-deploy-production.sh`:

```bash
#!/bin/bash

# Build
echo "Building..."
npm run build

# Subir vía rsync
echo "Deploying to server..."
rsync -avz --delete dist/ user@server:/var/www/html/

echo "Deploy complete!"
```

---

## 15. Performance y Optimización

### 15.1 Métricas Objetivo

| Métrica | Objetivo | Herramienta |
|---------|----------|-------------|
| First Contentful Paint (FCP) | < 1.5s | Lighthouse |
| Largest Contentful Paint (LCP) | < 2.5s | Lighthouse |
| Time to Interactive (TTI) | < 3.5s | Lighthouse |
| Total Blocking Time (TBT) | < 300ms | Lighthouse |
| Cumulative Layout Shift (CLS) | < 0.1 | Lighthouse |
| Speed Index | < 3.0s | Lighthouse |

### 15.2 Optimizaciones Implementadas

#### Frontend

- ✅ Static Site Generation (SSG)
- ✅ Compresión HTML/CSS/JS (astro-compress)
- ✅ Optimización de imágenes (Sharp + WebP)
- ✅ Lazy loading de imágenes (`loading="lazy"`)
- ✅ System font stack (sin cargar fuentes externas)
- ✅ Prefetch de enlaces en hover
- ✅ Animaciones con GPU (`transform3d`, `will-change`)
- ✅ Scroll con `requestAnimationFrame`
- ✅ IntersectionObserver para animaciones
- ✅ DNS prefetch de Google Analytics

#### Backend

- ✅ Prepared statements (previene SQL injection + performance)
- ✅ Índices en tablas (`idx_code`, `idx_status`, etc.)
- ✅ Compresión de respuestas JSON (mod_deflate)
- ✅ Session storage eficiente

### 15.3 Caché

#### Frontend (Netlify/Vercel)

**Headers automáticos:**
```
Cache-Control: public, max-age=31536000, immutable  # Assets /_ astro/*
Cache-Control: public, max-age=0, must-revalidate   # HTML
```

#### Backend (Apache)

**`.htaccess`:**
```apache
<IfModule mod_expires.c>
  ExpiresActive On

  # JSON API responses (sin caché)
  ExpiresByType application/json "access plus 0 seconds"

  # Imágenes (1 año)
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
```

### 15.4 Monitoreo

**Herramientas:**

1. **Google Lighthouse** (auditoría completa)
   ```bash
   npx lighthouse https://tudominio.com --view
   ```

2. **PageSpeed Insights**
   https://pagespeed.web.dev/

3. **WebPageTest**
   https://www.webpagetest.org/

4. **Google Analytics** (tiempo de carga real de usuarios)

---

## 16. Seguridad

### 16.1 Checklist de Seguridad

#### Frontend

- ✅ HTTPS obligatorio en producción
- ✅ Content Security Policy (CSP) headers
- ✅ SameSite cookies
- ✅ Validación de inputs en formularios
- ✅ Sanitización de datos antes de mostrar

#### Backend

- ✅ Prepared statements (previene SQL injection)
- ✅ Password hashing con `password_hash()` (bcrypt)
- ✅ Validación de sesiones
- ✅ CORS configurado (solo dominios permitidos)
- ✅ Headers de seguridad (X-Frame-Options, X-XSS-Protection)
- ✅ Protección contra CSRF (tokens en formularios)
- ✅ Rate limiting (limitar requests por IP)

### 16.2 Cambiar Password de Admin

```bash
# Conectar a MySQL
mysql -u inbolsa_user -p inbolsa_production
```

```sql
-- Generar hash del nuevo password
-- Usa: https://bcrypt-generator.com/ (rounds: 10)
-- O en PHP:
-- echo password_hash('tu_nuevo_password', PASSWORD_DEFAULT);

-- Actualizar password
UPDATE admins
SET password_hash = '$2y$10$HASH_GENERADO_AQUI'
WHERE email = 'admin@tudominio.com';
```

### 16.3 Protección de Archivos Sensibles

**`.htaccess` en raíz del backend:**

```apache
# Denegar acceso a archivos sensibles
<FilesMatch "\.(sql|env|log|md)$">
  Order allow,deny
  Deny from all
</FilesMatch>

# Denegar listado de directorios
Options -Indexes
```

### 16.4 Backup de Base de Datos

```bash
# Exportar backup
mysqldump -u inbolsa_user -p inbolsa_production > backup_$(date +%Y%m%d).sql

# Comprimir
gzip backup_$(date +%Y%m%d).sql

# Resultado: backup_20251120.sql.gz
```

**Automatizar con cron (Linux):**

```bash
# crontab -e
0 2 * * * /usr/bin/mysqldump -u inbolsa_user -pPASSWORD inbolsa_production | gzip > /backups/inbolsa_$(date +\%Y\%m\%d).sql.gz
```

---

## 17. Troubleshooting

### 17.1 Errores Comunes

#### Error: "API not reachable" en frontend

**Causa:** URL de API incorrecta o CORS bloqueado

**Solución:**

1. Verificar `.env`:
   ```bash
   PUBLIC_API_BASE=https://tudominio.com/inbolsa-api/api
   ```

2. Verificar CORS en backend (`index.php`):
   ```php
   header('Access-Control-Allow-Origin: https://tudominio.com');
   header('Access-Control-Allow-Credentials: true');
   ```

3. Rebuild frontend:
   ```bash
   npm run build
   ```

#### Error: "Session expired" constantemente

**Causa:** Cookies no se guardan (dominio diferente o HTTPS mal configurado)

**Solución:**

1. Verificar que frontend y backend estén en HTTPS
2. Verificar `SameSite` cookie:
   ```php
   session_set_cookie_params([
     'secure' => true,
     'httponly' => true,
     'samesite' => 'Lax'
   ]);
   ```

#### Error: "QR code not found" al validar

**Causa:** Código QR no existe en base de datos

**Solución:**

1. Verificar en MySQL:
   ```sql
   SELECT * FROM qr_codes WHERE code = 'ABC123';
   ```

2. Si no existe, regenerar QR desde panel admin

#### Error: Imágenes no cargan (404)

**Causa:** Rutas incorrectas o archivos no copiados en build

**Solución:**

1. Verificar que imagen existe en `public/img/`
2. Usar ruta absoluta desde raíz: `/img/producto.webp`
3. Rebuild:
   ```bash
   npm run build
   ```

### 17.2 Logs

#### Frontend (Desarrollo)

```bash
# Ver logs del servidor Astro
npm run dev

# Ver en navegador
# DevTools → Console
```

#### Backend (Producción)

```bash
# Apache error log (Linux)
sudo tail -f /var/log/apache2/error.log

# Apache error log (cPanel)
# Panel de control → Error Log

# PHP errors (si habilitados)
tail -f /path/to/php_error.log
```

**Habilitar logs PHP (solo desarrollo):**

```php
// config.php
if (DEBUG_MODE) {
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  error_log('/path/to/php_error.log');
}
```

### 17.3 Verificar Conectividad API

```bash
# Health check
curl https://tudominio.com/inbolsa-api/api/health

# Test login
curl -X POST https://tudominio.com/inbolsa-api/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@tudominio.com","password":"password"}' \
  -v

# Ver headers CORS
curl -H "Origin: https://tudominio.com" \
  -v https://tudominio.com/inbolsa-api/api/health
```

---

## 18. API Reference Completa

### 18.1 Health

```
GET /api/health
```

**Response:**
```json
{
  "ok": true,
  "message": "API operativa",
  "timestamp": 1700000000
}
```

### 18.2 Auth

#### Login

```
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@tudominio.com",
  "password": "password"
}
```

**Response Success:**
```json
{
  "ok": true,
  "admin": {
    "id": 1,
    "email": "admin@tudominio.com"
  }
}
```

**Response Error:**
```json
{
  "ok": false,
  "error": "invalid_credentials"
}
```

#### Logout

```
POST /api/auth/logout
Credentials: include
```

**Response:**
```json
{
  "ok": true
}
```

#### Me

```
GET /api/auth/me
Credentials: include
```

**Response:**
```json
{
  "ok": true,
  "admin": {
    "id": 1,
    "email": "admin@tudominio.com"
  }
}
```

### 18.3 QR

#### Create

```
POST /api/qr/create
Content-Type: application/json
Credentials: include

{
  "type": "productos",
  "recipient": "Cliente SA",
  "products": ["sacos-convencionales", "big-bag-tubular"],
  "allow": "include",
  "expires_at": "2025-12-31 23:59:59",
  "usage_limit": 10
}
```

**Response:**
```json
{
  "ok": true,
  "code": "ABC123XYZ",
  "url": "https://tudominio.com/qr?code=ABC123XYZ&m=120&p=sacos-convencionales,big-bag-tubular"
}
```

#### List

```
GET /api/qr/list
Credentials: include
```

**Response:**
```json
{
  "ok": true,
  "qrs": [
    {
      "id": 1,
      "code": "ABC123",
      "status": "active",
      "expires_at": "2025-12-31 23:59:59",
      "usage_limit": 10,
      "used_count": 3,
      "payload": {
        "section": "productos",
        "recipient": "Cliente SA",
        "products": ["sacos-convencionales"]
      },
      "created_at": "2025-11-01 10:00:00"
    }
  ]
}
```

#### Validate

```
GET /api/qr/validate?code=ABC123
```

**Response Success:**
```json
{
  "ok": true,
  "payload": {
    "section": "productos",
    "allow": "include",
    "products": ["sacos-convencionales"],
    "exp": 1735689599,
    "recipient": "Cliente SA"
  }
}
```

**Response Error:**
```json
{
  "ok": false,
  "error": "qr_revoked"
}
```

#### Revoke

```
POST /api/qr/revoke
Content-Type: application/json
Credentials: include

{
  "code": "ABC123"
}
```

**Response:**
```json
{
  "ok": true
}
```

### 18.4 Access

```
GET /api/access/payload
Credentials: include
```

**Response:**
```json
{
  "ok": true,
  "payload": {
    "section": "productos",
    "allow": "include",
    "products": ["sacos-convencionales"],
    "exp": 1735689599,
    "recipient": "Cliente SA"
  }
}
```

---

## 19. Mejores Prácticas

### 19.1 Desarrollo

1. **Usa Git Flow**
   - `main` → Producción
   - `develop` → Desarrollo
   - `feature/*` → Nuevas funcionalidades

2. **Commits descriptivos**
   ```bash
   git commit -m "feat: agregar filtro de productos en /soluciones"
   git commit -m "fix: corregir validación de QR expirado"
   git commit -m "docs: actualizar manual de desarrolladores"
   ```

3. **Testing local antes de deploy**
   ```bash
   npm run build
   npm run preview  # Prueba build local
   ```

4. **Nunca commitear:**
   - `.env`
   - `node_modules/`
   - `dist/`
   - `config.php` con passwords reales

### 19.2 Producción

1. **HTTPS siempre**
2. **Backups regulares** (diarios recomendado)
3. **Monitoreo de uptime** (UptimeRobot, Pingdom)
4. **Logs centralizados**
5. **Actualizar dependencias** (`npm audit`, `composer update`)

### 19.3 SEO

1. **Títulos únicos por página**
   ```astro
   <Base title="Historia - Inbolsa | 50 Años de Experiencia">
   ```

2. **Meta descriptions**
   ```astro
   <meta name="description" content="Conoce la historia de Inbolsa..." />
   ```

3. **Alt en imágenes**
   ```astro
   <img src="/img/producto.webp" alt="Sacos de polipropileno para agricultura" />
   ```

4. **Sitemap.xml** (generar con Astro)
   ```bash
   npm install @astrojs/sitemap
   ```

5. **robots.txt**
   ```
   User-agent: *
   Allow: /
   Disallow: /app/
   Disallow: /privado/

   Sitemap: https://tudominio.com/sitemap.xml
   ```

---

## 📚 Recursos Adicionales

### Documentación Oficial

- **Astro:** https://docs.astro.build
- **Tailwind CSS:** https://tailwindcss.com/docs
- **PHP:** https://www.php.net/manual/es/
- **MySQL:** https://dev.mysql.com/doc/

### Herramientas

- **Lighthouse:** https://developers.google.com/web/tools/lighthouse
- **Google Analytics:** https://analytics.google.com
- **WebP Converter:** https://squoosh.app
- **Tailwind Color Generator:** https://uicolors.app/create

---

**Fin del Manual de Desarrolladores - Producción**

**Versión:** 3.0
**Autor:** Equipo Inbolsa
**Fecha:** Noviembre 20, 2025
