<?php
/**
 * Theme Manager AJAX Handlers
 * 
 * Handles theme operations: toggle, uninstall, set default
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Toggle theme active/inactive
 */
function base47_he_ajax_toggle_theme() {

    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( 'You are not allowed to do this.', 'base47' ) );
    }

    $theme  = isset( $_POST['theme'] ) ? sanitize_key( wp_unslash( $_POST['theme'] ) ) : '';
    $active = isset( $_POST['active'] ) ? (int) $_POST['active'] : 0;

    $themes = base47_he_get_template_sets();

    if ( ! isset( $themes[ $theme ] ) ) {
        wp_send_json_error( __( 'Unknown theme.', 'base47' ) );
    }

    $active_themes = get_option( 'base47_active_themes', [] );
    if ( ! is_array( $active_themes ) ) {
        $active_themes = [];
    }

    if ( $active ) {
        if ( ! in_array( $theme, $active_themes, true ) ) {
            $active_themes[] = $theme;
        }
    } else {
        $active_themes = array_values( array_diff( $active_themes, [ $theme ] ) );
    }

    update_option( 'base47_active_themes', $active_themes );

    // Log theme toggle
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    $status = $active ? 'activated' : 'deactivated';
    base47_he_log( "Theme {$status}: {$theme} by {$username}", 'info' );

    wp_send_json_success( [
        'theme'         => $theme,
        'active'        => $active,
        'active_themes' => $active_themes,
    ] );
}
add_action( 'wp_ajax_base47_toggle_theme', 'base47_he_ajax_toggle_theme' );

/**
 * AJAX: Uninstall a theme (delete folder + cleanup options)
 */
function base47_he_ajax_uninstall_theme() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $slug = isset( $_POST['theme'] ) ? sanitize_key( $_POST['theme'] ) : '';

    if ( empty( $slug ) ) {
        wp_send_json_error( [ 'message' => 'Missing theme slug.' ] );
    }

    $themes = base47_he_get_template_sets();

    if ( ! isset( $themes[ $slug ] ) || empty( $themes[ $slug ]['path'] ) ) {
        wp_send_json_error( [ 'message' => 'Theme not found.' ] );
    }

    $theme_path = $themes[ $slug ]['path'];

    base47_he_rrmdir( $theme_path );

    // Remove from "active themes" option
    $active_themes = get_option( 'base47_active_themes', [] );
    if ( is_array( $active_themes ) ) {
        $active_themes = array_diff( $active_themes, [ $slug ] );
        update_option( 'base47_active_themes', array_values( $active_themes ) );
    }

    // Remove from "use manifest" option
    $use_manifest = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );
    if ( is_array( $use_manifest ) ) {
        $use_manifest = array_diff( $use_manifest, [ $slug ] );
        update_option( BASE47_HE_OPT_USE_MANIFEST, array_values( $use_manifest ) );
    }

    // Log theme uninstall
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "Theme uninstalled: {$slug} by {$username}", 'warning' );

    wp_send_json_success( [ 'message' => 'Theme uninstalled.', 'slug' => $slug ] );
}
add_action( 'wp_ajax_base47_he_uninstall_theme', 'base47_he_ajax_uninstall_theme' );

/**
 * AJAX: Set default theme
 */
function base47_he_ajax_set_default_theme() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( empty($_POST['theme']) ) {
        wp_send_json_error('Missing theme');
    }

    $theme = sanitize_text_field($_POST['theme']);

    update_option('base47_default_theme', $theme);

    // Log default theme change
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "Default theme set: {$theme} by {$username}", 'info' );

    wp_send_json_success(['saved' => $theme]);
}
add_action('wp_ajax_base47_set_default_theme', 'base47_he_ajax_set_default_theme');
