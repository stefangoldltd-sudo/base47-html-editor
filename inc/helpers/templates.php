<?php
/**
 * Template Helper Functions
 * 
 * Functions for counting and managing templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Count HTML templates in a theme folder
 * 
 * @param string $folder_name Theme folder slug
 * @return int Number of HTML templates found
 */
function base47_he_count_theme_templates( $folder_name ) {

    $sets = base47_he_get_template_sets();

    if ( ! isset( $sets[ $folder_name ] ) ) {
        return 0;
    }

    $dir = trailingslashit( $sets[ $folder_name ]['path'] );

    if ( ! is_dir( $dir ) ) {
        return 0;
    }

    $files = glob( $dir . '*.html' );
    if ( ! is_array( $files ) ) {
        return 0;
    }

    return count( $files );
}
