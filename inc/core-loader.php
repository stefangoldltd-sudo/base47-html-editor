<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Core Loader + Manifest Engine for Base47
 * - HTML shell stripping
 * - Asset URL rewriting
 * - Smart Loader++
 * - Manifest engine
 * - Template render
 * - Shortcodes
 */


/* -------------------------------------------------
| MANIFEST DISCOVERY (REQUIRED)
--------------------------------------------------*/

if ( ! function_exists( 'base47_he_get_all_manifests' ) ) {

    function base47_he_get_all_manifests() {

        $sets = base47_he_get_template_sets();
        $manifests = [];

        foreach ( $sets as $slug => $set ) {

            $manifest_file = trailingslashit( $set['path'] ) . 'manifest.json';

            if ( ! file_exists( $manifest_file ) ) {
                continue;
            }

            $json = file_get_contents( $manifest_file );
            if ( ! $json ) continue;

            $data = json_decode( $json, true );
            if ( ! is_array( $data ) ) continue;

            /* Auto-build helper fields (old behaviour) */
            $manifests[ $slug ] = array_merge( $data, [
                '_set_slug'      => $slug,
                '_base_url'      => trailingslashit( $set['url'] ) . 'assets/',
                '_base_path'     => trailingslashit( $set['path'] ) . 'assets/',
                '_handle_prefix' => 'base47-' . sanitize_key( $slug ),
            ]);
        }

        return $manifests;
    }
}

if ( ! function_exists( 'base47_he_get_manifest_for_set' ) ) {
    function base47_he_get_manifest_for_set( $slug ) {
        $all = base47_he_get_all_manifests();
        return $all[ $slug ] ?? null;
    }
}

/* -------------------------------------------------
| MANIFEST + FLAGS
--------------------------------------------------*/

function base47_he_theme_uses_manifest( $slug ) {
    $use = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );
    return in_array( $slug, $use, true );
}

function base47_he_theme_uses_smart( $slug ) {
    $use = get_option( BASE47_HE_OPT_USE_SMART_LOADER, [] );
    return in_array( $slug, $use, true );
}

function base47_he_load_manifest( $slug ) {
    $sets = base47_he_get_template_sets();
    if ( ! isset( $sets[$slug] ) ) return false;

    $path = trailingslashit( $sets[$slug]['path'] ) . 'manifest.json';
    if ( ! file_exists( $path ) ) return false;

    $json  = file_get_contents( $path );
    $data  = json_decode( $json, true );

    return is_array( $data ) ? $data : false;
}


/* -------------------------------------------------
| REWRITE ASSETS
--------------------------------------------------*/

function base47_he_rewrite_assets( $html, $base_url, $add_ver = true ) {

    $base = trailingslashit( $base_url );

    $patterns = [
        '#src="/assets/#i',     '#src=\'/assets/#i',
        '#href="/assets/#i',    '#href=\'/assets/#i',
        '#src="assets/#i',      '#src=\'assets/#i',
        '#href="assets/#i',     '#href=\'assets/#i',
        '#url\("/assets/#i',    '#url\(\'/assets/#i',  '#url\(/assets/#i',
        '#url\("assets/#i',     '#url\(\'assets/#i',   '#url\(assets/#i',
        '#data-background="/assets/#i', '#data-background=\'/assets/#i',
        '#data-background="assets/#i',  '#data-background=\'assets/#i',
    ];

    $replacements = [
        'src="' . $base . 'assets/',   "src='" . $base . 'assets/',
        'href="' . $base . 'assets/',  "href='" . $base . 'assets/',
        'src="' . $base . 'assets/',   "src='" . $base . 'assets/',
        'href="' . $base . 'assets/',  "href='" . $base . 'assets/',
        'url("' . $base . 'assets/',   "url('" . $base . 'assets/',   'url(' . $base . 'assets/',
        'url("' . $base . 'assets/',   "url('" . $base . 'assets/',   'url(' . $base . 'assets/',
        'data-background="' . $base . 'assets/',  "data-background='" . $base . 'assets/',
        'data-background="' . $base . 'assets/',  "data-background='" . $base . 'assets/',
    ];

    $html = preg_replace( $patterns, $replacements, $html );

    /* Smart Loader++ manifest rewrites */
    if ( ! empty( $GLOBALS['base47_current_set_slug'] ) ) {
        $slug = $GLOBALS['base47_current_set_slug'];

        if ( base47_he_theme_uses_manifest( $slug ) ) {
            $manifest = base47_he_load_manifest( $slug );

            if ( $manifest && ! empty( $manifest['assets'] ) ) {
                foreach ( $manifest['assets'] as $original => $mapped ) {
                    $html = str_replace( $original, $mapped, $html );
                }
            }
        }
    }

    if ( $add_ver ) {
        $ver = time();
        $html = preg_replace_callback(
            '#\b(src|href)=["\'](' . preg_quote( $base, '#' ) . 'assets/[^"\']+)#i',
            function( $m ) use ( $ver ) {
                $url = $m[2];
                $url .= ( strpos( $url, '?' ) === false ? '?ver=' : '&ver=' ) . $ver;
                return $m[1] . '="' . $url . '"';
            },
            $html
        );
    }

    return $html;
}


/* -------------------------------------------------
| STRIP SHELL
--------------------------------------------------*/

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
        if ( preg_match_all('#<style\b[^>]*>.*?</style>#is', $head, $ms )) {
            $inline = array_merge( $inline, $ms[0] );
        }
        if ( preg_match_all('#<script(?![^>]*\bsrc=)[^>]*>.*?</script>#is', $head, $ms )) {
            $inline = array_merge( $inline, $ms[0] );
        }
    }

    $body = preg_replace( '#<link[^>]+href=["\']/?assets/[^>]+>#i', '', $body );
    $body = preg_replace( '#<script[^>]+src=["\']/?assets/[^>]+></script>#i', '', $body );
    $body = preg_replace( '#<(?:!DOCTYPE|/?html|/?head|/?body)[^>]*>#i', '', $body );

    return implode("\n", $inline) . "\n" . $body;
}


/* -------------------------------------------------
| SMART LOADER++, MANIFEST, FALLBACK LOADER
--------------------------------------------------*/

function base47_he_enqueue_assets_for_set( $set_slug ) {

    $default = get_option( 'base47_default_theme', '' );
    if ( empty( $set_slug ) && $default ) {
        $set_slug = $default;
    }

    if ( ! base47_he_is_set_active( $set_slug ) ) return;

    $sets = base47_he_get_template_sets();
    if ( ! isset( $sets[$set_slug] ) ) return;

    $use_manifest = base47_he_theme_uses_manifest( $set_slug );
    $use_smart    = base47_he_theme_uses_smart( $set_slug );

    $manifests    = base47_he_get_all_manifests();
    $manifest_key = $set_slug;

    $theme_path = trailingslashit( $sets[$set_slug]['path'] );
    $theme_url  = trailingslashit( $sets[$set_slug]['url'] );


    /* ----------------------------
       1) SMART LOADER++ (first)
    -----------------------------*/
    if ( $use_smart ) {

        $css_dir = $theme_path . 'assets/css/';
        $js_dir  = $theme_path . 'assets/js/';

        if ( is_dir( $css_dir ) ) {
            foreach ( glob( $css_dir . '*.css' ) as $file ) {
                wp_enqueue_style(
                    'base47-smart-' . md5($file),
                    $theme_url . 'assets/css/' . basename($file),
                    [],
                    filemtime($file)
                );
            }
        }

        if ( is_dir( $js_dir ) ) {
            foreach ( glob( $js_dir . '*.js' ) as $file ) {
                wp_enqueue_script(
                    'base47-smart-' . md5($file),
                    $theme_url . 'assets/js/' . basename($file),
                    ['jquery'],
                    filemtime($file),
                    true
                );
            }
        }

        return;
    }


    /* ----------------------------
       2) MANIFEST MODE
    -----------------------------*/
    if ( $use_manifest && isset( $manifests[$manifest_key] ) ) {

        $m = $manifests[$manifest_key];

        $base_url  = trailingslashit( $m['_base_url'] );
        $base_path = trailingslashit( $m['_base_path'] );
        $prefix    = $m['_handle_prefix'];

        $css_list = $m['css'] ?? ( $m['global']['css'] ?? [] );
        $js_list  = $m['js']  ?? ( $m['global']['js']  ?? [] );

        foreach ( $css_list as $relative ) {
            $relative = ltrim($relative, '/');
            $file = $base_path . $relative;
            if ( file_exists( $file ) ) {
                wp_enqueue_style(
                    $prefix . '-css-' . md5($relative),
                    $base_url . $relative,
                    [],
                    filemtime($file)
                );
            }
        }

        foreach ( $js_list as $relative ) {
            $relative = ltrim($relative, '/');
            $file = $base_path . $relative;
            if ( file_exists( $file ) ) {
                wp_enqueue_script(
                    $prefix . '-js-' . md5($relative),
                    $base_url . $relative,
                    ['jquery'],
                    filemtime($file),
                    true
                );
            }
        }

        return;
    }


    /* ----------------------------
       3) FALLBACK LOADER
    -----------------------------*/
    $css_dir = $theme_path . 'assets/css/';
    $js_dir  = $theme_path . 'assets/js/';

    if ( is_dir( $css_dir ) ) {
        foreach ( glob( $css_dir . '*.css' ) as $file ) {
            wp_enqueue_style(
                'base47-fallback-' . md5($file),
                $theme_url . 'assets/css/' . basename($file),
                [],
                filemtime($file)
            );
        }
    }

    if ( is_dir( $js_dir ) ) {
        foreach ( glob( $js_dir . '*.js' ) as $file ) {
            wp_enqueue_script(
                'base47-fallback-' . md5($file),
                $theme_url . 'assets/js/' . basename($file),
                ['jquery'],
                filemtime($file),
                true
            );
        }
    }
}


/* -------------------------------------------------
| RENDER TEMPLATE
--------------------------------------------------*/

function base47_he_render_template( $filename, $set_slug = '' ) {

    $sets = base47_he_get_template_sets();

    if ( empty( $set_slug ) ) {
        $default = get_option('base47_default_theme', '');
        if ( $default ) $set_slug = $default;
    }

    if ( empty( $set_slug ) ) {
        $info = base47_he_locate_template( $filename );
        if ( ! $info ) return '';
        $set_slug = $info['set'];
        $full     = $info['path'];
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[$set_slug] ) ) return '';
        $full     = $sets[$set_slug]['path'] . $filename;
        $base_url = $sets[$set_slug]['url'];
        if ( ! file_exists( $full ) ) return '';
    }

    if ( ! base47_he_is_set_active( $set_slug ) ) {
        return '<!-- Base47: theme inactive -->';
    }

    $GLOBALS['base47_current_set_slug'] = $set_slug;

    $html = file_get_contents( $full );
    $html = base47_he_strip_shell( $html );
    $html = base47_he_rewrite_assets( $html, $base_url, true );
    $html = do_shortcode( $html );

    base47_he_enqueue_assets_for_set( $set_slug );

    return $html;
}


/* -------------------------------------------------
| SHORTCODES
--------------------------------------------------*/

function base47_he_register_shortcodes() {
    $all = base47_he_get_all_templates( false );
    foreach ( $all as $item ) {

        $set = $item['set'];
        $file = $item['file'];
        $slug = base47_he_filename_to_slug( $file );

        $set_clean = str_replace(['-templates','-templetes'], '', $set);
        $shortcode = 'base47-' . $set_clean . '-' . $slug;

        add_shortcode( $shortcode, function() use ( $file, $set ) {
            return base47_he_render_template( $file, $set );
        });
    }
}
add_action( 'init', 'base47_he_register_shortcodes', 20 );


/* -------------------------------------------------
| LEGACY SHORTCODES
--------------------------------------------------*/
function base47_he_register_legacy_shortcodes() {
    $all = base47_he_get_all_templates( false );

    foreach ( $all as $item ) {

        $set = $item['set'];
        $file = $item['file'];
        $slug = base47_he_filename_to_slug( $file );

        $set_clean = str_replace(['-templates','-templetes'], '', $set);
        $legacy_shortcode = 'mivon-' . $set_clean . '-' . $slug;

        add_shortcode( $legacy_shortcode, function() use ( $file, $set, $legacy_shortcode ) {

            if ( WP_DEBUG ) {
                error_log("Base47: Legacy shortcode [$legacy_shortcode] used.");
            }

            return base47_he_render_template( $file, $set );
        });
    }
}
add_action( 'init', 'base47_he_register_legacy_shortcodes', 21 );