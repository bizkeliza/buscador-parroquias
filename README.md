# Buscador de Parroquias — WordPress Plugin

Plugin de WordPress para publicar una guía de parroquias y horarios de misa configurable para cualquier diócesis o organización religiosa.

## Características

- **Buscador por nombre** de parroquia o localidad
- **Búsqueda por ubicación** ("Cerca de mí") con radio configurable en km
- **Tarjetas de resultado** con horarios de invierno/verano, contacto y enlace a Google Maps
- **Gestión desde WordPress**: cada parroquia es un Custom Post Type gestionable desde el panel de administración
- **Importación desde Excel** (.xlsx) directamente desde el admin, con detección automática de variantes de nombre de columna
- **Completamente configurable**: logotipo, textos, colores corporativos y pie de página desde *Ajustes*
- **Sin dependencias externas** en el frontend (no carga librerías de terceros para los visitantes)
- **API REST** incluida: `/wp-json/bp-guia/v1/parroquias`

## Requisitos

- WordPress 5.8 o superior
- PHP 7.4 o superior
- Plugin [Advanced Custom Fields (ACF)](https://www.advancedcustomfields.com/) instalado y activo (versión gratuita es suficiente)
- Permalinks habilitados (Ajustes → Enlaces permanentes → cualquier opción distinta de "Sin formato")

## Instalación y configuración paso a paso

### Paso 1 — Instalar los plugins necesarios

1. Ve a **Plugins → Añadir nuevo plugin**
2. Busca **Advanced Custom Fields** (autor: WP Engine) → **Instalar** → **Activar**
3. Ve de nuevo a **Plugins → Añadir nuevo plugin → Subir plugin**
4. Descarga el ZIP de este repositorio (botón **Code → Download ZIP**) y súbelo → **Instalar** → **Activar**

### Paso 2 — Regenerar los enlaces permanentes

Ve a **Ajustes → Enlaces permanentes** y pulsa **Guardar cambios** sin modificar nada.

> Esto es necesario para que la API REST del plugin funcione correctamente.

### Paso 3 — Configurar la identidad y textos

Ve a **Parroquias → Configuración** y rellena los campos:

| Campo | Descripción | Ejemplo |
|---|---|---|
| Nombre de la organización | Aparece en la cabecera del buscador | `Diócesis de Bilbao` |
| URL del logotipo | URL completa de la imagen del logo (subida a la Biblioteca de medios) | `https://tudominio.org/wp-content/uploads/logo.png` |
| Enlace del logotipo | URL a la que apunta el logo al hacer clic (opcional) | `https://tudominio.org` |
| Título de la cabecera | Título principal del widget | `Guía de parroquias y horarios de misa` |
| Subtítulo de la cabecera | Texto descriptivo bajo el título | `Localiza iglesias, horarios y datos de contacto.` |
| Organización (pie) | Nombre en el pie de página | `Diócesis de Bilbao` |
| Descripción (pie) | Línea descriptiva en el pie | `Obispado de Bilbao` |
| Dirección (pie) | Dirección postal de la organización | `C/ Henao, 5` |
| Ciudad / CP (pie) | Ciudad y código postal | `48009 Bilbao` |
| Email de contacto (pie) | Email visible en el pie del widget | `info@diocesisbilbao.org` |
| Radio "Cerca de mí" (km) | Distancia máxima para la búsqueda por ubicación GPS | `5` |

> **Cómo obtener la URL del logotipo:** Ve a **Biblioteca de medios**, sube el logo, haz clic sobre él y copia la URL que aparece en el panel derecho.

Pulsa **Guardar cambios**.

### Paso 4 — Preparar el Excel de parroquias

El archivo `.xlsx` debe tener una fila de cabecera. El plugin reconoce los nombres de columna en español y euskera:

| Campo | Nombres de columna aceptados |
|---|---|
| Parroquia | `Parroquia /Parrokia`, `PARROQUIA`, `Parroquia`, `Parrokia` |
| Localidad | `Localidad/Herria`, `LOCALIDAD`, `Localidad`, `Herria` |
| Dirección | `Helbidea/dirección`, `Dirección`, `Direccion`, `DIRECCION` |
| C.P. | `C.P.`, `CP`, `CÓDIGO POSTAL`, `Codigo Postal` |
| Teléfono | `TELÉFONO`, `TELEFONO`, `Teléfono`, `Telefono` |
| Email | `EMAIL`, `E-MAIL`, `Email` |
| Párroco | `PÁRROCO`, `PARROCO`, `Párroco`, `Parroco` |
| UP / Sector | `UP / SECTOR`, `UP/SECTOR`, `UP`, `SECTOR` |
| Horario invierno | `Horario misas invierno`, `HORARIO MISAS INVIERNO` |
| Horario verano | `Horario misas verano`, `HORARIO MISAS VERANO` |
| Latitud | `LATITUD`, `Latitud` |
| Longitud | `LONGITUD`, `Longitud` |

> Las columnas **Latitud** y **Longitud** son opcionales, pero necesarias para que funcione el botón "Cerca de mí".

### Paso 5 — Importar las parroquias

1. Ve a **Parroquias → Importar Excel**
2. Selecciona el archivo `.xlsx`
3. Marca **"Eliminar todas las parroquias existentes antes de importar"** si es una reimportación completa
4. Pulsa **Iniciar importación** y espera a que termine el log

### Paso 6 — Crear la página del buscador

1. Ve a **Páginas → Añadir nueva**
2. Dale un título (ej: `Horarios de misa`)
3. Inserta el shortcode en el contenido:

```
[buscador_parroquias]
```

4. Publica la página

El buscador ya está operativo. Los visitantes pueden buscar por nombre de parroquia o localidad, o pulsar **Cerca de mí** para ver iglesias en el radio configurado.

## Formato del Excel de importación

El archivo `.xlsx` debe tener una hoja con una fila de cabecera. El plugin detecta automáticamente las siguientes variantes de nombre de columna:

| Campo en WordPress | Columnas aceptadas en el Excel |
|---|---|
| `parroquia` | `Parroquia /Parrokia`, `PARROQUIA`, `Parroquia`, `Parrokia` |
| `localidad` | `Localidad/Herria`, `LOCALIDAD`, `Localidad`, `Herria` |
| `direccion` | `Helbidea/dirección`, `Dirección`, `Direccion`, `DIRECCION` |
| `cp` | `C.P.`, `CP`, `CÓDIGO POSTAL`, `Codigo Postal` |
| `telefono` | `TELÉFONO`, `TELEFONO`, `Teléfono`, `Telefono` |
| `email` | `EMAIL`, `E-MAIL`, `Email` |
| `parroco` | `PÁRROCO`, `PARROCO`, `Párroco`, `Parroco` |
| `up_sector` | `UP / SECTOR`, `UP/SECTOR`, `UP`, `SECTOR` |
| `horario_misas_invierno` | `Horario misas invierno`, `HORARIO MISAS INVIERNO` |
| `horario_misas_verano` | `Horario misas verano`, `HORARIO MISAS VERANO` |
| `latitud` | `LATITUD`, `Latitud` |
| `longitud` | `LONGITUD`, `Longitud` |

Las coordenadas (`latitud` / `longitud`) son opcionales pero necesarias para la función "Cerca de mí".

## Gestión manual de parroquias

Una vez importadas, puedes editar cada parroquia individualmente desde **Parroquias** en el menú lateral del admin de WordPress. Los campos se muestran gracias a ACF.

## API REST

El plugin expone un endpoint público con todos los datos:

```
GET /wp-json/bp-guia/v1/parroquias
```

Respuesta (array JSON):
```json
[
  {
    "parroquia": "...",
    "localidad": "...",
    "direccion": "...",
    "cp": "...",
    "telefono": "...",
    "email": "...",
    "parroco": "...",
    "upSector": "...",
    "horarioInvierno": "...",
    "horarioVerano": "...",
    "latitud": 43.26,
    "longitud": -2.93
  }
]
```

## Shortcode

```
[buscador_parroquias]
```

Insértalo en cualquier página o entrada de WordPress.

## Estructura de archivos

```
buscador-parroquias/
├── buscador-parroquias.php      ← Archivo principal del plugin
├── includes/
│   ├── cpt.php                  ← Registro del Custom Post Type
│   ├── acf-fields.php           ← Campos ACF (registro programático)
│   ├── rest-api.php             ← Endpoint REST público
│   ├── admin-settings.php       ← Página de configuración
│   ├── admin-import.php         ← Página de importación + handlers AJAX
│   └── shortcode.php            ← Shortcode [buscador_parroquias]
├── assets/
│   ├── css/buscador-parroquias.css   ← Estilos del widget
│   └── js/
│       ├── buscador-parroquias.js    ← Lógica del buscador (frontend)
│       └── bp-import.js             ← Lógica de importación (solo admin)
└── README.md
```

## Licencia

GPL-2.0+. Ver [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

---

Desarrollado por [Bizkeliza](https://bizkeliza.org) — Diócesis de Bilbao
