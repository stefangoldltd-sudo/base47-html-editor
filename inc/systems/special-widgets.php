<?php
/**
 * Special Widgets System
 * 
 * Auto-discovery and shortcode registration for special widgets
 */

if ( ! defined( 'ABSPATH' ) ) exit;

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

    $base_dir = BASE47_HE_PATH . 'special-widgets/';
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

/**
 * Special Widget Shortcode: [base47_widget slug="hero-slider"]
 */
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

    $plugin_url = BASE47_HE_URL;
    $plugin_dir = BASE47_HE_PATH;

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
