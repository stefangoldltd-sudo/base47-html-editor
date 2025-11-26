<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Base47_Theme_Manager {

    /**
     * Rebuild all caches (sets + templates).
     */
    public static function refresh() {
        require_once BASE47_HE_PATH . 'inc/class-base47-cache.php';
        Base47_Cache::clear_all();
        base47_he_get_template_sets( true );
        base47_he_get_template_list( true );
    }

    /**
     * Delete a theme folder by slug, then refresh caches.
     * Example slug: "mivon-templates"
     */
    public static function delete_theme( $slug ) {
        $path = BASE47_HE_PATH . $slug;
        if ( is_dir( $path ) ) {
            self::rrmdir( $path );
        }
        self::refresh();
    }

    /**
     * Recursively remove a directory.
     */
    private static function rrmdir( $dir ) {
        foreach ( glob( $dir . '/*' ) as $item ) {
            if ( is_dir( $item ) ) {
                self::rrmdir( $item );
            } else {
                @unlink( $item );
            }
        }
        @rmdir( $dir );
    }
}