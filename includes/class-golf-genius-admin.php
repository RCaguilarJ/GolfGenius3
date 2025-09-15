<?php
/**
 * Golf Genius Admin Class
 * Handles the admin panel and settings for the Golf Genius Elementor plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Golf_Genius_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', [$this, 'init_settings']);
        add_action('wp_ajax_golf_genius_generate_shortcode', [$this, 'ajax_generate_shortcode']);
        add_action('wp_ajax_golf_genius_test_api', [$this, 'ajax_test_api']);
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('golf_genius_settings', 'golf_genius_api_key');
        register_setting('golf_genius_settings', 'golf_genius_event_id');
        register_setting('golf_genius_settings', 'golf_genius_round_id');
        register_setting('golf_genius_settings', 'golf_genius_tournament_id');
        register_setting('golf_genius_settings', 'golf_genius_default_columns');
    }
    
    /**
     * Render the main admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap golf-genius-admin">
            <h1><?php _e('Golf Genius - Panel de Administración', 'golf-genius-elementor'); ?></h1>
            
            <div class="golf-genius-admin-container">
                <div class="golf-genius-admin-left">
                    <div class="golf-genius-card">
                        <h2><?php _e('Configurar Tabla', 'golf-genius-elementor'); ?></h2>
                        <p><?php _e('Personaliza las columnas que deseas mostrar en tu tabla de golf y genera el shortcode.', 'golf-genius-elementor'); ?></p>
                        
                        <form id="golf-genius-table-form">
                            <div class="form-group">
                                <label><?php _e('Selecciona las Columnas:', 'golf-genius-elementor'); ?></label>
                                <div class="columns-selector">
                                    <?php $this->render_column_checkboxes(); ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="table-title"><?php _e('Título de la Tabla (opcional):', 'golf-genius-elementor'); ?></label>
                                <input type="text" id="table-title" name="table_title" placeholder="<?php _e('Ej: Clasificación del Torneo', 'golf-genius-elementor'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="table-class"><?php _e('Clase CSS personalizada (opcional):', 'golf-genius-elementor'); ?></label>
                                <input type="text" id="table-class" name="table_class" placeholder="<?php _e('Ej: mi-tabla-golf', 'golf-genius-elementor'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="show-refresh"><?php _e('Mostrar botón de actualización:', 'golf-genius-elementor'); ?></label>
                                <select id="show-refresh" name="show_refresh">
                                    <option value="true"><?php _e('Sí', 'golf-genius-elementor'); ?></option>
                                    <option value="false"><?php _e('No', 'golf-genius-elementor'); ?></option>
                                </select>
                            </div>
                            
                            <button type="button" id="generate-shortcode" class="button button-primary">
                                <?php _e('Generar Shortcode', 'golf-genius-elementor'); ?>
                            </button>
                        </form>
                    </div>
                    
                    <div class="golf-genius-card">
                        <h3><?php _e('Shortcode Generado', 'golf-genius-elementor'); ?></h3>
                        <div id="shortcode-result">
                            <p><?php _e('Selecciona las columnas y haz clic en "Generar Shortcode" para obtener tu código.', 'golf-genius-elementor'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="golf-genius-admin-right">
                    <div class="golf-genius-card">
                        <h3><?php _e('Vista Previa', 'golf-genius-elementor'); ?></h3>
                        <div id="table-preview">
                            <p><?php _e('La vista previa aparecerá aquí cuando generes un shortcode.', 'golf-genius-elementor'); ?></p>
                        </div>
                    </div>
                    
                    <div class="golf-genius-card">
                        <h3><?php _e('Información Útil', 'golf-genius-elementor'); ?></h3>
                        <div class="info-box">
                            <h4><?php _e('¿Cómo usar el shortcode?', 'golf-genius-elementor'); ?></h4>
                            <ol>
                                <li><?php _e('Copia el shortcode generado', 'golf-genius-elementor'); ?></li>
                                <li><?php _e('Ve a cualquier página o entrada', 'golf-genius-elementor'); ?></li>
                                <li><?php _e('Pega el shortcode donde desees mostrar la tabla', 'golf-genius-elementor'); ?></li>
                                <li><?php _e('Publica o actualiza la página', 'golf-genius-elementor'); ?></li>
                            </ol>
                            
                            <h4><?php _e('¿Cómo usar con Elementor?', 'golf-genius-elementor'); ?></h4>
                            <ol>
                                <li><?php _e('Abre Elementor en tu página', 'golf-genius-elementor'); ?></li>
                                <li><?php _e('Busca el widget "Golf Genius Table"', 'golf-genius-elementor'); ?></li>
                                <li><?php _e('Arrastra el widget a tu diseño', 'golf-genius-elementor'); ?></li>
                                <li><?php _e('Configura las opciones en el panel lateral', 'golf-genius-elementor'); ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#generate-shortcode').click(function() {
                var columns = [];
                $('.columns-selector input:checked').each(function() {
                    columns.push($(this).val());
                });
                
                var data = {
                    action: 'golf_genius_generate_shortcode',
                    columns: columns,
                    title: $('#table-title').val(),
                    class: $('#table-class').val(),
                    show_refresh: $('#show-refresh').val(),
                    nonce: '<?php echo wp_create_nonce('golf_genius_admin'); ?>'
                };
                
                $.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        $('#shortcode-result').html(
                            '<p><strong><?php _e('Tu shortcode:', 'golf-genius-elementor'); ?></strong></p>' +
                            '<div class="shortcode-display">' +
                                '<input type="text" value="' + response.data.shortcode + '" readonly onclick="this.select()">' +
                                '<button class="button" onclick="navigator.clipboard.writeText(\'' + response.data.shortcode + '\')"><?php _e('Copiar', 'golf-genius-elementor'); ?></button>' +
                            '</div>'
                        );
                        
                        if (response.data.preview) {
                            $('#table-preview').html(response.data.preview);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render the settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Golf Genius - Configuración', 'golf-genius-elementor'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('golf_genius_settings');
                do_settings_sections('golf_genius_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="golf_genius_api_key"><?php _e('API Key de Golf Genius', 'golf-genius-elementor'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="golf_genius_api_key" name="golf_genius_api_key" 
                                   value="<?php echo esc_attr(get_option('golf_genius_api_key', 'MGMlbTG_APORWozDtgXHdQ')); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Ingresa tu API key de Golf Genius.', 'golf-genius-elementor'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="golf_genius_event_id"><?php _e('Event ID', 'golf-genius-elementor'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="golf_genius_event_id" name="golf_genius_event_id" 
                                   value="<?php echo esc_attr(get_option('golf_genius_event_id', '10733818833262361649')); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="golf_genius_round_id"><?php _e('Round ID', 'golf-genius-elementor'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="golf_genius_round_id" name="golf_genius_round_id" 
                                   value="<?php echo esc_attr(get_option('golf_genius_round_id', '10733997704590933397')); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="golf_genius_tournament_id"><?php _e('Tournament ID', 'golf-genius-elementor'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="golf_genius_tournament_id" name="golf_genius_tournament_id" 
                                   value="<?php echo esc_attr(get_option('golf_genius_tournament_id', '11025765214984354975')); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button-primary" 
                           value="<?php _e('Guardar Configuración', 'golf-genius-elementor'); ?>" />
                    <button type="button" id="test-api" class="button">
                        <?php _e('Probar Conexión API', 'golf-genius-elementor'); ?>
                    </button>
                </p>
            </form>
            
            <div id="api-test-result"></div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-api').click(function() {
                var button = $(this);
                button.prop('disabled', true).text('<?php _e('Probando...', 'golf-genius-elementor'); ?>');
                
                var data = {
                    action: 'golf_genius_test_api',
                    api_key: $('#golf_genius_api_key').val(),
                    event_id: $('#golf_genius_event_id').val(),
                    round_id: $('#golf_genius_round_id').val(),
                    tournament_id: $('#golf_genius_tournament_id').val(),
                    nonce: '<?php echo wp_create_nonce('golf_genius_admin'); ?>'
                };
                
                $.post(ajaxurl, data, function(response) {
                    button.prop('disabled', false).text('<?php _e('Probar Conexión API', 'golf-genius-elementor'); ?>');
                    
                    if (response.success) {
                        $('#api-test-result').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    } else {
                        $('#api-test-result').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render column checkboxes
     */
    private function render_column_checkboxes() {
        $columns = [
            'photo' => __('Foto', 'golf-genius-elementor'),
            'firstName' => __('Nombre', 'golf-genius-elementor'),
            'lastName' => __('Apellido', 'golf-genius-elementor'),
            'affiliation' => __('Afiliación del Club', 'golf-genius-elementor'),
            'position' => __('Posición', 'golf-genius-elementor'),
            'score' => __('Puntuación', 'golf-genius-elementor'),
            'rounds' => __('Rondas', 'golf-genius-elementor'),
            'highlights' => __('Destacados', 'golf-genius-elementor'),
            'previousRanking' => __('Ranking Anterior', 'golf-genius-elementor'),
            'email' => __('Email', 'golf-genius-elementor'),
            'handicap' => __('Handicap', 'golf-genius-elementor'),
            'field' => __('Campo', 'golf-genius-elementor'),
            'entry_number' => __('Número de Entrada', 'golf-genius-elementor'),
            'phone' => __('Teléfono', 'golf-genius-elementor'),
            'city' => __('Ciudad', 'golf-genius-elementor'),
            'state' => __('Estado', 'golf-genius-elementor')
        ];
        
        $default_columns = get_option('golf_genius_default_columns', ['photo', 'firstName', 'lastName', 'affiliation', 'position']);
        
        foreach ($columns as $key => $label) {
            $checked = in_array($key, $default_columns) ? 'checked' : '';
            echo '<label><input type="checkbox" name="columns[]" value="' . esc_attr($key) . '" ' . $checked . '> ' . esc_html($label) . '</label>';
        }
    }
    
    /**
     * AJAX handler for generating shortcode
     */
    public function ajax_generate_shortcode() {
        check_ajax_referer('golf_genius_admin', 'nonce');
        
        $columns = isset($_POST['columns']) ? $_POST['columns'] : [];
        $title = sanitize_text_field($_POST['title'] ?? '');
        $class = sanitize_html_class($_POST['class'] ?? '');
        $show_refresh = sanitize_text_field($_POST['show_refresh'] ?? 'true');
        
        $shortcode_atts = [];
        if (!empty($columns)) {
            $shortcode_atts[] = 'columns="' . implode(',', $columns) . '"';
        }
        if (!empty($title)) {
            $shortcode_atts[] = 'title="' . $title . '"';
        }
        if (!empty($class)) {
            $shortcode_atts[] = 'class="' . $class . '"';
        }
        if ($show_refresh !== 'true') {
            $shortcode_atts[] = 'show_refresh="false"';
        }
        
        $shortcode = '[golf_genius_table' . (!empty($shortcode_atts) ? ' ' . implode(' ', $shortcode_atts) : '') . ']';
        
        // Generate preview
        $preview = '<div class="golf-genius-preview">';
        if (!empty($title)) {
            $preview .= '<h3>' . esc_html($title) . '</h3>';
        }
        $preview .= '<table class="golf-genius-table ' . esc_attr($class) . '">';
        $preview .= '<thead><tr>';
        foreach ($columns as $column) {
            $preview .= '<th>' . esc_html($this->get_column_label($column)) . '</th>';
        }
        $preview .= '</tr></thead>';
        $preview .= '<tbody><tr><td colspan="' . count($columns) . '">' . __('Vista previa - Los datos se cargarán dinámicamente', 'golf-genius-elementor') . '</td></tr></tbody>';
        $preview .= '</table>';
        if ($show_refresh === 'true') {
            $preview .= '<button class="button">' . __('Actualizar Tabla', 'golf-genius-elementor') . '</button>';
        }
        $preview .= '</div>';
        
        wp_send_json_success([
            'shortcode' => $shortcode,
            'preview' => $preview
        ]);
    }
    
    /**
     * AJAX handler for testing API connection
     */
    public function ajax_test_api() {
        check_ajax_referer('golf_genius_admin', 'nonce');
        
        $api_key = sanitize_text_field($_POST['api_key']);
        $event_id = sanitize_text_field($_POST['event_id']);
        $round_id = sanitize_text_field($_POST['round_id']);
        $tournament_id = sanitize_text_field($_POST['tournament_id']);
        
        try {
            $api = new Golf_Genius_API();
            $result = $api->test_connection($api_key, $event_id, $round_id, $tournament_id);
            
            if ($result) {
                wp_send_json_success([
                    'message' => __('¡Conexión exitosa! Se encontraron ' . count($result) . ' jugadores.', 'golf-genius-elementor')
                ]);
            } else {
                wp_send_json_error([
                    'message' => __('No se pudieron obtener datos. Verifica la configuración.', 'golf-genius-elementor')
                ]);
            }
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => __('Error de conexión: ', 'golf-genius-elementor') . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get column label
     */
    private function get_column_label($column) {
        $labels = [
            'photo' => __('Foto', 'golf-genius-elementor'),
            'firstName' => __('Nombre', 'golf-genius-elementor'),
            'lastName' => __('Apellido', 'golf-genius-elementor'),
            'affiliation' => __('Afiliación', 'golf-genius-elementor'),
            'position' => __('Posición', 'golf-genius-elementor'),
            'score' => __('Puntuación', 'golf-genius-elementor'),
            'rounds' => __('Rondas', 'golf-genius-elementor'),
            'highlights' => __('Destacados', 'golf-genius-elementor'),
            'previousRanking' => __('Ranking Anterior', 'golf-genius-elementor'),
            'email' => __('Email', 'golf-genius-elementor'),
            'handicap' => __('Handicap', 'golf-genius-elementor'),
            'field' => __('Campo', 'golf-genius-elementor'),
            'entry_number' => __('N° Entrada', 'golf-genius-elementor'),
            'phone' => __('Teléfono', 'golf-genius-elementor'),
            'city' => __('Ciudad', 'golf-genius-elementor'),
            'state' => __('Estado', 'golf-genius-elementor')
        ];
        
        return $labels[$column] ?? $column;
    }
}