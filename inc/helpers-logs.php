<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Base47 – Write logs to /uploads/base47-logs/base47.log
 */
function base47_he_log( $message, $type = 'info' ) {

    $upload = wp_upload_dir();
    $dir    = trailingslashit( $upload['basedir'] ) . 'base47-logs/';

    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
    }

    $file = $dir . 'base47.log';

    $entry = sprintf(
        "[%s] [%s] %s\n",
        date("Y-m-d H:i:s"),
        strtoupper($type),
        $message
    );

    file_put_contents( $file, $entry, FILE_APPEND );
}

function base47_he_log( $message, $type = 'info' ) {

    $upload = wp_upload_dir();
    $dir    = trailingslashit( $upload['basedir'] ) . 'base47-logs/';

    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
    }

    $file = $dir . 'base47.log';

    $entry = sprintf(
        "[%s] [%s] %s\n",
        date("Y-m-d H:i:s"),
        strtoupper($type),
        $message
    );

    // Write log
    file_put_contents( $file, $entry, FILE_APPEND );

    // 🔥 Auto-trim to last 2000 lines
    base47_he_trim_logs(2000);
}


/**
 * Read logs
 */
function base47_he_get_logs() {

    $upload = wp_upload_dir();
    $file   = trailingslashit( $upload['basedir'] ) . 'base47-logs/base47.log';

    if ( ! file_exists( $file ) ) {
        return "";
    }

    return file_get_contents( $file );
}


/**
 * Clear logs
 */
function base47_he_clear_logs() {

    $upload = wp_upload_dir();
    $file   = trailingslashit( $upload['basedir'] ) . 'base47-logs/base47.log';

    if ( file_exists( $file ) ) {
        unlink( $file );
    }

    return true;
}

/**
 * Return full path to the Base47 log file.
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
 * Trim Base47 log file to last X lines (default 2000).
 */
function base47_he_trim_logs( $max_lines = 2000 ) {

    $log_file = base47_he_get_log_file();

    if ( ! file_exists( $log_file ) ) {
        return;
    }

    $lines = file( $log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

    if ( ! is_array( $lines ) ) {
        return;
    }

    $count = count( $lines );
    if ( $count <= $max_lines ) {
        return; // nothing to trim
    }

    // Keep only last N lines
    $trimmed = array_slice( $lines, -1 * $max_lines );

    file_put_contents( $log_file, implode( PHP_EOL, $trimmed ) . PHP_EOL );
}