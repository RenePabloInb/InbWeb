# Guía de Diseño y Branding - Inbolsa

**Versión:** 1.0
**Última actualización:** 20 de Noviembre, 2025
**Proyecto:** Sistema Web Inbolsa

---

## 📋 Tabla de Contenidos

1. [Tipografías](#tipografías)
2. [Paleta de Colores](#paleta-de-colores)
3. [Cómo Modificar Estilos](#cómo-modificar-estilos)
4. [Sistema de Espaciado](#sistema-de-espaciado)
5. [Componentes de UI](#componentes-de-ui)
6. [Animaciones y Transiciones](#animaciones-y-transiciones)

---

## 🔤 Tipografías

### Fuente Principal: **System Font Stack** (Nativa del Sistema)

El proyecto **NO utiliza fuentes personalizadas** de Google Fonts ni otras fuentes externas. En su lugar, utiliza la **pila de fuentes del sistema** (System Font Stack) que viene configurada por defecto en **Tailwind CSS**.

#### ¿Qué significa esto?

La aplicación utiliza las fuentes nativas del sistema operativo del usuario, lo que proporciona:

- ✅ **Mayor velocidad de carga** (no hay descargas de fuentes)
- ✅ **Mejor rendimiento** (fuentes ya instaladas en el dispositivo)
- ✅ **Consistencia** con el sistema operativo del usuario
- ✅ **Accesibilidad mejorada**

#### Pila de Fuentes por Sistema Operativo

```css
font-family:
  ui-sans-serif,           /* Fuente UI del sistema */
  system-ui,               /* Fallback genérico */
  -apple-system,           /* macOS/iOS */
  BlinkMacSystemFont,      /* macOS Chrome */
  "Segoe UI",              /* Windows */
  Roboto,                  /* Android */
  "Helvetica Neue",        /* macOS legacy */
  Arial,                   /* Fallback universal */
  "Noto Sans",             /* Linux */
  sans-serif,              /* Fallback genérico */
  "Apple Color Emoji",     /* Emojis iOS */
  "Segoe UI Emoji",        /* Emojis Windows */
  "Segoe UI Symbol",       /* Símbolos Windows */
  "Noto Color Emoji";      /* Emojis Android/Linux */
```

#### Fuentes Resultantes por Plataforma

| Sistema Operativo | Fuente Renderizada |
|-------------------|-------------------|
| **macOS** | San Francisco (SF Pro) |
| **iOS** | San Francisco (SF) |
| **Windows 10/11** | Segoe UI |
| **Android** | Roboto |
| **Linux** | Noto Sans / Liberation Sans |

---

### Clases de Peso de Fuente (Font Weight)

Tailwind CSS proporciona las siguientes clases de peso que se utilizan en el proyecto:

| Clase Tailwind | Peso CSS | Uso en el Proyecto |
|----------------|----------|-------------------|
| `font-light` | 300 | Textos descriptivos, subtítulos suaves |
| `font-normal` | 400 | Texto base, párrafos |
| `font-medium` | 500 | Énfasis ligero, labels, navegación |
| `font-semibold` | 600 | Subtítulos, títulos secundarios, botones |
| `font-bold` | 700 | Títulos principales, headings (h1-h4) |
| `font-black` | 900 | Títulos hero, impacto visual máximo |

#### Ejemplos de Uso en el Proyecto

```html
<!-- Hero principal -->
<h1 class="text-5xl font-black">
  Soluciones de embalaje industrial
</h1>

<!-- Subtítulos -->
<h2 class="text-3xl font-bold">
  Nuestros Valores
</h2>

<!-- Botones -->
<button class="font-semibold">
  Contáctanos
</button>

<!-- Párrafos descriptivos -->
<p class="text-lg font-light">
  Más de 50 años de experiencia en la industria
</p>

<!-- Labels de formulario -->
<label class="text-sm font-medium">
  Correo electrónico
</label>
```

---

### Optimizaciones Tipográficas Aplicadas

El proyecto incluye optimizaciones de renderizado de fuentes en [src/layouts/Base.astro](src/layouts/Base.astro#L202-L206):

```css
body {
  -webkit-font-smoothing: antialiased;      /* Suavizado macOS/iOS */
  -moz-osx-font-smoothing: grayscale;       /* Suavizado Firefox macOS */
  text-rendering: optimizeLegibility;       /* Mejora kerning y ligaduras */
}
```

**Clases Tailwind aplicadas al body:**

```html
<body class="antialiased">
  <!-- antialiased = -webkit-font-smoothing: antialiased -->
</body>
```

---

## 🎨 Paleta de Colores

### Color Principal: **Brand Blue** (#3E7DD2)

El proyecto utiliza un **color de marca azul corporativo** configurado en Tailwind como `brand`.

#### Configuración en [tailwind.config.js](tailwind.config.js#L6-L11)

```javascript
colors: {
  brand: {
    50:  '#eef6ff',  // Azul muy claro (backgrounds sutiles)
    100: '#d8e9ff',  // Azul claro (hover states, badges)
    200: '#b1d2ff',  // Azul pastel
    300: '#85b8fb',  // Azul medio claro
    400: '#5a96e6',  // Azul medio
    500: '#3e7dd2',  // ⭐ COLOR PRINCIPAL (brand blue)
    600: '#2f65b0',  // Azul oscuro (hover botones)
    700: '#285392',  // Azul muy oscuro
    800: '#234876',  // Azul profundo
    900: '#213e63'   // Azul casi negro
  }
}
```

---

### Usos del Color Brand

#### 1. **Botones Primarios**

```html
<!-- Botón principal -->
<button class="bg-brand-600 hover:bg-brand-700 text-white">
  Contáctanos
</button>

<!-- Gradiente de marca -->
<button class="bg-gradient-to-r from-brand-500 to-brand-600">
  Ver Productos
</button>
```

#### 2. **Encabezados y Navegación**

```html
<!-- Logo y navegación activa -->
<a class="text-brand-700 font-semibold">
  Inicio
</a>

<!-- Línea de progreso de scroll -->
<div class="bg-gradient-to-r from-brand-500 to-brand-600"></div>
```

#### 3. **Badges y Etiquetas**

```html
<!-- Badge informativo -->
<span class="bg-brand-100 text-brand-700 rounded-full px-4 py-2">
  Nuevo
</span>
```

#### 4. **Fondos y Secciones**

```html
<!-- Hero con gradiente -->
<section class="bg-gradient-to-br from-brand-600 to-brand-800">
  <!-- Contenido -->
</section>
```

#### 5. **Estados de Foco (Accesibilidad)**

Configurado en [src/layouts/Base.astro](src/layouts/Base.astro#L57-L64):

```css
a:focus-visible,
button:focus-visible,
input:focus-visible {
  outline: 3px solid #3E7DD2;  /* brand-500 */
  outline-offset: 2px;
  border-radius: 4px;
}
```

---

### Colores Secundarios (Tailwind por Defecto)

El proyecto también utiliza la paleta estándar de Tailwind CSS:

#### **Grises (Slate)** - Para textos y fondos neutros

| Clase | Hex | Uso |
|-------|-----|-----|
| `slate-50` | #f8fafc | Fondo página |
| `slate-100` | #f1f5f9 | Fondos cards |
| `slate-200` | #e2e8f0 | Bordes suaves |
| `slate-500` | #64748b | Textos secundarios |
| `slate-700` | #334155 | Textos primarios |
| `slate-800` | #1e293b | Textos muy oscuros |
| `slate-900` | #0f172a | Títulos negro |

**Uso en body:**
```html
<body class="bg-slate-50 text-slate-800">
```

#### **Verde (Emerald)** - Estados positivos

```html
<!-- Estado activo -->
<span class="bg-emerald-100 text-emerald-700">
  Activo
</span>
```

#### **Amarillo/Naranja (Amber/Orange)** - Alertas y advertencias

```html
<!-- Estado revocado -->
<span class="bg-amber-100 text-amber-700">
  Revocado
</span>
```

#### **Azul Cielo (Sky)** - Información secundaria

```html
<!-- Estadísticas -->
<div class="text-sky-700 font-bold">
  Total Usos: 150
</div>
```

#### **Índigo** - Detalles visuales

```html
<div class="text-indigo-600 font-bold">
  300+ Empleados
</div>
```

---

### Meta Theme Color

Configurado para barras de navegador móvil en [src/layouts/Base.astro](src/layouts/Base.astro#L15):

```html
<meta name="theme-color" content="#3E7DD2" />
```

---

## 🛠️ Cómo Modificar Estilos

### 1. **Cambiar el Color de Marca**

Para cambiar el color principal de la marca:

**Archivo:** [tailwind.config.js](tailwind.config.js)

```javascript
// Ubicación: tailwind.config.js líneas 6-11

export default {
  theme: {
    extend: {
      colors: {
        brand: {
          50:  '#nueva-tonalidad-50',
          100: '#nueva-tonalidad-100',
          // ... continuar con todas las tonalidades
          500: '#TU_COLOR_PRINCIPAL',  // ⭐ Cambiar este
          600: '#TU_COLOR_HOVER',      // ⭐ Y este para hover
          // ... resto de tonalidades
        }
      }
    }
  }
}
```

**Herramienta Recomendada:** Usa [UIColors](https://uicolors.app/create) o [Tailwind Color Generator](https://tailwind.simeongriggs.dev/blue/3E7DD2) para generar automáticamente todas las tonalidades desde un color base.

**Archivos adicionales a actualizar:**

1. **Meta theme color** en [src/layouts/Base.astro](src/layouts/Base.astro#L15):
   ```html
   <meta name="theme-color" content="#TU_COLOR" />
   ```

2. **Color de outline de foco** en [src/layouts/Base.astro](src/layouts/Base.astro#L61):
   ```css
   outline: 3px solid #TU_COLOR;
   ```

3. **Gradiente de progreso** en [src/layouts/Base.astro](src/layouts/Base.astro#L93):
   ```css
   background: linear-gradient(90deg, #TU_COLOR, #60a5fa);
   ```

---

### 2. **Cambiar la Tipografía a una Fuente Personalizada**

Si deseas usar una fuente personalizada (por ejemplo, Google Fonts):

#### Paso 1: Agregar el `<link>` en [src/layouts/Base.astro](src/layouts/Base.astro)

```html
<head>
  <!-- Agregar después de la línea 17 -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet" />
</head>
```

#### Paso 2: Configurar Tailwind en [tailwind.config.js](tailwind.config.js)

```javascript
export default {
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        // Para fuente secundaria:
        display: ['Playfair Display', 'serif'],
      }
    }
  }
}
```

#### Paso 3: Aplicar en HTML

```html
<!-- Usar la fuente principal (sans) -->
<body class="font-sans">

<!-- Usar fuente display -->
<h1 class="font-display text-5xl">
  Título con Playfair Display
</h1>
```

---

### 3. **Modificar Tamaños de Texto**

Tailwind proporciona una escala de tamaños predefinida. Los más usados en el proyecto:

| Clase | Tamaño | Uso |
|-------|--------|-----|
| `text-xs` | 0.75rem (12px) | Metadata, labels pequeños |
| `text-sm` | 0.875rem (14px) | Labels, texto secundario |
| `text-base` | 1rem (16px) | Párrafos, texto base |
| `text-lg` | 1.125rem (18px) | Párrafos destacados |
| `text-xl` | 1.25rem (20px) | Subtítulos pequeños |
| `text-2xl` | 1.5rem (24px) | Subtítulos medianos |
| `text-3xl` | 1.875rem (30px) | Títulos h2 |
| `text-4xl` | 2.25rem (36px) | Títulos h1 |
| `text-5xl` | 3rem (48px) | Hero títulos desktop |
| `text-6xl` | 3.75rem (60px) | Hero grandes |
| `text-7xl` | 4.5rem (72px) | Hero impacto máximo |

**Responsive:**

```html
<!-- Móvil 3xl, Tablet 5xl, Desktop 7xl -->
<h1 class="text-3xl md:text-5xl lg:text-7xl">
  Título Responsive
</h1>
```

---

### 4. **Modificar Espaciado Global**

Tailwind usa una escala de espaciado basada en `rem` (1 unidad = 0.25rem = 4px):

| Clase | Espacio | Píxeles |
|-------|---------|---------|
| `p-2` | 0.5rem | 8px |
| `p-4` | 1rem | 16px |
| `p-6` | 1.5rem | 24px |
| `p-8` | 2rem | 32px |
| `p-12` | 3rem | 48px |
| `p-16` | 4rem | 64px |

**Para modificar la escala base** (avanzado):

[tailwind.config.js](tailwind.config.js)
```javascript
theme: {
  extend: {
    spacing: {
      '128': '32rem',  // Nueva escala grande
      '144': '36rem',
    }
  }
}
```

---

### 5. **Modificar Sombras (Shadows)**

El proyecto usa las sombras estándar de Tailwind:

```html
<!-- Sombra pequeña -->
<div class="shadow-sm">

<!-- Sombra mediana -->
<div class="shadow-md">

<!-- Sombra grande -->
<div class="shadow-lg">

<!-- Sombra extra grande -->
<div class="shadow-xl">

<!-- Sombra con color personalizado -->
<div class="shadow-lg shadow-brand-500/30">
  <!-- /30 = 30% de opacidad -->
</div>
```

**Personalizar sombras:**

[tailwind.config.js](tailwind.config.js)
```javascript
theme: {
  extend: {
    boxShadow: {
      'inbolsa': '0 10px 40px -10px rgba(62, 125, 210, 0.3)',
    }
  }
}
```

Uso:
```html
<div class="shadow-inbolsa">
```

---

### 6. **Modificar Bordes y Radios**

#### Radios de Borde (Border Radius)

| Clase | Radio | Uso |
|-------|-------|-----|
| `rounded` | 0.25rem (4px) | Bordes sutiles |
| `rounded-md` | 0.375rem (6px) | Inputs, cards pequeños |
| `rounded-lg` | 0.5rem (8px) | Cards medianos |
| `rounded-xl` | 0.75rem (12px) | ⭐ Más usado en el proyecto |
| `rounded-2xl` | 1rem (16px) | Cards grandes |
| `rounded-3xl` | 1.5rem (24px) | Elementos destacados |
| `rounded-full` | 9999px | Círculos, pills, badges |

**Ejemplo del proyecto:**
```html
<!-- Botón con borde redondeado -->
<button class="rounded-xl bg-brand-600">
  Enviar
</button>

<!-- Badge circular -->
<span class="rounded-full bg-brand-100 px-4 py-2">
  Nuevo
</span>
```

---

### 7. **Modificar Gradientes**

El proyecto usa varios gradientes con el color de marca:

```html
<!-- Gradiente horizontal -->
<div class="bg-gradient-to-r from-brand-500 to-brand-600">

<!-- Gradiente diagonal -->
<div class="bg-gradient-to-br from-brand-600 to-brand-800">

<!-- Gradiente con múltiples colores -->
<div class="bg-gradient-to-r from-brand-500 via-blue-600 to-indigo-600">
```

**Direcciones disponibles:**
- `to-r` → Derecha
- `to-l` → Izquierda
- `to-t` → Arriba
- `to-b` → Abajo
- `to-br` → Diagonal abajo-derecha
- `to-tr` → Diagonal arriba-derecha

---

## 📐 Sistema de Espaciado

### Contenedores Máximos

El proyecto usa anchos máximos consistentes:

```html
<!-- Ancho máximo estándar (1280px) -->
<div class="max-w-7xl mx-auto px-6">

<!-- Ancho máximo para texto (672px) -->
<div class="max-w-3xl mx-auto">
```

### Breakpoints Responsive

| Breakpoint | Min-width | Clase |
|------------|-----------|-------|
| **sm** | 640px | `sm:` |
| **md** | 768px | `md:` |
| **lg** | 1024px | `lg:` |
| **xl** | 1280px | `xl:` |
| **2xl** | 1536px | `2xl:` |

**Ejemplo:**
```html
<h1 class="text-3xl md:text-5xl lg:text-7xl">
  <!-- Móvil: 3xl, Tablet: 5xl, Desktop: 7xl -->
</h1>
```

---

## 🎭 Componentes de UI

### Botones

#### Primario
```html
<button class="rounded-xl bg-brand-600 px-6 py-3 font-semibold text-white hover:bg-brand-700 transition">
  Botón Primario
</button>
```

#### Secundario (Outline)
```html
<button class="rounded-xl border-2 border-brand-600 px-6 py-3 font-semibold text-brand-700 hover:bg-brand-50 transition">
  Botón Secundario
</button>
```

#### Ghost/Texto
```html
<button class="font-semibold text-brand-600 hover:text-brand-700 transition">
  Botón Texto
</button>
```

---

### Cards

```html
<div class="rounded-xl bg-white p-6 shadow-lg hover:shadow-xl transition-shadow">
  <h3 class="text-xl font-bold text-slate-900">Título Card</h3>
  <p class="mt-2 text-slate-600">Descripción de la card</p>
</div>
```

---

### Badges

```html
<!-- Badge informativo -->
<span class="inline-flex items-center gap-2 rounded-full bg-brand-100 text-brand-700 px-4 py-2 text-sm font-semibold">
  Activo
</span>

<!-- Badge éxito -->
<span class="rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-medium">
  Completado
</span>
```

---

### Inputs

```html
<input
  type="text"
  class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition"
  placeholder="Tu nombre"
/>
```

---

## ✨ Animaciones y Transiciones

### Transiciones Estándar

```html
<!-- Transición genérica -->
<button class="transition hover:bg-brand-700">
  <!-- transition aplica: 150ms ease-in-out -->
</button>

<!-- Transición de múltiples propiedades -->
<div class="transition-all hover:scale-105">

<!-- Transición de colores -->
<div class="transition-colors">
```

### Duraciones

| Clase | Duración |
|-------|----------|
| `duration-75` | 75ms |
| `duration-150` | 150ms (default) |
| `duration-300` | 300ms |
| `duration-500` | 500ms |

```html
<div class="transition duration-300 hover:opacity-50">
```

---

### Animaciones Personalizadas

El proyecto incluye animaciones custom en [src/layouts/Base.astro](src/layouts/Base.astro):

#### 1. **Fade In on Scroll**

```html
<div class="fade-in-section">
  <!-- Se hace visible al hacer scroll -->
</div>

<!-- Con retraso escalonado -->
<div class="fade-in-section stagger-1"></div>
<div class="fade-in-section stagger-2"></div>
<div class="fade-in-section stagger-3"></div>
```

#### 2. **Skeleton Loading**

```html
<div class="skeleton h-40 rounded-xl">
  <!-- Animación de carga -->
</div>
```

#### 3. **Gradiente Animado**

```html
<div class="bg-gradient-to-r from-brand-500 to-brand-700 gradient-animate">
  <!-- Gradiente que se mueve suavemente -->
</div>
```

---

## 📝 Resumen Rápido

### ¿Dónde modificar cada aspecto?

| Aspecto | Archivo | Líneas |
|---------|---------|--------|
| **Color de marca** | [tailwind.config.js](tailwind.config.js) | 6-11 |
| **Fuentes personalizadas** | [src/layouts/Base.astro](src/layouts/Base.astro) | Agregar en `<head>` + tailwind.config.js |
| **Espaciado global** | [tailwind.config.js](tailwind.config.js) | `theme.extend.spacing` |
| **Sombras custom** | [tailwind.config.js](tailwind.config.js) | `theme.extend.boxShadow` |
| **Animaciones CSS** | [src/layouts/Base.astro](src/layouts/Base.astro) | 66-189 (estilos globales) |
| **Theme color móvil** | [src/layouts/Base.astro](src/layouts/Base.astro) | 15 |

---

## 🔄 Proceso de Aplicar Cambios

1. **Modificar archivos de configuración** (tailwind.config.js o Base.astro)
2. **Guardar cambios**
3. **Reiniciar el servidor de desarrollo:**
   ```bash
   npm run dev
   ```
4. **Verificar cambios en el navegador** (Ctrl+Shift+R para refrescar sin caché)
5. **Compilar para producción:**
   ```bash
   npm run build
   ```

---

## 📚 Recursos Adicionales

- **Tailwind CSS Documentación:** https://tailwindcss.com/docs
- **Paleta de Colores Tailwind:** https://tailwindcss.com/docs/customizing-colors
- **Generador de Paletas:** https://uicolors.app/create
- **Google Fonts:** https://fonts.google.com/
- **Herramienta de Contraste (Accesibilidad):** https://webaim.org/resources/contrastchecker/

---

**Fin de la Guía de Diseño y Branding**
