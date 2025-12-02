<?php
/**
 * Template Discovery & Caching
 * 
 * Handles theme set discovery, template scanning, and cache management.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
| DISCOVERY - Find all template sets 
-------------------------------------------------------------------------- */

/**
 * Discover theme sets (*-templates folders) with smart caching.
 * Also loads metadata from theme.json inside each folder.
 */
function base47_he_get_template_sets( $force = false ) {

    static $static = null;
    if ( $static !== null && ! $force ) {
        return $static;
    }

    require_once BASE47_HE_PATH . 'inc/class-base47-cache.php';

    // Get uploads/base47-themes root
    $root = base47_he_get_themes_root();
    $themes_dir = trailingslashit( $root['dir'] );
    $themes_url = trailingslashit( $root['url'] );

    // Check if caching is enabled (respects debug mode)
    $cache_enabled = base47_he_is_cache_enabled();

    // Signature based on uploads folder
    $saved             = get_transient( Base47_Cache::TRANS_SETS );
    $current_signature = Base47_Cache::get_signature( $themes_dir . '*-templates' );

    if (
        ! $force &&
        $cache_enabled &&
        is_array( $saved ) &&
        isset( $saved['sets'], $saved['signature'] ) &&
        hash_equals( $saved['signature'], $current_signature )
    ) {
        $static = $saved['sets'];
        return $static;
    }

    // Scan uploads/base47-themes
    $sets = [];

    foreach ( glob( $themes_dir . '*-templates', GLOB_ONLYDIR ) as $dir ) {

        $slug = basename( $dir );

        // Load metadata from theme.json
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

    // Only cache if caching is enabled
    if ( $cache_enabled ) {
        set_transient( Base47_Cache::TRANS_SETS, [
            'sets'      => $sets,
            'signature' => $current_signature,
        ], Base47_Cache::CACHE_TIME );
    }

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
 * STRUCTURE ONLY - CONTENT IS NOT CACHED.
 */
function base47_he_get_template_list( $force = false ) {

    static $static = null;
    if ( $static !== null && ! $force ) {
        return $static;
    }

    require_once BASE47_HE_PATH . 'inc/class-base47-cache.php';

    $sets = base47_he_get_template_sets( $force );

    // Check if caching is enabled (respects debug mode)
    $cache_enabled = base47_he_is_cache_enabled();

    // Signature path
    $root = base47_he_get_themes_root();
    $sig  = Base47_Cache::get_signature( $root['dir'] . '*-templates/*' );

    $saved = get_transient( Base47_Cache::TRANS_TEMPLATES );

    if (
        ! $force &&
        $cache_enabled &&
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

    // Only cache if caching is enabled
    if ( $cache_enabled ) {
        set_transient( Base47_Cache::TRANS_TEMPLATES, [
            'templates' => $templates,
            'signature' => $sig,
        ], Base47_Cache::CACHE_TIME );
    }

    $static = $templates;
    return $templates;
}

/**
 * Manually refresh caches related to theme sets + templates.
 * Called from Theme Manager (install/uninstall/refresh).
 */
function base47_he_refresh_theme_caches() {
    require_once BASE47_HE_PATH . 'inc/class-base47-cache.php';
    Base47_Cache::clear_all();
    base47_he_get_template_sets( true );
    base47_he_get_template_list( true );
}

/**
 * Helper: force refresh of template set cache.
 * Called from Theme Manager (e.g. after install/uninstall).
 */
function base47_he_refresh_template_sets_cache() {
    delete_transient( 'base47_he_cache_template_sets' );
    // Force next call to rescan filesystem
    base47_he_get_template_sets( true );
}

/* --------------------------------------------------------------------------
| ACTIVE SETS MANAGEMENT
-------------------------------------------------------------------------- */

/**
 * Return only the active theme set slugs (persisted).
 */
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

/**
 * True if a set slug is active.
 */
function base47_he_is_set_active( $set_slug ) {
    return in_array( $set_slug, base47_he_get_active_sets(), true );
}

/* --------------------------------------------------------------------------
| TEMPLATE UTILITIES
-------------------------------------------------------------------------- */

/**
 * All templates across sets (restricted to active sets unless $include_inactive = true).
 */
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

/**
 * Locate a filename across sets; prefer active, then inactive.
 */
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
