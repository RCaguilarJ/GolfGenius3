# Golf Genius Dashboard

Dashboard interactivo para visualizar datos de jugadores de Golf Genius con columnas personalizables.

## 📁 Estructura del Proyecto

```
golfGeniuso3/
├── index.html              # Página principal
├── README.md               # Documentación del proyecto
├── css/
│   └── style.css          # Estilos CSS con paleta Blue Zodiac
├── js/
│   └── script.js          # Lógica JavaScript frontend
└── php/
    └── api_proxy.php      # Proxy para API de Golf Genius
```

## 🚀 Características

### Funcionalidades Principales (Las 4 Funcionalidades)
1. **Menú Desplegable**: Selección de columnas personalizables
2. **Tabla Dinámica**: Visualización de datos con columnas seleccionables
3. **Integración de Fotos**: Sistema de fotos con placeholders automáticos
4. **API Real**: Consumo de datos reales de Golf Genius

### Columnas Disponibles
- **Foto**: Imágenes de jugadores con placeholders dinámicos
- **Nombre**: Nombre del jugador
- **Apellido**: Apellido del jugador  
- **Afiliación al Club**: Club o afiliación del jugador
- **Resúmenes**: Highlights y logros del jugador
- **Clasificación Año Anterior**: Ranking del torneo anterior
- **Puntuación Ronda**: Puntuación por ronda específica
- **Score vs Par**: Puntuación contra par

## 🌐 API de Golf Genius

El proyecto consume la API real de Golf Genius:
- **Base URL**: `https://www.golfgenius.com/api_v2/MGMlbTG_APORWozDtgXHdQ/`
- **Endpoint Principal**: `events/10733818833262361649/rounds/10733997704590933397/tournaments/11025765214984354975`

### Endpoints Disponibles
- `players` - Lista de jugadores (combina datos de torneo + master roster)
- `master_roster` - Roster completo de miembros
- `tournament` - Datos específicos del torneo
- `tournaments` - Lista de torneos disponibles
- `scores` - Puntuaciones por torneo

## 🎨 Diseño

### Paleta de Colores: Blue Zodiac
- **Primary**: #2873e8
- **Background**: #eff7ff
- **Accent**: #3e91f3
- **Dark**: #204cad

### Características de Diseño
- Diseño responsive (móvil y desktop)
- Animaciones suaves
- Fotos con placeholders automáticos
- Tabla con hover effects
- Sistema de colores para puntuaciones

## 🛠️ Instalación y Uso

### Requisitos
- Servidor web con PHP (Apache/Nginx)
- PHP 7.4+ con cURL habilitado
- Acceso a internet para la API de Golf Genius

### Configuración
1. Colocar archivos en servidor web
2. Asegurar que PHP tenga permisos de cURL
3. Verificar que la API key esté configurada en `php/api_proxy.php`

### Desarrollo Local
Para WAMP/XAMPP:
```
http://localhost/golfGeniuso3/
```

## 🔧 Configuración Técnica

### API Proxy (php/api_proxy.php)
- Manejo de CORS
- Sistema de fallback con datos de muestra
- Combinación inteligente de datos de torneo + master roster
- Generación automática de fotos placeholder
- Logging de errores mejorado

### Frontend (js/script.js)
- Configuración modular de columnas
- Estado de aplicación centralizado
- Renderizado dinámico de tabla
- Manejo de errores y loading states

### Estilos (css/style.css)
- Variables CSS para mantenimiento fácil
- Diseño responsive con media queries
- Animaciones CSS personalizadas
- Sistema de colores consistente

## 📱 Compatibilidad

- ✅ Chrome/Chromium
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ✅ Dispositivos móviles
- ✅ Tablets

## 🤝 Buenas Prácticas Implementadas

### Estructura de Carpetas
- `/css/` - Estilos separados por funcionalidad
- `/js/` - Scripts organizados modularmente  
- `/php/` - Backend y APIs en carpeta dedicada

### Código
- Separación de responsabilidades
- Configuración centralizada
- Manejo de errores robusto
- Documentación inline
- Variables CSS para mantenimiento

### Performance
- Carga bajo demanda
- Optimización de imágenes placeholder
- Requests API optimizados
- CSS/JS minificables

## 📊 Estado del Proyecto

**Estado**: ✅ Producción - Completamente funcional
- Todas las 4 funcionalidades implementadas
- API real integrada y funcionando
- Datos de 151+ jugadores cargando correctamente
- Fotos con sistema placeholder funcional
- Diseño responsive completado
- Estructura de carpetas reorganizada según buenas prácticas

## 🚀 Próximos Pasos

- [ ] Implementación de filtros avanzados
- [ ] Exportación de datos (CSV/PDF)
- [ ] Más opciones de personalización visual
- [ ] Cache de datos para mejor performance


versión 2025-08-01