<?php
/**
 * Preview AJAX Handlers
 * 
 * Handles template preview operations
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Lazy preview for shortcodes page
 */
function base47_he_ajax_lazy_preview() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file = isset($_POST['file']) ? sanitize_text_field( wp_unslash($_POST['file']) ) : '';
    $set  = isset($_POST['set'])  ? sanitize_text_field( wp_unslash($_POST['set']) )  : '';

    if ( ! $file ) {
        wp_send_json_error( 'Missing file parameter.' );
    }

    $sets = base47_he_get_template_sets();

    // Auto-detect set if none provided
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) {
            wp_send_json_error( 'Template not found.' );
        }
        $set      = $info['set'];
        $full     = $info['path'];
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[$set] ) ) {
            wp_send_json_error( 'Invalid template set.' );
        }
        $full     = $sets[$set]['path'] . $file;
        $base_url = $sets[$set]['url'];
        if ( ! file_exists( $full ) ) {
            wp_send_json_error( 'Template file not found.' );
        }
    }

    $html = file_get_contents( $full );
    if ( false === $html ) {
        wp_send_json_error( 'Failed reading template.' );
    }

    // Rewrite asset URLs
    $html = base47_he_rewrite_assets( $html, $base_url, true );

    wp_send_json_success( [
        'html' => $html,
        'set'  => $set,
        'file' => $file,
    ] );
}
add_action( 'wp_ajax_base47_he_lazy_preview', 'base47_he_ajax_lazy_preview' );
add_action( 'wp_ajax_nopriv_base47_he_lazy_preview', 'base47_he_ajax_lazy_preview' );

/**
 * AJAX: Full template preview
 */
function base47_he_ajax_preview() {

    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file = isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : '';
    $set  = isset( $_GET['set'] )  ? sanitize_text_field( wp_unslash( $_GET['set'] ) )  : '';

    if ( ! $file ) wp_die( 'Template not specified.' );

    $active = get_option( 'base47_active_themes', [] );
    if ( ! is_array( $active ) ) {
        $active = [];
    }

    // Fallback if nothing active
    if ( empty( $active ) ) {
        $sets = base47_he_get_template_sets();
        $active = [ array_key_first( $sets ) ];
    }

    // If set empty, use the first ACTIVE set
    if ( empty( $set ) ) {
        $set = $active[0];
    }

    $sets = base47_he_get_template_sets();

    // Validate chosen set
    if ( ! isset( $sets[ $set ] ) ) {
        wp_die( 'Template set not found.' );
    }

    // Build full path
    $full = $sets[ $set ]['path'] . $file;
    $base_url = $sets[ $set ]['url'];

    if ( ! file_exists( $full ) ) wp_die( 'Template not found.' );

    // Process HTML
    $html = file_get_contents( $full );
    $html = base47_he_rewrite_assets( $html, $base_url, true );

    echo $html;
    exit;
}
add_action( 'wp_ajax_base47_he_preview',        'base47_he_ajax_preview' );
add_action( 'wp_ajax_nopriv_base47_he_preview', 'base47_he_ajax_preview' );

/**
 * Helper: Detect default theme (for JS/editor)
 */
function base47_he_detect_default_theme() {
    $sets = base47_he_get_template_sets();
    return array_key_first($sets) ?: '';
}
