<?php
/**
 * Asset Mode AJAX Handler
 * 
 * Handles saving asset mode (loader/manifest/smart) for themes
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.3.2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Set asset mode for a theme
 */
function base47_he_ajax_set_asset_mode() {
    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $theme = isset( $_POST['theme'] ) ? sanitize_key( $_POST['theme'] ) : '';
    $mode  = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : '';

    if ( empty( $theme ) || empty( $mode ) ) {
        wp_send_json_error( [ 'message' => 'Missing theme or mode.' ] );
    }

    // Valid modes: loader, manifest, smart
    if ( ! in_array( $mode, [ 'loader', 'manifest', 'smart' ], true ) ) {
        wp_send_json_error( [ 'message' => 'Invalid mode.' ] );
    }

    // Get current options
    $use_manifest = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );
    $use_smart    = get_option( BASE47_HE_OPT_USE_SMART_LOADER, [] );

    if ( ! is_array( $use_manifest ) ) {
        $use_manifest = [];
    }
    if ( ! is_array( $use_smart ) ) {
        $use_smart = [];
    }

    // Remove theme from both arrays first
    $use_manifest = array_diff( $use_manifest, [ $theme ] );
    $use_smart    = array_diff( $use_smart, [ $theme ] );

    // Add to appropriate array based on mode
    if ( $mode === 'manifest' ) {
        $use_manifest[] = $theme;
    } elseif ( $mode === 'smart' ) {
        $use_smart[] = $theme;
    }
    // If 'loader', leave both arrays without this theme (default behavior)

    // Save options
    update_option( BASE47_HE_OPT_USE_MANIFEST, array_values( array_unique( $use_manifest ) ) );
    update_option( BASE47_HE_OPT_USE_SMART_LOADER, array_values( array_unique( $use_smart ) ) );

    wp_send_json_success( [
        'theme' => $theme,
        'mode'  => $mode,
        'manifest_themes' => $use_manifest,
        'smart_themes'    => $use_smart,
    ] );
}
add_action( 'wp_ajax_base47_set_asset_mode', 'base47_he_ajax_set_asset_mode' );
