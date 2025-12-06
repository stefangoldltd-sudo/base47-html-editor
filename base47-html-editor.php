<?php
/*
Plugin Name: Base47 HTML Editor
Description: Turn HTML templates in any *-templates folder into shortcodes, edit them live, and manage which theme-sets are active via toggle switches.
Version: 2.9.9.1
Author: Stefan Gold
Author URI: https://base47.com
Plugin URI: https://base47.com/html-editor
Text Domain: base47-html-editor
Domain Path: /languages
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.0
*/




if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
| CONSTANTS
-------------------------------------------------------------------------- */
define( 'BASE47_HE_VERSION', '2.9.9.1' );
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

// GitHub Updater (Base47)
require_once BASE47_HE_PATH . 'inc/class-base47-github-updater.php';

new Base47_GitHub_Updater(
    __FILE__,
    'stefangoldltd-sudo/base47-html-editor',  // GitHub repo
    BASE47_HE_VERSION                           // version from this plugin
);


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

// Admin initialization
require_once BASE47_HE_PATH . 'inc/admin-init.php';

// Helpers
require_once BASE47_HE_PATH . 'inc/helpers/settings.php';
require_once BASE47_HE_PATH . 'inc/helpers/logs.php';
require_once BASE47_HE_PATH . 'inc/helpers/templates.php';
require_once BASE47_HE_PATH . 'inc/helpers/metadata.php';
require_once BASE47_HE_PATH . 'inc/helpers/backups.php';

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

// Admin Pages
require_once BASE47_HE_PATH . 'inc/admin-pages/dashboard.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/shortcodes.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/editor.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/theme-manager.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/widgets.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/settings.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/changelog.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/logs.php';

/* --------------------------------------------------------------------------
| HOOK REGISTRATIONS
-------------------------------------------------------------------------- */

// Plugin activation (handled in inc/activation.php)
register_activation_hook( __FILE__, 'base47_he_activate' );

// Note: Shortcode registration happens via add_action('init') inside inc/shortcodes.php
// Note: Admin menu registration happens via add_action('admin_menu') inside inc/admin-init.php
// Note: Admin assets enqueuing happens via add_action('admin_enqueue_scripts') inside inc/admin-init.php
