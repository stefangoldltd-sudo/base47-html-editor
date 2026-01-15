<?php
/**
 * WordPress-Integrated Marketplace AJAX Handlers
 * 
 * Uses wp-content/uploads/base47-downloads/ structure
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Load marketplace templates (Local WordPress Version)
 */
function base47_he_ajax_load_marketplace_local() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $filters = $_POST['filters'] ?? [];
    
    // Load templates from local WordPress uploads directory
    $templates = base47_he_load_local_templates( $filters );
    
    if ( is_wp_error( $templates ) ) {
        wp_send_json_error( 'Could not load templates: ' . $templates->get_error_message() );
    }
    
    wp_send_json_success( $templates );
}
add_action( 'wp_ajax_base47_he_load_marketplace_local', 'base47_he_ajax_load_marketplace_local' );

/**
 * AJAX: Install marketplace template (Local WordPress Version)
 */
function base47_he_ajax_install_marketplace_template_local() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $template_id = sanitize_text_field( wp_unslash( $_POST['template_id'] ?? '' ) );
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Template ID not specified.' );
    }
    
    // Get template data from local storage
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
add_action( 'wp_ajax_base47_he_install_marketplace_template_local', 'base47_he_ajax_install_marketplace_template_local' );

/**
 * AJAX: Get template details (Local WordPress Version)
 */
function base47_he_ajax_get_template_details_local() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $template_id = sanitize_text_field( wp_unslash( $_POST['template_id'] ?? '' ) );
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Template ID not specified.' );
    }
    
    $template_details = base47_he_get_local_template( $template_id );
    if ( is_wp_error( $template_details ) ) {
        wp_send_json_error( 'Could not fetch template: ' . $template_details->get_error_message() );
    }
    if ( ! $template_details ) {
        wp_send_json_error( 'Template not found.' );
    }
    
    wp_send_json_success( $template_details );
}
add_action( 'wp_ajax_base47_he_get_template_details_local', 'base47_he_ajax_get_template_details_local' );

/**
 * AJAX: Submit template rating (Local WordPress Version)
 */
function base47_he_ajax_rate_template_local() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $template_id = sanitize_text_field( wp_unslash( $_POST['template_id'] ?? '' ) );
    $rating = intval( $_POST['rating'] ?? 0 );
    $review = sanitize_textarea_field( wp_unslash( $_POST['review'] ?? '' ) );
    
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
add_action( 'wp_ajax_base47_he_rate_template_local', 'base47_he_ajax_rate_template_local' );

/* --------------------------------------------------------------------------
| LOCAL WORDPRESS FUNCTIONS
-------------------------------------------------------------------------- */

/**
 * Get WordPress uploads directory path for Base47
 */
function base47_he_get_uploads_path() {
    $upload_dir = wp_upload_dir();
    return $upload_dir['basedir'] . '/base47-downloads/';
}

/**
 * Get WordPress uploads URL for Base47
 */
function base47_he_get_uploads_url() {
    $upload_dir = wp_upload_dir();
    return $upload_dir['baseurl'] . '/base47-downloads/';
}

/**
 * Load templates from local WordPress uploads directory
 */
function base47_he_load_local_templates( $filters = [] ) {
    $uploads_path = base47_he_get_uploads_path();
    $metadata_file = $uploads_path . 'metadata/templates.json';
    
    if ( ! file_exists( $metadata_file ) ) {
        return new WP_Error( 'no_metadata', 'Template metadata file not found.' );
    }
    
    $json_data = file_get_contents( $metadata_file );
    $data = json_decode( $json_data, true );
    
    if ( ! is_array( $data ) || ! isset( $data['templates'] ) ) {
        return new WP_Error( 'invalid_metadata', 'Invalid template metadata format.' );
    }
    
    $templates = $data['templates'];
    $uploads_url = base47_he_get_uploads_url();
    $site_url = home_url();
    
    // Replace URL placeholders with actual WordPress URLs
    foreach ( $templates as &$template ) {
        $template['thumbnail'] = str_replace( '{UPLOADS_URL}', $uploads_url, $template['thumbnail'] );
        $template['preview_url'] = str_replace( '{SITE_URL}', $site_url, $template['preview_url'] );
        $template['download_url'] = str_replace( '{UPLOADS_URL}', $uploads_url, $template['download_url'] );
        
        // If thumbnail doesn't exist, use a default placeholder based on category
        if (strpos($template['thumbnail'], 'thumbnails/') !== false) {
            $thumbnail_path = str_replace($uploads_url, $uploads_path, $template['thumbnail']);
            if (!file_exists($thumbnail_path)) {
                // Generate a simple SVG placeholder based on template category
                $category_colors = [
                    'Business' => '#667eea',
                    'E-commerce' => '#f093fb',
                    'Food & Dining' => '#4facfe',
                    'Health & Fitness' => '#fa709a',
                    'Real Estate' => '#a8edea',
                    'Education' => '#d299c2',
                    'Technology' => '#89f7fe',
                    'Portfolio' => '#ffecd2'
                ];
                
                $category_icons = [
                    'Business' => '🏢',
                    'E-commerce' => '🛒',
                    'Food & Dining' => '🍽️',
                    'Health & Fitness' => '💪',
                    'Real Estate' => '🏠',
                    'Education' => '📚',
                    'Technology' => '💻',
                    'Portfolio' => '🎨'
                ];
                
                $color = $category_colors[$template['category']] ?? '#667eea';
                $icon = $category_icons[$template['category']] ?? '📄';
                
                // Create inline SVG data URL
                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600">' .
                       '<defs><linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">' .
                       '<stop offset="0%" style="stop-color:' . $color . ';stop-opacity:1" />' .
                       '<stop offset="100%" style="stop-color:' . $color . '99;stop-opacity:1" />' .
                       '</linearGradient></defs>' .
                       '<rect fill="url(#grad)" width="800" height="600"/>' .
                       '<text x="50%" y="45%" font-size="120" text-anchor="middle" fill="white" opacity="0.9">' . $icon . '</text>' .
                       '<text x="50%" y="60%" font-size="24" text-anchor="middle" fill="white" opacity="0.8" font-family="Arial">' . htmlspecialchars($template['name']) . '</text>' .
                       '</svg>';
                
                $template['thumbnail'] = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }
    }
    
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
            return $template['type'] === $filters['type'];
        });
    }
    
    // Apply sorting
    if ( ! empty( $filters['sort'] ) ) {
        switch ( $filters['sort'] ) {
            case 'newest':
                usort( $templates, function( $a, $b ) {
                    return strtotime( $b['created_at'] ) - strtotime( $a['created_at'] );
                });
                break;
            case 'rating':
                usort( $templates, function( $a, $b ) {
                    return $b['rating'] <=> $a['rating'];
                });
                break;
            case 'name':
                usort( $templates, function( $a, $b ) {
                    return strcmp( $a['name'], $b['name'] );
                });
                break;
            case 'downloads':
            case 'popular':
            default:
                usort( $templates, function( $a, $b ) {
                    return $b['downloads'] <=> $a['downloads'];
                });
                break;
        }
    }
    
    return array_values( $templates );
}

/**
 * Get single template from local storage
 */
function base47_he_get_local_template( $template_id ) {
    $templates = base47_he_load_local_templates();
    
    if ( is_wp_error( $templates ) ) {
        return $templates;
    }
    
    foreach ( $templates as $template ) {
        if ( $template['id'] === $template_id ) {
            return $template;
        }
    }
    
    return false;
}

/**
 * Install template from local uploads directory
 */
function base47_he_install_local_template( $template_data, $install_path ) {
    $uploads_path = base47_he_get_uploads_path();
    $zip_file = $uploads_path . 'templates/' . $template_data['id'] . '.zip';
    
    if ( ! file_exists( $zip_file ) ) {
        return new WP_Error( 'no_zip_file', 'Template ZIP file not found: ' . $template_data['id'] . '.zip' );
    }
    
    // Extract ZIP file
    $result = unzip_file( $zip_file, $install_path );
    
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
 * Log template download locally
 */
function base47_he_log_local_download( $template_id ) {
    $uploads_path = base47_he_get_uploads_path();
    $logs_dir = $uploads_path . 'logs/';
    
    if ( ! is_dir( $logs_dir ) ) {
        wp_mkdir_p( $logs_dir );
    }
    
    $log_file = $logs_dir . 'downloads.log';
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    $log_entry = date( 'Y-m-d H:i:s' ) . " - Downloaded: $template_id by $username - IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    file_put_contents( $log_file, $log_entry, FILE_APPEND | LOCK_EX );
    
    // Update download count in metadata
    base47_he_update_local_download_count( $template_id );
}

/**
 * Update template download count in local metadata
 */
function base47_he_update_local_download_count( $template_id ) {
    $uploads_path = base47_he_get_uploads_path();
    $metadata_file = $uploads_path . 'metadata/templates.json';
    
    if ( ! file_exists( $metadata_file ) ) {
        return;
    }
    
    $json_data = file_get_contents( $metadata_file );
    $data = json_decode( $json_data, true );
    
    if ( ! is_array( $data ) || ! isset( $data['templates'] ) ) {
        return;
    }
    
    // Update download count
    foreach ( $data['templates'] as &$template ) {
        if ( $template['id'] === $template_id ) {
            $template['downloads']++;
            $template['updated_at'] = date( 'c' );
            break;
        }
    }
    
    // Update total stats
    if ( isset( $data['stats']['total_downloads'] ) ) {
        $data['stats']['total_downloads']++;
    }
    
    // Save updated data
    file_put_contents( $metadata_file, json_encode( $data, JSON_PRETTY_PRINT ) );
}

/**
 * Submit template rating to local storage
 */
function base47_he_submit_local_rating( $template_id, $rating, $review = '' ) {
    $uploads_path = base47_he_get_uploads_path();
    $logs_dir = $uploads_path . 'logs/';
    
    if ( ! is_dir( $logs_dir ) ) {
        wp_mkdir_p( $logs_dir );
    }
    
    // Log the rating
    $log_file = $logs_dir . 'ratings.log';
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    $log_entry = date( 'Y-m-d H:i:s' ) . " - Rating: $template_id - $rating/5 by $username";
    if ( $review ) {
        $log_entry .= " - Review: " . substr( $review, 0, 100 );
    }
    $log_entry .= "\n";
    
    file_put_contents( $log_file, $log_entry, FILE_APPEND | LOCK_EX );
    
    // Update rating in metadata (simplified version)
    base47_he_update_local_rating( $template_id, $rating );
    
    return true;
}

/**
 * Update template rating in local metadata
 */
function base47_he_update_local_rating( $template_id, $rating ) {
    $uploads_path = base47_he_get_uploads_path();
    $metadata_file = $uploads_path . 'metadata/templates.json';
    
    if ( ! file_exists( $metadata_file ) ) {
        return;
    }
    
    $json_data = file_get_contents( $metadata_file );
    $data = json_decode( $json_data, true );
    
    if ( ! is_array( $data ) || ! isset( $data['templates'] ) ) {
        return;
    }
    
    // Update rating (simplified calculation)
    foreach ( $data['templates'] as &$template ) {
        if ( $template['id'] === $template_id ) {
            $current_rating = $template['rating'];
            $current_reviews = $template['reviews'];
            
            $new_total = ( $current_rating * $current_reviews ) + $rating;
            $new_reviews = $current_reviews + 1;
            $new_rating = round( $new_total / $new_reviews, 1 );
            
            $template['rating'] = $new_rating;
            $template['reviews'] = $new_reviews;
            $template['updated_at'] = date( 'c' );
            break;
        }
    }
    
    // Save updated data
    file_put_contents( $metadata_file, json_encode( $data, JSON_PRETTY_PRINT ) );
}