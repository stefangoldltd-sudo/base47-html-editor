<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Future-ready special widget registry.
 *
 * Currently returns an empty array so it has ZERO effect on performance.
 * Later we can enable it to auto-detect special widgets in /special-widgets.
 */
class Base47_Widget_Registry {

    public static function get_widgets( $force = false ) {

        // Disabled for now – safe placeholder.
        return [];

        /*
        require_once BASE47_HE_PATH . 'inc/class-base47-cache.php';

        static $static = null;
        if ( $static !== null && ! $force ) {
            return $static;
        }

        $folder = BASE47_HE_PATH . 'special-widgets/';
        if ( ! is_dir( $folder ) ) {
            return [];
        }

        $saved = get_transient( Base47_Cache::TRANS_WIDGETS );
        $sig   = Base47_Cache::get_signature( $folder . '*' );

        if (
            ! $force &&
            is_array( $saved ) &&
            isset( $saved['widgets'], $saved['signature'] ) &&
            hash_equals( $saved['signature'], $sig )
        ) {
            $static = $saved['widgets'];
            return $static;
        }

        $widgets = [];
        foreach ( glob( $folder . '*', GLOB_ONLYDIR ) as $widget_dir ) {
            if ( file_exists( $widget_dir . '/widget.json' ) ) {
                $widgets[ basename( $widget_dir ) ] = $widget_dir;
            }
        }

        set_transient( Base47_Cache::TRANS_WIDGETS, [
            'widgets'   => $widgets,
            'signature' => $sig,
        ], Base47_Cache::CACHE_TIME );

        $static = $widgets;
        return $static;
        */
    }

}