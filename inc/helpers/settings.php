<?php
/**
 * Settings Helper Functions
 * 
 * Provides centralized access to plugin settings with smart defaults.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.4.5
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get plugin settings with defaults.
 * 
 * @return array Settings array
 */
function base47_he_get_settings() {
    static $settings = null;
    
    if ( $settings !== null ) {
        return $settings;
    }
    
    $defaults = base47_he_get_default_settings();
    $saved    = get_option( 'base47_he_settings', [] );
    
    // Merge with defaults (saved values override defaults)
    $settings = wp_parse_args( $saved, $defaults );
    
    return $settings;
}

/**
 * Get default settings array.
 * 
 * @return array Default settings
 */
function base47_he_get_default_settings() {
    return [
        // General
        'debug_mode'                  => false,
        'enable_cache'                => true,
        'cache_lifetime'              => 60,    // minutes
        
        // Live Editor
        'editor_theme'                => 'light',
        'editor_font_size'            => '14px',
        'editor_mode'                 => 'advanced',  // 'advanced' or 'classic'
        'line_numbers'                => true,
        'line_wrap'                   => false,
        'autosave_interval'           => 0,     // disabled by default
        
        // Logging
        'logging_enabled'             => true,
        'log_level'                   => 'warnings',
        'log_retention'               => 14,    // days
        'max_log_size'                => 5,     // MB
        
        // Developer Tools
        'show_file_paths'             => false,
        'show_asset_map'              => false,
        'disable_smart_loader_debug'  => true,
        'experimental_features'       => false,
        'show_performance_metrics'    => false,
        
        // Security
        'restrict_editor_admins'      => false,
        'disable_upload_editors'      => false,
        'sanitize_output'             => true,
    ];
}

/**
 * Update plugin settings.
 * 
 * @param array $new_settings New settings to save
 * @return bool Success
 */
function base47_he_update_settings( $new_settings ) {
    $defaults = base47_he_get_default_settings();
    $settings = wp_parse_args( $new_settings, $defaults );
    
    // Sanitize settings
    $settings = base47_he_sanitize_settings( $settings );
    
    // Clear static cache
    base47_he_clear_settings_cache();
    
    return update_option( 'base47_he_settings', $settings );
}

/**
 * Sanitize settings array.
 * 
 * @param array $settings Raw settings
 * @return array Sanitized settings
 */
function base47_he_sanitize_settings( $settings ) {
    $clean = [];
    
    // General
    $clean['debug_mode']     = ! empty( $settings['debug_mode'] );
    $clean['enable_cache']   = ! empty( $settings['enable_cache'] );
    $clean['cache_lifetime'] = absint( $settings['cache_lifetime'] ?? 60 );
    $clean['cache_lifetime'] = max( 1, min( 720, $clean['cache_lifetime'] ) ); // 1-720 minutes
    
    // Live Editor
    $clean['editor_theme']      = in_array( $settings['editor_theme'] ?? 'light', [ 'light', 'dark' ], true ) 
                                  ? $settings['editor_theme'] : 'light';
    $clean['editor_font_size']  = in_array( $settings['editor_font_size'] ?? '14px', [ '12px', '14px', '16px', '18px' ], true )
                                  ? $settings['editor_font_size'] : '14px';
    $clean['editor_mode']       = in_array( $settings['editor_mode'] ?? 'advanced', [ 'advanced', 'classic' ], true )
                                  ? $settings['editor_mode'] : 'advanced';
    $clean['line_numbers']      = ! empty( $settings['line_numbers'] );
    $clean['line_wrap']         = ! empty( $settings['line_wrap'] );
    $clean['autosave_interval'] = absint( $settings['autosave_interval'] ?? 0 );
    $clean['autosave_interval'] = in_array( $clean['autosave_interval'], [ 0, 30, 60, 120 ], true ) 
                                  ? $clean['autosave_interval'] : 0;
    
    // Logging
    $clean['logging_enabled'] = ! empty( $settings['logging_enabled'] );
    $clean['log_level']       = in_array( $settings['log_level'] ?? 'warnings', [ 'errors', 'warnings', 'info', 'debug' ], true )
                                ? $settings['log_level'] : 'warnings';
    $clean['log_retention']   = absint( $settings['log_retention'] ?? 14 );
    $clean['log_retention']   = max( 1, min( 90, $clean['log_retention'] ) ); // 1-90 days
    $clean['max_log_size']    = absint( $settings['max_log_size'] ?? 5 );
    $clean['max_log_size']    = max( 1, min( 50, $clean['max_log_size'] ) ); // 1-50 MB
    
    // Developer Tools
    $clean['show_file_paths']            = ! empty( $settings['show_file_paths'] );
    $clean['show_asset_map']             = ! empty( $settings['show_asset_map'] );
    $clean['disable_smart_loader_debug'] = ! empty( $settings['disable_smart_loader_debug'] );
    $clean['experimental_features']      = ! empty( $settings['experimental_features'] );
    $clean['show_performance_metrics']   = ! empty( $settings['show_performance_metrics'] );
    
    // Security
    $clean['restrict_editor_admins']  = ! empty( $settings['restrict_editor_admins'] );
    $clean['disable_upload_editors']  = ! empty( $settings['disable_upload_editors'] );
    $clean['sanitize_output']         = ! empty( $settings['sanitize_output'] );
    
    return $clean;
}

/**
 * Reset settings to defaults.
 * 
 * @return bool Success
 */
function base47_he_reset_settings() {
    base47_he_clear_settings_cache();
    return update_option( 'base47_he_settings', base47_he_get_default_settings() );
}

/**
 * Clear settings static cache.
 */
function base47_he_clear_settings_cache() {
    // Force next call to base47_he_get_settings() to reload from DB
    $GLOBALS['base47_he_settings_cache'] = null;
}

/**
 * Check if caching is enabled (respects debug mode override).
 * 
 * @return bool
 */
function base47_he_is_cache_enabled() {
    $settings = base47_he_get_settings();
    
    // Debug mode overrides cache setting
    if ( $settings['debug_mode'] ) {
        return false;
    }
    
    return $settings['enable_cache'];
}

/**
 * Check if logging is enabled.
 * 
 * @return bool
 */
function base47_he_is_logging_enabled() {
    $settings = base47_he_get_settings();
    return $settings['logging_enabled'];
}

/**
 * Check if debug mode is active.
 * 
 * @return bool
 */
function base47_he_is_debug_mode() {
    $settings = base47_he_get_settings();
    return $settings['debug_mode'];
}

/**
 * Get cache lifetime in minutes.
 * 
 * @return int Minutes
 */
function base47_he_get_cache_lifetime() {
    $settings = base47_he_get_settings();
    return absint( $settings['cache_lifetime'] );
}
