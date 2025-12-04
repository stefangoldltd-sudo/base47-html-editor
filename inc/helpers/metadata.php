<?php
/**
 * Theme Metadata Helper Functions
 * 
 * Functions for loading and processing theme.json metadata
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load theme metadata from theme.json inside a theme folder
 * Enhanced detection engine for v2.9.6.6
 * 
 * @param string $path Path to theme folder
 * @return array Theme metadata array with fallbacks and detection flags
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
    
    // Detection flags
    $has_theme_json = file_exists( $file );
    $has_thumbnail = false;
    $missing_fields = [];
    
    // Check for thumbnail
    $thumbnail_path = trailingslashit( $path ) . 'thumbnail.png';
    if ( ! file_exists( $thumbnail_path ) ) {
        $thumbnail_path = trailingslashit( $path ) . 'thumbnail.jpg';
        if ( ! file_exists( $thumbnail_path ) ) {
            $missing_fields[] = 'thumbnail';
        } else {
            $has_thumbnail = true;
        }
    } else {
        $has_thumbnail = true;
    }
    
    // Default fallbacks
    $defaults = [
        'label'       => $label,
        'version'     => '1.0.0',
        'description' => 'No description provided',
        'accent'      => '#7C5CFF',
        'thumbnail'   => '', // Will use default thumbnail
        'has_metadata' => false, // Flag to show badge
        'has_theme_json' => $has_theme_json,
        'has_thumbnail' => $has_thumbnail,
        'missing_fields' => $missing_fields,
        'metadata_complete' => false,
    ];

    if ( ! $has_theme_json ) {
        $missing_fields[] = 'theme.json';
        $defaults['missing_fields'] = $missing_fields;
        return $defaults;
    }

    $json = file_get_contents( $file );
    $data = json_decode( $json, true );
    
    if ( ! is_array( $data ) ) {
        $missing_fields[] = 'theme.json (invalid)';
        $defaults['missing_fields'] = $missing_fields;
        return $defaults;
    }
    
    // Check for required fields
    if ( empty( $data['label'] ) || $data['label'] === $folder_name ) {
        $missing_fields[] = 'label';
    }
    if ( empty( $data['version'] ) || $data['version'] === '1.0.0' ) {
        $missing_fields[] = 'version';
    }
    if ( empty( $data['description'] ) ) {
        $missing_fields[] = 'description';
    }
    
    // Determine if metadata is complete
    $metadata_complete = empty( $missing_fields ) && $has_theme_json && $has_thumbnail;
    
    // Merge with defaults, mark as having metadata
    $data['has_metadata'] = true;
    $data['has_theme_json'] = $has_theme_json;
    $data['has_thumbnail'] = $has_thumbnail;
    $data['missing_fields'] = $missing_fields;
    $data['metadata_complete'] = $metadata_complete;
    
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
