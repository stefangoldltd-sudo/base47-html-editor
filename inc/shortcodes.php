<?php
/**
 * Shortcode Registration
 * 
 * Registers all template shortcodes with unified format and backward compatibility.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

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

            // FINAL shortcode format – ALWAYS theme + template
            $shortcode = 'base47-' . $theme_prefix . '-' . $slug;

            add_shortcode($shortcode, function() use ($set, $file) {
                $full     = $set['path'] . $file;
                $base_url = $set['url'];

                if (!file_exists($full)) return '';

                $html = file_get_contents($full);
                $html = base47_he_rewrite_assets($html, $base_url, false);
                return $html;
            });

            // BACKWARD COMPATIBILITY – ONLY for old Mivon/Base47 shortcodes
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

/**
 * Convert filename to shortcode slug.
 */
function base47_he_filename_to_slug( $filename ) {
    $base = pathinfo( $filename, PATHINFO_FILENAME );
    $slug = sanitize_title_with_dashes( $base );
    return $slug ?: ( 'tpl-' . md5( $filename ) );
}
