<?php

/**
 * Trait Movies_CLI_Handler
 * Handles all WP-CLI related functionality for the Movies plugin
 */
trait Movies_CLI_Handler {
    /**
     * Manually sync movies from TMDB via WP-CLI
     * Command: wp movies sync
     *
     * @when after_wp_load
     */
    public function cli_sync_movies() {
        WP_CLI::log('Starting manual movie sync...');
        $this->update_movies();
        WP_CLI::success('Movies have been synced from TMDB.');
    }

    /**
     * Logs a message to WP-CLI if available, otherwise to error_log
     *
     * @param string $message The message to log
     */
    private function log_message($message) {
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::log('Movies Manager: ' . $message);
        } else {
            error_log('Movies Manager: ' . $message);
        }
    }
}

/**
 * Class Movies_Cron
 * Handles scheduled tasks for movie updates from TMDB API
 * Manages WordPress cron jobs and WP-CLI commands for movie synchronization
 */
class Movies_Cron {
    use Movies_CLI_Handler;

    /** @var Movies_API Instance of the Movies_API class for TMDB interactions */
    private $api;

    /**
     * Constructor - Sets up cron schedules and WP-CLI commands
     * Initializes the API instance and registers necessary WordPress hooks
     */
    public function __construct() {
        $this->api = new Movies_API();
        add_action('movies_daily_update', array($this, 'update_movies'));
        add_filter('cron_schedules', array($this, 'add_cron_intervals'));

        // Register WP-CLI command if available
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('movies sync', array($this, 'cli_sync_movies'));
        }
    }

    /**
     * Adds custom cron intervals to WordPress
     * Currently adds a weekly interval for movie updates
     *
     * @param array $schedules Existing cron schedules
     * @return array Modified cron schedules
     */
    public function add_cron_intervals($schedules) {
        $schedules['weekly'] = array(
            'interval' => 7 * 24 * 60 * 60,
            'display' => __('Once Weekly', 'movies-manager')
        );
        return $schedules;
    }

    /**
     * Main update function that fetches and saves movies from TMDB
     * Retrieves popular movies and their details, then saves them to WordPress
     */
    public function update_movies() {
        // Get plugin settings
        $options = get_option('movies_settings');
        if (empty($options['tmdb_api_key'])) {
            $this->log_message('TMDB API key is not set. Please set the TMDB API key in the plugin settings.');
            return;
        }

        // Fetch popular movies
        $response = $this->api->get_popular_movies();
        if (is_wp_error($response)) {
            $this->log_message('Error fetching movies - ' . $response->get_error_message());
            return;
        }

        if (!isset($response['results']) || empty($response['results'])) {
            return;
        }

        foreach ($response['results'] as $movie) {
            // Get detailed movie information
            $movie_details = $this->api->get_movie_details($movie['id']);
            
            if (!is_wp_error($movie_details)) {
                $this->api->save_movie($movie_details);
            }
        }

        // Update the last sync time
        update_option('movies_last_sync', current_time('mysql'));
    }

    /**
     * Reschedules the movie update cron job with a new frequency
     * Removes existing schedule if present and creates a new one
     *
     * @param string $frequency The frequency for updates (e.g., 'daily', 'weekly')
     */
    public function reschedule_updates($frequency) {
        $timestamp = wp_next_scheduled('movies_daily_update');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'movies_daily_update');
        }

        if (!wp_next_scheduled('movies_daily_update')) {
            wp_schedule_event(time(), $frequency, 'movies_daily_update');
        }
    }
} 