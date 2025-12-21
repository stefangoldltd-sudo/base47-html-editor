<?php
/**
 * Frontend Asset Enqueuing
 * 
 * Enqueues template assets on the frontend during wp_enqueue_scripts hook.
 * This ensures CSS/JS files are loaded in the <head> section.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.2.8
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Enqueue assets for all active template sets on frontend
 * 
 * This runs during wp_enqueue_scripts hook to ensure assets
 * are loaded in the <head> section before content is rendered.
 */
function base47_he_enqueue_frontend_assets() {
    // Only run on frontend
    if ( is_admin() ) {
        return;
    }
    
    // CRITICAL: Skip if we're in canvas mode
    // Canvas mode pages have inline assets, don't need enqueuing
    global $post;
    if ( $post && get_page_template_slug( $post->ID ) === 'template-canvas.php' ) {
        return; // Canvas mode - assets are inline
    }
    
    // Debug log
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '🔥 base47_he_enqueue_frontend_assets() RUNNING' );
    }
    
    // Get all active template sets
    $active_sets = base47_he_get_active_sets();
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '🔥 Active sets: ' . print_r( $active_sets, true ) );
    }
    
    if ( empty( $active_sets ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '🔥 NO ACTIVE SETS FOUND!' );
        }
        return;
    }
    
    // Enqueue assets for each active set
    foreach ( $active_sets as $set_slug ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '🔥 Enqueuing assets for: ' . $set_slug );
        }
        base47_he_enqueue_assets_for_set( $set_slug );
    }
}
add_action( 'wp_enqueue_scripts', 'base47_he_enqueue_frontend_assets', 10 );

/**
 * Alternative: Smart detection - only enqueue if shortcodes are present
 * 
 * This checks the post content for Base47 shortcodes and only enqueues
 * assets for the template sets that are actually used on the page.
 * 
 * More efficient but requires post content to be available.
 */
function base47_he_smart_enqueue_frontend_assets() {
    // Only run on frontend
    if ( is_admin() ) {
        return;
    }
    
    global $post;
    
    // Check if we have post content
    if ( ! $post || empty( $post->post_content ) ) {
        // Fallback: enqueue all active sets
        base47_he_enqueue_frontend_assets();
        return;
    }
    
    // Check if content has any Base47 shortcodes
    if ( ! has_shortcode( $post->post_content, 'base47' ) && 
         strpos( $post->post_content, '[base47-' ) === false &&
         strpos( $post->post_content, '[mivon-' ) === false ) {
        return; // No shortcodes, no need to enqueue
    }
    
    // Get all template sets
    $sets = base47_he_get_template_sets();
    
    // Check which sets are used in the content
    $used_sets = [];
    
    foreach ( $sets as $set_slug => $set_info ) {
        $set_clean = str_replace( ['-templates', '-templetes'], '', $set_slug );
        
        // Check for shortcodes from this set
        if ( strpos( $post->post_content, '[base47-' . $set_clean ) !== false ||
             strpos( $post->post_content, '[mivon-' . $set_clean ) !== false ) {
            $used_sets[] = $set_slug;
        }
    }
    
    // Enqueue assets for used sets
    if ( ! empty( $used_sets ) ) {
        foreach ( $used_sets as $set_slug ) {
            base47_he_enqueue_assets_for_set( $set_slug );
        }
    } else {
        // Fallback: enqueue all active sets
        base47_he_enqueue_frontend_assets();
    }
}
// Uncomment to use smart detection instead:
// remove_action( 'wp_enqueue_scripts', 'base47_he_enqueue_frontend_assets', 10 );
// add_action( 'wp_enqueue_scripts', 'base47_he_smart_enqueue_frontend_assets', 10 );

