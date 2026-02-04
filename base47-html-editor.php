<?php
/*
Plugin Name: Base47 HTML Editor
Description: Transform HTML templates into WordPress shortcodes with live Monaco editor, theme management, and smart asset loading. Perfect for developers and agencies working with HTML templates.
Version: 2.9.9.8
Author: Stefan Gold
Author URI: https://base47.com
Plugin URI: https://base47.com/html-editor
Text Domain: base47-html-editor
Domain Path: /languages
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.4
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
define( 'BASE47_HE_VERSION', '2.9.9.8' );
define( 'BASE47_HE_PATH', plugin_dir_path( __FILE__ ) );
define( 'BASE47_HE_URL',  plugin_dir_url( __FILE__ ) );

/* --------------------------------------------------------------------------
| CANVAS MODE RENDERING TAKEOVER
-------------------------------------------------------------------------- */

/**
 * Initialize Canvas Mode after WordPress is fully loaded
 */
function base47_init_canvas_mode() {
    // Add the template filter after WordPress is ready
    add_filter( 'template_include', 'base47_canvas_mode_takeover', 1 );
}
add_action( 'init', 'base47_init_canvas_mode' );

/**
 * Canvas Mode: Plugin takes full control of rendering
 * This ensures HTML templates render perfectly regardless of active theme
 */
function base47_canvas_mode_takeover( $template ) {
    if ( ! is_singular() ) {
        return $template;
    }
    
    $post_id = get_the_ID();
    if ( ! $post_id ) {
        return $template;
    }
    
    // Check if this page should use Canvas Mode
    if ( base47_is_canvas_page( $post_id ) ) {
        base47_render_canvas_html( $post_id );
        exit; // Plugin takes full control - no theme interference
    }
    
    return $template;
}

/**
 * Check if a page should use Canvas Mode rendering
 */
function base47_is_canvas_page( $post_id ) {
    // Check meta box setting
    $canvas_mode = get_post_meta( $post_id, '_nexus_canvas_mode', true );
    $app_canvas_mode = get_post_meta( $post_id, '_nexus_canvas_app_mode', true );
    
    if ( $canvas_mode === '1' || $app_canvas_mode === '1' ) {
        return true;
    }
    
    // Check page template
    $template = get_page_template_slug( $post_id );
    if ( $template === 'template-canvas.php' || $template === 'template-canvas-app.php' ) {
        return true;
    }
    
    // Check for Base47 shortcodes in content
    $content = get_post_field( 'post_content', $post_id );
    if ( base47_has_html_template_content( $content ) ) {
        return true;
    }
    
    return false;
}

/**
 * Detect if content contains HTML template patterns
 */
function base47_has_html_template_content( $content ) {
    $patterns = [
        // Base47/Mivon shortcodes
        '[mivon-',
        '[base47-',
        // HTML template indicators
        'class="header',
        'data-scroll-container',
        '<section class=',
        'data-aos=',
        'class="hero',
        'class="banner',
        'class="landing',
        // Bootstrap/Framework patterns
        'class="container-fluid',
        'class="row"',
        'data-bs-',
        // Animation libraries
        'data-wow-',
        'animate__',
        // Full HTML documents
        '<!DOCTYPE html>',
        '<html lang=',
    ];
    
    foreach ( $patterns as $pattern ) {
        if ( strpos( $content, $pattern ) !== false ) {
            return true;
        }
    }
    
    return false;
}

/**
 * Render Canvas Mode HTML with full control
 */
function base47_render_canvas_html( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post ) {
        return;
    }
    
    // Get the content
    $content = apply_filters( 'the_content', $post->post_content );
    
    // Check if content is a full HTML document
    $is_full_document = ( strpos( $content, '<!DOCTYPE' ) !== false || strpos( $content, '<html' ) !== false );
    
    if ( $is_full_document ) {
        // Content is a complete HTML document - output as-is with minimal WordPress integration
        base47_render_full_html_document( $content, $post );
    } else {
        // Content is HTML fragments - wrap in minimal document structure
        base47_render_html_fragment( $content, $post );
    }
}

/**
 * Render full HTML document with minimal WordPress integration
 */
function base47_render_full_html_document( $content, $post ) {
    // Parse the HTML to inject WordPress essentials
    $dom = new DOMDocument();
    libxml_use_internal_errors( true );
    $dom->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    libxml_clear_errors();
    
    // Find head tag and inject wp_head()
    $head = $dom->getElementsByTagName( 'head' )->item( 0 );
    if ( $head ) {
        // Capture wp_head() output
        ob_start();
        wp_head();
        $wp_head_content = ob_get_clean();
        
        // Create a comment node to mark where wp_head() content goes
        $head_marker = $dom->createComment( 'WP_HEAD_PLACEHOLDER' );
        $head->appendChild( $head_marker );
    }
    
    // Find body tag and inject wp_footer()
    $body = $dom->getElementsByTagName( 'body' )->item( 0 );
    if ( $body ) {
        // Add Canvas Mode body class
        $existing_class = $body->getAttribute( 'class' );
        $new_class = trim( $existing_class . ' base47-canvas' );
        $body->setAttribute( 'class', $new_class );
        
        // Create a comment node to mark where wp_footer() content goes
        $footer_marker = $dom->createComment( 'WP_FOOTER_PLACEHOLDER' );
        $body->appendChild( $footer_marker );
    }
    
    // Output the modified HTML
    $html = $dom->saveHTML();
    
    // Replace placeholders with actual WordPress content
    if ( isset( $wp_head_content ) ) {
        $html = str_replace( '<!--WP_HEAD_PLACEHOLDER-->', $wp_head_content, $html );
    }
    
    // Capture wp_footer() output
    ob_start();
    wp_footer();
    $wp_footer_content = ob_get_clean();
    
    $html = str_replace( '<!--WP_FOOTER_PLACEHOLDER-->', $wp_footer_content, $html );
    
    // Output final HTML
    echo $html;
}

/**
 * Render HTML fragment in minimal document structure
 */
function base47_render_html_fragment( $content, $post ) {
    ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( get_the_title( $post ) ); ?></title>
    <?php wp_head(); ?>
</head>
<body class="base47-canvas base47-fragment">
    <?php 
    // Handle admin bar spacing without hiding it
    if ( is_admin_bar_showing() ) {
        echo '<style>body.base47-canvas { padding-top: 32px; } @media screen and (max-width: 782px) { body.base47-canvas { padding-top: 46px; } }</style>';
    }
    ?>
    
    <?php echo $content; ?>
    
    <?php wp_footer(); ?>
</body>
</html><?php
}

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
require_once BASE47_HE_PATH . 'inc/admin-pages/upgrade.php';   // Phase 16.4
require_once BASE47_HE_PATH . 'inc/admin-pages/license.php';   // Phase 16.4

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
