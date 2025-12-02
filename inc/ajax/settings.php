<?php
/**
 * Settings AJAX Handlers
 * 
 * Handles AJAX requests for settings page actions.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.4.5
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Clear all caches
 */
function base47_he_ajax_clear_all_caches() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }
    
    // Clear theme caches
    base47_he_refresh_theme_caches();
    
    // Clear WordPress transients
    delete_transient( 'base47_he_cache_template_sets' );
    delete_transient( 'base47_he_cache_templates' );
    
    wp_send_json_success( [ 'message' => 'All caches cleared successfully.' ] );
}
add_action( 'wp_ajax_base47_clear_all_caches', 'base47_he_ajax_clear_all_caches' );

/**
 * AJAX: Clear logs
 */
function base47_he_ajax_clear_logs() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }
    
    $log_dir = BASE47_HE_PATH . 'logs/';
    
    if ( ! is_dir( $log_dir ) ) {
        wp_send_json_success( [ 'message' => 'No logs to clear.' ] );
    }
    
    $files = glob( $log_dir . '*.log' );
    $deleted = 0;
    
    foreach ( $files as $file ) {
        if ( is_file( $file ) ) {
            unlink( $file );
            $deleted++;
        }
    }
    
    wp_send_json_success( [ 
        'message' => sprintf( 'Cleared %d log file(s).', $deleted ),
        'deleted' => $deleted
    ] );
}
add_action( 'wp_ajax_base47_clear_logs', 'base47_he_ajax_clear_logs' );

/**
 * AJAX: Download logs as ZIP
 */
function base47_he_ajax_download_logs() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Insufficient permissions.' );
    }
    
    $log_dir = BASE47_HE_PATH . 'logs/';
    
    if ( ! is_dir( $log_dir ) ) {
        wp_die( 'No logs found.' );
    }
    
    $files = glob( $log_dir . '*.log' );
    
    if ( empty( $files ) ) {
        wp_die( 'No log files to download.' );
    }
    
    // Create ZIP file
    $zip_file = sys_get_temp_dir() . '/base47-logs-' . time() . '.zip';
    $zip = new ZipArchive();
    
    if ( $zip->open( $zip_file, ZipArchive::CREATE ) !== true ) {
        wp_die( 'Failed to create ZIP file.' );
    }
    
    foreach ( $files as $file ) {
        $zip->addFile( $file, basename( $file ) );
    }
    
    $zip->close();
    
    // Send ZIP file
    header( 'Content-Type: application/zip' );
    header( 'Content-Disposition: attachment; filename="base47-logs-' . date( 'Y-m-d' ) . '.zip"' );
    header( 'Content-Length: ' . filesize( $zip_file ) );
    
    readfile( $zip_file );
    unlink( $zip_file );
    
    exit;
}
add_action( 'wp_ajax_base47_download_logs', 'base47_he_ajax_download_logs' );

/**
 * AJAX: Reset settings to defaults
 */
function base47_he_ajax_reset_settings() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }
    
    if ( base47_he_reset_settings() ) {
        wp_send_json_success( [ 'message' => 'Settings reset to defaults.' ] );
    } else {
        wp_send_json_error( [ 'message' => 'Failed to reset settings.' ] );
    }
}
add_action( 'wp_ajax_base47_reset_settings', 'base47_he_ajax_reset_settings' );
