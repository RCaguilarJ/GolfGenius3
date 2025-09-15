<?php
/**
 * Golf Genius Shortcode Class
 * Handles the shortcode functionality for displaying golf tables
 */

if (!defined('ABSPATH')) {
    exit;
}

class Golf_Genius_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('golf_genius_table', [$this, 'render_table_shortcode']);
    }
    
    /**
     * Render the golf table shortcode
     */
    public function render_table_shortcode($atts, $content = null) {
        $atts = shortcode_atts([
            'columns' => 'photo,firstName,lastName,affiliation,position',
            'title' => '',
            'class' => '',
            'show_refresh' => 'true',
            'show_column_selector' => 'false',
            'max_height' => '',
            'cache_time' => '300' // 5 minutes default
        ], $atts, 'golf_genius_table');
        
        // Parse columns
        $columns = array_map('trim', explode(',', $atts['columns']));
        $columns = array_filter($columns); // Remove empty values
        
        if (empty($columns)) {
            return '<p>' . __('No se han especificado columnas para mostrar.', 'golf-genius-elementor') . '</p>';
        }
        
        // Generate unique ID for this table instance
        $table_id = 'golf-genius-table-' . uniqid();
        
        // Build table HTML
        $output = '<div class="golf-genius-container ' . esc_attr($atts['class']) . '" id="' . esc_attr($table_id) . '">';
        
        // Title
        if (!empty($atts['title'])) {
            $output .= '<h3 class="golf-genius-title">' . esc_html($atts['title']) . '</h3>';
        }
        
        // Controls
        $output .= '<div class="golf-genius-controls">';
        
        if ($atts['show_column_selector'] === 'true') {
            $output .= $this->render_column_selector($columns);
        }
        
        if ($atts['show_refresh'] === 'true') {
            $output .= '<button class="golf-genius-refresh-btn" data-table="' . esc_attr($table_id) . '">';
            $output .= __('Actualizar Tabla', 'golf-genius-elementor');
            $output .= '</button>';
        }
        
        $output .= '</div>';
        
        // Loading indicator
        $output .= '<div class="golf-genius-loading" id="' . esc_attr($table_id) . '-loading">';
        $output .= '<div class="golf-genius-spinner"></div>';
        $output .= '<p>' . __('Cargando datos de Golf Genius...', 'golf-genius-elementor') . '</p>';
        $output .= '</div>';
        
        // Error message
        $output .= '<div class="golf-genius-error" id="' . esc_attr($table_id) . '-error" style="display: none;">';
        $output .= '<p>' . __('Error al cargar los datos. Por favor, inténtelo de nuevo.', 'golf-genius-elementor') . '</p>';
        $output .= '</div>';
        
        // Table container
        $table_style = '';
        if (!empty($atts['max_height'])) {
            $table_style = 'style="max-height: ' . esc_attr($atts['max_height']) . '; overflow-y: auto;"';
        }
        
        $output .= '<div class="golf-genius-table-container" ' . $table_style . '>';
        $output .= '<table class="golf-genius-table" id="' . esc_attr($table_id) . '-table">';
        $output .= '<thead><tr>';
        
        foreach ($columns as $column) {
            $header = $this->get_column_header($column);
            $output .= '<th data-column="' . esc_attr($column) . '">' . esc_html($header) . '</th>';
        }
        
        $output .= '</tr></thead>';
        $output .= '<tbody id="' . esc_attr($table_id) . '-tbody">';
        $output .= '<tr><td colspan="' . count($columns) . '">' . __('Cargando...', 'golf-genius-elementor') . '</td></tr>';
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';
        
        $output .= '</div>';
        
        // Add JavaScript for this table instance
        $output .= $this->render_table_script($table_id, $columns, $atts);
        
        return $output;
    }
    
    /**
     * Render column selector
     */
    private function render_column_selector($selected_columns) {
        $all_columns = $this->get_available_columns();
        
        $output = '<div class="golf-genius-column-selector">';
        $output .= '<button class="golf-genius-columns-btn">' . __('Seleccionar Columnas ▼', 'golf-genius-elementor') . '</button>';
        $output .= '<div class="golf-genius-columns-dropdown">';
        
        foreach ($all_columns as $key => $label) {
            $checked = in_array($key, $selected_columns) ? 'checked' : '';
            $output .= '<label>';
            $output .= '<input type="checkbox" value="' . esc_attr($key) . '" ' . $checked . '> ';
            $output .= esc_html($label);
            $output .= '</label>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Render table script
     */
    private function render_table_script($table_id, $columns, $atts) {
        $columns_json = json_encode($columns);
        $config_json = json_encode($atts);
        
        $script = "
        <script>
        jQuery(document).ready(function($) {
            var tableConfig_{$table_id} = {
                tableId: '{$table_id}',
                columns: {$columns_json},
                config: {$config_json}
            };
            
            // Initialize table
            initializeGolfTable_{$table_id}();
            
            function initializeGolfTable_{$table_id}() {
                loadTableData_{$table_id}();
                
                // Refresh button
                $('#{$table_id} .golf-genius-refresh-btn').click(function() {
                    loadTableData_{$table_id}();
                });
                
                // Column selector
                $('#{$table_id} .golf-genius-columns-btn').click(function(e) {
                    e.stopPropagation();
                    $('#{$table_id} .golf-genius-columns-dropdown').toggle();
                });
                
                $(document).click(function() {
                    $('#{$table_id} .golf-genius-columns-dropdown').hide();
                });
                
                $('#{$table_id} .golf-genius-columns-dropdown').click(function(e) {
                    e.stopPropagation();
                });
                
                $('#{$table_id} .golf-genius-columns-dropdown input').change(function() {
                    var newColumns = [];
                    $('#{$table_id} .golf-genius-columns-dropdown input:checked').each(function() {
                        newColumns.push($(this).val());
                    });
                    
                    if (newColumns.length > 0) {
                        tableConfig_{$table_id}.columns = newColumns;
                        updateTableHeaders_{$table_id}();
                        loadTableData_{$table_id}();
                    }
                });
            }
            
            function loadTableData_{$table_id}() {
                showLoading_{$table_id}();
                hideError_{$table_id}();
                
                $.ajax({
                    url: golf_genius_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'golf_genius_get_players',
                        nonce: golf_genius_ajax.nonce
                    },
                    success: function(response) {
                        hideLoading_{$table_id}();
                        
                        if (response.success && response.data) {
                            renderTableData_{$table_id}(response.data);
                        } else {
                            showError_{$table_id}();
                        }
                    },
                    error: function() {
                        hideLoading_{$table_id}();
                        showError_{$table_id}();
                    }
                });
            }
            
            function renderTableData_{$table_id}(players) {
                var tbody = $('#{$table_id}-tbody');
                tbody.empty();
                
                if (!players || players.length === 0) {
                    tbody.append('<tr><td colspan=\"' + tableConfig_{$table_id}.columns.length + '\">' + '" . __('No se encontraron datos.', 'golf-genius-elementor') . "' + '</td></tr>');
                    return;
                }
                
                players.forEach(function(player) {
                    var row = '<tr>';
                    
                    tableConfig_{$table_id}.columns.forEach(function(column) {
                        var cellContent = getCellContent_{$table_id}(player, column);
                        row += '<td data-column=\"' + column + '\">' + cellContent + '</td>';
                    });
                    
                    row += '</tr>';
                    tbody.append(row);
                });
            }
            
            function getCellContent_{$table_id}(player, column) {
                switch(column) {
                    case 'photo':
                        if (player.photo && player.photo.startsWith('http')) {
                            var initials = (player.firstName ? player.firstName.charAt(0) : '') + (player.lastName ? player.lastName.charAt(0) : '');
                            return '<img src=\"' + player.photo + '\" alt=\"' + (player.firstName || '') + ' ' + (player.lastName || '') + '\" class=\"golf-genius-photo\" onerror=\"this.style.display=\\'none\\'; this.nextElementSibling.style.display=\\'flex\\';\"><div class=\"golf-genius-avatar\" style=\"display: none;\">' + initials + '</div>';
                        } else {
                            var initials = (player.firstName ? player.firstName.charAt(0) : '') + (player.lastName ? player.lastName.charAt(0) : '');
                            return '<div class=\"golf-genius-avatar\">' + initials + '</div>';
                        }
                    case 'firstName':
                        return player.firstName || '';
                    case 'lastName':
                        return player.lastName || '';
                    case 'affiliation':
                        return '<span class=\"golf-genius-affiliation\">' + (player.affiliation || '') + '</span>';
                    case 'position':
                        return '<span class=\"golf-genius-position\">' + (player.position || '') + '</span>';
                    case 'score':
                        var scoreClass = '';
                        if (player.score) {
                            if (player.score.indexOf('-') === 0) scoreClass = 'under-par';
                            else if (player.score === 'E' || player.score === '0') scoreClass = 'par';
                            else scoreClass = 'over-par';
                        }
                        return '<span class=\"golf-genius-score ' + scoreClass + '\">' + (player.score || '') + '</span>';
                    case 'rounds':
                        if (player.rounds && Array.isArray(player.rounds)) {
                            return player.rounds.join(', ');
                        }
                        return '';
                    case 'highlights':
                        return '<span class=\"golf-genius-highlights\">' + (player.highlights || '') + '</span>';
                    case 'previousRanking':
                        return '<span class=\"golf-genius-ranking\">' + (player.previousRanking ? '#' + player.previousRanking : '') + '</span>';
                    case 'email':
                        return player.email ? '<a href=\"mailto:' + player.email + '\">' + player.email + '</a>' : '';
                    case 'handicap':
                        return player.handicap || '';
                    case 'field':
                        return player.field || '';
                    case 'entry_number':
                        return player.entry_number || '';
                    case 'phone':
                        return player.phone || '';
                    case 'city':
                        return player.city || '';
                    case 'state':
                        return player.state || '';
                    default:
                        return player[column] || '';
                }
            }
            
            function updateTableHeaders_{$table_id}() {
                var thead = $('#{$table_id}-table thead tr');
                thead.empty();
                
                tableConfig_{$table_id}.columns.forEach(function(column) {
                    var header = getColumnHeader_{$table_id}(column);
                    thead.append('<th data-column=\"' + column + '\">' + header + '</th>');
                });
            }
            
            function getColumnHeader_{$table_id}(column) {
                var headers = {
                    'photo': '" . __('Foto', 'golf-genius-elementor') . "',
                    'firstName': '" . __('Nombre', 'golf-genius-elementor') . "',
                    'lastName': '" . __('Apellido', 'golf-genius-elementor') . "',
                    'affiliation': '" . __('Afiliación', 'golf-genius-elementor') . "',
                    'position': '" . __('Posición', 'golf-genius-elementor') . "',
                    'score': '" . __('Puntuación', 'golf-genius-elementor') . "',
                    'rounds': '" . __('Rondas', 'golf-genius-elementor') . "',
                    'highlights': '" . __('Destacados', 'golf-genius-elementor') . "',
                    'previousRanking': '" . __('Ranking Anterior', 'golf-genius-elementor') . "',
                    'email': '" . __('Email', 'golf-genius-elementor') . "',
                    'handicap': '" . __('Handicap', 'golf-genius-elementor') . "',
                    'field': '" . __('Campo', 'golf-genius-elementor') . "',
                    'entry_number': '" . __('N° Entrada', 'golf-genius-elementor') . "',
                    'phone': '" . __('Teléfono', 'golf-genius-elementor') . "',
                    'city': '" . __('Ciudad', 'golf-genius-elementor') . "',
                    'state': '" . __('Estado', 'golf-genius-elementor') . "'
                };
                
                return headers[column] || column;
            }
            
            function showLoading_{$table_id}() {
                $('#{$table_id}-loading').show();
                $('#{$table_id}-table').hide();
            }
            
            function hideLoading_{$table_id}() {
                $('#{$table_id}-loading').hide();
                $('#{$table_id}-table').show();
            }
            
            function showError_{$table_id}() {
                $('#{$table_id}-error').show();
                $('#{$table_id}-table').hide();
            }
            
            function hideError_{$table_id}() {
                $('#{$table_id}-error').hide();
            }
        });
        </script>";
        
        return $script;
    }
    
    /**
     * Get column header
     */
    private function get_column_header($column) {
        $headers = [
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
        
        return $headers[$column] ?? $column;
    }
    
    /**
     * Get available columns
     */
    private function get_available_columns() {
        return [
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
    }
}