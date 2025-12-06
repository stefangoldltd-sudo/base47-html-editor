<?php
/**
 * Uninstall Script for Base47 HTML Editor
 * 
 * This file is executed when the plugin is deleted via WordPress admin.
 * It cleans up all plugin data from the database.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.8
 */

// Exit if accessed directly or not uninstalling
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Clean up plugin options
 */
function base47_he_uninstall_cleanup() {
    
    // List of all plugin options
    $options = [
        'base47_active_themes',
        'base47_use_manifest',
        'base47_he_use_smart_loader',
        'base47_default_theme',
        'base47_he_editor_mode',
        'base47_he_editor_theme',
        'base47_he_cache_enabled',
        'base47_he_cache_ttl',
        'base47_he_minify_enabled',
        'base47_he_version',
    ];
    
    // Delete each option
    foreach ( $options as $option ) {
        delete_option( $option );
    }
    
    // Clean up transients (cached data)
    global $wpdb;
    
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_base47_he_%' 
         OR option_name LIKE '_transient_timeout_base47_he_%'"
    );
    
    // Clean up user meta (if any)
    $wpdb->query(
        "DELETE FROM {$wpdb->usermeta} 
         WHERE meta_key LIKE 'base47_he_%'"
    );
}

/**
 * Optional: Clean up backup files
 * 
 * Uncomment this if you want to delete backup files on uninstall.
 * By default, we keep backups for safety.
 */
function base47_he_uninstall_delete_backups() {
    
    $upload_dir = wp_upload_dir();
    $backup_dir = trailingslashit( $upload_dir['basedir'] ) . 'base47-backups';
    
    if ( is_dir( $backup_dir ) ) {
        // Recursively delete backup directory
        base47_he_uninstall_rmdir_recursive( $backup_dir );
    }
}

/**
 * Recursively delete directory
 */
function base47_he_uninstall_rmdir_recursive( $dir ) {
    
    if ( ! is_dir( $dir ) ) {
        return false;
    }
    
    $files = array_diff( scandir( $dir ), [ '.', '..' ] );
    
    foreach ( $files as $file ) {
        $path = $dir . '/' . $file;
        
        if ( is_dir( $path ) ) {
            base47_he_uninstall_rmdir_recursive( $path );
        } else {
            @unlink( $path );
        }
    }
    
    return @rmdir( $dir );
}

/**
 * Optional: Clean up log files
 */
function base47_he_uninstall_delete_logs() {
    
    $upload_dir = wp_upload_dir();
    $log_file = trailingslashit( $upload_dir['basedir'] ) . 'base47-logs/base47-he.log';
    
    if ( file_exists( $log_file ) ) {
        @unlink( $log_file );
    }
    
    $log_dir = trailingslashit( $upload_dir['basedir'] ) . 'base47-logs';
    if ( is_dir( $log_dir ) && count( scandir( $log_dir ) ) === 2 ) {
        @rmdir( $log_dir );
    }
}

// Execute cleanup
base47_he_uninstall_cleanup();

// Optional: Uncomment to delete backups and logs on uninstall
// base47_he_uninstall_delete_backups();
// base47_he_uninstall_delete_logs();

// Log uninstall event (if logging is still available)
if ( function_exists( 'error_log' ) ) {
    error_log( 'Base47 HTML Editor: Plugin uninstalled and data cleaned up.' );
}

