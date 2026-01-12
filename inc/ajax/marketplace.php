<?php
/**
 * Marketplace AJAX Handlers
 * 
 * Handles marketplace operations: load templates, install, preview, etc.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Load marketplace templates
 */
function base47_he_ajax_load_marketplace() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $filters = isset( $_POST['filters'] ) ? $_POST['filters'] : [];
    
    // Connect to 47-studio.com/base47 API
    $api_response = base47_he_fetch_marketplace_templates( $filters );
    
    if ( is_wp_error( $api_response ) ) {
        wp_send_json_error( 'Could not connect to marketplace: ' . $api_response->get_error_message() );
    }
    
    wp_send_json_success( $api_response );
}
add_action( 'wp_ajax_base47_he_load_marketplace', 'base47_he_ajax_load_marketplace' );

/**
 * AJAX: Install marketplace template
 */
function base47_he_ajax_install_marketplace_template() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Template ID not specified.' );
    }
    
    // Get template data from marketplace API (47-studio.com/base47)
    $template_data = base47_he_fetch_marketplace_template( $template_id );
    if ( is_wp_error( $template_data ) ) {
        wp_send_json_error( 'Could not fetch template: ' . $template_data->get_error_message() );
    }
    if ( ! $template_data ) {
        wp_send_json_error( 'Template not found in marketplace.' );
    }
    
    // Check if template already exists
    $themes_root = base47_he_get_themes_root();
    $template_path = $themes_root . $template_data['slug'] . '-templates/';
    
    if ( is_dir( $template_path ) ) {
        wp_send_json_error( 'Template set already installed.' );
    }
    
    // Create template directory
    if ( ! wp_mkdir_p( $template_path ) ) {
        wp_send_json_error( 'Could not create template directory. Check permissions.' );
    }
    
    // Download and extract template files
    $download_result = base47_he_download_template( $template_data, $template_path );
    if ( is_wp_error( $download_result ) ) {
        wp_send_json_error( $download_result->get_error_message() );
    }
    
    // Activate the new template set
    $active_themes = get_option( BASE47_HE_OPT_ACTIVE_THEMES, [] );
    if ( ! in_array( $template_data['slug'] . '-templates', $active_themes ) ) {
        $active_themes[] = $template_data['slug'] . '-templates';
        update_option( BASE47_HE_OPT_ACTIVE_THEMES, $active_themes );
    }
    
    // Log installation
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "Marketplace template installed: {$template_data['name']} ({$template_data['slug']}) by {$username}", 'info' );
    
    wp_send_json_success( [
        'message' => 'Template installed successfully!',
        'template_name' => $template_data['name'],
        'redirect_url' => admin_url( 'admin.php?page=base47-he-theme-manager' )
    ] );
}
add_action( 'wp_ajax_base47_he_install_marketplace_template', 'base47_he_ajax_install_marketplace_template' );

/**
 * AJAX: Get template preview URL
 */
function base47_he_ajax_get_template_preview() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Template ID not specified.' );
    }
    
    // Get preview URL from marketplace API (47-studio.com/base47)
    $preview_url = base47_he_fetch_template_preview_url( $template_id );
    if ( is_wp_error( $preview_url ) ) {
        wp_send_json_error( 'Could not fetch preview: ' . $preview_url->get_error_message() );
    }
    if ( ! $preview_url ) {
        wp_send_json_error( 'Preview not available for this template.' );
    }
    
    wp_send_json_success( [
        'preview_url' => $preview_url
    ] );
}
add_action( 'wp_ajax_base47_he_get_template_preview', 'base47_he_ajax_get_template_preview' );

/**
 * AJAX: Get template details
 */
function base47_he_ajax_get_template_details() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Template ID not specified.' );
    }
    
    // Get template details from marketplace API (47-studio.com/base47)
    $template_details = base47_he_fetch_marketplace_template( $template_id );
    if ( is_wp_error( $template_details ) ) {
        wp_send_json_error( 'Could not fetch template: ' . $template_details->get_error_message() );
    }
    if ( ! $template_details ) {
        wp_send_json_error( 'Template not found.' );
    }
    
    wp_send_json_success( $template_details );
}
add_action( 'wp_ajax_base47_he_get_template_details', 'base47_he_ajax_get_template_details' );

/**
 * AJAX: Submit template rating
 */
function base47_he_ajax_rate_template() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
    $rating = isset( $_POST['rating'] ) ? intval( $_POST['rating'] ) : 0;
    $review = isset( $_POST['review'] ) ? sanitize_textarea_field( wp_unslash( $_POST['review'] ) ) : '';
    
    if ( ! $template_id || $rating < 1 || $rating > 5 ) {
        wp_send_json_error( 'Invalid rating data.' );
    }
    
    // Submit rating to marketplace API (47-studio.com/base47)
    $result = base47_he_submit_marketplace_rating( $template_id, $rating, $review );
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( $result->get_error_message() );
    }
    
    wp_send_json_success( [
        'message' => 'Thank you for your rating!'
    ] );
}
add_action( 'wp_ajax_base47_he_rate_template', 'base47_he_ajax_rate_template' );

/* --------------------------------------------------------------------------
| API FUNCTIONS - 47-studio.com/base47
-------------------------------------------------------------------------- */

/**
 * Fetch templates from marketplace API
 */
function base47_he_fetch_marketplace_templates( $filters = [] ) {
    $api_url = 'https://47-studio.com/base47/api/templates';
    
    // Build query parameters
    $params = [];
    if ( ! empty( $filters['search'] ) ) {
        $params['search'] = sanitize_text_field( $filters['search'] );
    }
    if ( ! empty( $filters['category'] ) ) {
        $params['category'] = sanitize_text_field( $filters['category'] );
    }
    if ( ! empty( $filters['type'] ) ) {
        $params['type'] = sanitize_text_field( $filters['type'] );
    }
    if ( ! empty( $filters['sort'] ) ) {
        $params['sort'] = sanitize_text_field( $filters['sort'] );
    }
    
    if ( ! empty( $params ) ) {
        $api_url .= '?' . http_build_query( $params );
    }
    
    // Make API request
    $response = wp_remote_get( $api_url, [
        'timeout' => 15,
        'headers' => [
            'User-Agent' => 'Base47-HTML-Editor/' . BASE47_HE_VERSION,
            'Accept' => 'application/json'
        ]
    ] );
    
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code( $response );
    if ( $response_code !== 200 ) {
        return new WP_Error( 'api_error', 'Marketplace API returned error: ' . $response_code );
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( ! is_array( $data ) ) {
        return new WP_Error( 'invalid_response', 'Invalid response from marketplace API' );
    }
    
    return $data;
}

/**
 * Fetch single template from marketplace API
 */
function base47_he_fetch_marketplace_template( $template_id ) {
    $api_url = 'https://47-studio.com/base47/api/templates/' . urlencode( $template_id );
    
    $response = wp_remote_get( $api_url, [
        'timeout' => 15,
        'headers' => [
            'User-Agent' => 'Base47-HTML-Editor/' . BASE47_HE_VERSION,
            'Accept' => 'application/json'
        ]
    ] );
    
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code( $response );
    if ( $response_code === 404 ) {
        return false; // Template not found
    }
    if ( $response_code !== 200 ) {
        return new WP_Error( 'api_error', 'Marketplace API returned error: ' . $response_code );
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( ! is_array( $data ) ) {
        return new WP_Error( 'invalid_response', 'Invalid response from marketplace API' );
    }
    
    return $data;
}

/**
 * Fetch template preview URL from marketplace API
 */
function base47_he_fetch_template_preview_url( $template_id ) {
    $template_data = base47_he_fetch_marketplace_template( $template_id );
    
    if ( is_wp_error( $template_data ) ) {
        return $template_data;
    }
    
    if ( ! $template_data || empty( $template_data['preview_url'] ) ) {
        return false;
    }
    
    return $template_data['preview_url'];
}

/**
 * Download and install template from marketplace
 */
function base47_he_download_template( $template_data, $install_path ) {
    // Get download URL from template data
    $download_url = $template_data['download_url'] ?? '';
    
    if ( empty( $download_url ) ) {
        return new WP_Error( 'no_download_url', 'No download URL provided for template.' );
    }
    
    // Download template ZIP file
    $temp_file = download_url( $download_url );
    
    if ( is_wp_error( $temp_file ) ) {
        return $temp_file;
    }
    
    // Extract ZIP file
    $result = unzip_file( $temp_file, $install_path );
    
    // Clean up temp file
    @unlink( $temp_file );
    
    if ( is_wp_error( $result ) ) {
        return $result;
    }
    
    // Verify theme.json exists
    if ( ! file_exists( $install_path . 'theme.json' ) ) {
        return new WP_Error( 'invalid_template', 'Downloaded template is missing theme.json file.' );
    }
    
    return true;
}

/**
 * Submit template rating to marketplace
 */
function base47_he_submit_marketplace_rating( $template_id, $rating, $review ) {
    $api_url = 'https://47-studio.com/base47/api/templates/' . urlencode( $template_id ) . '/rate';
    
    $response = wp_remote_post( $api_url, [
        'timeout' => 15,
        'headers' => [
            'User-Agent' => 'Base47-HTML-Editor/' . BASE47_HE_VERSION,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode( [
            'rating' => intval( $rating ),
            'review' => sanitize_textarea_field( $review ),
            'site_url' => home_url(),
            'plugin_version' => BASE47_HE_VERSION
        ] )
    ] );
    
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code( $response );
    if ( $response_code !== 200 ) {
        return new WP_Error( 'api_error', 'Could not submit rating: ' . $response_code );
    }
    
    return true;
}
?>