<?php
/*
Plugin Name: Base47 HTML Editor
Description: Turn HTML templates in any *-templates folder into shortcodes, edit them live, and manage which theme-sets are active via toggle switches.
Version: 2.9.0
Author: Stefan Gold
Text Domain: base47-html-editor
*/



if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
| CONSTANTS
-------------------------------------------------------------------------- */
define( 'BASE47_HE_VERSION', '2.9.0' );
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
// Logging
require_once BASE47_HE_PATH . 'inc/helpers/logs.php';
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



/**
 * Safe theme metadata helper for Theme Manager cards.
 * Reads /theme.json if present, otherwise falls back to nice defaults.
 */
function base47_he_theme_metadata( $slug ) {

    // Default structure so array keys always exist
    $meta = array(
        'title'       => '',
        'version'     => '',
        'author'      => '',
        'description' => '',
        'tags'        => array(),
    );

    // Themes root
    if ( ! function_exists( 'base47_he_get_themes_root' ) ) {
        // If something went very wrong, just return defaults
        $meta['title'] = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
        return $meta;
    }

    $root       = base47_he_get_themes_root();
    $themes_dir = trailingslashit( $root['dir'] );

    $theme_dir  = $themes_dir . $slug . '/';
    $json_file  = $theme_dir . 'theme.json';

    if ( file_exists( $json_file ) ) {
        $raw = file_get_contents( $json_file );
        $arr = json_decode( $raw, true );
        if ( is_array( $arr ) ) {
            $meta = array_merge( $meta, $arr );
        }
    }

    // Fallback title if theme.json is missing/empty
    if ( empty( $meta['title'] ) ) {
        $pretty = preg_replace( '#-templates?$#', '', $slug );
        $pretty = str_replace( array( '-', '_' ), ' ', $pretty );
        $meta['title'] = ucwords( $pretty );
    }

    return $meta;
}




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



/** THEME MANAGER (glass UI + install/delete/scan) */
function base47_he_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $notices = [];

    // --------------------------------------------------
    // HANDLE FORM ACTIONS (install / delete / scan)
    // --------------------------------------------------
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
       check_admin_referer( 'base47_he', 'nonce' );
		
        $action = isset( $_POST['base47_he_theme_action'] )
            ? sanitize_text_field( wp_unslash( $_POST['base47_he_theme_action'] ) )
            : '';

        switch ( $action ) {

            case 'install_theme':
                $result = base47_he_install_theme_from_upload();
                if ( is_wp_error( $result ) ) {
                    $notices[] = [
                        'type' => 'error',
                        'msg'  => $result->get_error_message(),
                    ];
                } else {
                    $notices[] = [
                        'type' => 'updated',
                        'msg'  => sprintf(
                            'Theme <strong>%s</strong> installed successfully.',
                            esc_html( $result )
                        ),
                    ];
                    base47_he_refresh_theme_caches();
                }
                break;

            case 'delete_theme':
                $slug = isset( $_POST['base47_delete_theme'] )
                    ? sanitize_text_field( wp_unslash( $_POST['base47_delete_theme'] ) )
                    : '';

                if ( ! $slug ) {
                    $notices[] = [
                        'type' => 'error',
                        'msg'  => 'No theme selected for deletion.',
                    ];
                    break;
                }

                $result = base47_he_delete_theme_folder( $slug );
                if ( is_wp_error( $result ) ) {
                    $notices[] = [
                        'type' => 'error',
                        'msg'  => $result->get_error_message(),
                    ];
                } else {
                    $notices[] = [
                        'type' => 'updated',
                        'msg'  => sprintf(
                            'Theme <strong>%s</strong> deleted successfully.',
                            esc_html( $slug )
                        ),
                    ];
                    base47_he_refresh_theme_caches();
                }
                break;

            case 'scan_themes':
                base47_he_refresh_theme_caches();
                $notices[] = [
                    'type' => 'updated',
                    'msg'  => 'Theme list refreshed successfully.',
                ];
                break;

            // NOTE:
            // We no longer "save" active themes here.
            // Active/inactive is handled live via AJAX (base47_he_ajax_toggle_theme).
        }
    }

    ?>
    <div class="wrap base47-he-wrap">
        <h1>Theme Manager</h1>

        <?php
        // NOTICES
        foreach ( $notices as $notice ) {
            $class = $notice['type'] === 'error' ? 'notice notice-error' : 'notice notice-success';
            echo '<div class="' . esc_attr( $class ) . '"><p>' . wp_kses_post( $notice['msg'] ) . '</p></div>';
        }
        ?>

        <!-- TOP ACTION BAR: INSTALL + SCAN -->
        <div style="margin:20px 0;padding:15px;border:1px solid #ddd;background:#fff;border-radius:6px;">
            <h2 style="margin-top:0;">Theme Actions</h2>

            <!-- Install ZIP -->
            <form method="post" enctype="multipart/form-data" style="margin-bottom:12px;">
                <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
                <input type="hidden" name="base47_he_theme_action" value="install_theme">
                <label for="base47_theme_zip" style="display:inline-block;margin-right:8px;">
                    <strong>Install Theme (ZIP):</strong>
                </label>
                <input type="file" name="base47_theme_zip" id="base47_theme_zip" accept=".zip">
                <button type="submit" class="button button-primary" style="margin-left:6px;">
                    Upload &amp; Install
                </button>
                <p class="description" style="margin-top:6px;">
                    ZIP must contain a folder like <code>lezar-templates/</code> or <code>bfolio-templates/</code>.
                </p>
            </form>

            <!-- Scan themes -->
            <form method="post" style="margin-top:10px;">
                  <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
				<input type="hidden" name="base47_he_theme_action" value="scan_themes">
                <button type="submit" class="button">
                    Scan Themes
                </button>
                <span class="description" style="margin-left:8px;">
                    Refresh the list after uploading theme folders via FTP.
                </span>
            </form>
        </div>
		
		<!-- Rebuild all caches -->
<form method="post" style="margin-top:10px;">
     <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
    <button type="button"
            id="base47-rebuild-caches-btn"
            class="button button-secondary">
        Rebuild All Caches
    </button>
    <span class="description" style="margin-left:8px;">

        <div class="base47-tm-footer-note">
            <p>Tip: keep only the themes you use enabled. Fewer active themes = faster Base47.</p>
        </div>

    </div>
    <?php
}

/**
 * Simple recursive rmdir helper.
 */
function base47_he_rrmdir( $dir ) {
    if ( ! is_dir( $dir ) ) {
        return true;
    }

    $items = scandir( $dir );
    if ( ! $items ) {
        return false;
    }

    foreach ( $items as $item ) {
        if ( $item === '.' || $item === '..' ) {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if ( is_dir( $path ) ) {
            if ( ! base47_he_rrmdir( $path ) ) {
                return false;
            }
        } else {
            if ( ! @unlink( $path ) ) {
                return false;
            }
        }
    }

    return @rmdir( $dir );
}

	


/**
 * Load theme metadata from theme.json inside a theme folder.
 *
 * This function looks for a file named "theme.json" inside the given path.
 * If found, it reads the file, decodes the JSON, and returns the metadata
 * (name, version, colors, thumbnail, etc.) as an array.
 *
 * If the file is missing or invalid, it returns an empty array so nothing breaks.
 */
function base47_he_load_theme_metadata( $path ) {
    $file = trailingslashit( $path ) . 'theme.json';

    // No metadata file ? return empty fallback
    if ( ! file_exists( $file ) ) {
        return [];
    }

    // Read JSON file
    $json = file_get_contents( $file );

    // Decode JSON into array (or empty array if invalid)
    $data = json_decode( $json, true );

    return is_array( $data ) ? $data : [];
}


/* --------------------------------------------------------------------------
| AJAX: Lazy Template Preview (For Shortcodes Page)
-------------------------------------------------------------------------- */
function base47_he_ajax_lazy_preview() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file = isset($_POST['file']) ? sanitize_text_field( wp_unslash($_POST['file']) ) : '';
    $set  = isset($_POST['set'])  ? sanitize_text_field( wp_unslash($_POST['set']) )  : '';

    if ( ! $file ) {
        wp_send_json_error( 'Missing file parameter.' );
    }

    $sets = base47_he_get_template_sets();

    // Auto-detect set if none provided
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) {
            wp_send_json_error( 'Template not found.' );
        }
        $set      = $info['set'];
        $full     = $info['path'];
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[$set] ) ) {
            wp_send_json_error( 'Invalid template set.' );
        }
        $full     = $sets[$set]['path'] . $file;
        $base_url = $sets[$set]['url'];
        if ( ! file_exists( $full ) ) {
            wp_send_json_error( 'Template file not found.' );
        }
    }

    $html = file_get_contents( $full );
    if ( false === $html ) {
        wp_send_json_error( 'Failed reading template.' );
    }

    // Rewrite asset URLs
    $html = base47_he_rewrite_assets( $html, $base_url, true );

    wp_send_json_success( [
        'html' => $html,
        'set'  => $set,
        'file' => $file,
    ] );
}
add_action( 'wp_ajax_base47_he_lazy_preview', 'base47_he_ajax_lazy_preview' );
add_action( 'wp_ajax_nopriv_base47_he_lazy_preview', 'base47_he_ajax_lazy_preview' );

/* --------------------------------------------------------------------------
| AJAX PREVIEW / GET / SAVE / LIVE PREVIEW
-------------------------------------------------------------------------- */
/** Detect default theme (for JS/editor). */
function base47_he_detect_default_theme() {
 $sets = base47_he_get_template_sets();
return array_key_first($sets) ?: '';
}


function base47_he_ajax_preview() {

    // Correct AJAX nonce check
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file = isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : '';
    $set  = isset( $_GET['set'] )  ? sanitize_text_field( wp_unslash( $_GET['set'] ) )  : '';

    if ( ! $file ) wp_die( 'Template not specified.' );

    // FIX: use the new correct option name
    $active = get_option( 'base47_active_themes', [] );
    if ( ! is_array( $active ) ) {
        $active = [];
    }

    // FIX: fallback if nothing active
    if ( empty( $active ) ) {
        $sets = base47_he_get_template_sets();
        $active = [ array_key_first( $sets ) ];
    }

    // FIX: If ?set? empty, use the first ACTIVE set
    if ( empty( $set ) ) {
        $set = $active[0];
    }

    $sets = base47_he_get_template_sets();

    // Validate chosen set
    if ( ! isset( $sets[ $set ] ) ) {
        wp_die( 'Template set not found.' );
    }

    // Build full path
    $full = $sets[ $set ]['path'] . $file;
    $base_url = $sets[ $set ]['url'];

    if ( ! file_exists( $full ) ) wp_die( 'Template not found.' );

    // Process HTML
    $html = file_get_contents( $full );
    $html = base47_he_rewrite_assets( $html, $base_url, true );

    echo $html;
    exit;
}
add_action( 'wp_ajax_base47_he_preview',        'base47_he_ajax_preview' );
add_action( 'wp_ajax_nopriv_base47_he_preview', 'base47_he_ajax_preview' );

function base47_he_ajax_get_template() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    $file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set  = isset( $_POST['set'] )  ? sanitize_text_field( wp_unslash( $_POST['set'] ) )  : '';

/* --------------------------------------------------------------------------
| SPECIAL WIDGETS: AUTO DISCOVERY VIA widget.json
-------------------------------------------------------------------------- */

/**
 * Scan /special-widgets/ for folders with widget.json
 * Returns array of widgets, keyed by slug.
 *
 * Structure:
 * [
 *   'hero-slider-mivon' => [
 *      'name'        => 'Hero Slider (Base47)',
 *      'slug'        => 'hero-slider-mivon',
 *      'description' => '...',
 *      'folder'      => 'hero-slider-mivon',
 *      'html'        => 'hero-slider-mivon.html',
 *      'css'         => [...],
 *      'js'          => [...],
 *   ],
 *   ...
 * ]
 */
function base47_he_get_special_widgets_registry() {
    static $cache = null;

    if ( $cache !== null ) {
        return $cache;
    }

    $cache = [];

    $base_dir = plugin_dir_path( __FILE__ ) . 'special-widgets/';
    if ( ! is_dir( $base_dir ) ) {
        return $cache;
    }

    $folders = scandir( $base_dir );
    if ( ! $folders ) {
        return $cache;
    }

    foreach ( $folders as $folder ) {
        if ( $folder === '.' || $folder === '..' ) {
            continue;
        }

        $widget_dir = $base_dir . $folder . '/';
        if ( ! is_dir( $widget_dir ) ) {
            continue;
        }

        $json_file = $widget_dir . 'widget.json';
        if ( ! file_exists( $json_file ) ) {
            // No widget.json => ignore this folder completely
            continue;
        }

        $json_raw = file_get_contents( $json_file );
        if ( ! $json_raw ) {
            continue;
        }

        $data = json_decode( $json_raw, true );
        if ( ! is_array( $data ) ) {
            continue;
        }

        // Minimal required fields
        if ( empty( $data['html'] ) ) {
            continue;
        }

        // Derive slug if missing
        $slug = ! empty( $data['slug'] ) ? sanitize_title( $data['slug'] ) : sanitize_title( $folder );

        $cache[ $slug ] = [
            'name'        => isset( $data['name'] ) ? $data['name'] : $slug,
            'slug'        => $slug,
            'description' => isset( $data['description'] ) ? $data['description'] : '',
            'folder'      => $folder,
            'html'        => $data['html'],
            'css'         => isset( $data['css'] ) && is_array( $data['css'] ) ? $data['css'] : [],
            'js'          => isset( $data['js'] ) && is_array( $data['js'] ) ? $data['js'] : [],
        ];
    }

    return $cache;
}

/* --------------------------------------------------------------------------
| SPECIAL WIDGET SHORTCODE: [base47_widget slug="hero-slider"]
-------------------------------------------------------------------------- */

function base47_he_special_widget_shortcode( $atts = [], $content = '' ) {
    $atts = shortcode_atts([
        'slug' => '',
    ], $atts, 'base47_widget' );

    $slug = sanitize_title( $atts['slug'] );
    if ( ! $slug ) {
        return '';
    }

    $widgets = base47_he_get_special_widgets_registry();
    if ( empty( $widgets[ $slug ] ) ) {
        // Fail silently - no widget with that slug
        return '';
    }

    $widget = $widgets[ $slug ];
    $folder = $widget['folder'];

    $plugin_url = plugin_dir_url( __FILE__ );
    $plugin_dir = plugin_dir_path( __FILE__ );

    $widget_dir_url  = $plugin_url . 'special-widgets/' . $folder . '/';
    $widget_dir_path = $plugin_dir . 'special-widgets/' . $folder . '/';

    // Enqueue CSS
    foreach ( $widget['css'] as $index => $css_rel ) {
        $css_path = $widget_dir_path . $css_rel;
        if ( ! file_exists( $css_path ) ) {
            continue;
        }

        $handle = 'base47-sw-' . $slug . '-css-' . $index;

        if ( ! wp_style_is( $handle, 'enqueued' ) ) {
            wp_enqueue_style(
                $handle,
                $widget_dir_url . $css_rel,
                [],
                filemtime( $css_path )
            );
        }
    }

    // Enqueue JS
    foreach ( $widget['js'] as $index => $js_rel ) {
        $js_path = $widget_dir_path . $js_rel;
        if ( ! file_exists( $js_path ) ) {
            continue;
        }

        $handle = 'base47-sw-' . $slug . '-js-' . $index;

        if ( ! wp_script_is( $handle, 'enqueued' ) ) {
            wp_enqueue_script(
                $handle,
                $widget_dir_url . $js_rel,
                [],
                filemtime( $js_path ),
                true
            );
        }
    }

    // Load HTML
    $html_file = $widget_dir_path . $widget['html'];
    if ( ! file_exists( $html_file ) ) {
        return '';
    }

    $html = file_get_contents( $html_file );
    if ( ! $html ) {
        return '';
    }

    // Path fix: if you used hardcoded /wp-content/plugins/... for this widget, normalize it
    $html = str_replace(
        '/wp-content/plugins/base47-html-editor/special-widgets/' . $folder . '/',
        $widget_dir_url,
        $html
    );
    // Backward compatibility: also replace old mivon path
    $html = str_replace(
        '/wp-content/plugins/mivon-html-editor/special-widgets/' . $folder . '/',
        $widget_dir_url,
        $html
    );

    return $html;
}
add_shortcode( 'base47_widget', 'base47_he_special_widget_shortcode' );
// Backward compatibility: support old mivon_widget shortcode
add_shortcode( 'mivon_widget', 'base47_he_special_widget_shortcode' );

// -----------------------------------------------
// LAZY PREVIEW MODAL (GLOBAL ADMIN FOOTER)
// -----------------------------------------------
function base47_he_preview_modal() {
    ?>
    <div id="base47-modal-overlay" style="display:none;">
        <div id="base47-modal-wrapper">
            <div id="base47-modal-header">
                <span id="base47-modal-title">Preview</span>
                <button id="base47-modal-close">?</button>
            </div>

            <div id="base47-modal-body">
                <iframe id="base47-modal-iframe"></iframe>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'admin_footer', 'base47_he_preview_modal' );


/**
 * AJAX: Toggle theme active/inactive
 */
function base47_he_ajax_toggle_theme() {

    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( 'You are not allowed to do this.', 'base47' ) );
    }

    $theme  = isset( $_POST['theme'] ) ? sanitize_key( wp_unslash( $_POST['theme'] ) ) : '';
    $active = isset( $_POST['active'] ) ? (int) $_POST['active'] : 0;

    $themes = base47_he_get_template_sets();

    if ( ! isset( $themes[ $theme ] ) ) {
        wp_send_json_error( __( 'Unknown theme.', 'base47' ) );
    }

    $active_themes = get_option( 'base47_active_themes', [] );
    if ( ! is_array( $active_themes ) ) {
        $active_themes = [];
    }

    if ( $active ) {
        if ( ! in_array( $theme, $active_themes, true ) ) {
            $active_themes[] = $theme;
        }
    } else {
        $active_themes = array_values( array_diff( $active_themes, [ $theme ] ) );
    }

    update_option( 'base47_active_themes', $active_themes );

    wp_send_json_success( [
        'theme'         => $theme,
        'active'        => $active,
        'active_themes' => $active_themes,
    ] );
}
add_action( 'wp_ajax_base47_toggle_theme', 'base47_he_ajax_toggle_theme' );


/**
 * Save default theme (AJAX)
 */
function base47_he_ajax_set_default_theme() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( empty($_POST['theme']) ) {
        wp_send_json_error('Missing theme');
    }

    $theme = sanitize_text_field($_POST['theme']);

    update_option('base47_default_theme', $theme);

    wp_send_json_success(['saved' => $theme]);
}
add_action('wp_ajax_base47_set_default_theme', 'base47_he_ajax_set_default_theme');


/**
 * AJAX: Rebuild ALL Base47 caches (sets + templates)
 */
add_action('wp_ajax_base47_rebuild_caches', 'base47_he_ajax_rebuild_caches');

function base47_he_ajax_rebuild_caches() {
    check_ajax_referer('base47_he', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied']);
    }

    // Force-refresh all caches
    base47_he_refresh_theme_caches();

    wp_send_json_success(['message' => 'All caches rebuilt']);
}