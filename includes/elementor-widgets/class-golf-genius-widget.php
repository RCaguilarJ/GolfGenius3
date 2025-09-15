<?php
/**
 * Golf Genius Elementor Widget
 * Custom Elementor widget for Golf Genius tables
 */

if (!defined('ABSPATH')) {
    exit;
}

class Golf_Genius_Elementor_Widget extends \Elementor\Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'golf_genius_table';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('Golf Genius Table', 'golf-genius-elementor');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-table';
    }
    
    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['general'];
    }
    
    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return ['golf', 'table', 'genius', 'tournament', 'players'];
    }
    
    /**
     * Register widget controls
     */
    protected function register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Configuración de la Tabla', 'golf-genius-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'table_title',
            [
                'label' => __('Título de la Tabla', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Ej: Clasificación del Torneo', 'golf-genius-elementor'),
            ]
        );
        
        $this->add_control(
            'selected_columns',
            [
                'label' => __('Columnas a Mostrar', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
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
                    'state' => __('Estado', 'golf-genius-elementor'),
                ],
                'default' => ['photo', 'firstName', 'lastName', 'affiliation', 'position'],
                'description' => __('Selecciona las columnas que deseas mostrar en la tabla.', 'golf-genius-elementor'),
            ]
        );
        
        $this->add_control(
            'show_refresh',
            [
                'label' => __('Mostrar Botón de Actualización', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sí', 'golf-genius-elementor'),
                'label_off' => __('No', 'golf-genius-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_column_selector',
            [
                'label' => __('Mostrar Selector de Columnas', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sí', 'golf-genius-elementor'),
                'label_off' => __('No', 'golf-genius-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => __('Permite a los usuarios cambiar las columnas visibles desde el frontend.', 'golf-genius-elementor'),
            ]
        );
        
        $this->add_control(
            'max_height',
            [
                'label' => __('Altura Máxima de la Tabla', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 1000,
                        'step' => 50,
                    ],
                    'vh' => [
                        'min' => 20,
                        'max' => 100,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 600,
                ],
                'description' => __('Altura máxima de la tabla con scroll automático.', 'golf-genius-elementor'),
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Table
        $this->start_controls_section(
            'table_style_section',
            [
                'label' => __('Estilo de la Tabla', 'golf-genius-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'table_border_width',
            [
                'label' => __('Ancho del Borde', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1,
                ],
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-table' => 'border-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .golf-genius-table th' => 'border-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .golf-genius-table td' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'table_border_color',
            [
                'label' => __('Color del Borde', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ddd',
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-table' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .golf-genius-table th' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .golf-genius-table td' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'table_typography',
                'label' => __('Tipografía de la Tabla', 'golf-genius-elementor'),
                'selector' => '{{WRAPPER}} .golf-genius-table',
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Header
        $this->start_controls_section(
            'header_style_section',
            [
                'label' => __('Estilo del Encabezado', 'golf-genius-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'header_background_color',
            [
                'label' => __('Color de Fondo del Encabezado', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f8f9fa',
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-table thead th' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'header_text_color',
            [
                'label' => __('Color del Texto del Encabezado', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333',
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-table thead th' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'header_typography',
                'label' => __('Tipografía del Encabezado', 'golf-genius-elementor'),
                'selector' => '{{WRAPPER}} .golf-genius-table thead th',
            ]
        );
        
        $this->add_responsive_control(
            'header_padding',
            [
                'label' => __('Padding del Encabezado', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 12,
                    'right' => 15,
                    'bottom' => 12,
                    'left' => 15,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-table thead th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Rows
        $this->start_controls_section(
            'rows_style_section',
            [
                'label' => __('Estilo de las Filas', 'golf-genius-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'row_background_color',
            [
                'label' => __('Color de Fondo de las Filas', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-table tbody td' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'row_alternate_background_color',
            [
                'label' => __('Color Alternativo de las Filas', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f9f9f9',
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-table tbody tr:nth-child(even) td' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'row_hover_background_color',
            [
                'label' => __('Color de Fondo al Pasar el Mouse', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f0f8ff',
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-table tbody tr:hover td' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'row_padding',
            [
                'label' => __('Padding de las Filas', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 10,
                    'right' => 15,
                    'bottom' => 10,
                    'left' => 15,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-table tbody td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Title
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => __('Estilo del Título', 'golf-genius-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'table_title!' => '',
                ],
            ]
        );
        
        $this->add_control(
            'title_color',
            [
                'label' => __('Color del Título', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333',
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Tipografía del Título', 'golf-genius-elementor'),
                'selector' => '{{WRAPPER}} .golf-genius-title',
            ]
        );
        
        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Margen del Título', 'golf-genius-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 20,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .golf-genius-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Prepare shortcode attributes
        $columns = !empty($settings['selected_columns']) ? implode(',', $settings['selected_columns']) : 'photo,firstName,lastName,affiliation,position';
        $title = !empty($settings['table_title']) ? $settings['table_title'] : '';
        $show_refresh = $settings['show_refresh'] === 'yes' ? 'true' : 'false';
        $show_column_selector = $settings['show_column_selector'] === 'yes' ? 'true' : 'false';
        
        $max_height = '';
        if (!empty($settings['max_height']['size'])) {
            $max_height = $settings['max_height']['size'] . $settings['max_height']['unit'];
        }
        
        // Build shortcode
        $shortcode_atts = [
            'columns="' . esc_attr($columns) . '"'
        ];
        
        if (!empty($title)) {
            $shortcode_atts[] = 'title="' . esc_attr($title) . '"';
        }
        
        if ($show_refresh !== 'true') {
            $shortcode_atts[] = 'show_refresh="false"';
        }
        
        if ($show_column_selector === 'true') {
            $shortcode_atts[] = 'show_column_selector="true"';
        }
        
        if (!empty($max_height)) {
            $shortcode_atts[] = 'max_height="' . esc_attr($max_height) . '"';
        }
        
        $shortcode = '[golf_genius_table ' . implode(' ', $shortcode_atts) . ']';
        
        // Render the shortcode
        echo do_shortcode($shortcode);
    }
    
    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <# 
        var columns = settings.selected_columns || ['photo', 'firstName', 'lastName', 'affiliation', 'position'];
        var title = settings.table_title || '';
        #>
        
        <div class="golf-genius-container">
            <# if (title) { #>
                <h3 class="golf-genius-title">{{{ title }}}</h3>
            <# } #>
            
            <div class="golf-genius-controls">
                <# if (settings.show_column_selector === 'yes') { #>
                    <div class="golf-genius-column-selector">
                        <button class="golf-genius-columns-btn"><?php _e('Seleccionar Columnas ▼', 'golf-genius-elementor'); ?></button>
                    </div>
                <# } #>
                
                <# if (settings.show_refresh === 'yes') { #>
                    <button class="golf-genius-refresh-btn"><?php _e('Actualizar Tabla', 'golf-genius-elementor'); ?></button>
                <# } #>
            </div>
            
            <div class="golf-genius-table-container">
                <table class="golf-genius-table">
                    <thead>
                        <tr>
                            <# columns.forEach(function(column) { 
                                var headers = {
                                    'photo': '<?php _e('Foto', 'golf-genius-elementor'); ?>',
                                    'firstName': '<?php _e('Nombre', 'golf-genius-elementor'); ?>',
                                    'lastName': '<?php _e('Apellido', 'golf-genius-elementor'); ?>',
                                    'affiliation': '<?php _e('Afiliación', 'golf-genius-elementor'); ?>',
                                    'position': '<?php _e('Posición', 'golf-genius-elementor'); ?>',
                                    'score': '<?php _e('Puntuación', 'golf-genius-elementor'); ?>',
                                    'rounds': '<?php _e('Rondas', 'golf-genius-elementor'); ?>',
                                    'highlights': '<?php _e('Destacados', 'golf-genius-elementor'); ?>',
                                    'previousRanking': '<?php _e('Ranking Anterior', 'golf-genius-elementor'); ?>',
                                    'email': '<?php _e('Email', 'golf-genius-elementor'); ?>',
                                    'handicap': '<?php _e('Handicap', 'golf-genius-elementor'); ?>',
                                    'field': '<?php _e('Campo', 'golf-genius-elementor'); ?>',
                                    'entry_number': '<?php _e('N° Entrada', 'golf-genius-elementor'); ?>',
                                    'phone': '<?php _e('Teléfono', 'golf-genius-elementor'); ?>',
                                    'city': '<?php _e('Ciudad', 'golf-genius-elementor'); ?>',
                                    'state': '<?php _e('Estado', 'golf-genius-elementor'); ?>'
                                };
                                var header = headers[column] || column;
                            #>
                                <th>{{{ header }}}</th>
                            <# }); #>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="{{{ columns.length }}}"><?php _e('Vista previa - Los datos se cargarán en el frontend', 'golf-genius-elementor'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}