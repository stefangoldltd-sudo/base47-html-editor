<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Base47 Logging System
 * 
 * Provides simple file-based logging for debugging and monitoring.
 * Logs are stored in /wp-content/uploads/base47-logs/base47.log
 */

/**
 * Ensure logs folder exists
 */
function base47_he_logs_init() {
    $uploads = wp_upload_dir();
    $dir = trailingslashit( $uploads['basedir'] ) . 'base47-logs/';
    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
    }
}
add_action( 'init', 'base47_he_logs_init' );

/**
 * Get log file path
 * 
 * @return string Full path to log file
 */
function base47_he_get_log_file() {
    $uploads = wp_upload_dir();
    $dir     = trailingslashit( $uploads['basedir'] ) . 'base47-logs/';

    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
    }

    return $dir . 'base47.log';
}

/**
 * Write log entry
 * 
 * @param string $message Log message
 * @param string $type    Log type (info, error, warning)
 */
function base47_he_log( $message, $type = 'info' ) {

    $file = base47_he_get_log_file();

    $entry = sprintf(
        "[%s] [%s] %s\n",
        date("Y-m-d H:i:s"),
        strtoupper($type),
        $message
    );

    // Check file size before writing (max 5MB)
    if ( file_exists( $file ) && filesize( $file ) > 5 * 1024 * 1024 ) {
        base47_he_trim_logs( 1000 ); // Aggressive trim
    }

    file_put_contents( $file, $entry, FILE_APPEND );

    // Regular trim to keep file manageable
    base47_he_trim_logs( 2000 );
}

/**
 * Read all logs
 * 
 * @return string Log contents
 */
function base47_he_get_logs() {
    $file = base47_he_get_log_file();
    if ( ! file_exists( $file ) ) {
        return "";
    }
    return file_get_contents( $file );
}

/**
 * Clear all logs
 * 
 * @return bool Success
 */
function base47_he_clear_logs() {
    $file = base47_he_get_log_file();
    if ( file_exists( $file ) ) {
        unlink( $file );
    }
    return true;
}

/**
 * Trim log file to last X lines
 * 
 * @param int $max_lines Maximum number of lines to keep
 */
function base47_he_trim_logs( $max_lines = 2000 ) {

    $file = base47_he_get_log_file();
    if ( ! file_exists( $file ) ) return;

    $lines = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    if ( ! is_array( $lines ) ) return;

    if ( count( $lines ) <= $max_lines ) return;

    $trimmed = array_slice( $lines, -$max_lines );
    file_put_contents( $file, implode( PHP_EOL, $trimmed ) . PHP_EOL );
}
