<?php
/**
 * Marketplace AJAX Handlers
 * 
 * Handles marketplace operations: load templates, install, preview, etc.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Include local WordPress marketplace functions
require_once plugin_dir_path( __FILE__ ) . 'marketplace-local.php';

/**
 * AJAX: Load marketplace templates
 */
function base47_he_ajax_load_marketplace() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $filters = isset( $_POST['filters'] ) ? $_POST['filters'] : [];
    
    // Use local WordPress marketplace system
    $templates = base47_he_load_local_templates( $filters );
    
    if ( is_wp_error( $templates ) ) {
        wp_send_json_error( 'Could not load templates: ' . $templates->get_error_message() );
    }
    
    // Format response to match expected structure
    $response_data = [
        'templates' => $templates,
        'pagination' => [
            'current_page' => 1,
            'total_pages' => 1,
            'total_templates' => count($templates),
            'per_page' => count($templates)
        ]
    ];
    
    wp_send_json_success( $response_data );
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
    
    // Get template data from local WordPress uploads
    $template_data = base47_he_get_local_template( $template_id );
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
    
    // Download and extract template files from local uploads
    $download_result = base47_he_install_local_template( $template_data, $template_path );
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
    base47_he_log_local_download( $template_id );
    
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
    
    // Get preview URL from local template data
    $template_data = base47_he_get_local_template( $template_id );
    if ( is_wp_error( $template_data ) ) {
        wp_send_json_error( 'Could not fetch template: ' . $template_data->get_error_message() );
    }
    if ( ! $template_data || empty( $template_data['preview_url'] ) ) {
        wp_send_json_error( 'Preview not available for this template.' );
    }
    
    wp_send_json_success( [
        'preview_url' => $template_data['preview_url']
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
    
    // Get template details from local storage
    $template_details = base47_he_get_local_template( $template_id );
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
    
    // Submit rating to local storage
    $result = base47_he_submit_local_rating( $template_id, $rating, $review );
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
    
    // If API is not available, return mock data for now
    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return base47_he_get_mock_marketplace_data( $filters );
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( ! is_array( $data ) ) {
        return base47_he_get_mock_marketplace_data( $filters );
    }
    
    return $data;
}

/**
 * Get mock marketplace data (fallback when API is not available)
 */
function base47_he_get_mock_marketplace_data( $filters = [] ) {
    $templates = [
        [
            'id' => 'business-pro-v1',
            'name' => 'Business Pro',
            'description' => 'Professional business template with modern design and responsive layout.',
            'category' => 'Business',
            'type' => 'Landing Page',
            'rating' => 4.8,
            'downloads' => 1250,
            'preview_url' => 'https://47-studio.com/base47/previews/business-pro',
            'thumbnail' => 'https://47-studio.com/base47/thumbs/business-pro.jpg',
            'price' => 'Free',
            'author' => 'Base47 Team',
            'tags' => ['business', 'corporate', 'professional'],
            'created' => '2026-01-10',
            'updated' => '2026-01-12'
        ],
        [
            'id' => 'agency-creative-v2',
            'name' => 'Creative Agency',
            'description' => 'Bold and creative template perfect for design agencies and creative professionals.',
            'category' => 'Agency',
            'type' => 'Portfolio',
            'rating' => 4.9,
            'downloads' => 890,
            'preview_url' => 'https://47-studio.com/base47/previews/agency-creative',
            'thumbnail' => 'https://47-studio.com/base47/thumbs/agency-creative.jpg',
            'price' => 'Free',
            'author' => 'Base47 Team',
            'tags' => ['agency', 'creative', 'portfolio'],
            'created' => '2026-01-08',
            'updated' => '2026-01-11'
        ],
        [
            'id' => 'ecommerce-shop-v1',
            'name' => 'E-commerce Shop',
            'description' => 'Complete e-commerce template with product showcase and shopping cart integration.',
            'category' => 'E-commerce',
            'type' => 'Shop',
            'rating' => 4.7,
            'downloads' => 2100,
            'preview_url' => 'https://47-studio.com/base47/previews/ecommerce-shop',
            'thumbnail' => 'https://47-studio.com/base47/thumbs/ecommerce-shop.jpg',
            'price' => 'Free',
            'author' => 'Base47 Team',
            'tags' => ['ecommerce', 'shop', 'products'],
            'created' => '2026-01-05',
            'updated' => '2026-01-10'
        ],
        [
            'id' => 'restaurant-deluxe-v1',
            'name' => 'Restaurant Deluxe',
            'description' => 'Elegant restaurant template with menu showcase and reservation system.',
            'category' => 'Restaurant',
            'type' => 'Business',
            'rating' => 4.6,
            'downloads' => 750,
            'preview_url' => 'https://47-studio.com/base47/previews/restaurant-deluxe',
            'thumbnail' => 'https://47-studio.com/base47/thumbs/restaurant-deluxe.jpg',
            'price' => 'Free',
            'author' => 'Base47 Team',
            'tags' => ['restaurant', 'food', 'menu'],
            'created' => '2026-01-03',
            'updated' => '2026-01-09'
        ],
        [
            'id' => 'fitness-gym-v1',
            'name' => 'Fitness Gym',
            'description' => 'Dynamic fitness template with class schedules and trainer profiles.',
            'category' => 'Fitness',
            'type' => 'Business',
            'rating' => 4.5,
            'downloads' => 650,
            'preview_url' => 'https://47-studio.com/base47/previews/fitness-gym',
            'thumbnail' => 'https://47-studio.com/base47/thumbs/fitness-gym.jpg',
            'price' => 'Free',
            'author' => 'Base47 Team',
            'tags' => ['fitness', 'gym', 'health'],
            'created' => '2026-01-01',
            'updated' => '2026-01-08'
        ],
        [
            'id' => 'real-estate-pro-v1',
            'name' => 'Real Estate Pro',
            'description' => 'Professional real estate template with property listings and agent profiles.',
            'category' => 'Real Estate',
            'type' => 'Business',
            'rating' => 4.8,
            'downloads' => 920,
            'preview_url' => 'https://47-studio.com/base47/previews/real-estate-pro',
            'thumbnail' => 'https://47-studio.com/base47/thumbs/real-estate-pro.jpg',
            'price' => 'Free',
            'author' => 'Base47 Team',
            'tags' => ['real-estate', 'property', 'listings'],
            'created' => '2025-12-28',
            'updated' => '2026-01-07'
        ]
    ];
    
    // Apply filters
    if ( ! empty( $filters['search'] ) ) {
        $search = strtolower( $filters['search'] );
        $templates = array_filter( $templates, function( $template ) use ( $search ) {
            return strpos( strtolower( $template['name'] ), $search ) !== false ||
                   strpos( strtolower( $template['description'] ), $search ) !== false ||
                   in_array( $search, array_map( 'strtolower', $template['tags'] ) );
        });
    }
    
    if ( ! empty( $filters['category'] ) && $filters['category'] !== 'all' ) {
        $templates = array_filter( $templates, function( $template ) use ( $filters ) {
            return strtolower( $template['category'] ) === strtolower( $filters['category'] );
        });
    }
    
    if ( ! empty( $filters['type'] ) && $filters['type'] !== 'all' ) {
        $templates = array_filter( $templates, function( $template ) use ( $filters ) {
            return strtolower( $template['type'] ) === strtolower( $filters['type'] );
        });
    }
    
    // Sort results
    if ( ! empty( $filters['sort'] ) ) {
        switch ( $filters['sort'] ) {
            case 'popular':
                usort( $templates, function( $a, $b ) {
                    return $b['downloads'] - $a['downloads'];
                });
                break;
            case 'rating':
                usort( $templates, function( $a, $b ) {
                    return $b['rating'] <=> $a['rating'];
                });
                break;
            case 'newest':
                usort( $templates, function( $a, $b ) {
                    return strcmp( $b['created'], $a['created'] );
                });
                break;
            case 'name':
                usort( $templates, function( $a, $b ) {
                    return strcmp( $a['name'], $b['name'] );
                });
                break;
        }
    }
    
    return array_values( $templates );
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
    
    // If API is not available, return mock data for now
    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return base47_he_get_mock_template_data( $template_id );
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( ! is_array( $data ) ) {
        return base47_he_get_mock_template_data( $template_id );
    }
    
    return $data;
}

/**
 * Get mock template data for single template
 */
function base47_he_get_mock_template_data( $template_id ) {
    $mock_templates = base47_he_get_mock_marketplace_data();
    
    foreach ( $mock_templates as $template ) {
        if ( $template['id'] === $template_id ) {
            // Add download URL for mock data
            $template['download_url'] = 'https://47-studio.com/base47/downloads/' . $template_id . '.zip';
            return $template;
        }
    }
    
    return false; // Template not found
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