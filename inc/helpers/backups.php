<?php
/**
 * Backup System for Live Editor
 * 
 * Automatic backup and restore functionality for edited files
 * Stores backups outside plugin directory for safety
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get backup directory path
 */
function base47_he_get_backup_dir() {
    return WP_CONTENT_DIR . '/base47-backups';
}

/**
 * Ensure backup directory exists with security
 */
function base47_he_ensure_backup_security() {
    $backup_dir = base47_he_get_backup_dir();
    
    // Create main backup directory
    if ( ! file_exists( $backup_dir ) ) {
        wp_mkdir_p( $backup_dir );
    }
    
    // Create .htaccess to prevent direct access
    $htaccess_file = $backup_dir . '/.htaccess';
    if ( ! file_exists( $htaccess_file ) ) {
        $htaccess_content = "# Deny all direct access\n";
        $htaccess_content .= "Order deny,allow\n";
        $htaccess_content .= "Deny from all\n";
        file_put_contents( $htaccess_file, $htaccess_content );
    }
    
    // Create index.html to prevent directory listing
    $index_file = $backup_dir . '/index.html';
    if ( ! file_exists( $index_file ) ) {
        file_put_contents( $index_file, '<!-- Silence is golden -->' );
    }
}

/**
 * Save backup of a file
 * 
 * @param string $file_path Full path to the file being saved
 * @param string $content File content
 * @param string $theme Theme/set name
 * @return bool Success status
 */
function base47_he_save_backup( $file_path, $content, $theme ) {
    base47_he_ensure_backup_security();
    
    $backup_dir = base47_he_get_backup_dir();
    $theme_dir = $backup_dir . '/' . sanitize_file_name( $theme );
    
    // Create theme directory if needed
    if ( ! file_exists( $theme_dir ) ) {
        wp_mkdir_p( $theme_dir );
    }
    
    // Get relative path and filename
    $filename = basename( $file_path );
    $relative_dir = dirname( str_replace( wp_normalize_path( WP_CONTENT_DIR ), '', wp_normalize_path( $file_path ) ) );
    
    // Create subdirectory structure if needed
    $backup_subdir = $theme_dir . $relative_dir;
    if ( ! file_exists( $backup_subdir ) ) {
        wp_mkdir_p( $backup_subdir );
    }
    
    // Generate backup filename with timestamp
    $timestamp = current_time( 'Ymd-His' );
    $backup_filename = $filename . '.' . $timestamp . '.backup';
    $backup_path = $backup_subdir . '/' . $backup_filename;
    
    // Save backup file
    $result = file_put_contents( $backup_path, $content );
    
    if ( false !== $result ) {
        // Update backup index
        base47_he_update_backup_index( $theme, $filename, $backup_filename, $relative_dir );
        
        // Cleanup old backups
        base47_he_cleanup_file_backups( $theme, $filename, $relative_dir );
        
        return true;
    }
    
    return false;
}

/**
 * Update backup index file
 * 
 * @param string $theme Theme name
 * @param string $filename Original filename
 * @param string $backup_filename Backup filename with timestamp
 * @param string $relative_dir Relative directory path
 */
function base47_he_update_backup_index( $theme, $filename, $backup_filename, $relative_dir ) {
    $backup_dir = base47_he_get_backup_dir();
    $theme_dir = $backup_dir . '/' . sanitize_file_name( $theme );
    $index_file = $theme_dir . '/index.json';
    
    // Load existing index
    $index = [];
    if ( file_exists( $index_file ) ) {
        $json = file_get_contents( $index_file );
        $index = json_decode( $json, true );
        if ( ! is_array( $index ) ) {
            $index = [];
        }
    }
    
    // Create unique key for file (includes path)
    $file_key = ltrim( $relative_dir . '/' . $filename, '/' );
    
    // Add new backup to index
    if ( ! isset( $index[ $file_key ] ) ) {
        $index[ $file_key ] = [];
    }
    
    // Prepend new backup (newest first)
    array_unshift( $index[ $file_key ], $backup_filename );
    
    // Keep only last 5 backups in index
    $index[ $file_key ] = array_slice( $index[ $file_key ], 0, 5 );
    
    // Save index
    file_put_contents( $index_file, json_encode( $index, JSON_PRETTY_PRINT ) );
}

/**
 * List available backups for a file
 * 
 * @param string $file_path Full path to the file
 * @param string $theme Theme name
 * @return array List of backups with metadata
 */
function base47_he_list_backups( $file_path, $theme ) {
    $backup_dir = base47_he_get_backup_dir();
    $theme_dir = $backup_dir . '/' . sanitize_file_name( $theme );
    $index_file = $theme_dir . '/index.json';
    
    if ( ! file_exists( $index_file ) ) {
        return [];
    }
    
    // Load index
    $json = file_get_contents( $index_file );
    $index = json_decode( $json, true );
    if ( ! is_array( $index ) ) {
        return [];
    }
    
    // Get file key
    $filename = basename( $file_path );
    $relative_dir = dirname( str_replace( wp_normalize_path( WP_CONTENT_DIR ), '', wp_normalize_path( $file_path ) ) );
    $file_key = ltrim( $relative_dir . '/' . $filename, '/' );
    
    if ( ! isset( $index[ $file_key ] ) ) {
        return [];
    }
    
    // Build backup list with metadata
    $backups = [];
    $backup_subdir = $theme_dir . $relative_dir;
    
    foreach ( $index[ $file_key ] as $backup_filename ) {
        $backup_path = $backup_subdir . '/' . $backup_filename;
        
        if ( file_exists( $backup_path ) ) {
            // Extract timestamp from filename
            preg_match( '/\.(\d{8}-\d{6})\.backup$/', $backup_filename, $matches );
            $timestamp_str = isset( $matches[1] ) ? $matches[1] : '';
            
            // Parse timestamp
            if ( $timestamp_str ) {
                $date_part = substr( $timestamp_str, 0, 8 );
                $time_part = substr( $timestamp_str, 9, 6 );
                $formatted_date = substr( $date_part, 0, 4 ) . '-' . substr( $date_part, 4, 2 ) . '-' . substr( $date_part, 6, 2 );
                $formatted_time = substr( $time_part, 0, 2 ) . ':' . substr( $time_part, 2, 2 ) . ':' . substr( $time_part, 4, 2 );
                $display_date = $formatted_date . ' ' . $formatted_time;
            } else {
                $display_date = 'Unknown date';
            }
            
            $backups[] = [
                'filename' => $backup_filename,
                'path' => $backup_path,
                'timestamp' => $timestamp_str,
                'display_date' => $display_date,
                'size' => filesize( $backup_path ),
            ];
        }
    }
    
    return $backups;
}

/**
 * Restore a backup file
 * 
 * @param string $backup_filename Backup filename
 * @param string $theme Theme name
 * @param string $original_path Original file path
 * @return string|false Backup content or false on failure
 */
function base47_he_restore_backup( $backup_filename, $theme, $original_path ) {
    $backup_dir = base47_he_get_backup_dir();
    $theme_dir = $backup_dir . '/' . sanitize_file_name( $theme );
    
    // Get relative directory
    $relative_dir = dirname( str_replace( wp_normalize_path( WP_CONTENT_DIR ), '', wp_normalize_path( $original_path ) ) );
    $backup_subdir = $theme_dir . $relative_dir;
    $backup_path = $backup_subdir . '/' . $backup_filename;
    
    if ( ! file_exists( $backup_path ) ) {
        return false;
    }
    
    return file_get_contents( $backup_path );
}

/**
 * Cleanup old backups for a specific file (keep only 5)
 * 
 * @param string $theme Theme name
 * @param string $filename Original filename
 * @param string $relative_dir Relative directory path
 */
function base47_he_cleanup_file_backups( $theme, $filename, $relative_dir ) {
    $backup_dir = base47_he_get_backup_dir();
    $theme_dir = $backup_dir . '/' . sanitize_file_name( $theme );
    $backup_subdir = $theme_dir . $relative_dir;
    
    if ( ! file_exists( $backup_subdir ) ) {
        return;
    }
    
    // Get all backup files for this file
    $pattern = $backup_subdir . '/' . $filename . '.*.backup';
    $files = glob( $pattern );
    
    if ( ! $files || count( $files ) <= 5 ) {
        return;
    }
    
    // Sort by modification time (newest first)
    usort( $files, function( $a, $b ) {
        return filemtime( $b ) - filemtime( $a );
    });
    
    // Delete files beyond the 5 most recent
    $files_to_delete = array_slice( $files, 5 );
    foreach ( $files_to_delete as $file ) {
        @unlink( $file );
    }
}

/**
 * Cleanup all backups older than 30 days
 */
function base47_he_cleanup_old_backups() {
    $backup_dir = base47_he_get_backup_dir();
    
    if ( ! file_exists( $backup_dir ) ) {
        return;
    }
    
    $thirty_days_ago = time() - ( 30 * DAY_IN_SECONDS );
    
    // Recursively find all .backup files
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $backup_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ( $iterator as $file ) {
        if ( $file->isFile() && $file->getExtension() === 'backup' ) {
            if ( $file->getMTime() < $thirty_days_ago ) {
                @unlink( $file->getPathname() );
            }
        }
    }
}

/**
 * Download a backup file
 * 
 * @param string $backup_filename Backup filename
 * @param string $theme Theme name
 * @param string $original_path Original file path
 */
function base47_he_download_backup( $backup_filename, $theme, $original_path ) {
    $content = base47_he_restore_backup( $backup_filename, $theme, $original_path );
    
    if ( false === $content ) {
        return false;
    }
    
    // Set headers for download
    header( 'Content-Type: application/octet-stream' );
    header( 'Content-Disposition: attachment; filename="' . $backup_filename . '"' );
    header( 'Content-Length: ' . strlen( $content ) );
    
    echo $content;
    exit;
}
