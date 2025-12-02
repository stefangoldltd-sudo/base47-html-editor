<?php
/**
 * Theme Deletion Operations
 * 
 * Handles theme folder deletion with safety checks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Delete a theme folder from the uploads/base47-themes directory
 * 
 * @param string $slug Theme slug (e.g. 'lezar-templates')
 * @return true|WP_Error True on success, WP_Error on failure
 */
function base47_he_delete_theme_folder( $slug ) {

    $root = base47_he_get_themes_root();
    $themes_dir = $root['dir'];

    $target = realpath( $themes_dir . $slug );

    if ( ! $target || ! is_dir( $target ) ) {
        return new WP_Error( 'not_found', 'Theme set not found.' );
    }

    // Safety: ensure we ONLY delete inside base47-themes directory
    $themes_root_real = realpath( $themes_dir );
    if ( strpos( $target, $themes_root_real ) !== 0 ) {
        return new WP_Error( 'unsafe_path', 'Refusing to delete outside theme directory.' );
    }

    if ( ! base47_he_rrmdir( $target ) ) {
        return new WP_Error( 'delete_failed', 'Could not delete theme folder. Check permissions.' );
    }

    return true;
}

/**
 * Recursive directory deletion helper
 * 
 * @param string $dir Directory path to delete
 * @return bool True on success, false on failure
 */
function base47_he_rrmdir( $dir ) {
    if ( ! is_dir( $dir ) ) {
        return true;
    }

    $items = scandir( $dir );
    if ( ! $items ) {
        return false;
    }

    foreach ( $items as $item ) {
        if ( $item === '.' || $item === '..' ) {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if ( is_dir( $path ) ) {
            if ( ! base47_he_rrmdir( $path ) ) {
                return false;
            }
        } else {
            if ( ! @unlink( $path ) ) {
                return false;
            }
        }
    }

    return @rmdir( $dir );
}
