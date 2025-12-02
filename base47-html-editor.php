<?php
/*
Plugin Name: Base47 HTML Editor
Description: Turn HTML templates in any *-templates folder into shortcodes, edit them live, and manage which theme-sets are active via toggle switches.
Version: 2.9.2.1
Author: Stefan Gold
Text Domain: base47-html-editor
*/



if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
| CONSTANTS
-------------------------------------------------------------------------- */
define( 'BASE47_HE_VERSION', '2.9.2.1' );
define( 'BASE47_HE_PATH', plugin_dir_path( __FILE__ ) );
define( 'BASE47_HE_URL',  plugin_dir_url( __FILE__ ) );


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
| calling helper
-------------------------------------------------------------------------- */

// Core loader + manifest engine
require_once BASE47_HE_PATH . 'inc/core-loader.php';

// Helpers
require_once BASE47_HE_PATH . 'inc/helpers/logs.php';
require_once BASE47_HE_PATH . 'inc/helpers/templates.php';
require_once BASE47_HE_PATH . 'inc/helpers/metadata.php';

// Operations
require_once BASE47_HE_PATH . 'inc/operations/theme-install.php';
require_once BASE47_HE_PATH . 'inc/operations/theme-delete.php';

// Systems
require_once BASE47_HE_PATH . 'inc/systems/special-widgets.php';

// AJAX Handlers
require_once BASE47_HE_PATH . 'inc/ajax/preview.php';
require_once BASE47_HE_PATH . 'inc/ajax/editor.php';
require_once BASE47_HE_PATH . 'inc/ajax/theme-manager.php';
require_once BASE47_HE_PATH . 'inc/ajax/cache.php';

// Admin Pages
require_once BASE47_HE_PATH . 'inc/admin-pages/logs.php';



/* --------------------------------------------------------------------------
| OPTIONS
-------------------------------------------------------------------------- */
const BASE47_HE_OPT_ACTIVE_THEMES  = 'base47_active_themes';     // array of active set slugs
const BASE47_HE_OPT_USE_MANIFEST   = 'base47_use_manifest';      // array of sets using manifest
const BASE47_HE_OPT_USE_SMART_LOADER = 'base47_he_use_smart_loader'; 
const BASE47_HE_OPT_SETTINGS_NONCE = 'base47_he_settings_nonce';


/* --------------------------------------------------------------------------
| DISCOVERY  find all template sets 
-------------------------------------------------------------------------- */
/**
 * Discover theme sets (*-templates folders) with smart caching.
 * Now ALSO loads metadata from theme.json inside each folder.
 */
function base47_he_get_template_sets( $force = false ) {

    static $static = null;
    if ( $static !== null && ! $force ) {
        return $static;
    }

    require_once BASE47_HE_PATH . 'inc/class-base47-cache.php';

    // NEW: get uploads/base47-themes root
    $root = base47_he_get_themes_root();
    $themes_dir = trailingslashit( $root['dir'] );
    $themes_url = trailingslashit( $root['url'] );

    // --- SIGNATURE BASED ON UPLOADS FOLDER ---
    $saved             = get_transient( Base47_Cache::TRANS_SETS );
    $current_signature = Base47_Cache::get_signature( $themes_dir . '*-templates' );

    if (
        ! $force &&
        is_array( $saved ) &&
        isset( $saved['sets'], $saved['signature'] ) &&
        hash_equals( $saved['signature'], $current_signature )
    ) {
        $static = $saved['sets'];
        return $static;
    }

    // --- SCAN uploads/base47-themes ---
    $sets = [];

    foreach ( glob( $themes_dir . '*-templates', GLOB_ONLYDIR ) as $dir ) {

        $slug = basename( $dir );

      // ------------------------------------------------
// NEW: Load metadata from theme.json
// ------------------------------------------------
$meta = base47_he_load_theme_metadata( $dir );

$sets[ $slug ] = [
    'slug'        => $slug,
    'path'        => trailingslashit( $dir ),
    'url'         => trailingslashit( $themes_url . $slug ),

    // Metadata from theme.json (or empty fallback)
    'label'       => $meta['label']       ?? $slug,
    'description' => $meta['description'] ?? '',
    'version'     => $meta['version']     ?? '1.0.0',
    'accent'      => $meta['accent']      ?? '#7C5CFF',
    'thumbnail'   => $meta['thumbnail']   ?? '',
];
    }

    ksort( $sets, SORT_NATURAL | SORT_FLAG_CASE );

    set_transient( Base47_Cache::TRANS_SETS, [
        'sets'      => $sets,
        'signature' => $current_signature,
    ], Base47_Cache::CACHE_TIME );

    $static = $sets;
    return $sets;
}


/**
 * Get list of templates per theme set.
 *
 * Returns array like:
 * [
 *   'mivon-templates' => [
 *       'home-1.html' => '/full/path/to/home-1.html',
 *       ...
 *   ],
 *   'redox-templates' => [ ... ],
 * ]
 *
 * STRUCTURE ONLY ? CONTENT IS NOT CACHED.
 */
function base47_he_get_template_list( $force = false ) {

    static $static = null;
    if ( $static !== null && ! $force ) {
        return $static;
    }

    require_once BASE47_HE_PATH . 'inc/class-base47-cache.php';

    $sets = base47_he_get_template_sets( $force );

// NEW signature path
$root = base47_he_get_themes_root();
$sig  = Base47_Cache::get_signature( $root['dir'] . '*-templates/*' );

$saved = get_transient( Base47_Cache::TRANS_TEMPLATES );

    if (
        ! $force &&
        is_array( $saved ) &&
        isset( $saved['templates'], $saved['signature'] ) &&
        hash_equals( $saved['signature'], $sig )
    ) {
        $static = $saved['templates'];
        return $static;
    }

    $templates = [];

    foreach ( $sets as $set_slug => $info ) {
        $templates[ $set_slug ] = [];

        foreach ( glob( $info['path'] . '*.html' ) as $file ) {
            $name = basename( $file );
            $templates[ $set_slug ][ $name ] = $file;
        }
    }

    set_transient( Base47_Cache::TRANS_TEMPLATES, [
        'templates' => $templates,
        'signature' => $sig,
    ], Base47_Cache::CACHE_TIME );

    $static = $templates;
    return $templates;
}

/**
 * Manually refresh caches related to theme sets + templates.
 * We'll call this from Theme Manager (install/uninstall/refresh).
 */
function base47_he_refresh_theme_caches() {
    require_once BASE47_HE_PATH . 'inc/class-base47-cache.php';
    Base47_Cache::clear_all();
    base47_he_get_template_sets( true );
    base47_he_get_template_list( true );
}




/**
 * Helper: force refresh of template set cache.
 * We will call this later from Theme Manager (e.g. after install/uninstall).
 */
function base47_he_refresh_template_sets_cache() {
    delete_transient( 'base47_he_cache_template_sets' );
    // Force next call to rescan filesystem
    base47_he_get_template_sets( true );
}


/** Return only the active theme set slugs (persisted). */
function base47_he_get_active_sets() {
    $all  = base47_he_get_template_sets();
    $opt  = get_option( BASE47_HE_OPT_ACTIVE_THEMES, [] );
    $opt  = is_array( $opt ) ? array_values( array_unique( array_filter( $opt ) ) ) : [];

    // Filter to only those that still exist
    $active = array_values( array_intersect( array_keys( $all ), $opt ) );

    // If nothing persisted, fall back to sane default
    if ( empty( $active ) && ! empty( $all ) ) {
        $active = [ array_key_first( $all ) ];
        update_option( BASE47_HE_OPT_ACTIVE_THEMES, $active );
    }

    return $active;
}

/** True if a set slug is active. */
function base47_he_is_set_active( $set_slug ) {
    return in_array( $set_slug, base47_he_get_active_sets(), true );
}

/** All templates across sets (restricted to active sets unless $include_inactive = true). */
function base47_he_get_all_templates( $include_inactive = false ) {
    $sets   = base47_he_get_template_sets();
    $active = $include_inactive ? array_keys( $sets ) : base47_he_get_active_sets();
    $all    = [];

    foreach ( $active as $set_slug ) {
        if ( ! isset( $sets[ $set_slug ] ) ) continue;
        $dir = $sets[ $set_slug ]['path'];
        if ( ! is_dir( $dir ) ) continue;

        $it = new DirectoryIterator( $dir );
        foreach ( $it as $f ) {
            if ( $f->isFile() ) {
                $name = $f->getFilename();
                $ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
                if ( in_array( $ext, ['html','htm'], true ) ) {
                    $all[] = [ 'set' => $set_slug, 'file' => $name ];
                }
            }
        }
    }

    usort( $all, function( $a, $b ) {
        return strcasecmp( $a['set'] . '/' . $a['file'], $b['set'] . '/' . $b['file'] );
    });

    return $all;
}

/** Locate a filename across sets; prefer active, then inactive. */
function base47_he_locate_template( $filename ) {
    $sets = base47_he_get_template_sets();
    // First pass: active sets
    foreach ( base47_he_get_active_sets() as $set_slug ) {
        if ( isset( $sets[ $set_slug ] ) ) {
            $full = $sets[ $set_slug ]['path'] . $filename;
            if ( file_exists( $full ) ) {
                return [
                    'set'  => $set_slug,
                    'path' => $full,
                    'url'  => $sets[ $set_slug ]['url'],
                ];
            }
        }
    }
    // Second pass: any set
    foreach ( $sets as $set_slug => $set ) {
        $full = $set['path'] . $filename;
        if ( file_exists( $full ) ) {
            return [
                'set'  => $set_slug,
                'path' => $full,
                'url'  => $set['url'],
            ];
        }
    }
    return null;
}

/* --------------------------------------------------------------------------
| ACTIVATION
-------------------------------------------------------------------------- */
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

function base47_he_activate() {

    // 1?? Run migration first ? ensures the folder exists
    base47_he_migrate_options();

    // 2?? Ensure default active sets saved
    base47_he_get_active_sets();

    // 3?? Ensure default theme exists
    $sets = base47_he_get_template_sets(true); // force scan
    $default = get_option('base47_default_theme', '');

    if ( empty($default) && ! empty($sets) ) {
        update_option('base47_default_theme', array_key_first($sets));
    }
}
register_activation_hook( __FILE__, 'base47_he_activate' );

/**
 * Register shortcodes using unified format:
 * [base47-{theme}-{template}]
 *
 * Plus backward compatibility:
 *  - [base47-{template}]  (old Base47 / Mivon style)
 *  - [mivon-{template}]   (very old Mivon plugin)
 */
add_action( 'init', function() {

    $sets = base47_he_get_template_sets();
    if ( empty( $sets ) ) return;

    foreach ( $sets as $set_slug => $set ) {

        // theme prefix = mivon, redox, lezar, bfolio
        $theme_prefix = str_replace([ '-templates', '-templetes' ], '', $set_slug);

        foreach (glob($set['path'] . '*.html') as $file_path) {

            $file = basename($file_path);
            $slug = base47_he_filename_to_slug($file);

            // FINAL shortcode format ? ALWAYS theme + template
            $shortcode = 'base47-' . $theme_prefix . '-' . $slug;

            add_shortcode($shortcode, function() use ($set, $file) {
                $full     = $set['path'] . $file;
                $base_url = $set['url'];

                if (!file_exists($full)) return '';

                $html = file_get_contents($full);
                $html = base47_he_rewrite_assets($html, $base_url, false);
                return $html;
            });

            // BACKWARD COMPATIBILITY ? ONLY for old Mivon/Base47 shortcodes
            if ($theme_prefix === 'mivon') {
                add_shortcode('mivon-' . $slug, function() use ($set, $file) {
                    $full     = $set['path'] . $file;
                    $base_url = $set['url'];
                    if (!file_exists($full)) return '';
                    $html = file_get_contents($full);
                    return base47_he_rewrite_assets($html, $base_url, false);
                });
            }
        }
    }
});
/* --------------------------------------------------------------------------
| UTILITIES
-------------------------------------------------------------------------- */
function base47_he_filename_to_slug( $filename ) {
    $base = pathinfo( $filename, PATHINFO_FILENAME );
    $slug = sanitize_title_with_dashes( $base );
    return $slug ?: ( 'tpl-' . md5( $filename ) );
}

/* --------------------------------------------------------------------------
| SPECIAL WIDGETS ADMIN PAGE - Moved to inc/admin-pages/widgets.php
-------------------------------------------------------------------------- */

/* --------------------------------------------------------------------------
| ADMIN PAGES (Load UI files)
-------------------------------------------------------------------------- */
require_once BASE47_HE_PATH . 'inc/admin-pages/dashboard.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/shortcodes.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/editor.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/theme-manager.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/widgets.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/changelog.php';

/* --------------------------------------------------------------------------
| ADMIN MENUS
-------------------------------------------------------------------------- */
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
        'base47-he-settings',
        'base47_he_settings_page'
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
 * Enqueue admin assets for Base47 (including Theme Manager)
 */
function base47_he_admin_assets( $hook ) {
	$screen = get_current_screen();
    if ( ! $screen || strpos( $screen->id, 'base47-he-' ) === false ) {
    return;
}

 // Existing admin CSS/JS for Base47
wp_enqueue_style(
    'base47-he-admin',
    BASE47_HE_URL . 'admin-assets/admin.css',
    [],
    BASE47_HE_VERSION
);

wp_enqueue_script(
    'base47-he-admin',
    BASE47_HE_URL . 'admin-assets/admin.js',
    [ 'jquery' ],
    BASE47_HE_VERSION,
    true
);

/**
 * LOCALIZE ? admin.js (IMPORTANT)
 * Provides AJAX + NONCE for editor + lazy preview
 */
wp_localize_script(
    'base47-he-admin',
    'BASE47_HE',
    [
        'ajax_url'     => admin_url('admin-ajax.php'),
        'nonce'        => wp_create_nonce('base47_he'),
		'default_set'  => base47_he_detect_default_theme(),
    ]
);


// NEW: Theme Manager glass CSS
wp_enqueue_style(
    'base47-he-theme-manager',
    BASE47_HE_URL . 'admin-assets/theme-manager.css',
    [ 'base47-he-admin' ],
    BASE47_HE_VERSION
);

// NEW: Theme Manager JS
wp_enqueue_script(
    'base47-he-theme-manager',
    BASE47_HE_URL . 'admin-assets/theme-manager.js',
    [ 'jquery' ],
    BASE47_HE_VERSION,
    true
);

/**
 * LOCALIZE ? theme-manager.js
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



/* --------------------------------------------------------------------------
| ADMIN PAGES - Moved to inc/admin-pages/
-------------------------------------------------------------------------- */
// Dashboard: inc/admin-pages/dashboard.php
// Shortcodes: inc/admin-pages/shortcodes.php  
// Editor: inc/admin-pages/editor.php
// Theme Manager: inc/admin-pages/theme-manager.php
// Widgets: inc/admin-pages/widgets.php
// Changelog: inc/admin-pages/changelog.php
// Logs: inc/admin-pages/logs.php
