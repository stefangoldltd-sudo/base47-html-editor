<?php
/**
 * Editor AJAX Handlers
 * 
 * Handles live editor operations: get, save, and live preview
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Get template content for editing
 */
function base47_he_ajax_get_template() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set  = isset( $_POST['set'] )  ? sanitize_text_field( wp_unslash( $_POST['set'] ) )  : '';

    if ( ! $file ) wp_send_json_error( 'Template not specified.' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $set      = $info['set'];
        $full     = $info['path'];
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $full     = $sets[ $set ]['path'] . $file;
        $base_url = $sets[ $set ]['url'];
        if ( ! file_exists( $full ) ) wp_send_json_error( 'Template not found.' );
    }

    $content = file_get_contents( $full );
    $preview = base47_he_rewrite_assets( base47_he_strip_shell( $content ), $base_url, true );

    wp_send_json_success( [
        'content' => $content,
        'preview' => $preview,
        'set'     => $set,
    ] );
}
add_action( 'wp_ajax_base47_he_get_template', 'base47_he_ajax_get_template' );

/**
 * AJAX: Save template content
 */
function base47_he_ajax_save_template() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file    = isset( $_POST['file'] )    ? sanitize_text_field( wp_unslash( $_POST['file'] ) )    : '';
    $set     = isset( $_POST['set'] )     ? sanitize_text_field( wp_unslash( $_POST['set'] ) )     : '';
    $content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';

    if ( ! $file ) wp_send_json_error( 'Template not specified.' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $full = $info['path'];
        $theme = $info['set'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $full = $sets[ $set ]['path'] . $file;
        if ( ! file_exists( $full ) ) wp_send_json_error( 'Template not found.' );
        $theme = $set;
    }

    // Save backup before overwriting
    base47_he_save_backup( $full, $content, $theme );

    $written = file_put_contents( $full, $content );
    if ( false === $written ) {
        base47_he_log( "Failed to save template: {$file} (Theme: {$theme})", 'error' );
        wp_send_json_error( 'Could not write file. Check permissions.' );
    }

    // Log successful file edit
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "Template edited: {$file} (Theme: {$theme}) by {$username}", 'info' );

    wp_send_json_success( 'saved' );
}
add_action( 'wp_ajax_base47_he_save_template', 'base47_he_ajax_save_template' );

/**
 * AJAX: Live preview (real-time preview in editor)
 */
add_action( 'wp_ajax_base47_he_live_preview', function() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file    = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set     = isset( $_POST['set'] )  ? sanitize_text_field( wp_unslash( $_POST['set'] ) )  : '';
    $content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';

    if ( ! $file ) wp_send_json_error( 'No file' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $base_url = $sets[ $set ]['url'];
    }

    $html = base47_he_rewrite_assets( $content, $base_url, false );
    wp_send_json_success( [ 'html' => $html ] );
});

/**
 * AJAX: List available backups for a file
 */
function base47_he_ajax_list_backups() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set  = isset( $_POST['set'] )  ? sanitize_text_field( wp_unslash( $_POST['set'] ) )  : '';

    if ( ! $file ) wp_send_json_error( 'Template not specified.' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $full = $info['path'];
        $theme = $info['set'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $full = $sets[ $set ]['path'] . $file;
        $theme = $set;
    }

    $backups = base47_he_list_backups( $full, $theme );
    wp_send_json_success( $backups );
}
add_action( 'wp_ajax_base47_he_list_backups', 'base47_he_ajax_list_backups' );

/**
 * AJAX: Restore a backup
 */
function base47_he_ajax_restore_backup() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file           = isset( $_POST['file'] )           ? sanitize_text_field( wp_unslash( $_POST['file'] ) )           : '';
    $set            = isset( $_POST['set'] )            ? sanitize_text_field( wp_unslash( $_POST['set'] ) )            : '';
    $backup_filename = isset( $_POST['backup_filename'] ) ? sanitize_text_field( wp_unslash( $_POST['backup_filename'] ) ) : '';

    if ( ! $file || ! $backup_filename ) wp_send_json_error( 'Missing parameters.' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $full = $info['path'];
        $theme = $info['set'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $full = $sets[ $set ]['path'] . $file;
        $theme = $set;
    }

    $content = base47_he_restore_backup( $backup_filename, $theme, $full );
    
    if ( false === $content ) {
        base47_he_log( "Failed to restore backup: {$backup_filename} for {$file} (Theme: {$theme})", 'error' );
        wp_send_json_error( 'Backup not found.' );
    }

    // Log successful backup restore
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "Backup restored: {$file} from {$backup_filename} (Theme: {$theme}) by {$username}", 'info' );

    wp_send_json_success( [ 'content' => $content ] );
}
add_action( 'wp_ajax_base47_he_ajax_restore_backup', 'base47_he_ajax_restore_backup' );

/**
 * AJAX: Download a backup file
 */
function base47_he_ajax_download_backup() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file           = isset( $_GET['file'] )           ? sanitize_text_field( wp_unslash( $_GET['file'] ) )           : '';
    $set            = isset( $_GET['set'] )            ? sanitize_text_field( wp_unslash( $_GET['set'] ) )            : '';
    $backup_filename = isset( $_GET['backup_filename'] ) ? sanitize_text_field( wp_unslash( $_GET['backup_filename'] ) ) : '';
    $nonce          = isset( $_GET['nonce'] )          ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) )          : '';

    if ( ! wp_verify_nonce( $nonce, 'base47_he' ) ) {
        wp_die( 'Security check failed.' );
    }

    if ( ! $file || ! $backup_filename ) {
        wp_die( 'Missing parameters.' );
    }

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_die( 'Template not found.' );
        $full = $info['path'];
        $theme = $info['set'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_die( 'Template set not found.' );
        $full = $sets[ $set ]['path'] . $file;
        $theme = $set;
    }

    base47_he_download_backup( $backup_filename, $theme, $full );
}
add_action( 'wp_ajax_base47_he_download_backup', 'base47_he_ajax_download_backup' );
