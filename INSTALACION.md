# Golf Genius Elementor - Instalación y Uso

## 🎯 Resumen del Plugin

He convertido exitosamente el proyecto Golf Genius en un plugin completo de WordPress/Elementor que incluye:

### ✅ Características Implementadas

1. **Plugin de WordPress Completo**
   - Archivo principal con headers estándar de WordPress
   - Hooks de activación/desactivación
   - Gestión de dependencias (Elementor, PHP, WordPress)

2. **Panel de Administración Profesional**
   - Interfaz visual para configurar tablas
   - Generador automático de shortcodes
   - Vista previa en tiempo real
   - Selector de columnas intuitivo
   - Configuración de API integrada

3. **Widget de Elementor**
   - Arrastrar y soltar desde el panel de Elementor
   - Opciones completas de personalización
   - Vista previa en el editor
   - Estilos personalizables
   - Responsive design

4. **Sistema de Shortcodes**
   - `[golf_genius_table]` con múltiples parámetros
   - Uso en cualquier página/entrada de WordPress
   - Configuración flexible de columnas
   - Títulos y clases CSS personalizables

5. **Integración API Completa**
   - Conexión con la API de Golf Genius
   - Sistema de cache para optimización
   - Fallback con datos de ejemplo
   - Manejo robusto de errores

## 📁 Estructura del Plugin

```
golf-genius-elementor/
├── golf-genius-elementor.php          # Archivo principal del plugin
├── includes/
│   ├── class-golf-genius-admin.php    # Panel de administración
│   ├── class-golf-genius-api.php      # Integración con API
│   ├── class-golf-genius-shortcode.php # Sistema de shortcodes
│   └── elementor-widgets/
│       └── class-golf-genius-widget.php # Widget de Elementor
├── assets/
│   ├── css/
│   │   ├── golf-genius.css           # Estilos frontend
│   │   └── admin.css                 # Estilos del panel admin
│   └── js/
│       ├── golf-genius.js            # JavaScript frontend
│       └── admin.js                  # JavaScript admin
├── README-PLUGIN.md                   # Documentación completa
└── README.md                          # Documentación original
```

## 🚀 Instalación

### Paso 1: Subir el Plugin
1. Comprime todos los archivos del plugin en `golf-genius-elementor.zip`
2. Ve a WordPress Admin > Plugins > Añadir nuevo > Subir plugin
3. Selecciona el archivo ZIP y haz clic en "Instalar ahora"
4. Activa el plugin

### Paso 2: Configurar la API
1. Ve a **Golf Genius > Configuración** en el menú de WordPress
2. Ingresa tu API Key de Golf Genius
3. Configura los IDs de evento, ronda y torneo
4. Haz clic en "Probar Conexión API" para verificar

### Paso 3: Crear tu Primera Tabla
1. Ve a **Golf Genius > Panel de Administración**
2. Selecciona las columnas que deseas mostrar
3. Configura el título y opciones adicionales
4. Haz clic en "Generar Shortcode"
5. Copia el shortcode generado

## 💡 Formas de Uso

### Opción 1: Shortcode (Más Simple)
```
[golf_genius_table columns="photo,firstName,lastName,affiliation,position" title="Clasificación del Torneo"]
```

### Opción 2: Widget de Elementor (Más Flexible)
1. Abre Elementor en cualquier página
2. Busca "Golf Genius Table" en los widgets
3. Arrastra el widget a tu diseño
4. Configura las opciones en el panel lateral

## 🎨 Panel de Administración

![Panel de Administración](https://github.com/user-attachments/assets/f9cfe1b4-2c94-4a67-9c98-3e9bf8043ee4)

El panel de administración incluye:

- **Configuración de Columnas**: Selector visual con checkboxes
- **Vista Previa**: Muestra cómo se verá la tabla
- **Generador de Shortcode**: Crea automáticamente el código
- **Información de Uso**: Guías paso a paso
- **Configuración de API**: Prueba y configuración de conexión

## 🔧 Opciones Avanzadas

### Parámetros del Shortcode
```
[golf_genius_table 
    columns="photo,firstName,lastName,affiliation,position,score"
    title="Clasificación del Torneo"
    class="mi-tabla-personalizada"
    show_refresh="true"
    show_column_selector="false"
    max_height="500px"]
```

### Columnas Disponibles
- `photo` - Foto del jugador
- `firstName` - Nombre
- `lastName` - Apellido
- `affiliation` - Afiliación del club
- `position` - Posición en el torneo
- `score` - Puntuación
- `rounds` - Rondas jugadas
- `highlights` - Destacados
- `previousRanking` - Ranking anterior
- `email` - Email
- `handicap` - Handicap
- `field` - Campo
- `entry_number` - Número de entrada
- `phone` - Teléfono
- `city` - Ciudad
- `state` - Estado

## 🎯 Beneficios del Plugin

1. **Facilidad de Uso**: No requiere conocimientos técnicos
2. **Flexibilidad**: Uso tanto con shortcodes como con Elementor
3. **Personalización**: Control total sobre columnas y estilos
4. **Responsive**: Se adapta a todos los dispositivos
5. **Mantenimiento**: Sistema robusto con cache y fallbacks
6. **Escalabilidad**: Fácil agregar nuevas características

## 🔄 Migración desde el Proyecto Original

Los administradores pueden:
1. **Mantener la funcionalidad actual**: Todo funciona igual que antes
2. **Usar el nuevo panel**: Interfaz más amigable para generar shortcodes
3. **Aprovechar Elementor**: Mejor integración visual
4. **Personalizar diseños**: Más opciones de estilo y configuración

## 🏆 Resultado Final

El plugin convierte exitosamente el proyecto Golf Genius en una solución profesional de WordPress que:

- ✅ Mantiene toda la funcionalidad original
- ✅ Añade un panel de administración profesional  
- ✅ Permite uso desde cualquier contenedor (Elementor, páginas, entradas)
- ✅ Genera shortcodes automáticamente
- ✅ Proporciona opciones avanzadas de personalización
- ✅ Es completamente responsive y optimizado

El administrador ahora puede colocar fácilmente la tabla de Golf Genius en cualquier parte de su sitio web usando una interfaz visual intuitiva, sin necesidad de conocimientos técnicos.