<?php
/**
 * Cache AJAX Handlers
 * 
 * Handles cache rebuild operations
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Rebuild ALL Base47 caches (sets + templates)
 */
function base47_he_ajax_rebuild_caches() {
    check_ajax_referer('base47_he', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied']);
    }

    // Force-refresh all caches
    base47_he_refresh_theme_caches();

    wp_send_json_success(['message' => 'All caches rebuilt']);
}
add_action('wp_ajax_base47_rebuild_caches', 'base47_he_ajax_rebuild_caches');
