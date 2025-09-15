<?php
/**
 * Plugin Name: Golf Genius Elementor
 * Plugin URI: https://github.com/RCaguilarJ/GolfGenius3
 * Description: Un plugin de Elementor para mostrar tablas personalizables de Golf Genius con panel de administración y shortcodes.
 * Version: 1.0.0
 * Author: Golf Genius Team
 * Text Domain: golf-genius-elementor
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Elementor tested up to: 3.18
 * Elementor Pro tested up to: 3.18
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GOLF_GENIUS_ELEMENTOR_VERSION', '1.0.0');
define('GOLF_GENIUS_ELEMENTOR_FILE', __FILE__);
define('GOLF_GENIUS_ELEMENTOR_PATH', plugin_dir_path(__FILE__));
define('GOLF_GENIUS_ELEMENTOR_URL', plugin_dir_url(__FILE__));

/**
 * Main Golf Genius Elementor Class
 */
final class Golf_Genius_Elementor {
    
    /**
     * Plugin instance
     */
    private static $_instance = null;
    
    /**
     * Ensure only one instance of the plugin is loaded
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if Elementor is installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }
        
        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, '3.0.0', '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }
        
        // Check for required PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }
        
        // Initialize plugin
        $this->includes();
        $this->hooks();
        
        // Load text domain
        load_plugin_textdomain('golf-genius-elementor', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once GOLF_GENIUS_ELEMENTOR_PATH . 'includes/class-golf-genius-admin.php';
        require_once GOLF_GENIUS_ELEMENTOR_PATH . 'includes/class-golf-genius-api.php';
        require_once GOLF_GENIUS_ELEMENTOR_PATH . 'includes/class-golf-genius-shortcode.php';
        require_once GOLF_GENIUS_ELEMENTOR_PATH . 'includes/elementor-widgets/class-golf-genius-widget.php';
        
        // Initialize classes
        new Golf_Genius_API();
        new Golf_Genius_Shortcode();
    }
    
    /**
     * Setup hooks
     */
    private function hooks() {
        // Register Elementor widget
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }
    
    /**
     * Register Elementor widgets
     */
    public function register_widgets() {
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Golf_Genius_Elementor_Widget());
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'golf-genius-style',
            GOLF_GENIUS_ELEMENTOR_URL . 'assets/css/golf-genius.css',
            [],
            GOLF_GENIUS_ELEMENTOR_VERSION
        );
        
        wp_enqueue_script(
            'golf-genius-script',
            GOLF_GENIUS_ELEMENTOR_URL . 'assets/js/golf-genius.js',
            ['jquery'],
            GOLF_GENIUS_ELEMENTOR_VERSION,
            true
        );
        
        // Localize script for AJAX calls
        wp_localize_script('golf-genius-script', 'golf_genius_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('golf_genius_nonce')
        ]);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook_suffix) {
        if (strpos($hook_suffix, 'golf-genius') !== false) {
            wp_enqueue_style(
                'golf-genius-admin-style',
                GOLF_GENIUS_ELEMENTOR_URL . 'assets/css/admin.css',
                [],
                GOLF_GENIUS_ELEMENTOR_VERSION
            );
            
            wp_enqueue_script(
                'golf-genius-admin-script',
                GOLF_GENIUS_ELEMENTOR_URL . 'assets/js/admin.js',
                ['jquery'],
                GOLF_GENIUS_ELEMENTOR_VERSION,
                true
            );
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Golf Genius', 'golf-genius-elementor'),
            __('Golf Genius', 'golf-genius-elementor'),
            'manage_options',
            'golf-genius',
            [$this, 'admin_page'],
            'dashicons-analytics',
            30
        );
        
        add_submenu_page(
            'golf-genius',
            __('Settings', 'golf-genius-elementor'),
            __('Settings', 'golf-genius-elementor'),
            'manage_options',
            'golf-genius-settings',
            [$this, 'settings_page']
        );
    }
    
    /**
     * Admin page callback
     */
    public function admin_page() {
        $admin = new Golf_Genius_Admin();
        $admin->render_admin_page();
    }
    
    /**
     * Settings page callback
     */
    public function settings_page() {
        $admin = new Golf_Genius_Admin();
        $admin->render_settings_page();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create default options
        add_option('golf_genius_api_key', '');
        add_option('golf_genius_default_columns', [
            'photo', 'firstName', 'lastName', 'affiliation', 'position'
        ]);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Admin notice for missing Elementor
     */
    public function admin_notice_missing_elementor() {
        $message = sprintf(
            __('El plugin "%1$s" requiere que "%2$s" esté instalado y activado.', 'golf-genius-elementor'),
            '<strong>' . __('Golf Genius Elementor', 'golf-genius-elementor') . '</strong>',
            '<strong>' . __('Elementor', 'golf-genius-elementor') . '</strong>'
        );
        
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
    
    /**
     * Admin notice for minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        $message = sprintf(
            __('El plugin "%1$s" requiere Elementor versión %2$s o superior.', 'golf-genius-elementor'),
            '<strong>' . __('Golf Genius Elementor', 'golf-genius-elementor') . '</strong>',
            '3.0.0'
        );
        
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
    
    /**
     * Admin notice for minimum PHP version
     */
    public function admin_notice_minimum_php_version() {
        $message = sprintf(
            __('El plugin "%1$s" requiere PHP versión %2$s o superior.', 'golf-genius-elementor'),
            '<strong>' . __('Golf Genius Elementor', 'golf-genius-elementor') . '</strong>',
            '7.4'
        );
        
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
}

// Initialize the plugin
Golf_Genius_Elementor::instance();