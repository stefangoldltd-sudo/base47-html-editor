<?php
/**
 * Marketplace AJAX Handlers
 * 
 * Handles template loading, installation, and preview
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load marketplace templates
 */
add_action( 'wp_ajax_base47_he_load_marketplace', 'base47_he_load_marketplace' );
function base47_he_load_marketplace() {
    check_ajax_referer( 'base47_he_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }
    
    // Load templates from JSON file
    $upload_dir = wp_upload_dir();
    $json_file = $upload_dir['basedir'] . '/base47-downloads/metadata/templates.json';
    
    if ( file_exists( $json_file ) ) {
        $json_content = file_get_contents( $json_file );
        $data = json_decode( $json_content, true );
        
        if ( $data && isset( $data['templates'] ) ) {
            // Replace placeholders with actual URLs
            $site_url = get_site_url();
            $uploads_url = $upload_dir['baseurl'];
            
            $templates = array();
            foreach ( $data['templates'] as $template ) {
                // Replace URL placeholders
                $template['thumbnail'] = str_replace( '{UPLOADS_URL}', $uploads_url, $template['thumbnail'] );
                $template['preview_url'] = str_replace( '{SITE_URL}', $site_url, $template['preview_url'] );
                $template['download_url'] = str_replace( '{UPLOADS_URL}', $uploads_url, $template['download_url'] );
                
                // Convert to format expected by JavaScript
                $templates[] = array(
                    'id' => $template['id'],
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'category' => strtolower( str_replace( ' ', '', $template['category'] ) ),
                    'type' => $template['type'],
                    'price' => $template['price'] > 0 ? '$' . $template['price'] : 'Free',
                    'rating' => $template['rating'],
                    'reviews' => $template['reviews'],
                    'downloads' => $template['downloads'],
                    'thumbnail' => $template['thumbnail'],
                    'preview_url' => $template['preview_url'],
                    'download_url' => $template['download_url']
                );
            }
            
            wp_send_json_success( array(
                'templates' => $templates
            ) );
        }
    }
    
    // Fallback to sample data if JSON file doesn't exist
    $templates = array(
        array(
            'id' => 1,
            'name' => 'SaaS Landing',
            'description' => 'High-converting landing page designed for SaaS products with modern design and call-to-actions.',
            'category' => 'landing',
            'type' => 'free',
            'price' => 'Free',
            'rating' => 4.9,
            'reviews' => 312,
            'downloads' => 7250,
            'thumbnail' => 'https://via.placeholder.com/400x300/667eea/ffffff?text=SaaS+Landing',
            'preview_url' => 'https://example.com/preview/saas-landing'
        ),
        array(
            'id' => 2,
            'name' => 'E-Commerce Pro',
            'description' => 'Complete e-commerce solution with product galleries, cart, and checkout pages.',
            'category' => 'business',
            'type' => 'premium',
            'price' => '$49',
            'rating' => 4.7,
            'reviews' => 256,
            'downloads' => 5840,
            'thumbnail' => 'https://via.placeholder.com/400x300/f97316/ffffff?text=E-Commerce',
            'preview_url' => 'https://example.com/preview/ecommerce-pro'
        ),
        array(
            'id' => 3,
            'name' => 'Developer Portfolio',
            'description' => 'Tech-focused portfolio for developers with project showcase and contact form.',
            'category' => 'portfolio',
            'type' => 'free',
            'price' => 'Free',
            'rating' => 4.8,
            'reviews' => 145,
            'downloads' => 5120,
            'thumbnail' => 'https://via.placeholder.com/400x300/3b82f6/ffffff?text=Portfolio',
            'preview_url' => 'https://example.com/preview/developer-portfolio'
        ),
        array(
            'id' => 4,
            'name' => 'Creative Agency',
            'description' => 'Bold and creative template for digital agencies with portfolio and team sections.',
            'category' => 'business',
            'type' => 'premium',
            'price' => '$59',
            'rating' => 4.6,
            'reviews' => 178,
            'downloads' => 3890,
            'thumbnail' => 'https://via.placeholder.com/400x300/8b5cf6/ffffff?text=Agency',
            'preview_url' => 'https://example.com/preview/creative-agency'
        ),
        array(
            'id' => 5,
            'name' => 'Blog Modern',
            'description' => 'Beautiful blog template with modern design, perfect for content creators.',
            'category' => 'blog',
            'type' => 'free',
            'price' => 'Free',
            'rating' => 4.5,
            'reviews' => 203,
            'downloads' => 4120,
            'thumbnail' => 'https://via.placeholder.com/400x300/10b981/ffffff?text=Blog',
            'preview_url' => 'https://example.com/preview/blog-modern'
        ),
        array(
            'id' => 6,
            'name' => 'Real Estate Prime',
            'description' => 'Luxury real estate template with property listings, agent profiles and search.',
            'category' => 'realestate',
            'type' => 'premium',
            'price' => '$65',
            'rating' => 4.9,
            'reviews' => 178,
            'downloads' => 3890,
            'thumbnail' => 'https://via.placeholder.com/400x300/f59e0b/ffffff?text=Real+Estate',
            'preview_url' => 'https://example.com/preview/realestate-prime'
        ),
        array(
            'id' => 7,
            'name' => 'Restaurant Deluxe',
            'description' => 'Elegant restaurant template with menu, reservations, and gallery sections.',
            'category' => 'restaurant',
            'type' => 'free',
            'price' => 'Free',
            'rating' => 4.7,
            'reviews' => 145,
            'downloads' => 3420,
            'thumbnail' => 'https://via.placeholder.com/400x300/ef4444/ffffff?text=Restaurant',
            'preview_url' => 'https://example.com/preview/restaurant-deluxe'
        ),
        array(
            'id' => 8,
            'name' => 'Fitness Studio',
            'description' => 'Dynamic fitness template with class schedules, trainer profiles, and pricing.',
            'category' => 'fitness',
            'type' => 'premium',
            'price' => '$45',
            'rating' => 4.8,
            'reviews' => 124,
            'downloads' => 2890,
            'thumbnail' => 'https://via.placeholder.com/400x300/10b981/ffffff?text=Fitness',
            'preview_url' => 'https://example.com/preview/fitness-studio'
        ),
        array(
            'id' => 9,
            'name' => 'EduLearn',
            'description' => 'Online learning platform template with courses, instructors, and student dashboard.',
            'category' => 'education',
            'type' => 'premium',
            'price' => '$55',
            'rating' => 4.6,
            'reviews' => 203,
            'downloads' => 4670,
            'thumbnail' => 'https://via.placeholder.com/400x300/3b82f6/ffffff?text=Education',
            'preview_url' => 'https://example.com/preview/edulearn'
        ),
        array(
            'id' => 10,
            'name' => 'Medical Clinic',
            'description' => 'Professional medical template with appointment booking and doctor profiles.',
            'category' => 'medical',
            'type' => 'free',
            'price' => 'Free',
            'rating' => 4.5,
            'reviews' => 167,
            'downloads' => 3240,
            'thumbnail' => 'https://via.placeholder.com/400x300/667eea/ffffff?text=Medical',
            'preview_url' => 'https://example.com/preview/medical-clinic'
        ),
        array(
            'id' => 11,
            'name' => 'Business Starter',
            'description' => 'A clean and professional template for small businesses and startups.',
            'category' => 'business',
            'type' => 'free',
            'price' => 'Free',
            'rating' => 4.4,
            'reviews' => 189,
            'downloads' => 3420,
            'thumbnail' => 'https://via.placeholder.com/400x300/f97316/ffffff?text=Business',
            'preview_url' => 'https://example.com/preview/business-starter'
        ),
        array(
            'id' => 12,
            'name' => 'App Landing Pro',
            'description' => 'Modern app landing page with features showcase, pricing, and download buttons.',
            'category' => 'landing',
            'type' => 'premium',
            'price' => '$39',
            'rating' => 4.9,
            'reviews' => 234,
            'downloads' => 5890,
            'thumbnail' => 'https://via.placeholder.com/400x300/8b5cf6/ffffff?text=App+Landing',
            'preview_url' => 'https://example.com/preview/app-landing-pro'
        )
    );
    
    wp_send_json_success( array(
        'templates' => $templates
    ) );
}

/**
 * Install marketplace template
 */
add_action( 'wp_ajax_base47_he_install_marketplace_template', 'base47_he_install_marketplace_template' );
function base47_he_install_marketplace_template() {
    check_ajax_referer( 'base47_he_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( $_POST['template_id'] ) : '';
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Invalid template ID' );
    }
    
    // Get upload directory
    $upload_dir = wp_upload_dir();
    $templates_dir = $upload_dir['basedir'] . '/base47-downloads/templates/';
    $zip_file = $templates_dir . $template_id . '.zip';
    
    // Check if ZIP file exists
    if ( ! file_exists( $zip_file ) ) {
        wp_send_json_error( 'Template ZIP file not found. Please ensure the template is available.' );
    }
    
    // Load theme installation functions
    if ( ! function_exists( 'base47_he_install_theme_from_zip' ) ) {
        require_once plugin_dir_path( __FILE__ ) . '../operations/theme-install.php';
    }
    
    // Install the template using existing installation function
    $result = base47_he_install_theme_from_zip( $zip_file );
    
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( $result->get_error_message() );
    }
    
    // Success - template installed
    wp_send_json_success( array(
        'message' => 'Template installed successfully! You can now use it in the Live Editor.',
        'theme_slug' => $result,
        'redirect_url' => admin_url( 'admin.php?page=base47-he-theme-manager' )
    ) );
}

/**
 * Download marketplace template
 */
add_action( 'wp_ajax_base47_he_download_marketplace_template', 'base47_he_download_marketplace_template' );
function base47_he_download_marketplace_template() {
    check_ajax_referer( 'base47_he_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( $_POST['template_id'] ) : '';
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Invalid template ID' );
    }
    
    // Get upload directory
    $upload_dir = wp_upload_dir();
    $templates_dir = $upload_dir['basedir'] . '/base47-downloads/templates/';
    $zip_file = $templates_dir . $template_id . '.zip';
    
    // Check if ZIP file exists
    if ( ! file_exists( $zip_file ) ) {
        wp_send_json_error( 'Template ZIP file not found: ' . $template_id . '.zip' );
    }
    
    // Get file info
    $file_size = filesize( $zip_file );
    $file_name = basename( $zip_file );
    
    // Send download URL
    $download_url = $upload_dir['baseurl'] . '/base47-downloads/templates/' . $template_id . '.zip';
    
    wp_send_json_success( array(
        'download_url' => $download_url,
        'file_name' => $file_name,
        'file_size' => $file_size,
        'message' => 'Download ready'
    ) );
}
add_action( 'wp_ajax_base47_he_get_template_preview', 'base47_he_get_template_preview' );
function base47_he_get_template_preview() {
    check_ajax_referer( 'base47_he_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( $_POST['template_id'] ) : '';
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Invalid template ID' );
    }
    
    // Get uploads directory
    $upload_dir = wp_upload_dir();
    $uploads_url = $upload_dir['baseurl'];
    
    // Build preview URL - point directly to the template's index.html file
    $preview_url = $uploads_url . '/base47-downloads/templates/' . $template_id . '/index.html';
    
    wp_send_json_success( array(
        'preview_url' => $preview_url
    ) );
}
