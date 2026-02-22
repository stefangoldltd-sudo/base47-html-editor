<?php
/**
 * License AJAX Handlers
 * 
 * Handles license activation and deactivation via AJAX.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.2.6
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Activate License
 * 
 * WordPress.org Free Version: License features not available.
 * For Pro version with license activation, visit: https://47-studio.com
 */
add_action( 'wp_ajax_base47_activate_license', 'base47_he_ajax_activate_license' );
function base47_he_ajax_activate_license() {
    // Verify nonce
    check_ajax_referer( 'base47_he', 'nonce' );
    
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied' ] );
    }
    
    // WordPress.org free version - license features not available
    wp_send_json_error( [ 
        'message' => 'License activation is available in the Pro version. Visit https://47-studio.com for more information.',
        'upgrade_url' => 'https://47-studio.com/base47-html-editor-pro/'
    ] );
}

/**
 * AJAX: Deactivate License
 * 
 * WordPress.org Free Version: License features not available.
 */
add_action( 'wp_ajax_base47_deactivate_license', 'base47_he_ajax_deactivate_license' );
function base47_he_ajax_deactivate_license() {
    // Verify nonce
    check_ajax_referer( 'base47_he', 'nonce' );
    
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied' ] );
    }
    
    // WordPress.org free version - license features not available
    wp_send_json_error( [ 
        'message' => 'License management is available in the Pro version.',
        'upgrade_url' => 'https://47-studio.com/base47-html-editor-pro/'
    ] );
}

/**
 * Daily License Verification (Cron)
 * 
 * WordPress.org Free Version: No license verification needed.
 */
function base47_he_verify_license() {
    // WordPress.org free version - no license verification
    return;
}

