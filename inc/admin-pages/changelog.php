<?php
/**
 * Changelog Admin Page
 * 
 * Displays plugin changelog from changelog.txt
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_changelog_page() {
    $file    = BASE47_HE_PATH . 'changelog.txt';
    $content = file_exists( $file )
        ? file_get_contents( $file )
        : "• 2.3.0 – Special Widgets admin page, Redox slider v1 integration.\n• 2.1.0 – Theme Manager (toggle switches), active-only shortcodes, safer defaults.\n• 2.0.x – Multi-set foundations.\n";

    echo '<div class="wrap base47-he-wrap"><h1>Changelog</h1><pre class="base47-he-changelog">' . esc_html( $content ) . '</pre></div>';
}
