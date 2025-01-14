<?php

/**
 * Class Movies_Post_Type
 * Handles the registration and management of the custom 'movie' post type
 * Includes meta boxes, taxonomies, and associated data management
 */
class Movies_Post_Type {
    /**
     * Constructor - Sets up WordPress hooks for the movie post type
     * Registers post type, taxonomies, meta boxes, and save handlers
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_movie_meta_boxes'));
        add_action('save_post', array($this, 'save_movie_meta'));
    }

    /**
     * Registers the 'movie' custom post type in WordPress
     * Sets up labels, capabilities, and support features
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Movies', 'movies-manager'),
            'singular_name'      => __('Movie', 'movies-manager'),
            'menu_name'          => __('Movies', 'movies-manager'),
            'add_new'            => __('Add New', 'movies-manager'),
            'add_new_item'       => __('Add New Movie', 'movies-manager'),
            'edit_item'          => __('Edit Movie', 'movies-manager'),
            'new_item'           => __('New Movie', 'movies-manager'),
            'view_item'          => __('View Movie', 'movies-manager'),
            'search_items'       => __('Search Movies', 'movies-manager'),
            'not_found'          => __('No movies found', 'movies-manager'),
            'not_found_in_trash' => __('No movies found in Trash', 'movies-manager'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'movie'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true, // Enable Gutenberg editor
            'menu_icon'          => 'dashicons-video-alt2',
        );

        register_post_type('movie', $args);
    }

    /**
     * Registers taxonomies associated with the movie post type
     * Currently sets up the 'movie_category' taxonomy for genre classification
     */
    public function register_taxonomies() {
        // Register Movie Category taxonomy
        $labels = array(
            'name'              => __('Movie Categories', 'movies-manager'),
            'singular_name'     => __('Movie Category', 'movies-manager'),
            'search_items'      => __('Search Movie Categories', 'movies-manager'),
            'all_items'         => __('All Movie Categories', 'movies-manager'),
            'parent_item'       => __('Parent Movie Category', 'movies-manager'),
            'parent_item_colon' => __('Parent Movie Category:', 'movies-manager'),
            'edit_item'         => __('Edit Movie Category', 'movies-manager'),
            'update_item'       => __('Update Movie Category', 'movies-manager'),
            'add_new_item'      => __('Add New Movie Category', 'movies-manager'),
            'new_item_name'     => __('New Movie Category Name', 'movies-manager'),
            'menu_name'         => __('Categories', 'movies-manager'),
        );

        register_taxonomy('movie_category', 'movie', array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'movie-category'),
            'show_in_rest'      => true,
        ));
    }

    /**
     * Adds meta boxes to the movie post type edit screen
     * Creates the movie details meta box for additional movie information
     */
    public function add_movie_meta_boxes() {
        add_meta_box(
            'movie_details',
            __('Movie Details', 'movies-manager'),
            array($this, 'render_movie_meta_box'),
            'movie',
            'normal',
            'high'
        );
    }

    /**
     * Renders the movie details meta box content
     * Displays form fields for movie metadata like release date, rating, etc.
     *
     * @param WP_Post $post The current post object
     */
    public function render_movie_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('movie_meta_box', 'movie_meta_box_nonce');

        // Get existing meta values
        $tmdb_id = get_post_meta($post->ID, '_tmdb_id', true);
        $release_date = get_post_meta($post->ID, '_release_date', true);
        $rating = get_post_meta($post->ID, '_rating', true);
        $poster_path = get_post_meta($post->ID, '_poster_path', true);
        $backdrop_path = get_post_meta($post->ID, '_backdrop_path', true);
        $budget = get_post_meta($post->ID, '_budget', true);
        $vote_count = get_post_meta($post->ID, '_vote_count', true);
        // Output the form fields
        ?>
        <input type="hidden" id="tmdb_id" name="tmdb_id" value="<?php echo esc_attr($tmdb_id); ?>" />
        <p>
            <label for="budget"><?php _e('Budget:', 'movies-manager'); ?></label>
            <input type="number" id="budget" name="budget" value="<?php echo esc_attr($budget); ?>" />
        </p>
        <p>
            <label for="release_date"><?php _e('Release Date:', 'movies-manager'); ?></label>
            <input type="date" id="release_date" name="release_date" value="<?php echo esc_attr($release_date); ?>" />
        </p>
        <p>
            <label for="rating"><?php _e('Rating:', 'movies-manager'); ?></label>
            <input type="number" id="rating" name="rating" value="<?php echo esc_attr($rating); ?>" step="0.1" min="0" max="10" />
        </p>
        <p>
            <label for="vote_count"><?php _e('Vote count:', 'movies-manager'); ?></label>
            <input type="number" id="vote_count" name="vote_count" value="<?php echo esc_attr($vote_count); ?>" />
        </p>
        <p>
            <label for="backdrop_path"><?php _e('Backdrop:', 'movies-manager'); ?></label>
            <button type="button" class="button open-media-library">Select Image</button>
            <input type="hidden" id="backdrop_path" name="backdrop_path" value="<?php echo $backdrop_path; ?>">
            <div id="image-preview" style="margin-top: 10px;">
                <?php if ($backdrop_path): ?>
                    <img src="<?php echo esc_url(wp_get_attachment_url($backdrop_path)); ?>" style="max-width: 100%; height: auto;">
                <?php endif; ?>
            </div>
            <!-- Add a button to upload the image -->
            <script>
                (function($) {
            $(document).ready(function() {
                // Open the media library
                $('.open-media-library').on('click', function(e) {
                    e.preventDefault();

                    // Create or reuse the media frame
                    let mediaFrame = wp.media({
                        title: 'Select Image',
                        button: { text: 'Use This Image' },
                        multiple: false
                    });

                    // Handle the selection
                    mediaFrame.on('select', function() {
                        let attachment = mediaFrame.state().get('selection').first().toJSON();
                        $('#backdrop_path').val(attachment.id); // Save URL in the hidden input
                        $('#image-preview').html('<img src="' + attachment.url + '" style="max-width: 100%; height: auto;">'); // Preview image
                    });

                    mediaFrame.open();
                });
            });
        })(jQuery);
            </script>
        </p>
        <?php
    }

    /**
     * Saves the movie meta box data
     * Handles security checks and updates post meta with sanitized values
     *
     * @param int $post_id The ID of the post being saved
     */
    public function save_movie_meta($post_id) {
        // Security checks
        if (!isset($_POST['movie_meta_box_nonce'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['movie_meta_box_nonce'], 'movie_meta_box')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save meta values
        $fields = array('tmdb_id', 'release_date', 'rating', 'poster_path', 'backdrop_path');
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
} 