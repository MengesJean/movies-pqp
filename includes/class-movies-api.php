<?php
/**
 * Trait Movies_Image_Handler
 * Handles all image-related functionality for the Movies plugin
 */
trait Movies_Image_Handler {
    /**
     * Downloads and sets the movie poster as the featured image
     * Checks for existing images to avoid duplicates
     * 
     * @param int $post_id WordPress post ID
     * @param string $poster_path TMDB poster path
     */
    private function set_featured_image($post_id, $poster_path) {
        $upload_dir = wp_upload_dir();
        $image_url = 'https://image.tmdb.org/t/p/original' . $poster_path;
        $image_name = basename($poster_path);

        // Check if image already exists in media library
        $existing_attachment = get_posts(array(
            'post_type' => 'attachment',
            'meta_key' => '_tmdb_poster_path',
            'meta_value' => $poster_path,
            'posts_per_page' => 1
        ));

        if (!empty($existing_attachment)) {
            set_post_thumbnail($post_id, $existing_attachment[0]->ID);
            return;
        }

        // Download image
        $image_data = wp_remote_get($image_url);
        if (is_wp_error($image_data)) {
            return;
        }

        $image_content = wp_remote_retrieve_body($image_data);
        $filename = wp_unique_filename($upload_dir['path'], $image_name);
        $filepath = $upload_dir['path'] . '/' . $filename;

        file_put_contents($filepath, $image_content);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $filepath, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
        wp_update_attachment_metadata($attach_id, $attach_data);
        update_post_meta($attach_id, '_tmdb_poster_path', $poster_path);
        
        set_post_thumbnail($post_id, $attach_id);
    }

    /**
     * Downloads and sets the movie backdrop image
     * Checks for existing images to avoid duplicates
     * 
     * @param int $post_id WordPress post ID
     * @param string $backdrop_path TMDB backdrop path
     */
    private function set_backdrop_image($post_id, $backdrop_path) {
        $upload_dir = wp_upload_dir();
        $image_url = 'https://image.tmdb.org/t/p/original' . $backdrop_path;
        $image_name = basename($backdrop_path);

        // Check if image already exists in media library
        $existing_attachment = get_posts(array(
            'post_type' => 'attachment',
            'meta_key' => '_tmdb_backdrop_path',
            'meta_value' => $backdrop_path,
            'posts_per_page' => 1
        ));

        if (!empty($existing_attachment)) {
            update_post_meta($post_id, '_backdrop_path', $existing_attachment[0]->ID);
            return;
        }

        // Download image
        $image_data = wp_remote_get($image_url);
        if (is_wp_error($image_data)) {
            return;
        }

        $image_content = wp_remote_retrieve_body($image_data);
        $filename = wp_unique_filename($upload_dir['path'], $image_name);
        $filepath = $upload_dir['path'] . '/' . $filename;

        file_put_contents($filepath, $image_content);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $filepath, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
        wp_update_attachment_metadata($attach_id, $attach_data);
        update_post_meta($attach_id, '_tmdb_backdrop_path', $backdrop_path);
        
        update_post_meta($post_id, '_backdrop_path', $attach_id);
    }
}

/**
 * Class Movies_API
 * Handles all interactions with The Movie Database (TMDB) API
 * Manages movie data retrieval and storage in WordPress
 */
class Movies_API {
    use Movies_Image_Handler;

    /** @var string TMDB API key from plugin settings */
    private $api_key;
    
    /** @var string Base URL for TMDB API v3 */
    private $api_base_url = 'https://api.themoviedb.org/3';

    /**
     * Constructor - Initializes the API key from WordPress options
     */
    public function __construct() {
        $options = get_option('movies_settings');
        $this->api_key = isset($options['tmdb_api_key']) ? $options['tmdb_api_key'] : '';
    }

    /**
     * Retrieves popular movies from TMDB API
     * 
     * @param int $page Page number for pagination
     * @return array|WP_Error Movie data or error object
     */
    public function get_popular_movies($page = 1) {
        $endpoint = '/trending/all/day?language=en-US';
        $params = array(
            'page' => $page
        );

        return $this->make_request($endpoint, $params);
    }

    /**
     * Retrieves detailed information for a specific movie
     * 
     * @param int $movie_id TMDB movie ID
     * @return array|WP_Error Movie details or error object
     */
    public function get_movie_details($movie_id) {
        $endpoint = "/movie/{$movie_id}";
        $params = array(
            'append_to_response' => 'genres'
        );

        return $this->make_request($endpoint, $params);
    }

    /**
     * Makes an HTTP request to the TMDB API
     * 
     * @param string $endpoint API endpoint to call
     * @param array $params Additional query parameters
     * @return array|WP_Error Response data or error object
     */
    private function make_request($endpoint, $params = array()) {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', __('TMDB API key is not configured', 'movies-manager'));
        }

        $url = add_query_arg($params, $this->api_base_url . $endpoint);

        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json'
            )
        );
        $response = wp_remote_get($url, $args);
        sleep(1);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['status_code']) && $data['status_code'] !== 200) {
            return new WP_Error(
                'tmdb_api_error',
                isset($data['status_message']) ? $data['status_message'] : __('Unknown TMDB API error', 'movies-manager')
            );
        }

        return $data;
    }

    /**
     * Saves or updates a movie in WordPress as a custom post type
     * Handles movie metadata, genres, and image attachments
     * 
     * @param array $movie_data Movie data from TMDB API
     * @return int|WP_Error Post ID or error object
     */
    public function save_movie($movie_data) {
        // Check if movie already exists
        $existing_movie = get_posts(array(
            'post_type' => 'movie',
            'meta_key' => '_tmdb_id',
            'meta_value' => $movie_data['id'],
            'posts_per_page' => 1
        ));

        $post_data = array(
            'post_title' => !empty($movie_data['title']) ? $movie_data['title'] : $movie_data['name'],
            'post_content' => $movie_data['overview'],
            'post_status' => 'publish',
            'post_type' => 'movie'
        );

        if (!empty($existing_movie)) {
            $post_id = $existing_movie[0]->ID;
            wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Save movie meta data
        update_post_meta($post_id, '_tmdb_id', $movie_data['id']);
        update_post_meta($post_id, '_release_date', $movie_data['release_date']);
        update_post_meta($post_id, '_rating', $movie_data['vote_average']);
        update_post_meta($post_id, '_poster_path', $movie_data['poster_path']);
        update_post_meta($post_id, '_backdrop_path', $movie_data['backdrop_path']);
        update_post_meta($post_id, '_budget', $movie_data['budget']);
        update_post_meta($post_id, '_vote_count', $movie_data['vote_count']);

        // Save genres as terms
        if (isset($movie_data['genres'])) {
            $genres = array();
            foreach ($movie_data['genres'] as $genre) {
                $term = term_exists($genre['name'], 'movie_category');
                if (!$term) {
                    $term = wp_insert_term($genre['name'], 'movie_category');
                }
                if (!is_wp_error($term)) {
                    $genres[] = $term['term_id'];
                }
            }
            $genres = array_map('intval', $genres);
            wp_set_object_terms($post_id, $genres, 'movie_category');
        }

        // Download and set featured image
        if (!empty($movie_data['poster_path'])) {
            $this->set_featured_image($post_id, $movie_data['poster_path']);
        }

        if (!empty($movie_data['backdrop_path'])) {
            $this->set_backdrop_image($post_id, $movie_data['backdrop_path']);
        }

        return $post_id;
    }
} 
