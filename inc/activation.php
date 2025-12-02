<?php
/**
 * Plugin Activation & Migration
 * 
 * Handles plugin activation, option migration from old versions.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Migrate options from old Mivon plugin to Base47.
 */
function base47_he_migrate_options() {
    // Migrate active themes option
    $old_active = get_option( 'mivon_active_themes' );
    $new_active = get_option( 'base47_active_themes' );
    
    if ( $old_active && ! $new_active && is_array( $old_active ) ) {
        update_option( 'base47_active_themes', $old_active );
    }
    
    // Migrate manifest option
    $old_manifest = get_option( 'mivon_use_manifest' );
    $new_manifest = get_option( 'base47_use_manifest' );
    
    if ( $old_manifest && ! $new_manifest && is_array( $old_manifest ) ) {
        update_option( 'base47_use_manifest', $old_manifest );
    }
}

/**
 * Plugin activation hook.
 */
function base47_he_activate() {

    // 1️⃣ Run migration first – ensures the folder exists
    base47_he_migrate_options();

    // 2️⃣ Ensure default active sets saved
    base47_he_get_active_sets();

    // 3️⃣ Ensure default theme exists
    $sets = base47_he_get_template_sets(true); // force scan
    $default = get_option('base47_default_theme', '');

    if ( empty($default) && ! empty($sets) ) {
        update_option('base47_default_theme', array_key_first($sets));
    }
    
    // 4️⃣ Initialize settings with defaults if not exists
    $settings = get_option( 'base47_he_settings', false );
    if ( $settings === false ) {
        update_option( 'base47_he_settings', base47_he_get_default_settings() );
    }
}
