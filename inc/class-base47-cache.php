<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Base47 Smart Cache Engine
 *
 * - Caches theme sets
 * - Caches template lists
 * - Caches widget lists
 * - Auto-invalidates when folders change
 * - NEVER caches template contents (safe for Live Editor)
 */
class Base47_Cache {

    const TRANS_SETS      = 'base47_he_cache_template_sets';
    const TRANS_TEMPLATES = 'base47_he_cache_template_list';
    const TRANS_WIDGETS   = 'base47_he_cache_widget_list';
    const CACHE_TIME      = 30 * MINUTE_IN_SECONDS; // adjust as you like

    /**
     * Build a signature (hash) based on folder names matching a pattern.
     */
    public static function get_signature( $pattern ) {
        $dirs   = glob( $pattern, GLOB_ONLYDIR ) ?: [];
        $names  = array_map( 'basename', $dirs );
        sort( $names, SORT_NATURAL | SORT_FLAG_CASE );
        return md5( implode( '|', $names ) );
    }

    /**
     * Clear all Base47 caches.
     * Use this after theme install/remove, or from Theme Manager.
     */
    public static function clear_all() {
        delete_transient( self::TRANS_SETS );
        delete_transient( self::TRANS_TEMPLATES );
        delete_transient( self::TRANS_WIDGETS );
    }
}