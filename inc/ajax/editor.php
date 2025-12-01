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
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $full = $sets[ $set ]['path'] . $file;
        if ( ! file_exists( $full ) ) wp_send_json_error( 'Template not found.' );
    }

    $written = file_put_contents( $full, $content );
    if ( false === $written ) wp_send_json_error( 'Could not write file. Check permissions.' );

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
