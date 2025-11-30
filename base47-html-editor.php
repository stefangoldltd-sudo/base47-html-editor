<?php
/*
Plugin Name: Base47 HTML Editor
Description: Turn HTML templates in any *-templates folder into shortcodes, edit them live, and manage which theme-sets are active via toggle switches.
Version: 2.8.6.5
Author: Stefan Gold
Text Domain: base47-html-editor
*/



if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
| CONSTANTS
-------------------------------------------------------------------------- */
define( 'BASE47_HE_VERSION', '2.8.6.5' );
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
require_once BASE47_HE_PATH . 'inc/helpers-logs.php';
require_once BASE47_HE_PATH . 'inc/admin-logs-page.php';



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

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $active = base47_he_get_active_sets();
    $sets   = base47_he_get_template_sets();

    if ( empty( $active ) ) {
        echo '<div class="wrap"><h1>Shortcodes</h1><p>No active themes. Go to <strong>Theme Manager</strong> to enable one.</p></div>';
        return;
    }

    // Group templates by set
    $by_set = [];
    foreach ( base47_he_get_all_templates( false ) as $item ) {
        $by_set[ $item['set'] ][] = $item['file'];
    }

    ?>

    <h1>Shortcodes</h1>
    <p>
        Only <strong>active</strong> theme sets are listed.<br>
        Previews are now <strong>lazy-loaded</strong> ? click <em>Load preview</em>.
    </p>

    <?php foreach ( $active as $set_slug ) : ?>

        <?php $files = $by_set[ $set_slug ] ?? []; ?>

        <h2><?php echo esc_html( $set_slug ); ?></h2>

        <?php if ( empty( $files ) ) : ?>

            <p class="base47-muted">No templates found in this set.</p>

        <?php else : ?>

            <div class="base47-he-template-grid">

                <?php foreach ( $files as $file ) :

$slug = base47_he_filename_to_slug( $file );

// Unified shortcode naming for ALL themes
$set_clean = str_replace( [ '-templates', '-templetes' ], '', $set_slug );
$shortcode = '[base47-' . $set_clean . '-' . $slug . ']';

                    // Classic preview
                    $preview_url = admin_url(
    'admin-ajax.php?action=base47_he_preview'
    . '&file=' . rawurlencode( $file )
    . '&set=' . rawurlencode( $set_slug )
    . '&_wpnonce=' . wp_create_nonce( 'base47_he' )
);

                    // Live editor
                    $editor_url = admin_url(
                        'admin.php?page=base47-he-editor&set=' . rawurlencode( $set_slug ) .
                        '&file=' . rawurlencode( $file )
                    );
                    ?>

                    <div class="base47-he-template-box">

                        <strong><?php echo esc_html( $file ); ?></strong>
                        <code><?php echo esc_html( $shortcode ); ?></code>

                        <div class="base47-he-template-thumb">
                            <iframe class="base47-he-template-iframe"
                                    src="about:blank"
                                    loading="lazy"></iframe>
                        </div>

                        <div class="base47-he-template-actions">

                            <a class="button" target="_blank"
                               href="<?php echo esc_url( $preview_url ); ?>">
                                Preview
                            </a>

                            <button type="button"
                                    class="button base47-he-copy"
                                    data-shortcode="<?php echo esc_attr( $shortcode ); ?>">
                                Copy shortcode
                            </button>

                            <a class="button" href="<?php echo esc_url( $editor_url ); ?>">
                                Edit
                            </a>

                            <button type="button"
                                    class="button button-secondary base47-load-preview-btn"
                                    data-file="<?php echo esc_attr( $file ); ?>"
                                    data-set="<?php echo esc_attr( $set_slug ); ?>">
                                Load preview
                            </button>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    <?php endforeach; ?>

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
        . '&_wpnonce=' . wp_create_nonce( 'base47_he' )
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
          <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
    </div>
    <?php
}





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
        Clears and regenerates all template + theme caches.
    </span>
</form>

        <!-- GLASS THEME MANAGER -->
        <?php base47_he_render_theme_manager_section(); ?>

    </div>
    <?php
}
/**
 * Install a theme from uploaded ZIP (V3 ? uploads directory).
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
 * AJAX: Uninstall a theme (delete folder + cleanup options)
 */
add_action( 'wp_ajax_base47_he_uninstall_theme', 'base47_he_ajax_uninstall_theme' );

function base47_he_ajax_uninstall_theme() {
    // Basic security
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $slug = isset( $_POST['theme'] ) ? sanitize_key( $_POST['theme'] ) : '';

    if ( empty( $slug ) ) {
        wp_send_json_error( [ 'message' => 'Missing theme slug.' ] );
    }

    // Get all template sets so we can find the path of this theme
    $themes = base47_he_get_template_sets();

    if ( ! isset( $themes[ $slug ] ) || empty( $themes[ $slug ]['path'] ) ) {
        wp_send_json_error( [ 'message' => 'Theme not found.' ] );
    }

    $theme_path = $themes[ $slug ]['path'];

    // Recursively delete folder
    base47_he_rrmdir( $theme_path );

    // Remove from "active themes" option
    $active_themes = get_option( 'base47_active_themes', [] );
    if ( is_array( $active_themes ) ) {
        $active_themes = array_diff( $active_themes, [ $slug ] );
        update_option( 'base47_active_themes', array_values( $active_themes ) );
    }

    // Remove from "use manifest" option
    $use_manifest = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );
    if ( is_array( $use_manifest ) ) {
        $use_manifest = array_diff( $use_manifest, [ $slug ] );
        update_option( BASE47_HE_OPT_USE_MANIFEST, array_values( $use_manifest ) );
    }

    wp_send_json_success( [ 'message' => 'Theme uninstalled.', 'slug' => $slug ] );
}


/**
 * Render Theme Manager (glass UI)
 */
function base47_he_render_theme_manager_section() {

    $themes        = base47_he_get_template_sets();       // real uploaded sets
    $active_themes = get_option( 'base47_active_themes', [] );

    if ( ! is_array( $active_themes ) ) {
        $active_themes = [];
    }
    ?>
    <div class="base47-he-wrap base47-tm-wrap">

        <div class="base47-tm-header">
            <h2 class="base47-tm-title">Theme Manager</h2>
            <p class="base47-tm-subtitle">
                Choose which theme sets are active. Only active themes load templates and assets.
            </p>
        </div>

		<?php
// Default Theme Selector
$default_theme = get_option('base47_default_theme', array_key_first($themes));
?>

<div class="base47-default-theme-row" style="margin-bottom:20px;">
    <label for="base47_default_theme" style="font-weight:600; margin-right:10px;">
        Default Theme:
    </label>

    <select id="base47_default_theme" style="padding:6px 10px; border-radius:6px;">
        <?php foreach ( $themes as $slug => $t ) : ?>
            <option value="<?php echo esc_attr($slug); ?>"
                <?php selected( $slug, $default_theme ); ?>>
                <?php echo esc_html( $t['label'] ); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
		
        <div class="base47-tm-grid">
            <?php foreach ( $themes as $slug => $theme ) :

                 $info = [
                          'label'       => $theme['label']       ?? $slug,
                          'version'     => $theme['version']     ?? '1.0.0',
                          'description' => $theme['description'] ?? '',
                          'accent'      => $theme['accent']      ?? '#7C5CFF',
                          'thumbnail'   => $theme['thumbnail']   ?? '',
                       ];

                $is_active    = in_array( $slug, $active_themes, true );
                $templates    = base47_he_count_theme_templates( $slug );
                $accent       = $info['accent'];
                $first_letter = strtoupper( mb_substr( $slug, 0, 1 ) );
                ?>
                
<div class="base47-tm-card <?php echo $is_active ? 'is-active' : 'is-inactive'; ?>"
     data-theme="<?php echo esc_attr( $slug ); ?>"
     data-active="<?php echo $is_active ? '1' : '0'; ?>"
     style="--base47-tm-accent: <?php echo esc_attr( $accent ); ?>;">

    <div class="base47-tm-card-bg"></div>

    <div class="base47-tm-card-inner">

        <!-- Badge -->
        <div class="base47-tm-badge">
            <span class="base47-tm-badge-dot"></span>
            <span class="base47-tm-badge-text">
                <?php echo $is_active ? 'Active' : 'Disabled'; ?>
            </span>
        </div>

        <!-- MAIN INFO ROW -->
        <div class="base47-tm-card-main">

            <div class="base47-tm-logo">
                <span class="base47-tm-logo-inner">
                    <?php echo esc_html( $first_letter ); ?>
                </span>
            </div>

            <div class="base47-tm-text">
                <h3 class="base47-tm-name"><?php echo esc_html( $info['label'] ); ?></h3>

                <div class="base47-tm-meta">
                    <span class="base47-tm-meta-item">
                        <span class="dashicons dashicons-admin-appearance"></span>
                        Version <?php echo esc_html( $info['version'] ); ?>
                    </span>

                    <span class="base47-tm-meta-sep">?</span>

                    <span class="base47-tm-meta-item">
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        <?php echo esc_html( $templates ); ?> templates
                    </span>
                </div>

                <?php if ( ! empty( $info['description'] ) ) : ?>
                    <p class="base47-tm-description">
                        <?php echo esc_html( $info['description'] ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- THUMBNAIL -->
        <?php if ( ! empty( $info['thumbnail'] ) ) : ?>
            <div class="base47-tm-thumb">
                <img src="<?php echo esc_url( $theme['url'] . $info['thumbnail'] ); ?>" alt="">
            </div>
        <?php endif; ?>

        <!-- FOOTER -->
        <div class="base47-tm-footer">

            <!-- Toggle -->
            <label class="base47-tm-toggle">
                <input type="checkbox"
                       class="base47-tm-toggle-input"
                       data-theme="<?php echo esc_attr( $slug ); ?>"
                       <?php checked( $is_active ); ?> />

                <span class="base47-tm-toggle-track">
                    <span class="base47-tm-toggle-thumb"></span>
                </span>

                <span class="base47-tm-toggle-label">
                    <?php echo $is_active ? 'Enabled' : 'Disabled'; ?>
                </span>
            </label>

            <div class="base47-tm-footer-right">

                <!-- Uninstall -->
                <button type="button"
                        class="button-link-delete base47-tm-uninstall-btn"
                        data-theme="<?php echo esc_attr( $slug ); ?>">
                    Uninstall
                </button>

                <!-- Coming soon -->
                <button type="button" class="button button-secondary base47-tm-details-btn" disabled>
                    <span class="dashicons dashicons-visibility"></span>
                    Coming soon
                </button>
            </div>

        </div>

  <!-- ASSET MODES -->
<div class="base47-tm-asset-modes">

    <?php
        // Existing options
        $use_manifest_arr = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );

        // NEW: Smart Loader++
        $smart_loader_arr = get_option( BASE47_HE_OPT_USE_SMART_LOADER, [] );

        $use_manifest = in_array( $slug, $use_manifest_arr, true );
        $use_smart    = in_array( $slug, $smart_loader_arr, true );

        $manifest_path = trailingslashit( $theme['path'] ) . 'manifest.json';
        $has_manifest  = file_exists( $manifest_path );
    ?>

    <!-- 1) Classic Loader (default) -->
    <label class="tm-mode">
        <input type="radio"
               name="asset_mode_<?php echo esc_attr( $slug ); ?>"
               value="loader"
               <?php checked( ! $use_manifest && ! $use_smart ); ?>>
        <span>Loader (default)</span>
    </label>

    <!-- 2) Manifest -->
    <label class="tm-mode">
        <input type="radio"
               name="asset_mode_<?php echo esc_attr( $slug ); ?>"
               value="manifest"
               <?php checked( $use_manifest ); ?>
               <?php disabled( ! $has_manifest ); ?>>
        <span>Manifest</span>
    </label>

    <!-- 3) Smart Loader++ -->
    <label class="tm-mode">
        <input type="radio"
               name="asset_mode_<?php echo esc_attr( $slug ); ?>"
               value="smart"
               <?php checked( $use_smart ); ?>>
        <span>Smart Loader++</span>
    </label>

    <!-- Hidden save fields -->

    <!-- Manifest save -->
    <input type="checkbox"
           class="tm-hidden-manifest"
           name="base47_use_manifest[]"
           value="<?php echo esc_attr( $slug ); ?>"
           <?php checked( $use_manifest ); ?>>

    <!-- Smart Loader save -->
    <input type="checkbox"
           class="tm-hidden-smart"
           name="base47_he_use_smart_loader[]"
           value="<?php echo esc_attr( $slug ); ?>"
           <?php checked( $use_smart ); ?>>
</div>
		
		
    </div> <!-- END card inner -->

</div> <!-- END card -->

            <?php endforeach; ?>
        </div>

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

	
function base47_he_changelog_page() {
    $file    = BASE47_HE_PATH . 'changelog.txt';
    $content = file_exists( $file )
        ? file_get_contents( $file )
        : "• 2.3.0 — Special Widgets admin page, Redox slider v1 integration.\n• 2.1.0 — Theme Manager (toggle switches), active-only shortcodes, safer defaults.\n• 2.0.x — Multi-set foundations.\n";

    echo '<div class="wrap base47-he-wrap"><h1>Changelog</h1><pre class="base47-he-changelog">' . esc_html( $content ) . '</pre></div>';
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
    check_ajax_referer( 'base47_he', 'nonce' );
    
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
    check_ajax_referer( 'base47_he', 'nonce' );
    
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


/**
 * Count HTML templates in a theme folder (correct: uploads/base47-themes/)
 */
function base47_he_count_theme_templates( $folder_name ) {

    $sets = base47_he_get_template_sets();

    if ( ! isset( $sets[ $folder_name ] ) ) {
        return 0;
    }

    $dir = trailingslashit( $sets[ $folder_name ]['path'] );

    if ( ! is_dir( $dir ) ) {
        return 0;
    }

    $files = glob( $dir . '*.html' );
    if ( ! is_array( $files ) ) {
        return 0;
    }

    return count( $files );
}

/**
 * AJAX: Save asset mode (loader / manifest / smart)
 */
add_action('wp_ajax_base47_set_asset_mode', function() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
    }

    $theme = sanitize_text_field($_POST['theme'] ?? '');
    $mode  = sanitize_text_field($_POST['mode'] ?? '');

    if (!$theme) {
        wp_send_json_error('Missing theme slug.');
    }

    // Load existing
    $manifest = get_option(BASE47_HE_OPT_USE_MANIFEST, []);
    $smart    = get_option(BASE47_HE_OPT_USE_SMART_LOADER, []);

    // Remove theme from both arrays first
    $manifest = array_diff($manifest, [$theme]);
    $smart    = array_diff($smart, [$theme]);

    // Apply new mode
    if ($mode === 'manifest') {
        $manifest[] = $theme;
    } elseif ($mode === 'smart') {
        $smart[] = $theme;
    }

    update_option(BASE47_HE_OPT_USE_MANIFEST, array_values($manifest));
    update_option(BASE47_HE_OPT_USE_SMART_LOADER, array_values($smart));

    wp_send_json_success(['theme' => $theme, 'mode' => $mode]);
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