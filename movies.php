<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Plugin Name: Movies Manager
 * Description: A WordPress plugin to manage movies with TMDB API integration and Gutenberg blocks
 * Version: 1.0.0
 * Author: Menges Jean
 * Text Domain: movies-manager
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MOVIES_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MOVIES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MOVIES_PLUGIN_VERSION', '1.0.0');

// Include required files
require_once MOVIES_PLUGIN_PATH . 'includes/utils.php';
require_once MOVIES_PLUGIN_PATH . 'includes/class-movies-post-type.php';
require_once MOVIES_PLUGIN_PATH . 'includes/class-movies-admin.php';
require_once MOVIES_PLUGIN_PATH . 'includes/class-movies-api.php';
require_once MOVIES_PLUGIN_PATH . 'includes/class-movies-cron.php';
require_once MOVIES_PLUGIN_PATH . 'includes/class-movies-template-loader.php';

// Initialize the plugin
class MoviesPlugin {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    private function init() {
        // Initialize Custom Post Type
        new Movies_Post_Type();

        // Initialize Admin
        if (is_admin()) {
            new Movies_Admin();
        }

        // Initialize API Handler
        new Movies_API();

        // Initialize Cron Jobs
        new Movies_Cron();

        // Initialize Gutenberg blocks
        add_action('init', array($this, 'register_blocks'));

        // Initialize Template Loader
        new Movies_Template_Loader();
    }

    public function register_blocks() {
        register_block_type(MOVIES_PLUGIN_PATH . "/build/blocks/movie-block");
    }
}

// Initialize the plugin
MoviesPlugin::get_instance();

// Activation hook
register_activation_hook(__FILE__, function() {
    // Schedule cron job for daily movie updates
    if (!wp_next_scheduled('movies_daily_update')) {
        wp_schedule_event(time(), 'daily', 'movies_daily_update');
    }
    
    // Create necessary database tables if needed
    flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clear scheduled hooks
    wp_clear_scheduled_hook('movies_daily_update');
    
    // Clean up if needed
    flush_rewrite_rules();
}); 
