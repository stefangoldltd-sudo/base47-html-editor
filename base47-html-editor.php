<?php
/*
Plugin Name: Base47 HTML Editor
Description: Transform HTML templates into WordPress shortcodes with live Monaco editor, theme management, and smart asset loading. Perfect for developers and agencies working with HTML templates.
Version: 3.0.0
Author: Stefan Gold
Author URI: https://base47.com
Plugin URI: https://base47.com/html-editor
Text Domain: base47-html-editor
Domain Path: /languages
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.9.1
Requires PHP: 7.4
Network: false
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
| INTERNATIONALIZATION
-------------------------------------------------------------------------- */

/**
 * Load plugin textdomain for translations
 */
function base47_he_load_textdomain() {
    load_plugin_textdomain( 'base47-html-editor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'base47_he_load_textdomain' );

/* --------------------------------------------------------------------------
| CONSTANTS
-------------------------------------------------------------------------- */
define( 'BASE47_HE_VERSION', '3.0.0' );
define( 'BASE47_HE_PATH', plugin_dir_path( __FILE__ ) );
define( 'BASE47_HE_URL',  plugin_dir_url( __FILE__ ) );

/* --------------------------------------------------------------------------
| OPTIONS
-------------------------------------------------------------------------- */
const BASE47_HE_OPT_ACTIVE_THEMES  = 'base47_active_themes';     // array of active set slugs
const BASE47_HE_OPT_USE_MANIFEST   = 'base47_use_manifest';      // array of sets using manifest
const BASE47_HE_OPT_USE_SMART_LOADER = 'base47_he_use_smart_loader'; 
const BASE47_HE_OPT_SETTINGS_NONCE = 'base47_he_settings_nonce';


function base47_he_get_nonce() {
    return wp_create_nonce('base47_he');
}

/**
 * Central storage location for user themes.
 * /wp-content/uploads/base47-themes/{set}/
 */

function base47_he_get_themes_root() {
    static $root = null;
    if ( $root !== null ) {
        return $root;
    }

    $uploads = wp_upload_dir();
    $dir     = trailingslashit( $uploads['basedir'] ) . 'base47-themes/';
    $url     = trailingslashit( $uploads['baseurl'] ) . 'base47-themes/';

    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
    }

    $root = [
        'dir' => $dir,
        'url' => $url,
    ];

    return $root;
}

/* --------------------------------------------------------------------------
| INCLUDES
-------------------------------------------------------------------------- */

// Core loader + manifest engine
require_once BASE47_HE_PATH . 'inc/core-loader.php';

// Discovery & caching
require_once BASE47_HE_PATH . 'inc/discovery.php';

// Error handling & fallback mode (v2.9.8)
require_once BASE47_HE_PATH . 'inc/helpers/error-handler.php';
require_once BASE47_HE_PATH . 'inc/helpers/fallback-mode.php';
require_once BASE47_HE_PATH . 'inc/helpers/woocommerce-compat.php';

// Shortcode registration
require_once BASE47_HE_PATH . 'inc/shortcodes.php';

// Activation & migration
require_once BASE47_HE_PATH . 'inc/activation.php';

// Helpers
require_once BASE47_HE_PATH . 'inc/helpers/feature-detection.php';
require_once BASE47_HE_PATH . 'inc/helpers/settings.php';
require_once BASE47_HE_PATH . 'inc/helpers/logs.php';
require_once BASE47_HE_PATH . 'inc/helpers/templates.php';
require_once BASE47_HE_PATH . 'inc/helpers/metadata.php';
require_once BASE47_HE_PATH . 'inc/helpers/backups.php';
require_once BASE47_HE_PATH . 'inc/helpers/tooltips.php';

// Operations
require_once BASE47_HE_PATH . 'inc/operations/theme-install.php';
require_once BASE47_HE_PATH . 'inc/operations/theme-delete.php';

// Systems
require_once BASE47_HE_PATH . 'inc/systems/special-widgets.php';

// AJAX Handlers
require_once BASE47_HE_PATH . 'inc/ajax/preview.php';
require_once BASE47_HE_PATH . 'inc/ajax/editor.php';
require_once BASE47_HE_PATH . 'inc/ajax/theme-manager.php';
require_once BASE47_HE_PATH . 'inc/ajax/asset-mode.php';
require_once BASE47_HE_PATH . 'inc/ajax/cache.php';
require_once BASE47_HE_PATH . 'inc/ajax/settings.php';
require_once BASE47_HE_PATH . 'inc/ajax/license.php';
require_once BASE47_HE_PATH . 'inc/ajax/marketplace.php';
require_once BASE47_HE_PATH . 'inc/ajax/support.php';

// Admin Pages
require_once BASE47_HE_PATH . 'inc/admin-pages/dashboard.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/onboarding.php';  // V3 Feature
require_once BASE47_HE_PATH . 'inc/admin-pages/shortcodes.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/editor.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/theme-manager.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/marketplace.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/support.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/widgets.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/settings.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/changelog.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/logs.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/upgrade.php';  
require_once BASE47_HE_PATH . 'inc/admin-pages/license.php';   

// Admin initialization (MUST be after admin pages so functions exist)
require_once BASE47_HE_PATH . 'inc/admin-init.php';

/* --------------------------------------------------------------------------
| ONBOARDING REDIRECT (V3 FEATURE)
-------------------------------------------------------------------------- */

/**
 * Redirect new users to onboarding wizard
 */
function base47_he_onboarding_redirect() {
    // Only run in admin
    if ( ! is_admin() ) {
        return;
    }
    
    // Don't redirect during AJAX requests
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    
    // Don't redirect if already on onboarding page
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'base47-he-onboarding' ) {
        return;
    }
    
    // Check if user should see onboarding
    if ( ! base47_he_should_show_onboarding() ) {
        return;
    }
    
    // Only redirect on Base47 pages or dashboard
    $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
    $base47_pages = array(
        'base47-he-dashboard',
        'base47-he-templates',
        'base47-he-editor',
        'base47-he-theme-manager',
        'base47-he-marketplace',
        'base47-special-widgets',
        'base47-he-support',
        'base47-he-settings',
        'base47-he-logs',
        'base47-he-changelog',
        'base47-he-license',
        'base47-he-upgrade'
    );
    
    // Redirect if on a Base47 page
    if ( in_array( $current_page, $base47_pages ) ) {
        wp_redirect( admin_url( 'admin.php?page=base47-he-onboarding' ) );
        exit;
    }
}
add_action( 'admin_init', 'base47_he_onboarding_redirect' );

/* --------------------------------------------------------------------------
| HOOK REGISTRATIONS
-------------------------------------------------------------------------- */

// Plugin activation (handled in inc/activation.php)
register_activation_hook( __FILE__, 'base47_he_activate' );

// Note: Shortcode registration happens via add_action('init') inside inc/shortcodes.php
// Note: Admin menu registration happens via add_action('admin_menu') inside inc/admin-init.php
// Note: Admin assets enqueuing happens via add_action('admin_enqueue_scripts') inside inc/admin-init.php