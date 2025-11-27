<?php
/*
Plugin Name: Base47 HTML Editor
Description: Turn HTML templates in any *-templates folder into shortcodes, edit them live, and manage which theme-sets are active via toggle switches.
Version: 2.6.4.3
Author: Stefan Gold
Text Domain: base47-html-editor
*/


if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
| CONSTANTS
-------------------------------------------------------------------------- */
define( 'BASE47_HE_VERSION', '2.6.4.3' );
define( 'BASE47_HE_PATH', plugin_dir_path( __FILE__ ) );
define( 'BASE47_HE_URL',  plugin_dir_url( __FILE__ ) );

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
| OPTIONS
-------------------------------------------------------------------------- */
const BASE47_HE_OPT_ACTIVE_THEMES  = 'base47_active_themes';     // array of active set slugs
const BASE47_HE_OPT_USE_MANIFEST   = 'base47_use_manifest';      // array of sets using manifest
const BASE47_HE_OPT_SETTINGS_NONCE = 'base47_he_settings_nonce';

/* --------------------------------------------------------------------------
| DISCOVERY  find all template sets 
-------------------------------------------------------------------------- */
/**
 * Discover theme sets (*-templates folders) with smart caching.
 *
 * - Uses static cache (per request) so multiple calls are cheap.
 * - Uses transient cache (between requests) to avoid repeated glob().
 * - Detects folder add/remove using a signature of folder names.
 *
 * IMPORTANT:
 * This ONLY caches folder structure (paths + URLs), NOT template contents.
 * Live Editor still reads actual HTML files from disk, so changes are instant.
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
        $base = basename( $dir );

        $sets[ $base ] = [
            'slug' => $base,
            'path' => trailingslashit( $dir ),
            'url'  => trailingslashit( $themes_url . $base ),
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
 * STRUCTURE ONLY ñ CONTENT IS NOT CACHED.
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
    // Run migration first
    base47_he_migrate_options();
    // Ensure default active sets saved
    base47_he_get_active_sets();
}
register_activation_hook( __FILE__, 'base47_he_activate' );

/* --------------------------------------------------------------------------
| UTILITIES
-------------------------------------------------------------------------- */
function base47_he_filename_to_slug( $filename ) {
    $base = pathinfo( $filename, PATHINFO_FILENAME );
    $slug = sanitize_title_with_dashes( $base );
    return $slug ?: ( 'tpl-' . md5( $filename ) );
}

function base47_he_rewrite_assets( $html, $base_url, $add_ver = true ) {

    $base = trailingslashit( $base_url );

    // Catch ALL patterns of asset usage
    $patterns = [
        // src and href absolute
        '#src="/assets/#i',
        '#src=\'/assets/#i',
        '#href="/assets/#i',
        '#href=\'/assets/#i',

        // src and href relative
        '#src="assets/#i',
        '#src=\'assets/#i',
        '#href="assets/#i',
        '#href=\'assets/#i',

        // url(...)
        '#url\("/assets/#i',
        '#url\(\'/assets/#i',
        '#url\(/assets/#i',

        '#url\("assets/#i',
        '#url\(\'assets/#i',
        '#url\(assets/#i',

        // data-background
        '#data-background="/assets/#i',
        '#data-background=\'/assets/#i',
        '#data-background="assets/#i',
        '#data-background=\'assets/#i',
    ];

    $replacements = [
        'src="' . $base . 'assets/',
        "src='" . $base . 'assets/',
        'href="' . $base . 'assets/',
        "href='" . $base . 'assets/',

        'src="' . $base . 'assets/',
        "src='" . $base . 'assets/',
        'href="' . $base . 'assets/',
        "href='" . $base . 'assets/',

        'url("' . $base . 'assets/',
        "url('" . $base . 'assets/',
        'url(' . $base . 'assets/',

        'url("' . $base . 'assets/',
        "url('" . $base . 'assets/',
        'url(' . $base . 'assets/',

        'data-background="' . $base . 'assets/',
        "data-background='" . $base . 'assets/',
        'data-background="' . $base . 'assets/',
        "data-background='" . $base . 'assets/',
    ];

    // Rewrite the HTML
    $html = preg_replace( $patterns, $replacements, $html );

    // Optionally add version for cache busting
    if ( $add_ver ) {
        $ver = time();
        $html = preg_replace_callback(
            '#\b(src|href)=["\']('.preg_quote($base,'#').'assets/[^"\']+)#i',
            function( $m ) use ( $ver ) {
                $url = $m[2];
                if ( strpos( $url, '?ver=' ) === false ) {
                    $url .= ( strpos( $url, '?' ) === false ? '?ver=' : '&ver=' ) . $ver;
                }
                return $m[1] . '="' . $url . '"';
            },
            $html
        );
    }

    return $html;
}

/** Strip outer html/head/body while preserving inline styles/scripts. Also remove external assets tags. */
function base47_he_strip_shell( $html ) {
    $head = '';
    if ( preg_match( '#<head\b[^>]*>(.*?)</head>#is', $html, $m ) ) {
        $head = $m[1];
    }

    $body = $html;
    if ( preg_match( '#<body\b[^>]*>(.*?)</body>#is', $html, $m2 ) ) {
        $body = $m2[1];
    } else {
        $body = preg_replace( '#^.*?<html\b[^>]*>#is', '', $body );
        $body = preg_replace( '#</html>.*$#is', '', $body );
    }

    $inline = [];
    if ( $head ) {
        if ( preg_match_all( '#<style\b[^>]*>.*?</style>#is', $head, $ms ) ) {
            $inline = array_merge( $inline, $ms[0] );
        }
        if ( preg_match_all( '#<script(?![^>]*\bsrc=)[^>]*>.*?</script>#is', $head, $ms ) ) {
            $inline = array_merge( $inline, $ms[0] );
        }
    }

    $body = preg_replace( '#<link[^>]+href=["\']/?assets/[^>]+>#i', '', $body );
    $body = preg_replace( '#<script[^>]+src=["\']/?assets/[^>]+></script>#i', '', $body );
    $body = preg_replace( '#<(?:!DOCTYPE|/?:?html|/?:?head|/?:?body)[^>]*>#i', '', $body );

    return implode( "\n", $inline ) . "\n" . $body;
}

/** General asset URL helper for a given set and relative path (e.g. 'assets/css/main.css'). */
function base47_he_asset_url( $set_slug, $relative ) {
    $sets = base47_he_get_template_sets();
    if ( ! isset( $sets[ $set_slug ] ) ) return '';
    return trailingslashit( $sets[ $set_slug ]['url'] ) . ltrim( $relative, '/' );
}

/* Deprecated set-specific shortcuts (kept for back-compat) */
function base47_he_asset( $relative_path ) { return plugins_url( $relative_path, __FILE__ ); }
function base47_bfolio_asset( $file )       { return plugins_url( 'bfolio-rtl-templates/assets/' . ltrim( $file, '/' ), __FILE__ ); }
function base47_asset( $file )              { return plugins_url( 'mivon-templates/assets/' . ltrim( $file, '/' ), __FILE__ ); }


/**
 * Discover all manifest.json files inside each themeís folder
 * located in /wp-content/uploads/base47-themes/.
 *
 * Expected structure:
 *
 *   /wp-content/uploads/base47-themes/{set}-templates/
 *       ??? manifest.json
 *       ??? assets/
 *       ?     ??? css/
 *       ?     ??? js/
 *       ?     ??? img/
 *       ?     ??? vendor/
 *       ??? home-1.html
 *       ??? about.html
 *       ??? ...
 *
 * Each theme must follow the naming rule:
 *      {slug}-templates
 *
 * This loader supports:
 *   ï Unlimited themes
 *   ï Auto-loading manifest.json if present
 *   ï Fallback to loader mode for themes without manifest.json
 *
 * Manifest files define:
 *   {
 *     "name": "Redox Theme",
 *     "version": "1.0.0",
 *     "assets": {
 *         "css": [...],
 *         "js":  [...]
 *     }
 *   }
 *
 * NOTE:
 *   The plugin no longer stores themes inside its own /plugins directory.
 *   Themes now live permanently in uploads/base47-themes/
 *   so plugin updates will NEVER delete theme folders again.
 */
function base47_he_get_all_manifests() {
    static $cache = null;
    if ( $cache !== null ) return $cache;

    $cache = [];

    // NEW ROOT
    $root      = base47_he_get_themes_root();
    $themes_dir = $root['dir'];
    $themes_url = $root['url'];

    foreach ( glob( $themes_dir . '*-template*', GLOB_ONLYDIR ) as $template_dir ) {

        $set_folder = basename( $template_dir );
        $manifest   = $template_dir . '/manifest.json';

        if ( ! file_exists( $manifest ) ) continue;

        $raw  = file_get_contents( $manifest );
        $data = json_decode( $raw, true );
        if ( ! is_array( $data ) ) continue;

        $set_slug = $set_folder;

        // NEW PATHS
        $base_url  = trailingslashit( $themes_url . $set_folder . '/assets' );
        $base_path = trailingslashit( $themes_dir . $set_folder . '/assets' );

        $data['_base_url']      = $base_url;
        $data['_base_path']     = $base_path;
        $data['_set_slug']      = $set_slug;
        $data['_handle_prefix'] = ! empty( $data['handle_prefix'] )
            ? sanitize_key( $data['handle_prefix'] )
            : 'base47-' . sanitize_key( $set_slug );

        $cache[ $set_slug ] = $data;
    }

    return $cache;
}


/**
 * Enqueue assets for a given set.
 *
 * 1. If there is a manifest for this set õ use it (Option 2).
 * 2. If no manifest õ fall back to old "assets/css/*.css" + "assets/js/*.js".
 *
 * $set_slug is the folder name, e.g. "lezar-templates", "mivon-templates".
 */
function base47_he_enqueue_assets_for_set( $set_slug ) {

    // Only enqueue for active sets
    if ( ! base47_he_is_set_active( $set_slug ) ) {
        return;
    }

    $sets = base47_he_get_template_sets();
    if ( ! isset( $sets[ $set_slug ] ) ) {
        return;
    }

    // Check if this set is configured to use manifest
    $use_manifest_sets = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );
    $use_manifest = in_array( $set_slug, $use_manifest_sets, true );

    /* -------------------------------------------------
     * 1) Try manifest-based loading (only if enabled for this set)
     * ------------------------------------------------- */
    $manifests    = base47_he_get_all_manifests();
    $manifest_key = $set_slug; // use full folder name e.g. "lezar-templates"

    if ( $use_manifest && isset( $manifests[ $manifest_key ] ) ) {

        $m         = $manifests[ $manifest_key ];
        $base_url  = trailingslashit( $m['_base_url'] );   // .../assets/
        $base_path = trailingslashit( $m['_base_path'] );  // filesystem path to /assets/
        $prefix    = $m['_handle_prefix'];

        // Allow both:
        //  - "css": [...]
        //  - "global": { "css": [...], "js": [...] }
        $css_list = array();
        $js_list  = array();

        if ( ! empty( $m['css'] ) && is_array( $m['css'] ) ) {
            $css_list = $m['css'];
        } elseif ( ! empty( $m['global']['css'] ) && is_array( $m['global']['css'] ) ) {
            $css_list = $m['global']['css'];
        }

        if ( ! empty( $m['js'] ) && is_array( $m['js'] ) ) {
            $js_list = $m['js'];
        } elseif ( ! empty( $m['global']['js'] ) && is_array( $m['global']['js'] ) ) {
            $js_list = $m['global']['js'];
        }

        // CSS from manifest
        foreach ( $css_list as $relative ) {
            $relative = ltrim( $relative, '/\\' );
            $file     = $base_path . $relative;
            if ( ! file_exists( $file ) ) {
                continue;
            }
            $handle = $prefix . '-css-' . md5( $relative );
            wp_enqueue_style(
                $handle,
                $base_url . $relative,
                array(),
                @filemtime( $file )
            );
        }

        // JS from manifest
        foreach ( $js_list as $relative ) {
            $relative = ltrim( $relative, '/\\' );
            $file     = $base_path . $relative;
            if ( ! file_exists( $file ) ) {
                continue;
            }
            $handle = $prefix . '-js-' . md5( $relative );
            wp_enqueue_script(
                $handle,
                $base_url . $relative,
                array( 'jquery' ),
                @filemtime( $file ),
                true
            );
        }

        // Done, no need for fallback
        return;
    }


	
	
    /* -------------------------------------------------
     * 2) Fallback: old simple loader
     * ------------------------------------------------- */
    $css_dir = trailingslashit( $sets[ $set_slug ]['path'] ) . 'assets/css/';
    $js_dir  = trailingslashit( $sets[ $set_slug ]['path'] ) . 'assets/js/';

    if ( is_dir( $css_dir ) ) {
        foreach ( glob( $css_dir . '*.css' ) as $f ) {
            $handle = 'base47-he-css-' . md5( $set_slug . $f );
            wp_enqueue_style(
                $handle,
                $sets[ $set_slug ]['url'] . 'assets/css/' . basename( $f ),
                array(),
                @filemtime( $f )
            );
        }
    }

    if ( is_dir( $js_dir ) ) {
        foreach ( glob( $js_dir . '*.js' ) as $f ) {
            $handle = 'base47-he-js-' . md5( $set_slug . $f );
            wp_enqueue_script(
                $handle,
                $sets[ $set_slug ]['url'] . 'assets/js/' . basename( $f ),
                array( 'jquery' ),
                @filemtime( $f ),
                true
            );
        }
    }
}



/* --------------------------------------------------------------------------
| RENDERING
-------------------------------------------------------------------------- */

function base47_he_render_template( $filename, $set_slug = '' ) {
    $sets = base47_he_get_template_sets();

    if ( empty( $set_slug ) ) {
        $info = base47_he_locate_template( $filename );
        if ( ! $info ) return '';
        $set_slug = $info['set'];
        $full     = $info['path'];
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[ $set_slug ] ) ) return '';
        $full     = $sets[ $set_slug ]['path'] . $filename;
        $base_url = $sets[ $set_slug ]['url'];
        if ( ! file_exists( $full ) ) return '';
    }

    // If set is inactive õ do not render
    if ( ! base47_he_is_set_active( $set_slug ) ) {
        return '<!-- Base47 HTML: "'.$set_slug.'" is inactive. Enable it in Settings õ Theme Manager. -->';
    }

    $html = file_get_contents( $full );
    $html = base47_he_strip_shell( $html );
    $html = base47_he_rewrite_assets( $html, $base_url, true );

    // √¢≈ì‚Ä¶ allow nested shortcodes inside the HTML template
    $html = do_shortcode( $html );

    base47_he_enqueue_assets_for_set( $set_slug );
    return $html;
}

/* --------------------------------------------------------------------------
| SHORTCODES register ONLY for active sets
-------------------------------------------------------------------------- */
function base47_he_register_shortcodes() {
    $all = base47_he_get_all_templates( false ); // active only

    foreach ( $all as $item ) {
        $set  = $item['set'];
        $file = $item['file'];
        $slug = base47_he_filename_to_slug( $file );

        if ( $set === 'base47-templates' || $set === 'mivon-templates' ) {
            $shortcode = 'base47-' . $slug;
        } else {
            $set_clean = str_replace( ['-templates','-templetes'], '', $set );
            $shortcode = 'base47-' . $set_clean . '-' . $slug;
        }

        add_shortcode( $shortcode, function( $atts = [], $content = '' ) use ( $file, $set ) {
            return base47_he_render_template( $file, $set );
        } );
    }
}
add_action( 'init', 'base47_he_register_shortcodes', 20 );

/* --------------------------------------------------------------------------
| BACKWARD COMPATIBILITY: Legacy mivon-* shortcodes
-------------------------------------------------------------------------- */
function base47_he_register_legacy_shortcodes() {
    $all = base47_he_get_all_templates( false ); // active only

    foreach ( $all as $item ) {
        $set  = $item['set'];
        $file = $item['file'];
        $slug = base47_he_filename_to_slug( $file );

        if ( $set === 'base47-templates' || $set === 'mivon-templates' ) {
            $legacy_shortcode = 'mivon-' . $slug;
        } else {
            $set_clean = str_replace( ['-templates','-templetes'], '', $set );
            $legacy_shortcode = 'mivon-' . $set_clean . '-' . $slug;
        }

        add_shortcode( $legacy_shortcode, function( $atts = [], $content = '' ) use ( $file, $set, $legacy_shortcode ) {
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                error_log( "Base47 HTML Editor: Legacy shortcode [$legacy_shortcode] is deprecated. Use [base47-*] shortcodes instead." );
            }
            return base47_he_render_template( $file, $set );
        } );
    }
}
add_action( 'init', 'base47_he_register_legacy_shortcodes', 21 );

/* --------------------------------------------------------------------------
| SPECIAL WIDGETS ADMIN PAGE (AUTO)
-------------------------------------------------------------------------- */
function base47_special_widgets_page() {
    $widgets = base47_he_get_special_widgets_registry();
    ?>
    <div class="wrap base47-he-wrap">
        <h1 style="margin-bottom:20px;">Special Widgets</h1>

        <p style="font-size:15px;color:#555;margin-bottom:25px;">
            Below is a list of all special widgets discovered in the
            <code>special-widgets</code> folder (only folders that contain <code>widget.json</code>).
            Copy the shortcode to insert in any Base47 HTML template.
        </p>

        <?php if ( empty( $widgets ) ) : ?>

            <p style="margin-top:15px;color:#777;">
                No special widgets found. To add one, create a folder in
                <code>special-widgets/</code> with a <code>widget.json</code> file.
            </p>

        <?php else : ?>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:200px;">Widget</th>
                    <th>Description</th>
                    <th style="width:220px;">Shortcode</th>
                    <th style="width:100px;">Preview</th>
                </tr>
            </thead>

            <tbody>
            <?php
            $plugin_url = plugin_dir_url( __FILE__ );
            foreach ( $widgets as $w ) :
                $folder  = $w['folder'];
                $html    = $w['html'];
                $name    = $w['name'];
                $desc    = $w['description'];
                $slug    = $w['slug'];
                $shortcode = '[base47_widget slug="' . esc_attr( $slug ) . '"]';
                $preview  = $plugin_url . 'special-widgets/' . $folder . '/' . $html;
            ?>
                <tr>
                    <td><strong><?php echo esc_html( $name ); ?></strong></td>
                    <td><?php echo esc_html( $desc ); ?></td>
                    <td><code><?php echo esc_html( $shortcode ); ?></code></td>
                    <td>
                        <a href="<?php echo esc_url( $preview ); ?>"
                           target="_blank"
                           class="button button-primary button-small">
                           Preview
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>

        <p style="margin-top:25px;color:#777;font-size:13px;">
            This list is generated automatically from folders in
            <code>special-widgets/</code>. Only folders with a <code>widget.json</code> file are shown.
        </p>
    </div>
    <?php
}

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
function base47_he_admin_assets( $hook ) {
    if ( strpos( $hook, 'base47-he-' ) === false && strpos( $hook, 'base47-special-widgets' ) === false ) {
        return;
    }

    wp_enqueue_style(
        'base47-he-admin',
        BASE47_HE_URL . 'admin-assets/admin.css',
        [],
        BASE47_HE_VERSION
    );

    wp_enqueue_script(
        'base47-he-admin',
        BASE47_HE_URL . 'admin-assets/admin.js',
        ['jquery'],
        BASE47_HE_VERSION,
        true
    );

    wp_localize_script(
        'base47-he-admin',
        'BASE47_HE_DATA',
        [
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'base47_he_editor' ),
            'default_set' => base47_he_detect_default_theme(),
        ]
    );

    // Minimal inline styles for toggle switches if admin.css missing
    $css = '
    .base47-switch { position:relative; display:inline-block; width:54px; height:28px; }
    .base47-switch input{ display:none; }
    .base47-slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:#ccc; transition:.3s; border-radius:28px; }
    .base47-slider:before { position:absolute; content:""; height:22px; width:22px; left:3px; top:3px; background:white; transition:.3s; border-radius:50%; }
    .base47-switch input:checked + .base47-slider { background:#2ecc71; }
    .base47-switch input:checked + .base47-slider:before { transform: translateX(26px); }
    .base47-he-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;}
    .base47-box{border:1px solid #ddd;border-radius:8px;padding:12px;background:#fff;}
    .base47-box h3{margin:4px 0 10px;font-size:15px;}
    .base47-muted{color:#666;font-size:12px;}
    .base47-he-template-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin:16px 0;}
    .base47-he-template-box{border:1px solid #ddd;border-radius:8px;padding:12px;background:#fff;}
    .base47-he-template-thumb iframe{width:100%;height:320px;border:1px solid #eee;border-radius:6px;}
    .base47-he-preview-toolbar{display:flex;gap:8px;margin-bottom:10px;}
    ';
    wp_add_inline_style( 'base47-he-admin', $css );
}
add_action( 'admin_enqueue_scripts', 'base47_he_admin_assets' );

/* --------------------------------------------------------------------------
| ADMIN PAGES (existing)
-------------------------------------------------------------------------- */
function base47_he_dashboard_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $sets   = base47_he_get_template_sets();
    $active = base47_he_get_active_sets();
    $all    = base47_he_get_all_templates( true );

    $counts = [];
    foreach ( $all as $item ) {
        $counts[ $item['set'] ] = ( $counts[ $item['set'] ] ?? 0 ) + 1;
    }
    ?>
    <div class="wrap base47-he-wrap">
        <h1>Base47 HTML Editor</h1>
        <p>Version: <?php echo esc_html( BASE47_HE_VERSION ); ?></p>

        <h2 style="margin-top:24px;">Theme Sets</h2>
        <div class="base47-he-grid">
            <?php foreach ( $sets as $slug => $set ) : ?>
                <div class="base47-box">
                    <h3><?php echo esc_html( $slug ); ?></h3>
                    <p class="base47-muted">
                        Status: <?php echo base47_he_is_set_active( $slug ) ? 'Active' : 'Inactive'; ?> |
                        Templates: <?php echo intval( $counts[ $slug ] ?? 0 ); ?>
                    </p>
                    <p class="base47-muted">Path: <code><?php echo esc_html( $set['path'] ); ?></code></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function base47_he_templates_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $active = base47_he_get_active_sets();
    $sets   = base47_he_get_template_sets();

    if ( empty( $active ) ) {
        echo '<div class="wrap"><h1>Shortcodes</h1><p>No active themes. Go to <strong>Theme Manager</strong> to enable one.</p></div>';
        return;
    }

    $by_set = [];
    foreach ( base47_he_get_all_templates( false ) as $item ) {
        $by_set[ $item['set'] ][] = $item['file'];
    }
    ?>
    <div class="wrap base47-he-wrap">
        <h1>Shortcodes</h1>
        <p>Only <strong>active</strong> theme sets are listed. Toggle sets in <em>Theme Manager</em>.</p>

        <?php foreach ( $active as $set_slug ) :
            $files = $by_set[ $set_slug ] ?? [];
            ?>
            <h2><?php echo esc_html( $set_slug ); ?></h2>

            <?php if ( empty( $files ) ) : ?>
                <p class="base47-muted">No templates found in this set.</p>

            <?php else : ?>
                <div class="base47-he-template-grid">
                    <?php foreach ( $files as $file ) :

                        // Shortcode generation
                        $slug = base47_he_filename_to_slug( $file );

                        if ( $set_slug === 'base47-templates' || $set_slug === 'mivon-templates' ) {
                            $shortcode = '[base47-'.$slug.']';
                        } else {
                            $set_clean = str_replace( ['-templates','-templetes'], '', $set_slug );
                            $shortcode = '[base47-'.$set_clean.'-'.$slug.']';
                        }

                        ?>
                        <div class="base47-he-template-box">
                            <strong><?php echo esc_html( $file ); ?></strong>
                            <code><?php echo esc_html( $shortcode ); ?></code>

                            <!-- ? REPLACED IFRAME WITH PREVIEW BUTTON -->
                            <div class="base47-he-template-thumb">
                                <button class="button button-secondary base47-preview-btn"
                                        data-file="<?php echo esc_attr( $file ); ?>"
                                        data-set="<?php echo esc_attr( $set_slug ); ?>">
                                    Preview
                                </button>
                            </div>

                            <div class="base47-he-template-actions">
                                <button class="button base47-he-copy" 
                                        data-shortcode="<?php echo esc_attr( $shortcode ); ?>">
                                    Copy shortcode
                                </button>

                                <a class="button"
                                   href="<?php echo admin_url( 
                                       'admin.php?page=base47-he-editor&set=' 
                                       . rawurlencode( $set_slug ) 
                                       . '&file=' 
                                       . rawurlencode( $file ) 
                                   ); ?>">
                                   Edit
                                </a>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    </div>
    <?php
}

function base47_he_editor_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $sets_all   = base47_he_get_template_sets();
    $active     = base47_he_get_active_sets();

    if ( empty( $active ) ) {
        echo '<div class="wrap"><h1>Live Editor</h1><p>No active themes. Enable at least one in <strong>Theme Manager</strong>.</p></div>';
        return;
    }

    $current_set = isset( $_GET['set'] ) ? sanitize_text_field( wp_unslash( $_GET['set'] ) ) : $active[0];
    if ( ! in_array( $current_set, $active, true ) ) {
        $current_set = $active[0];
    }

    $files = [];
    if ( isset( $sets_all[ $current_set ] ) && is_dir( $sets_all[ $current_set ]['path'] ) ) {
        foreach ( new DirectoryIterator( $sets_all[ $current_set ]['path'] ) as $f ) {
            if ( $f->isFile() ) {
                $ext = strtolower( pathinfo( $f->getFilename(), PATHINFO_EXTENSION ) );
                if ( in_array( $ext, ['html','htm'], true ) ) {
                    $files[] = $f->getFilename();
                }
            }
        }
    }
    sort( $files, SORT_NATURAL | SORT_FLAG_CASE );

    $selected = isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : ( $files[0] ?? '' );
    $content  = '';
    if ( $selected && isset( $sets_all[ $current_set ] ) && file_exists( $sets_all[ $current_set ]['path'] . $selected ) ) {
        $content = file_get_contents( $sets_all[ $current_set ]['path'] . $selected );
    }

    $preview = $selected
        ? admin_url(
            'admin-ajax.php?action=base47_he_preview&file='
            . rawurlencode( $selected )
            . '&set=' . rawurlencode( $current_set )
            . '&_wpnonce=' . wp_create_nonce( 'base47_he_preview' )
        )
        : '';

    ?>
    <div class="wrap base47-he-wrap">
        <h1>Live Editor</h1>
        <div class="base47-he-editor-topbar">
            <form method="get">
                <input type="hidden" name="page" value="base47-he-editor">
                <select name="set" onchange="this.form.submit()">
                    <?php foreach ( $active as $set_slug ) : ?>
                        <option value="<?php echo esc_attr( $set_slug ); ?>" <?php selected( $set_slug, $current_set ); ?>>
                            <?php echo esc_html( $set_slug ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="file" onchange="this.form.submit()">
                    <?php foreach ( $files as $f ) : ?>
                        <option value="<?php echo esc_attr( $f ); ?>" <?php selected( $f, $selected ); ?>>
                            <?php echo esc_html( $f ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
             <?php if ( $selected ) : ?>
    <button id="base47-he-save" class="button button-primary">Save</button>
    <button id="base47-he-restore" class="button">Restore</button>
    <button id="base47-he-open-preview" class="button">Open Preview</button>
<?php endif; ?>
        </div>

        <div id="base47-he-editor-shell" class="base47-he-editor-shell">
            <div id="base47-he-editor-left" class="base47-he-editor-left">
                <textarea id="base47-he-code" style="width:100%;height:520px;"><?php echo esc_textarea( $content ); ?></textarea>
            </div>
            <div id="base47-he-resizer" class="base47-he-resizer"></div>
            <div class="base47-he-editor-right">
                <div class="base47-he-preview-toolbar">
                    <button type="button" class="button preview-size-btn active" data-size="100%">Full</button>
                    <button type="button" class="button preview-size-btn" data-size="1024">Desktop</button>
                    <button type="button" class="button preview-size-btn" data-size="768">Tablet</button>
                    <button type="button" class="button preview-size-btn" data-size="375">Mobile</button>
                </div>
                <div class="base47-he-preview-wrap">
                    <iframe id="base47-he-preview" src="<?php echo esc_url( $preview ); ?>"></iframe>
                </div>
            </div>
        </div>

     </div> <!-- close base47-he-editor-shell --> 
      
   <div class="base47-he-shortcuts-panel">
    <h2 class="base47-he-shortcuts-title">Keyboard Shortcuts</h2>

    <div class="base47-he-shortcuts-grid">

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + S</span>
            <span class="base47-he-shortcut-desc">Save template</span>
        </div>

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + P</span>
            <span class="base47-he-shortcut-desc">Open preview in new tab</span>
        </div>

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + 1</span>
            <span class="base47-he-shortcut-desc">Desktop preview</span>
        </div>

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + 2</span>
            <span class="base47-he-shortcut-desc">Tablet preview</span>
        </div>

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + 3</span>
            <span class="base47-he-shortcut-desc">Mobile preview</span>
        </div>

    </div>
</div>
      

        <input type="hidden" id="base47-he-current-file" value="<?php echo esc_attr( $selected ); ?>">
        <input type="hidden" id="base47-he-current-set" value="<?php echo esc_attr( $current_set ); ?>">
        <?php wp_nonce_field( 'base47_he_editor', 'base47_he_editor_nonce' ); ?>
    </div>
    <?php
}

/** THEME MANAGER (toggle switches + install/delete/scan) */
function base47_he_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $sets        = base47_he_get_template_sets();
    $active      = base47_he_get_active_sets();
    $notices     = [];

    // --------------------------------------------------
    // HANDLE FORM ACTIONS
    // --------------------------------------------------
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        check_admin_referer( BASE47_HE_OPT_SETTINGS_NONCE );

        $action = isset( $_POST['base47_he_theme_action'] )
            ? sanitize_text_field( wp_unslash( $_POST['base47_he_theme_action'] ) )
            : 'save';

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
                    $sets   = base47_he_get_template_sets( true );
                    $active = base47_he_get_active_sets();
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
                    $sets   = base47_he_get_template_sets( true );
                    $active = base47_he_get_active_sets();
                }
                break;

            case 'scan_themes':
                base47_he_refresh_theme_caches();
                $sets   = base47_he_get_template_sets( true );
                $active = base47_he_get_active_sets();
                $notices[] = [
                    'type' => 'updated',
                    'msg'  => 'Theme list refreshed successfully.',
                ];
                break;

            case 'save':
            default:
                // Active sets
                $new_active = isset( $_POST['base47_active_sets'] ) && is_array( $_POST['base47_active_sets'] )
                    ? array_values( array_intersect(
                        array_keys( $sets ),
                        array_map( 'sanitize_text_field', $_POST['base47_active_sets'] )
                    ) )
                    : [];

                if ( empty( $new_active ) && ! empty( $sets ) ) {
                    $first      = array_key_first( $sets );
                    $new_active = [ $first ];
                }

                update_option( BASE47_HE_OPT_ACTIVE_THEMES, $new_active );
                $active = $new_active;

                // Manifest preferences
                $use_manifest = isset( $_POST['base47_use_manifest'] ) && is_array( $_POST['base47_use_manifest'] )
                    ? array_values( array_intersect(
                        array_keys( $sets ),
                        array_map( 'sanitize_text_field', $_POST['base47_use_manifest'] )
                    ) )
                    : [];

                update_option( BASE47_HE_OPT_USE_MANIFEST, $use_manifest );

                $notices[] = [
                    'type' => 'updated',
                    'msg'  => 'Settings saved.',
                ];
                break;
        }
    }

    $use_manifest_sets = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );
    $manifests         = base47_he_get_all_manifests();

    ?>
    <div class="wrap base47-he-wrap">
        <h1>Theme Manager</h1>
        <p>
            Toggle which theme sets are <strong>Active</strong>. Only active sets are exposed as shortcodes
            and appear in Live Editor &amp; Shortcodes.
        </p>
        <p style="background:#fff3cd;padding:12px;border-left:4px solid #ffc107;margin:16px 0;">
            <strong>? Asset Loading:</strong>
            Choose <strong>Loader</strong> for heavy themes (Base47, Lezar, Bfolio, etc.),
            or <strong>Manifest</strong> for lightweight themes with a manifest.json.
        </p>

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
            <form method="post" enctype="multipart/form-data" style="margin-bottom:12px;">
                <?php wp_nonce_field( BASE47_HE_OPT_SETTINGS_NONCE ); ?>
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

            <form method="post" style="margin-top:10px;">
                <?php wp_nonce_field( BASE47_HE_OPT_SETTINGS_NONCE ); ?>
                <input type="hidden" name="base47_he_theme_action" value="scan_themes">
                <button type="submit" class="button">
                    Scan Themes
                </button>
                <span class="description" style="margin-left:8px;">
                    Refresh the list after uploading theme folders via FTP.
                </span>
            </form>
        </div>

        <!-- MAIN SETTINGS FORM (ACTIVE + ASSET MODE + DELETE) -->
        <form method="post" style="margin-top:20px;">
            <?php wp_nonce_field( BASE47_HE_OPT_SETTINGS_NONCE ); ?>
            <input type="hidden" name="base47_he_theme_action" value="save">

            <h2>Installed Theme Sets</h2>

            <div class="base47-he-grid" style="margin-top:16px;">
                <?php if ( empty( $sets ) ) : ?>
                    <p>No <code>*-templates</code> folders found in the plugin directory. Install a theme via ZIP or FTP.</p>
                <?php else : ?>
                    <?php foreach ( $sets as $slug => $set ) :
                        $is_active   = in_array( $slug, $active, true );
                        $use_manifest = in_array( $slug, $use_manifest_sets, true );
                        $has_manifest = isset( $manifests[ $slug ] );
                    ?>
                        <div class="base47-box">
                            <h3><?php echo esc_html( $slug ); ?></h3>
                            <p class="base47-muted">
                                Path: <code><?php echo esc_html( $set['path'] ); ?></code>
                            </p>

                            <!-- ACTIVE TOGGLE -->
                            <div style="margin:12px 0;">
                                <strong>Active:</strong>
                                <label class="base47-switch" title="<?php echo $is_active ? 'Active' : 'Inactive'; ?>" style="margin-left:8px;">
                                    <input type="checkbox"
                                           name="base47_active_sets[]"
                                           value="<?php echo esc_attr( $slug ); ?>"
                                           <?php checked( $is_active ); ?>>
                                    <span class="base47-slider"></span>
                                </label>
                            </div>

                            <!-- ASSET MODE -->
                            <div style="margin:12px 0;padding-top:8px;border-top:1px solid #eee;">
                                <strong>Asset Loading:</strong>
                                <div style="margin-top:8px;">
                                    <label style="display:inline-flex;align-items:center;margin-right:16px;">
                                        <input type="radio"
                                               name="asset_mode_<?php echo esc_attr( $slug ); ?>"
                                               value="loader"
                                               <?php checked( ! $use_manifest ); ?>
                                               onchange="(function(field){ if(field){ field.checked = false; } })(document.querySelector('input[name=&quot;base47_use_manifest[]&quot;][value=&quot;<?php echo esc_attr( $slug ); ?>&quot;]'));"
                                        >
                                        <span style="margin-left:4px;">? Loader (Fast)</span>
                                    </label>
                                    <label style="display:inline-flex;align-items:center;">
                                        <input type="radio"
                                               name="asset_mode_<?php echo esc_attr( $slug ); ?>"
                                               value="manifest"
                                               <?php checked( $use_manifest ); ?>
                                               <?php disabled( ! $has_manifest ); ?>
                                               onchange="(function(field){ if(field){ field.checked = true; } })(document.querySelector('input[name=&quot;base47_use_manifest[]&quot;][value=&quot;<?php echo esc_attr( $slug ); ?>&quot;]'));"
                                        >
                                        <span style="margin-left:4px;">?? Manifest <?php echo $has_manifest ? '' : '(No manifest.json)'; ?></span>
                                    </label>

                                    <!-- real option field -->
                                    <input type="checkbox"
                                           name="base47_use_manifest[]"
                                           value="<?php echo esc_attr( $slug ); ?>"
                                           <?php checked( $use_manifest ); ?>
                                           style="display:none;">
                                </div>
                            </div>

                            <!-- DELETE THEME -->
                            <div style="margin-top:12px;padding-top:8px;border-top:1px solid #eee;">
                                <strong>Danger Zone:</strong>
                                <p class="base47-muted" style="margin:6px 0 8px;">
                                    Permanently delete this theme folder from the plugin.
                                </p>
                                <form method="post"
                                      onsubmit="return confirm('Delete theme <?php echo esc_js( $slug ); ?>? This cannot be undone.');">
                                    <?php wp_nonce_field( BASE47_HE_OPT_SETTINGS_NONCE ); ?>
                                    <input type="hidden" name="base47_he_theme_action" value="delete_theme">
                                    <input type="hidden" name="base47_delete_theme" value="<?php echo esc_attr( $slug ); ?>">
                                    <button type="submit" class="button button-link-delete">
                                        Delete Theme
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <p style="margin-top:16px;">
                <button type="submit" class="button button-primary">Save Changes</button>
            </p>
        </form>
    </div>
    <?php
}
	
/**
 * Install a theme from uploaded ZIP (V3 ñ uploads directory).
 *
 * Expects ZIP structure:
 *   /{slug}-templates/
 *       home.html
 *       manifest.json
 *       assets/
 *
 * The theme will be installed into:
 *   /wp-content/uploads/base47-themes/{slug}-templates/
 */
function base47_he_install_theme_from_upload() {

    if ( ! isset($_FILES['base47_theme_zip']) || empty($_FILES['base47_theme_zip']['name']) ) {
        return new WP_Error('no_file', 'No ZIP file uploaded.');
    }

    $file = $_FILES['base47_theme_zip'];

    if (! empty($file['error'])) {
        return new WP_Error('upload_error', 'Upload error: ' . intval($file['error']));
    }

    $name      = $file['name'];
    $tmp       = $file['tmp_name'];
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if ($extension !== 'zip') {
        return new WP_Error('invalid_type', 'File must be a .zip archive.');
    }

    if (! class_exists('ZipArchive')) {
        return new WP_Error('no_zip', 'ZipArchive is not available on this server.');
    }

    $zip = new ZipArchive();
    if (true !== $zip->open($tmp)) {
        return new WP_Error('open_failed', 'Could not open ZIP file.');
    }

    // -----------------------------
    // Detect root folder inside ZIP
    // -----------------------------
    $root_folder = '';
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if (! $stat || empty($stat['name'])) continue;

        $name_in_zip = $stat['name'];

        if (substr($name_in_zip, -1) === '/') {
            $root_folder = trim($name_in_zip, '/');
            break;
        }
    }

    if (! $root_folder) {
        $zip->close();
        return new WP_Error('no_root_folder', 'ZIP must contain a root folder (e.g. lezar-templates/).');
    }

    // Must follow naming rule
    if (! str_ends_with($root_folder, '-templates')) {
        $zip->close();
        return new WP_Error('invalid_folder', 'Root folder must end with "-templates".');
    }

    // -----------------------------
    // Determine install location
    // -----------------------------
    $root       = base47_he_get_themes_root(); // returns ['dir' => ..., 'url' => ...]
    $themes_dir = $root['dir'];

    $target_dir = trailingslashit($themes_dir . $root_folder);

    if (file_exists($target_dir)) {
        $zip->close();
        return new WP_Error('exists', 'A theme with this name already exists.');
    }

    // -----------------------------
    // Extract ONLY into uploads dir
    // -----------------------------
    if (! $zip->extractTo($themes_dir)) {
        $zip->close();
        return new WP_Error('extract_failed', 'Could not extract ZIP into themes directory.');
    }

    $zip->close();

    if (! is_dir($target_dir)) {
        return new WP_Error('no_target', 'Theme folder not found after extraction.');
    }

    return $root_folder;
}
/**
 * Delete a theme folder from the uploads/base47-themes directory.
 *
 * Example slug:
 *   'lezar-templates'
 *   'redox-templates'
 *
 * Returns true on success, WP_Error on failure.
 */
function base47_he_delete_theme_folder( $slug ) {

    // Get root theme directory
    $root = base47_he_get_themes_root(); // ['dir' => '/path/', 'url' => '...']
    $themes_dir = $root['dir'];

    // Full path of the theme set to delete
    $target = realpath( $themes_dir . $slug );

    // Validate target exists
    if ( ! $target || ! is_dir( $target ) ) {
        return new WP_Error( 'not_found', 'Theme set not found.' );
    }

    // Safety: ensure we ONLY delete inside base47-themes directory
    $themes_root_real = realpath( $themes_dir );
    if ( strpos( $target, $themes_root_real ) !== 0 ) {
        return new WP_Error( 'unsafe_path', 'Refusing to delete outside theme directory.' );
    }

    // Delete recursively
    if ( ! base47_he_rrmdir( $target ) ) {
        return new WP_Error( 'delete_failed', 'Could not delete theme folder. Check permissions.' );
    }

    return true;
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

	
function base47_he_changelog_page() {
    $file    = BASE47_HE_PATH . 'changelog.txt';
    $content = file_exists( $file )
        ? file_get_contents( $file )
        : "√¢‚Ç¨¬¢ 2.3.0 √¢‚Ç¨‚Äù Special Widgets admin page, Redox slider v1 integration.\n√¢‚Ç¨¬¢ 2.1.0 √¢‚Ç¨‚Äù Theme Manager (toggle switches), active-only shortcodes, safer defaults.\n√¢‚Ç¨¬¢ 2.0.x √¢‚Ç¨‚Äù Multi-set foundations.\n";

    echo '<div class="wrap base47-he-wrap"><h1>Changelog</h1><pre class="base47-he-changelog">' . esc_html( $content ) . '</pre></div>';
}


/* --------------------------------------------------------------------------
| AJAX: Lazy Template Preview (For Shortcodes Page)
-------------------------------------------------------------------------- */
function base47_he_ajax_lazy_preview() {
    check_ajax_referer( 'base47_he_preview', 'nonce' );

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


/* --------------------------------------------------------------------------
| AJAX PREVIEW / GET / SAVE / LIVE PREVIEW
-------------------------------------------------------------------------- */
/** Detect default theme (for JS/editor). */
function base47_he_detect_default_theme() {
 $sets = base47_he_get_template_sets();
return array_key_first($sets) ?: '';
}


function base47_he_ajax_preview() {
    check_admin_referer( 'base47_he_preview' );

    $file = isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : '';
    $set  = isset( $_GET['set'] )  ? sanitize_text_field( wp_unslash( $_GET['set'] ) )  : '';

    if ( ! $file ) wp_die( 'Template not specified.' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_die( 'Template not found.' );
        $set      = $info['set'];
        $full     = $info['path'];
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_die( 'Template set not found.' );
        $full     = $sets[ $set ]['path'] . $file;
        $base_url = $sets[ $set ]['url'];
        if ( ! file_exists( $full ) ) wp_die( 'Template not found.' );
    }

    $html = file_get_contents( $full );
    $html = base47_he_rewrite_assets( $html, $base_url, true );
    echo $html;
    exit;
}
add_action( 'wp_ajax_base47_he_preview',        'base47_he_ajax_preview' );
add_action( 'wp_ajax_nopriv_base47_he_preview', 'base47_he_ajax_preview' );

function base47_he_ajax_get_template() {
    check_ajax_referer( 'base47_he_editor', 'nonce' );

    $file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set  = isset( $_POST['set'] )  ? sanitize_text_field( wp_unslash( $_POST['set'] ) )  : '';

    if ( ! $file ) wp_send_json_error( 'Template not specified.' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $set      = $info['set'];
        $full     = $info['path'];
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $full     = $sets[ $set ]['path'] . $file;
        $base_url = $sets[ $set ]['url'];
        if ( ! file_exists( $full ) ) wp_send_json_error( 'Template not found.' );
    }

    $content = file_get_contents( $full );
    $preview = base47_he_rewrite_assets( base47_he_strip_shell( $content ), $base_url, true );

    wp_send_json_success( [
        'content' => $content,
        'preview' => $preview,
        'set'     => $set,
    ] );
}
add_action( 'wp_ajax_base47_he_get_template', 'base47_he_ajax_get_template' );

function base47_he_ajax_save_template() {
    check_ajax_referer( 'base47_he_editor', 'nonce' );

    $file    = isset( $_POST['file'] )    ? sanitize_text_field( wp_unslash( $_POST['file'] ) )    : '';
    $set     = isset( $_POST['set'] )     ? sanitize_text_field( wp_unslash( $_POST['set'] ) )     : '';
    $content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';

    if ( ! $file ) wp_send_json_error( 'Template not specified.' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $full = $info['path'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $full = $sets[ $set ]['path'] . $file;
        if ( ! file_exists( $full ) ) wp_send_json_error( 'Template not found.' );
    }

    $written = file_put_contents( $full, $content );
    if ( false === $written ) wp_send_json_error( 'Could not write file. Check permissions.' );

    wp_send_json_success( 'saved' );
}
add_action( 'wp_ajax_base47_he_save_template', 'base47_he_ajax_save_template' );

add_action( 'wp_ajax_base47_he_live_preview', function() {
    check_ajax_referer( 'base47_he_editor', 'nonce' );

    $file    = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set     = isset( $_POST['set'] )  ? sanitize_text_field( wp_unslash( $_POST['set'] ) )  : '';
    $content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';

    if ( ! $file ) wp_send_json_error( 'No file' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $base_url = $sets[ $set ]['url'];
    }

    $html = base47_he_rewrite_assets( $content, $base_url, false );
    wp_send_json_success( [ 'html' => $html ] );
});

/* --------------------------------------------------------------------------
| ADMIN LAYOUT FIX
-------------------------------------------------------------------------- */
add_action( 'admin_head', function() {
    $screen = get_current_screen();
    if ( ! $screen ) return;
    if ( strpos( $screen->id, 'base47-he' ) !== false || strpos( $screen->id, 'base47-special-widgets' ) !== false ) {
        echo '<style>
            #wpcontent {max-width:100%!important;margin-left:160px!important;padding-left:20px!important;box-sizing:border-box!important;}
            .wrap.base47-he-wrap {max-width:96%!important;width:100%!important;margin:0 auto;}
            @media (max-width: 960px) { #wpcontent {margin-left:0!important;width:100%!important;} }
        </style>';
    }
});

/* --------------------------------------------------------------------------
| PHP 8 polyfill for str_ends_with (if missing)
-------------------------------------------------------------------------- */
if ( ! function_exists( 'str_ends_with' ) ) {
    function str_ends_with( $haystack, $needle ) {
        $len = strlen( $needle );
        if ( $len === 0 ) return true;
        return ( substr( $haystack, -$len ) === $needle );
    }
}


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
                <button id="base47-modal-close">◊</button>
            </div>

            <div id="base47-modal-body">
                <iframe id="base47-modal-iframe"></iframe>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'admin_footer', 'base47_he_preview_modal' );