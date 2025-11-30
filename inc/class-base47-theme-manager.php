<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Theme Manager – install, delete, refresh theme sets
 */
class Base47_Theme_Manager {

    /**
     * Rebuild all caches (sets + templates)
     */
    public static function refresh() {
        require_once BASE47_HE_PATH . 'inc/class-base47-cache.php';

        Base47_Cache::clear_all();          // Clear all plugin caches
        base47_he_get_template_sets(true);  // Re-scan theme folders
        base47_he_get_template_list(true);  // Re-scan template files

        // Log action
        if ( function_exists('base47_he_log') ) {
            base47_he_log('Theme caches rebuilt.', 'info');
        }
    }

    /**
     * Delete a theme folder from uploads/base47-themes/{slug}
     */
    public static function delete_theme($slug) {

        // Root themes folder
        $root = base47_he_get_themes_root(); 
        $themes_dir = trailingslashit($root['dir']);

        // Full path to target
        $path = realpath($themes_dir . $slug);

        // Validations
        if ( ! $path || ! is_dir($path) ) {
            base47_he_log("Delete failed – theme not found: {$slug}", "error");
            return false;
        }

        // Prevent deleting outside safe directory
        $root_real = realpath($themes_dir);
        if ( strpos($path, $root_real) !== 0 ) {
            base47_he_log("SECURITY BLOCK: Attempt to delete outside base47-themes", "error");
            return false;
        }

        // Delete recursively
        self::rrmdir($path);

        // Refresh caches
        self::refresh();

        base47_he_log("Theme deleted: {$slug}", "info");
        return true;
    }

    /**
     * Recursively delete a folder
     */
    private static function rrmdir($dir) {
        foreach (glob($dir . '/*') as $item) {
            if (is_dir($item)) {
                self::rrmdir($item);
            } else {
                @unlink($item);
            }
        }
        @rmdir($dir);
    }
}