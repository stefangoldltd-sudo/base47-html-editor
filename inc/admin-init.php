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
    // Base47 icon as data URI (SVG)
    $icon_svg = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none"><rect x="2" y="2" width="5.5" height="11.5" rx="0.8" fill="#fb6340"/><rect x="8" y="2" width="5.5" height="5.5" rx="0.8" fill="#32325d"/><rect x="8" y="8" width="5.5" height="5.5" rx="0.8" fill="#32325d"/><rect x="2" y="14" width="11.5" height="4" rx="0.8" fill="#fb6340"/><rect x="14" y="2" width="5.5" height="16" rx="0.8" fill="#32325d"/></svg>');
    
    // MAIN
    add_menu_page(
        'Base47 HTML',
        'Base47 HTML',
        'manage_options',
        'base47-he-dashboard',
        'base47_he_dashboard_page',
        $icon_svg,
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

    // Marketplace
    add_submenu_page(
        'base47-he-dashboard',
        'Marketplace',
        'Marketplace',
        'manage_options',
        'base47-he-marketplace',
        'base47_he_marketplace_page'
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
    
    // License (Always show - for activation)
    add_submenu_page(
        'base47-he-dashboard',
        'License',
        'License',
        'manage_options',
        'base47-he-license',
        'base47_he_license_page'
    );
    
    // Upgrade (Only show in Free version)
    if ( ! base47_he_is_pro_active() ) {
        add_submenu_page(
            'base47-he-dashboard',
            'Upgrade to Pro',
            '<span style="color:#f97316;font-weight:700;">🚀 Upgrade to Pro</span>',
            'manage_options',
            'base47-he-upgrade',
            'base47_he_upgrade_page'
        );
    }
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
    // Check for both base47-he- and base47-special-widgets pages
    if ( ! $screen || ( strpos( $screen->id, 'base47-he-' ) === false && strpos( $screen->id, 'base47-special-widgets' ) === false ) ) {
        return;
    }

    // ========================================
    // BASE47 UNIFIED COLOR PALETTE (Phase 16.8)
    // ========================================
    
    // Base47 Color System - MUST load first
    wp_enqueue_style(
        'base47-colors',
        BASE47_HE_URL . 'admin-assets/base47-colors.css',
        [],
        BASE47_HE_VERSION
    );
    
    // Pro Notices & Badges (Phase 16.1)
    wp_enqueue_style(
        'base47-pro-notices',
        BASE47_HE_URL . 'admin-assets/pro-notices.css',
        [ 'base47-colors' ],
        BASE47_HE_VERSION
    );
    
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
    
    // Settings page specific CSS (Phase 13.5.3 - Soft UI)
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-settings' ) {
        wp_enqueue_style(
            'base47-he-settings',
            BASE47_HE_URL . 'admin-assets/settings-soft-ui.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }
    
    // Shortcodes page specific CSS (Phase 13)
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-templates' ) {
        wp_enqueue_style(
            'base47-he-shortcodes',
            BASE47_HE_URL . 'admin-assets/shortcodes.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }
    
    // Changelog page specific CSS (Phase 13.5.1)
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-changelog' ) {
        wp_enqueue_style(
            'base47-he-changelog',
            BASE47_HE_URL . 'admin-assets/changelog.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }
    
    // Logs page specific CSS (Phase 13.5.2)
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-logs' ) {
        wp_enqueue_style(
            'base47-he-logs',
            BASE47_HE_URL . 'admin-assets/logs.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }
    
    // Special Widgets page specific CSS (Phase 13.5.4)
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-special-widgets' ) {
        wp_enqueue_style(
            'base47-he-widgets',
            BASE47_HE_URL . 'admin-assets/widgets.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }
    
    // Dashboard page specific CSS (Phase 14)
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-dashboard' ) {
        wp_enqueue_style(
            'base47-he-dashboard',
            BASE47_HE_URL . 'admin-assets/dashboard.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }
    
    // Upgrade page CSS (Phase 16.4)
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-upgrade' ) {
        wp_enqueue_style(
            'base47-he-upgrade',
            BASE47_HE_URL . 'admin-assets/upgrade.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }
    
    // License page CSS (Phase 16.4)
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-license' ) {
        wp_enqueue_style(
            'base47-he-license',
            BASE47_HE_URL . 'admin-assets/license.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }
    
    // Marketplace page CSS (Phase 16.5)
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-marketplace' ) {
        wp_enqueue_style(
            'base47-he-marketplace',
            BASE47_HE_URL . 'admin-assets/marketplace.css',
            [ 'base47-he-admin' ],
            BASE47_HE_VERSION
        );
    }
    
    // Live Editor page - editor.css removed in 2.9.7.7 (caused layout issues)

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
