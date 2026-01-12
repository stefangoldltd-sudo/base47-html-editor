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
 * AJAX: Duplicate template
 */
function base47_he_ajax_duplicate_template() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file     = isset( $_POST['file'] )     ? sanitize_text_field( wp_unslash( $_POST['file'] ) )     : '';
    $set      = isset( $_POST['set'] )      ? sanitize_text_field( wp_unslash( $_POST['set'] ) )      : '';
    $new_name = isset( $_POST['new_name'] ) ? sanitize_text_field( wp_unslash( $_POST['new_name'] ) ) : '';
    $content  = isset( $_POST['content'] )  ? wp_unslash( $_POST['content'] ) : '';

    if ( ! $file ) wp_send_json_error( 'Original template not specified.' );
    if ( ! $new_name ) wp_send_json_error( 'New template name not specified.' );

    // Validate new filename
    if ( ! preg_match( '/^[a-zA-Z0-9_-]+\.html?$/i', $new_name ) ) {
        wp_send_json_error( 'Invalid filename. Use only letters, numbers, hyphens, underscores, and .html extension.' );
    }

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Original template not found.' );
        $set_path = dirname( $info['path'] ) . '/';
        $theme = $info['set'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $set_path = $sets[ $set ]['path'];
        $theme = $set;
        
        $original_file = $set_path . $file;
        if ( ! file_exists( $original_file ) ) wp_send_json_error( 'Original template not found.' );
    }

    $new_file_path = $set_path . $new_name;

    // Check if new file already exists
    if ( file_exists( $new_file_path ) ) {
        wp_send_json_error( 'A template with this name already exists.' );
    }

    // Use current content from editor if provided, otherwise read from original file
    if ( empty( $content ) ) {
        $content = file_get_contents( $original_file );
        if ( false === $content ) {
            wp_send_json_error( 'Could not read original template content.' );
        }
    }

    // Create the duplicate file
    $written = file_put_contents( $new_file_path, $content );
    if ( false === $written ) {
        base47_he_log( "Failed to duplicate template: {$file} to {$new_name} (Theme: {$theme})", 'error' );
        wp_send_json_error( 'Could not create duplicate file. Check permissions.' );
    }

    // Update theme.json to include the new page
    $theme_json_path = $set_path . 'theme.json';
    if ( file_exists( $theme_json_path ) ) {
        $theme_data = json_decode( file_get_contents( $theme_json_path ), true );
        if ( $theme_data && isset( $theme_data['pages'] ) ) {
            if ( ! in_array( $new_name, $theme_data['pages'] ) ) {
                $theme_data['pages'][] = $new_name;
                file_put_contents( $theme_json_path, json_encode( $theme_data, JSON_PRETTY_PRINT ) );
            }
        }
    }

    // Log successful duplication
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "Template duplicated: {$file} to {$new_name} (Theme: {$theme}) by {$username}", 'info' );

    wp_send_json_success( [
        'message' => 'Template duplicated successfully!',
        'new_file' => $new_name,
        'redirect_url' => admin_url( 'admin.php?page=base47-he-editor&set=' . urlencode( $set ) . '&file=' . urlencode( $new_name ) )
    ] );
}
add_action( 'wp_ajax_base47_he_duplicate_template', 'base47_he_ajax_duplicate_template' );

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
