<?php
/**
 * Class Movies_Template_Loader
 * Handles loading of movie templates from the plugin
 */
class Movies_Template_Loader {
    /**
     * Constructor - Sets up template filters
     */
    public function __construct() {
        add_filter('template_include', array($this, 'template_loader'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    /**
     * Loads the appropriate template file
     * 
     * @param string $template The current template path
     * @return string The path to the template file to use
     */
    public function template_loader($template) {
        $post_type = get_post_type();
        
        if ($post_type !== 'movie') {
            return $template;
        }

        if (is_archive() || is_search()) {
            $file = 'archive-movie.php';
        } elseif (is_singular('movie')) {
            $file = 'single-movie.php';
        }

        if (empty($file)) {
            return $template;
        }

        // Look for template in theme first
        $theme_file = locate_template(array('movie/' . $file));
        
        if ($theme_file) {
            return $theme_file;
        }

        // Fall back to plugin template
        $plugin_file = MOVIES_PLUGIN_PATH . 'templates/movie/' . $file;
        
        if (file_exists($plugin_file)) {
            return $plugin_file;
        }

        return $template;
    }

    /**
     * Enqueues the movie styles
     */
    public function enqueue_styles() {
        if (is_singular('movie') || is_post_type_archive('movie') || is_tax('movie_category')) {
            wp_enqueue_style(
                'movies-styles',
                MOVIES_PLUGIN_URL . 'assets/css/movies.css',
                array(),
                MOVIES_PLUGIN_VERSION
            );
        }
    }
} 