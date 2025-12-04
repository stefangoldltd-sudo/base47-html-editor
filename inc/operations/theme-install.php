<?php
/**
 * Theme Installation Operations
 * 
 * Handles theme ZIP upload and extraction
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install a theme from uploaded ZIP
 * 
 * Expects ZIP structure:
 *   /{slug}-templates/
 *       home.html
 *       manifest.json
 *       assets/
 *
 * The theme will be installed into:
 *   /wp-content/uploads/base47-themes/{slug}-templates/
 */
function base47_he_install_theme_from_upload() {

    if ( ! isset($_FILES['base47_theme_zip']) || empty($_FILES['base47_theme_zip']['name']) ) {
        return new WP_Error('no_file', 'No ZIP file uploaded.');
    }

    $file = $_FILES['base47_theme_zip'];

    if (! empty($file['error'])) {
        return new WP_Error('upload_error', 'Upload error: ' . intval($file['error']));
    }

    $name      = $file['name'];
    $tmp       = $file['tmp_name'];
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if ($extension !== 'zip') {
        return new WP_Error('invalid_type', 'File must be a .zip archive.');
    }

    if (! class_exists('ZipArchive')) {
        return new WP_Error('no_zip', 'ZipArchive is not available on this server.');
    }

    $zip = new ZipArchive();
    if (true !== $zip->open($tmp)) {
        return new WP_Error('open_failed', 'Could not open ZIP file.');
    }

    // Detect root folder inside ZIP
    $root_folder = '';
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if (! $stat || empty($stat['name'])) continue;

        $name_in_zip = $stat['name'];

        if (substr($name_in_zip, -1) === '/') {
            $root_folder = trim($name_in_zip, '/');
            break;
        }
    }

    if (! $root_folder) {
        $zip->close();
        return new WP_Error('no_root_folder', 'ZIP must contain a root folder (e.g. lezar-templates/).');
    }

    // VALIDATION: Folder must end with -templates or -templetes
    if (! preg_match('/-templates?$/i', $root_folder)) {
        $zip->close();
        return new WP_Error(
            'invalid_structure', 
            'Invalid theme structure. The ZIP must contain a folder ending with "-templates" (e.g., lezar-templates, kiro-templates).',
            ['type' => 'structure']
        );
    }
    
    // Clean the folder name to a safe slug (lowercase)
    $root_folder = strtolower( $root_folder );

    // Determine install location
    $root       = base47_he_get_themes_root();
    $themes_dir = $root['dir'];

    $target_dir = trailingslashit($themes_dir . $root_folder);

    // VALIDATION: Check if theme already exists (overwrite protection)
    if (file_exists($target_dir)) {
        $zip->close();
        
        // Get theme name for better error message
        $theme_name = str_replace(['-templates', '-templetes', '-', '_'], ['', '', ' ', ' '], $root_folder);
        $theme_name = ucwords(trim($theme_name));
        
        return new WP_Error(
            'theme_exists', 
            sprintf('A theme named "%s" already exists. Please delete the existing theme first or rename your theme folder.', $theme_name),
            ['type' => 'exists', 'theme' => $root_folder]
        );
    }
    
    // Note: HTML validation removed for performance (was causing 30+ second delays)

    // Extract ONLY into uploads dir
    if (! $zip->extractTo($themes_dir)) {
        $zip->close();
        return new WP_Error('extract_failed', 'Could not extract ZIP into themes directory.');
    }

    $zip->close();

    if (! is_dir($target_dir)) {
        return new WP_Error('no_target', 'Theme folder not found after extraction.');
    }

    // Auto-rebuild cache after successful install
    base47_he_refresh_theme_caches();

    return $root_folder;
}
