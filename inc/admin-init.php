<?php
/**
 * Admin Initialization
 * 
 * Handles admin menu registration and asset enqueuing.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
| ADMIN MENUS
-------------------------------------------------------------------------- */

/**
 * Register admin menu pages.
 */
function base47_he_admin_menu() {
    // MAIN
    add_menu_page(
        'Base47 HTML',
        'Base47 HTML',
        'manage_options',
        'base47-he-dashboard',
        'base47_he_dashboard_page',
        'dashicons-layout',
        60
    );

    // Shortcodes
    add_submenu_page(
        'base47-he-dashboard',
        'Shortcodes',
        'Shortcodes',
        'manage_options',
        'base47-he-templates',
        'base47_he_templates_page'
    );

    // Live Editor
    add_submenu_page(
        'base47-he-dashboard',
        'Live Editor',
        'Live Editor',
        'manage_options',
        'base47-he-editor',
        'base47_he_editor_page'
    );

    // Theme Manager
    add_submenu_page(
        'base47-he-dashboard',
        'Theme Manager',
        'Theme Manager',
        'manage_options',
        'base47-he-theme-manager',
        'base47_he_theme_manager_page'
    );

    // Special Widgets 
    add_submenu_page(
        'base47-he-dashboard',
        'Special Widgets',
        'Special Widgets',
        'manage_options',
        'base47-special-widgets',
        'base47_special_widgets_page'
    );
    
    // Settings
    add_submenu_page(
        'base47-he-dashboard',
        'Settings',
        'Settings',
        'manage_options',
        'base47-he-settings',
        'base47_he_settings_page'
    );
	
    // Logs
    add_submenu_page(
        'base47-he-dashboard',
        'Logs',
        'Logs',
        'manage_options',
        'base47-he-logs',
        'base47_he_render_logs_page'
    );

    // Changelog
    add_submenu_page(
        'base47-he-dashboard',
        'Changelog',
        'Changelog',
        'manage_options',
        'base47-he-changelog',
        'base47_he_changelog_page'
    );
}
add_action( 'admin_menu', 'base47_he_admin_menu' );

/* --------------------------------------------------------------------------
| ADMIN ASSETS
-------------------------------------------------------------------------- */

/**
 * Enqueue admin assets for Base47 (including Theme Manager).
 */
function base47_he_admin_assets( $hook ) {
    $screen = get_current_screen();
    if ( ! $screen || strpos( $screen->id, 'base47-he-' ) === false ) {
        return;
    }

    // ========================================
    // SOFT UI DASHBOARD CSS (Phase 12)
    // ========================================
    
    // Google Fonts - Inter
    wp_enqueue_style(
        'google-fonts-inter',
        'https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800',
        [],
        null
    );
    
    // Soft UI Dashboard - Main CSS
    wp_enqueue_style(
        'base47-soft-ui',
        BASE47_HE_URL . 'admin-assets/soft-ui/css/soft-ui-dashboard.min.css',
        [],
        BASE47_HE_VERSION
    );
    
    // Nucleo Icons
    wp_enqueue_style(
        'base47-nucleo-icons',
        BASE47_HE_URL . 'admin-assets/soft-ui/css/nucleo-icons.css',
        [],
        BASE47_HE_VERSION
    );
    
    // Nucleo SVG Icons
    wp_enqueue_style(
        'base47-nucleo-svg',
        BASE47_HE_URL . 'admin-assets/soft-ui/css/nucleo-svg.css',
        [],
        BASE47_HE_VERSION
    );
    
    // ========================================
    // EXISTING BASE47 CSS (Compatibility)
    // ========================================
    
    // Existing admin CSS/JS for Base47
    wp_enqueue_style(
        'base47-he-admin',
        BASE47_HE_URL . 'admin-assets/admin.css',
        [ 'base47-soft-ui' ],
        BASE47_HE_VERSION
    );
    
    // Monaco Editor on editor page
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-editor' ) {
        wp_enqueue_script(
            'monaco-loader',
            BASE47_HE_URL . 'admin-assets/monaco/vs/loader.js',
            [],
            BASE47_HE_VERSION,
            true
        );
        wp_enqueue_style(
            'monaco-editor',
            BASE47_HE_URL . 'admin-assets/monaco/vs/editor/editor.main.css',
            [],
            BASE47_HE_VERSION
        );
    }
    
    // Settings page specific CSS
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-settings' ) {
        wp_enqueue_style(
            'base47-he-settings',
            BASE47_HE_URL . 'admin-assets/settings.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }

    wp_enqueue_script(
        'base47-he-admin',
        BASE47_HE_URL . 'admin-assets/admin.js',
        [ 'jquery' ],
        BASE47_HE_VERSION,
        true
    );

    /**
     * LOCALIZE – admin.js (IMPORTANT)
     * Provides AJAX + NONCE for editor + lazy preview
     */
    $settings = base47_he_get_settings();
    wp_localize_script(
        'base47-he-admin',
        'BASE47_HE',
        [
            'ajax_url'     => admin_url('admin-ajax.php'),
            'nonce'        => wp_create_nonce('base47_he'),
            'default_set'  => base47_he_detect_default_theme(),
            'plugin_url'   => BASE47_HE_URL,
            'editor_mode'  => $settings['editor_mode'] ?? 'advanced',
            'editor_theme' => $settings['editor_theme'] ?? 'light',
        ]
    );

    // Theme Manager CSS (Soft UI - Phase 12 v2.9.6.5)
    wp_enqueue_style(
        'base47-he-theme-manager',
        BASE47_HE_URL . 'admin-assets/theme-manager.css',
        [ 'base47-he-admin' ],
        BASE47_HE_VERSION
    );

    // Theme Manager JS
    wp_enqueue_script(
        'base47-he-theme-manager',
        BASE47_HE_URL . 'admin-assets/theme-manager.js',
        [ 'jquery' ],
        BASE47_HE_VERSION,
        true
    );

    /**
     * LOCALIZE – theme-manager.js
     * (Used only for toggling themes ON/OFF)
     */
    wp_localize_script(
        'base47-he-theme-manager',
        'base47ThemeManager',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('base47_he'),   
        ]
    );
}
add_action( 'admin_enqueue_scripts', 'base47_he_admin_assets' );
