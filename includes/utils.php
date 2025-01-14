<?php
/**
 * Class Movies_Utils
 * Utility functions for the Movies plugin
 * Handles template loading and path resolution
 */
class Movies_Utils {
    /**
     * Loads a template part from the plugin directory
     * Similar to WordPress get_template_part() but for plugin templates
     *
     * @param string $slug The slug name for the generic template
     * @param string|null $name The name of the specialized template
     * @param array $args Additional arguments passed to the template
     */
    public static function get_template_part($slug, $name = null, $args = []) {
        do_action("ccm_get_template_part_{$slug}", $slug, $name);

        $templates = array();
        if (isset($name)) {
            $templates[] = "{$slug}-{$name}.php";
        }

        $templates[] = "{$slug}.php";

        self::get_template_path($templates, true, false, $args);
    }

    /**
     * Locates a template file in the plugin directory
     * Extended version of WordPress locate_template() for plugin use
     *
     * @param string|array $template_names Template file(s) to search for
     * @param bool $load If true, the template will be loaded if found
     * @param bool $require_once Whether to require_once or require the template
     * @param array $args Additional arguments passed to the template
     * @return string The path of the found template file
     */
    public static function get_template_path($template_names, $load = false, $require_once = true, $args = []) {
        $located = '';
        foreach ((array) $template_names as $template_name) {
            if (!$template_name) {
                continue;
            }

            /* search file within the PLUGIN_DIR_PATH only */
            if (file_exists(MOVIES_PLUGIN_PATH . $template_name)) {
                $located = MOVIES_PLUGIN_PATH . $template_name;
                break;
            }
        }

        if ($load && '' != $located) {
            load_template($located, $require_once, $args);
        }

        return $located;
    }
}