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

## Instalación

1. Descarga o clona este repositorio en la carpeta `/wp-content/plugins/buscador-parroquias/`
2. Activa el plugin desde *Plugins → Plugins instalados*
3. Ve a **Parroquias → Configuración** y rellena los datos de tu organización
4. Importa las parroquias desde **Parroquias → Importar Excel**
5. Inserta el shortcode `[buscador_parroquias]` en la página donde quieras mostrar el buscador

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
