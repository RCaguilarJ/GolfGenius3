# Golf Genius Elementor Plugin

Un plugin de WordPress/Elementor para mostrar tablas personalizables de datos de Golf Genius con panel de administración completo.

## Características

### 🎯 Funcionalidades Principales

- **Widget de Elementor**: Arrastra y suelta el widget "Golf Genius Table" en cualquier página
- **Shortcode**: Usa `[golf_genius_table]` en cualquier lugar de WordPress
- **Panel de Administración**: Interfaz intuitiva para configurar tablas y generar shortcodes
- **Columnas Personalizables**: Selecciona exactamente qué columnas mostrar
- **API Real**: Se conecta directamente con la API de Golf Genius
- **Diseño Responsive**: Se adapta perfectamente a dispositivos móviles

### 📊 Columnas Disponibles

- Foto del jugador (con avatares automáticos)
- Nombre y apellido
- Afiliación del club
- Posición en el torneo
- Puntuación
- Rondas jugadas
- Destacados/achievements
- Ranking del año anterior
- Email, teléfono, ciudad, estado
- Handicap, campo, número de entrada

### 🎨 Estilo y Personalización

- Paleta de colores Blue Zodiac integrada
- Estilos completamente personalizables desde Elementor
- CSS responsive con breakpoints optimizados
- Animaciones y transiciones suaves
- Compatible con cualquier tema de WordPress

## Instalación

### Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- Elementor 3.0 o superior
- cURL habilitado en PHP

### Pasos de Instalación

1. **Subir el Plugin**
   ```bash
   # Opción 1: Subir ZIP desde WordPress Admin
   # Opción 2: Subir archivos via FTP a wp-content/plugins/golf-genius-elementor/
   ```

2. **Activar el Plugin**
   - Ve a Plugins > Plugins Instalados
   - Busca "Golf Genius Elementor"
   - Haz clic en "Activar"

3. **Configurar API**
   - Ve a Golf Genius > Configuración
   - Ingresa tu API Key de Golf Genius
   - Configura los IDs de evento, ronda y torneo
   - Haz clic en "Probar Conexión API"

## Uso

### 🔧 Panel de Administración

1. **Acceder al Panel**
   - Ve a "Golf Genius" en el menú de WordPress
   - Selecciona las columnas que deseas mostrar
   - Configura el título y opciones adicionales
   - Haz clic en "Generar Shortcode"

2. **Generar Shortcode**
   ```
   [golf_genius_table columns="photo,firstName,lastName,affiliation,position" title="Clasificación del Torneo" show_refresh="true"]
   ```

### 🎨 Widget de Elementor

1. **Usar el Widget**
   - Abre Elementor en cualquier página
   - Busca "Golf Genius Table" en los widgets
   - Arrastra el widget a tu diseño
   - Configura las opciones en el panel lateral

2. **Opciones Disponibles**
   - Selección de columnas
   - Título de la tabla
   - Mostrar botón de actualización
   - Mostrar selector de columnas
   - Altura máxima de la tabla
   - Estilos personalizados

### 📝 Shortcode Manual

```php
// Shortcode básico
[golf_genius_table]

// Shortcode con columnas específicas
[golf_genius_table columns="photo,firstName,lastName,position,score"]

// Shortcode completo con todas las opciones
[golf_genius_table 
    columns="photo,firstName,lastName,affiliation,position,score" 
    title="Ranking del Torneo" 
    class="mi-tabla-personalizada"
    show_refresh="true"
    show_column_selector="false"
    max_height="500px"]
```

## Configuración

### 🔑 API de Golf Genius

1. **Obtener API Key**
   - Contacta con Golf Genius para obtener tu API key
   - Anota los IDs de evento, ronda y torneo

2. **Configurar en WordPress**
   - Golf Genius > Configuración
   - Ingresa la información de API
   - Prueba la conexión

### ⚙️ Opciones Avanzadas

```php
// Personalizar columnas por defecto
add_option('golf_genius_default_columns', [
    'photo', 'firstName', 'lastName', 'affiliation', 'position'
]);

// Personalizar tiempo de cache
add_filter('golf_genius_cache_time', function() {
    return 300; // 5 minutos
});
```

## Personalización

### 🎨 CSS Personalizado

```css
/* Personalizar colores de la tabla */
.golf-genius-container {
    --primary: #your-color;
    --primary-dark: #your-dark-color;
}

/* Personalizar tamaño de fotos */
.golf-genius-photo,
.golf-genius-avatar {
    width: 60px;
    height: 60px;
}

/* Personalizar puntuaciones */
.golf-genius-score.under-par {
    background-color: #green;
}
```

### 🔧 Hooks de WordPress

```php
// Filtrar datos de jugadores
add_filter('golf_genius_players_data', function($players) {
    // Modificar datos de jugadores
    return $players;
});

// Personalizar columnas disponibles
add_filter('golf_genius_available_columns', function($columns) {
    $columns['custom_field'] = 'Mi Campo Personalizado';
    return $columns;
});
```

## Desarrollo

### 📁 Estructura del Plugin

```
golf-genius-elementor/
├── golf-genius-elementor.php          # Archivo principal
├── includes/
│   ├── class-golf-genius-admin.php    # Panel de administración
│   ├── class-golf-genius-api.php      # Comunicación con API
│   ├── class-golf-genius-shortcode.php # Funcionalidad shortcode
│   └── elementor-widgets/
│       └── class-golf-genius-widget.php # Widget de Elementor
├── assets/
│   ├── css/
│   │   ├── golf-genius.css           # Estilos frontend
│   │   └── admin.css                 # Estilos admin
│   └── js/
│       ├── golf-genius.js            # JavaScript frontend
│       └── admin.js                  # JavaScript admin
└── README.md
```

### 🔄 API Integration

El plugin se conecta con la API de Golf Genius usando:
- Endpoint de master roster para datos de jugadores
- Endpoint de torneos para datos específicos del torneo
- Sistema de fallback con datos de ejemplo
- Cache inteligente para optimizar performance

## Solución de Problemas

### ❌ Problemas Comunes

1. **No se cargan los datos**
   - Verifica la API key en Configuración
   - Prueba la conexión API
   - Revisa los logs de error de WordPress

2. **Widget no aparece en Elementor**
   - Asegúrate de que Elementor esté instalado y activado
   - Verifica la versión mínima de Elementor (3.0+)

3. **Estilos no se aplican**
   - Limpia la cache del sitio
   - Verifica que los archivos CSS se estén cargando
   - Revisa conflictos con el tema actual

### 📝 Logs y Debug

```php
// Habilitar logs de debug
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Ver logs en wp-content/debug.log
```

## Soporte

- **Documentación**: [Enlace a documentación completa]
- **Issues**: Reporta problemas en GitHub
- **Email**: [email de soporte]

## Licencia

Este plugin está licenciado bajo GPL v2 o posterior.

## Créditos

Desarrollado por el equipo de Golf Genius, basado en el proyecto original Golf Genius Dashboard.