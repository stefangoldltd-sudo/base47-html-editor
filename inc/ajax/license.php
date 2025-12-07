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
 * Validates license key and activates Pro features.
 */
add_action( 'wp_ajax_base47_activate_license', 'base47_he_ajax_activate_license' );
function base47_he_ajax_activate_license() {
    // Verify nonce
    check_ajax_referer( 'base47_he', 'nonce' );
    
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied' ] );
    }
    
    // Get license key
    $license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( $_POST['license_key'] ) : '';
    
    if ( empty( $license_key ) ) {
        wp_send_json_error( [ 'message' => 'License key is required' ] );
    }
    
    // Get current domain
    $domain = parse_url( home_url(), PHP_URL_HOST );
    
    // ========================================
    // TODO: REPLACE WITH YOUR API VALIDATION
    // ========================================
    
    // For now, accept any key starting with "PRO-" or "DEV-"
    // This is for TESTING ONLY - replace with real API call
    
    if ( strpos( $license_key, 'PRO-' ) === 0 || strpos( $license_key, 'DEV-' ) === 0 ) {
        // Simulate successful activation
        update_option( 'base47_he_license_key', $license_key );
        update_option( 'base47_he_license_status', 'active' );
        update_option( 'base47_he_license_data', [
            'plan' => 'Unlimited',
            'expires' => date( 'Y-m-d', strtotime( '+1 year' ) ),
            'status' => 'active',
            'activated_at' => current_time( 'mysql' ),
            'domain' => $domain,
        ] );
        
        wp_send_json_success( [ 
            'message' => 'License activated successfully!',
            'plan' => 'Unlimited',
            'expires' => date( 'Y-m-d', strtotime( '+1 year' ) ),
        ] );
    } else {
        wp_send_json_error( [ 'message' => 'Invalid license key format' ] );
    }
    
    /*
    // ========================================
    // PRODUCTION CODE (Uncomment when ready)
    // ========================================
    
    // Call your license API
    $api_url = 'https://47-studio.com/api/licenses/activate';
    
    $response = wp_remote_post( $api_url, [
        'body' => [
            'license_key' => $license_key,
            'domain' => $domain,
            'product' => 'base47-html-editor',
            'version' => BASE47_HE_VERSION,
        ],
        'timeout' => 15,
    ] );
    
    // Check for errors
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( [ 'message' => 'Connection error: ' . $response->get_error_message() ] );
    }
    
    // Parse response
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    // Check if activation was successful
    if ( isset( $data['success'] ) && $data['success'] === true ) {
        // Save license data
        update_option( 'base47_he_license_key', $license_key );
        update_option( 'base47_he_license_status', 'active' );
        update_option( 'base47_he_license_data', [
            'plan' => $data['plan'] ?? 'Pro',
            'expires' => $data['expires'] ?? 'Never',
            'status' => 'active',
            'activated_at' => current_time( 'mysql' ),
            'domain' => $domain,
        ] );
        
        wp_send_json_success( [ 
            'message' => 'License activated successfully!',
            'plan' => $data['plan'] ?? 'Pro',
            'expires' => $data['expires'] ?? 'Never',
        ] );
    } else {
        // Activation failed
        $error_message = $data['message'] ?? 'License activation failed';
        wp_send_json_error( [ 'message' => $error_message ] );
    }
    */
}

/**
 * AJAX: Deactivate License
 * 
 * Deactivates license and removes Pro features.
 */
add_action( 'wp_ajax_base47_deactivate_license', 'base47_he_ajax_deactivate_license' );
function base47_he_ajax_deactivate_license() {
    // Verify nonce
    check_ajax_referer( 'base47_he', 'nonce' );
    
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied' ] );
    }
    
    // Get current license key
    $license_key = get_option( 'base47_he_license_key', '' );
    
    if ( empty( $license_key ) ) {
        wp_send_json_error( [ 'message' => 'No license key found' ] );
    }
    
    // Get current domain
    $domain = parse_url( home_url(), PHP_URL_HOST );
    
    // ========================================
    // TODO: REPLACE WITH YOUR API DEACTIVATION
    // ========================================
    
    // For now, just delete local options
    // This is for TESTING ONLY - replace with real API call
    
    delete_option( 'base47_he_license_key' );
    delete_option( 'base47_he_license_status' );
    delete_option( 'base47_he_license_data' );
    
    wp_send_json_success( [ 'message' => 'License deregistered successfully' ] );
    
    /*
    // ========================================
    // PRODUCTION CODE (Uncomment when ready)
    // ========================================
    
    // Call your license API
    $api_url = 'https://47-studio.com/api/licenses/deactivate';
    
    $response = wp_remote_post( $api_url, [
        'body' => [
            'license_key' => $license_key,
            'domain' => $domain,
            'product' => 'base47-html-editor',
        ],
        'timeout' => 15,
    ] );
    
    // Even if API call fails, deactivate locally
    delete_option( 'base47_he_license_key' );
    delete_option( 'base47_he_license_status' );
    delete_option( 'base47_he_license_data' );
    
    // Check API response
    if ( is_wp_error( $response ) ) {
        wp_send_json_success( [ 
            'message' => 'License deregistered locally (API unreachable)',
            'warning' => true,
        ] );
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( isset( $data['success'] ) && $data['success'] === true ) {
        wp_send_json_success( [ 'message' => 'License deregistered successfully' ] );
    } else {
        wp_send_json_success( [ 
            'message' => 'License deregistered locally',
            'warning' => true,
        ] );
    }
    */
}

/**
 * Daily License Verification (Cron)
 * 
 * Verifies license is still valid with API.
 * Called by wp-cron daily.
 */
function base47_he_verify_license() {
    $license_key = get_option( 'base47_he_license_key', '' );
    $license_status = get_option( 'base47_he_license_status', 'inactive' );
    
    // Skip if no license or already inactive
    if ( empty( $license_key ) || $license_status !== 'active' ) {
        return;
    }
    
    // Get current domain
    $domain = parse_url( home_url(), PHP_URL_HOST );
    
    // ========================================
    // TODO: REPLACE WITH YOUR API VERIFICATION
    // ========================================
    
    // For now, do nothing (keep license active)
    // This is for TESTING ONLY - replace with real API call
    
    /*
    // ========================================
    // PRODUCTION CODE (Uncomment when ready)
    // ========================================
    
    // Call your license API
    $api_url = 'https://47-studio.com/api/licenses/verify';
    
    $response = wp_remote_post( $api_url, [
        'body' => [
            'license_key' => $license_key,
            'domain' => $domain,
            'product' => 'base47-html-editor',
            'version' => BASE47_HE_VERSION,
        ],
        'timeout' => 15,
    ] );
    
    // If API is unreachable, keep current status (grace period)
    if ( is_wp_error( $response ) ) {
        return;
    }
    
    // Parse response
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    // Check if license is still valid
    if ( isset( $data['valid'] ) && $data['valid'] === true ) {
        // Update license data
        $license_data = get_option( 'base47_he_license_data', [] );
        $license_data['expires'] = $data['expires'] ?? $license_data['expires'];
        $license_data['status'] = 'active';
        $license_data['last_check'] = current_time( 'mysql' );
        update_option( 'base47_he_license_data', $license_data );
    } else {
        // License is invalid - deactivate
        update_option( 'base47_he_license_status', 'inactive' );
        
        $license_data = get_option( 'base47_he_license_data', [] );
        $license_data['status'] = 'inactive';
        $license_data['deactivated_reason'] = $data['reason'] ?? 'License verification failed';
        $license_data['last_check'] = current_time( 'mysql' );
        update_option( 'base47_he_license_data', $license_data );
        
        // Log the deactivation
        if ( function_exists( 'base47_he_log' ) ) {
            base47_he_log( 'License deactivated: ' . ( $data['reason'] ?? 'Unknown reason' ), 'warning' );
        }
    }
    */
}

