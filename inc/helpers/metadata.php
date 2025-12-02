<?php
/**
 * Theme Metadata Helper Functions
 * 
 * Functions for loading and processing theme.json metadata
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load theme metadata from theme.json inside a theme folder
 * 
 * @param string $path Path to theme folder
 * @return array Theme metadata array with fallbacks
 */
function base47_he_load_theme_metadata( $path ) {
    $file = trailingslashit( $path ) . 'theme.json';
    
    // Get folder name for fallback label
    $folder_name = basename( $path );
    
    // Create readable label from folder name
    // kiro-templates → KIRO Templates
    $label = str_replace( [ '-templates', '-templetes', '-', '_' ], [ '', '', ' ', ' ' ], $folder_name );
    $label = ucwords( trim( $label ) );
    if ( empty( $label ) ) {
        $label = $folder_name;
    }
    
    // Default fallbacks
    $defaults = [
        'label'       => $label,
        'version'     => '1.0.0',
        'description' => '',
        'accent'      => '#7C5CFF',
        'thumbnail'   => '', // Will use default thumbnail
        'has_metadata' => false, // Flag to show badge
    ];

    if ( ! file_exists( $file ) ) {
        return $defaults;
    }

    $json = file_get_contents( $file );
    $data = json_decode( $json, true );
    
    if ( ! is_array( $data ) ) {
        return $defaults;
    }
    
    // Merge with defaults, mark as having metadata
    $data['has_metadata'] = true;
    return array_merge( $defaults, $data );
}

/**
 * Safe theme metadata helper for Theme Manager cards
 * 
 * Reads theme.json if present, otherwise falls back to nice defaults
 * 
 * @param string $slug Theme slug
 * @return array Theme metadata with fallbacks
 */
function base47_he_theme_metadata( $slug ) {

    $meta = array(
        'title'       => '',
        'version'     => '',
        'author'      => '',
        'description' => '',
        'tags'        => array(),
    );

    if ( ! function_exists( 'base47_he_get_themes_root' ) ) {
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

    if ( empty( $meta['title'] ) ) {
        $pretty = preg_replace( '#-templates?$#', '', $slug );
        $pretty = str_replace( array( '-', '_' ), ' ', $pretty );
        $meta['title'] = ucwords( $pretty );
    }

    return $meta;
}
