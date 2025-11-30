<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get full path to the Base47 log file.
 */
function base47_he_get_log_file() {
    $upload = wp_upload_dir();
    $dir    = trailingslashit( $upload['basedir'] ) . 'base47-logs/';

    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
    }

    return $dir . 'base47.log';
}

/**
 * Write log entry
 */
function base47_he_log( $message, $type = 'info' ) {

    $file = base47_he_get_log_file();

    $entry = sprintf(
        "[%s] [%s] %s\n",
        date("Y-m-d H:i:s"),
        strtoupper($type),
        $message
    );

    file_put_contents( $file, $entry, FILE_APPEND );

    base47_he_trim_logs( 2000 );
}

/**
 * Read logs
 */
function base47_he_get_logs() {

    $file = base47_he_get_log_file();

    if ( ! file_exists( $file ) ) {
        return "";
    }

    return file_get_contents( $file );
}

/**
 * Clear logs
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
 */
function base47_he_trim_logs( $max_lines = 2000 ) {

    $file = base47_he_get_log_file();

    if ( ! file_exists( $file ) ) return;

    $lines = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

    if ( ! is_array( $lines ) ) return;

    $count = count( $lines );
    if ( $count <= $max_lines ) return;

    $trimmed = array_slice( $lines, -$max_lines );

    file_put_contents( $file, implode( PHP_EOL, $trimmed ) . PHP_EOL );
}