<?php

/**
 * Class Movies_Admin
 * Handles all WordPress admin functionality for the Movies plugin
 * Manages settings page, options, and manual sync functionality
 */
class Movies_Admin {
    /** @var array Stored plugin options from WordPress database */
    private $options;

    /**
     * Constructor - Sets up WordPress admin hooks
     * Initializes admin menu, settings, and manual sync handler
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_init', array($this, 'handle_manual_sync'));
    }

    /**
     * Adds the plugin settings page to WordPress admin
     * Creates a submenu under the Movies post type menu
     */
    public function add_plugin_page() {

        // Add Settings submenu under Movies
        add_submenu_page(
            'edit.php?post_type=movie',
            __('Movies Settings', 'movies-manager'),
            __('Settings', 'movies-manager'),
            'manage_options',
            'movies-settings',
            array($this, 'create_admin_page')
        );

        // Categories is automatically added by WordPress for hierarchical taxonomies
        // Add New is automatically added by WordPress for the post type
    }

    /**
     * Renders the admin settings page
     * Displays form for API settings and manual sync option
     */
    public function create_admin_page() {
        // Get option values
        $this->options = get_option('movies_settings');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
            <?php
                settings_fields('movies_settings_group');
                do_settings_sections('movies-settings');
                submit_button();
            ?>
            </form>

            <hr />
            <h2><?php _e('Manual Sync', 'movies-manager'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('manual_sync', 'manual_sync_nonce'); ?>
                <p><?php _e('Click the button below to manually sync movies from TMDB:', 'movies-manager'); ?></p>
                <input type="submit" name="manual_sync" class="button button-primary" value="<?php esc_attr_e('Sync Now', 'movies-manager'); ?>" />
            </form>
        </div>
        <?php
    }

    /**
     * Initializes the settings page
     * Registers settings, sections, and fields for the admin form
     */
    public function page_init() {
        register_setting(
            'movies_settings_group',
            'movies_settings',
            array($this, 'sanitize')
        );

        add_settings_section(
            'movies_settings_section',
            __('TMDB API Settings', 'movies-manager'),
            array($this, 'section_info'),
            'movies-settings'
        );

        add_settings_field(
            'tmdb_api_key',
            __('API Key', 'movies-manager'),
            array($this, 'api_key_callback'),
            'movies-settings',
            'movies_settings_section'
        );

        add_settings_field(
            'update_frequency',
            __('Update Frequency', 'movies-manager'),
            array($this, 'update_frequency_callback'),
            'movies-settings',
            'movies_settings_section'
        );
    }

    /**
     * Sanitizes the settings input before saving
     * Cleans API key and update frequency values
     *
     * @param array $input Raw input from the settings form
     * @return array Sanitized input values
     */
    public function sanitize($input) {
        $sanitized = array();
        
        if (isset($input['tmdb_api_key'])) {
            $sanitized['tmdb_api_key'] = sanitize_text_field($input['tmdb_api_key']);
        }

        if (isset($input['update_frequency'])) {
            $sanitized['update_frequency'] = sanitize_text_field($input['update_frequency']);
        }

        return $sanitized;
    }

    /**
     * Renders the settings section information
     * Displays helper text for TMDB API settings
     */
    public function section_info() {
        echo __('Enter your TMDB API settings below:', 'movies-manager');
    }

    /**
     * Renders the API key input field
     * Displays and manages the TMDB API key setting
     */
    public function api_key_callback() {
        $value = isset($this->options['tmdb_api_key']) ? $this->options['tmdb_api_key'] : '';
        printf(
            '<input type="text" id="tmdb_api_key" name="movies_settings[tmdb_api_key]" value="%s" class="regular-text" />',
            esc_attr($value)
        );
    }

    /**
     * Renders the update frequency dropdown
     * Allows selection of how often to sync with TMDB
     */
    public function update_frequency_callback() {
        $value = isset($this->options['update_frequency']) ? $this->options['update_frequency'] : 'daily';
        ?>
        <select name="movies_settings[update_frequency]" id="update_frequency">
            <option value="hourly" <?php selected($value, 'hourly'); ?>><?php _e('Hourly', 'movies-manager'); ?></option>
            <option value="twicedaily" <?php selected($value, 'twicedaily'); ?>><?php _e('Twice Daily', 'movies-manager'); ?></option>
            <option value="daily" <?php selected($value, 'daily'); ?>><?php _e('Daily', 'movies-manager'); ?></option>
            <option value="weekly" <?php selected($value, 'weekly'); ?>><?php _e('Weekly', 'movies-manager'); ?></option>
        </select>
        <?php
    }

    /**
     * Handles the manual sync action
     * Processes the manual sync form submission with security checks
     */
    public function handle_manual_sync() {
        if (
            isset($_POST['manual_sync']) && 
            isset($_POST['manual_sync_nonce']) && 
            wp_verify_nonce($_POST['manual_sync_nonce'], 'manual_sync') &&
            current_user_can('manage_options')
        ) {
            // Get the cron instance and run the update
            $cron = new Movies_Cron();
            $cron->update_movies();

            // Add admin notice
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Movies have been manually synced from TMDB.', 'movies-manager'); ?></p>
                </div>
                <?php
            });
        }
    }
}