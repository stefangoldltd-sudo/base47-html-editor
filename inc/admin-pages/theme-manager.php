<?php
/**
 * Theme Manager Admin Page
 * 
 * Install, delete, scan, and manage theme sets
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Theme Manager Page
 */
function base47_he_theme_manager_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $notices = [];

    // --------------------------------------------------
    // HANDLE FORM ACTIONS (install / delete / scan)
    // --------------------------------------------------
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
       check_admin_referer( 'base47_he', 'nonce' );
		
        $action = isset( $_POST['base47_he_theme_action'] )
            ? sanitize_text_field( wp_unslash( $_POST['base47_he_theme_action'] ) )
            : '';

        switch ( $action ) {

            case 'install_theme':
                $result = base47_he_install_theme_from_upload();
                if ( is_wp_error( $result ) ) {
                    $notices[] = [
                        'type' => 'error',
                        'msg'  => $result->get_error_message(),
                    ];
                } else {
                    $notices[] = [
                        'type' => 'updated',
                        'msg'  => sprintf(
                            'Theme <strong>%s</strong> installed successfully.',
                            esc_html( $result )
                        ),
                    ];
                    base47_he_refresh_theme_caches();
                }
                break;

            case 'delete_theme':
                $slug = isset( $_POST['base47_delete_theme'] )
                    ? sanitize_text_field( wp_unslash( $_POST['base47_delete_theme'] ) )
                    : '';

                if ( ! $slug ) {
                    $notices[] = [
                        'type' => 'error',
                        'msg'  => 'No theme selected for deletion.',
                    ];
                    break;
                }

                $result = base47_he_delete_theme_folder( $slug );
                if ( is_wp_error( $result ) ) {
                    $notices[] = [
                        'type' => 'error',
                        'msg'  => $result->get_error_message(),
                    ];
                } else {
                    $notices[] = [
                        'type' => 'updated',
                        'msg'  => sprintf(
                            'Theme <strong>%s</strong> deleted successfully.',
                            esc_html( $slug )
                        ),
                    ];
                    base47_he_refresh_theme_caches();
                }
                break;

            case 'scan_themes':
                base47_he_refresh_theme_caches();
                $notices[] = [
                    'type' => 'updated',
                    'msg'  => 'Theme list refreshed successfully.',
                ];
                break;

            // NOTE:
            // We no longer "save" active themes here.
            // Active/inactive is handled live via AJAX (base47_he_ajax_toggle_theme).
        }
    }

    ?>
    <div class="wrap base47-he-wrap base47-tm-soft-ui">
        
        <?php
        // NOTICES
        foreach ( $notices as $notice ) {
            $class = $notice['type'] === 'error' ? 'notice notice-error' : 'notice notice-success';
            echo '<div class="' . esc_attr( $class ) . '"><p>' . wp_kses_post( $notice['msg'] ) . '</p></div>';
        }
        ?>

        <!-- SOFT UI HEADER -->
        <div class="base47-tm-header-soft">
            <h1>Theme Manager</h1>
            <p>Manage your theme collections with style. Install, activate, and configure themes with ease.</p>
        </div>

        <!-- ACTION CARDS GRID -->
        <div class="base47-tm-actions-grid">
            
            <!-- Install Theme Card -->
            <div class="base47-tm-action-card">
                <h3>
                    <span class="dashicons dashicons-upload"></span>
                    Install Theme
                </h3>
                <p class="description">Upload a theme ZIP file to add new templates to your collection.</p>
                
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
                    <input type="hidden" name="base47_he_theme_action" value="install_theme">
                    
                    <div class="base47-file-input-wrapper">
                        <input type="file" name="base47_theme_zip" id="base47_theme_zip" accept=".zip">
                        <label for="base47_theme_zip" class="base47-file-input-label">
                            <span class="dashicons dashicons-media-archive"></span>
                            Choose ZIP File
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-soft-primary">
                        <span class="dashicons dashicons-upload" style="margin-right:4px;"></span>
                        Upload & Install
                    </button>
                </form>
            </div>

            <!-- Scan Themes Card -->
            <div class="base47-tm-action-card">
                <h3>
                    <span class="dashicons dashicons-update"></span>
                    Refresh Themes
                </h3>
                <p class="description">Scan for new themes uploaded via FTP and refresh the theme list.</p>
                
                <form method="post">
                    <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
                    <input type="hidden" name="base47_he_theme_action" value="scan_themes">
                    <button type="submit" class="btn-soft-secondary">
                        <span class="dashicons dashicons-update" style="margin-right:4px;"></span>
                        Scan Themes
                    </button>
                </form>
            </div>

            <!-- Rebuild Caches Card -->
            <div class="base47-tm-action-card">
                <h3>
                    <span class="dashicons dashicons-database"></span>
                    Rebuild Caches
                </h3>
                <p class="description">Clear and regenerate all template and theme caches for optimal performance.</p>
                
                <button type="button" id="base47-rebuild-caches-btn" class="btn-soft-secondary">
                    <span class="dashicons dashicons-database" style="margin-right:4px;"></span>
                    Rebuild All Caches
                </button>
            </div>

        </div>

        <!-- THEME MANAGER SECTION -->
        <?php base47_he_render_theme_manager_section(); ?>

    </div>
    <?php
}

/**
 * Render Theme Manager Soft UI Section
 */
function base47_he_render_theme_manager_section() {

    $themes        = base47_he_get_template_sets();       // real uploaded sets
    $active_themes = get_option( 'base47_active_themes', [] );

    if ( ! is_array( $active_themes ) ) {
        $active_themes = [];
    }
    
    // Default Theme Selector
    $default_theme = get_option('base47_default_theme', array_key_first($themes));
    ?>

    <!-- DEFAULT THEME SELECTOR -->
    <div class="base47-default-theme-card">
        <label for="base47_default_theme">
            <span class="dashicons dashicons-star-filled" style="margin-right:6px;"></span>
            Default Theme
        </label>
        <select id="base47_default_theme">
            <?php foreach ( $themes as $slug => $t ) : ?>
                <option value="<?php echo esc_attr($slug); ?>"
                    <?php selected( $slug, $default_theme ); ?>>
                    <?php echo esc_html( $t['label'] ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <!-- THEME CARDS GRID -->
    <div class="base47-tm-grid-soft">
        <?php foreach ( $themes as $slug => $theme ) :

            $info = [
                'label'       => $theme['label']       ?? $slug,
                'version'     => $theme['version']     ?? '1.0.0',
                'description' => $theme['description'] ?? 'No description provided',
                'accent'      => $theme['accent']      ?? '#7C5CFF',
                'thumbnail'   => $theme['thumbnail']   ?? '',
            ];

            $is_active    = in_array( $slug, $active_themes, true );
            $templates    = base47_he_count_theme_templates( $slug );
            $first_letter = strtoupper( mb_substr( $slug, 0, 1 ) );
            
            // Check if theme has proper metadata using enhanced detection
            $has_metadata = isset($theme['metadata_complete']) ? $theme['metadata_complete'] : true;
            $missing_fields = isset($theme['missing_fields']) ? $theme['missing_fields'] : [];
            
            // Show warning if metadata is incomplete
            $show_warning = !empty($missing_fields) || !$has_metadata;
            
            // Generate unique color for avatar
            $avatar_hue = crc32( $slug ) % 360;
            
            // Use default thumbnail if theme doesn't have one
            $thumbnail_url = '';
            if ( ! empty( $info['thumbnail'] ) && file_exists( $theme['path'] . $info['thumbnail'] ) ) {
                $thumbnail_url = $theme['url'] . $info['thumbnail'];
            } else {
                $thumbnail_url = BASE47_HE_URL . 'admin-assets/default-thumbnail.png';
            }
            
            // Asset modes
            $use_manifest_arr = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );
            $smart_loader_arr = get_option( BASE47_HE_OPT_USE_SMART_LOADER, [] );
            $use_manifest = in_array( $slug, $use_manifest_arr, true );
            $use_smart    = in_array( $slug, $smart_loader_arr, true );
            $manifest_path = trailingslashit( $theme['path'] ) . 'manifest.json';
            $has_manifest  = file_exists( $manifest_path );
            ?>
            
        <!-- THEME CARD -->
        <div class="base47-tm-card-soft <?php echo $is_active ? 'is-active' : 'is-inactive'; ?>"
             data-theme="<?php echo esc_attr( $slug ); ?>"
             data-active="<?php echo $is_active ? '1' : '0'; ?>">

            <!-- Card Header with Thumbnail -->
            <div class="base47-tm-card-header">
                <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $info['label'] ); ?>">
                
                <!-- Status Badge -->
                <div class="base47-tm-status-badge <?php echo $is_active ? 'is-active' : 'is-inactive'; ?>">
                    <?php echo $is_active ? 'Active' : 'Inactive'; ?>
                </div>
            </div>

            <!-- Card Body -->
            <div class="base47-tm-card-body">

                <!-- Theme Info -->
                <div class="base47-tm-theme-info">
                    <div class="base47-tm-avatar" style="background: hsl(<?php echo esc_attr( $avatar_hue ); ?>, 70%, 50%);">
                        <?php echo esc_html( $first_letter ); ?>
                    </div>

                    <div class="base47-tm-theme-details">
                        <h3 class="base47-tm-theme-name"><?php echo esc_html( $info['label'] ); ?></h3>

                        <div class="base47-tm-theme-meta">
                            <span class="base47-tm-meta-item">
                                <span class="dashicons dashicons-admin-appearance"></span>
                                v<?php echo esc_html( $info['version'] ); ?>
                            </span>

                            <span class="base47-tm-meta-sep">•</span>

                            <span class="base47-tm-meta-item">
                                <span class="dashicons dashicons-media-spreadsheet"></span>
                                <?php echo esc_html( $templates ); ?> templates
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <?php if ( ! empty( $info['description'] ) ) : ?>
                    <p class="base47-tm-description">
                        <?php echo esc_html( $info['description'] ); ?>
                    </p>
                <?php endif; ?>

                <!-- Warning Badge for Missing Metadata -->
                <?php if ( $show_warning ) : ?>
                    <div class="base47-tm-warning-badge">
                        <span class="dashicons dashicons-warning"></span>
                        Missing Metadata
                    </div>
                <?php endif; ?>

                <!-- Asset Modes -->
                <div class="base47-tm-asset-modes">
                    <div class="base47-tm-asset-modes-title">Asset Loading Mode</div>
                    
                    <div class="base47-tm-mode-options">
                        <!-- Classic Loader -->
                        <div class="base47-tm-mode-option">
                            <input type="radio"
                                   id="mode_loader_<?php echo esc_attr( $slug ); ?>"
                                   name="asset_mode_<?php echo esc_attr( $slug ); ?>"
                                   value="loader"
                                   <?php checked( ! $use_manifest && ! $use_smart ); ?>>
                            <label for="mode_loader_<?php echo esc_attr( $slug ); ?>" class="base47-tm-mode-label">
                                Loader
                            </label>
                        </div>

                        <!-- Manifest -->
                        <div class="base47-tm-mode-option">
                            <input type="radio"
                                   id="mode_manifest_<?php echo esc_attr( $slug ); ?>"
                                   name="asset_mode_<?php echo esc_attr( $slug ); ?>"
                                   value="manifest"
                                   <?php checked( $use_manifest ); ?>
                                   <?php disabled( ! $has_manifest ); ?>>
                            <label for="mode_manifest_<?php echo esc_attr( $slug ); ?>" class="base47-tm-mode-label">
                                Manifest
                            </label>
                        </div>

                        <!-- Smart Loader++ -->
                        <div class="base47-tm-mode-option">
                            <input type="radio"
                                   id="mode_smart_<?php echo esc_attr( $slug ); ?>"
                                   name="asset_mode_<?php echo esc_attr( $slug ); ?>"
                                   value="smart"
                                   <?php checked( $use_smart ); ?>>
                            <label for="mode_smart_<?php echo esc_attr( $slug ); ?>" class="base47-tm-mode-label">
                                Smart++
                            </label>
                        </div>
                    </div>

                    <!-- Hidden save fields -->
                    <input type="checkbox"
                           class="tm-hidden-manifest"
                           name="base47_use_manifest[]"
                           value="<?php echo esc_attr( $slug ); ?>"
                           <?php checked( $use_manifest ); ?>
                           style="display:none;">

                    <input type="checkbox"
                           class="tm-hidden-smart"
                           name="base47_he_use_smart_loader[]"
                           value="<?php echo esc_attr( $slug ); ?>"
                           <?php checked( $use_smart ); ?>
                           style="display:none;">
                </div>

            </div>

            <!-- Card Footer -->
            <div class="base47-tm-card-footer">
                
                <!-- Toggle Switch -->
                <div class="base47-tm-toggle-wrapper">
                    <label class="base47-tm-toggle">
                        <input type="checkbox"
                               class="base47-tm-toggle-input"
                               data-theme="<?php echo esc_attr( $slug ); ?>"
                               <?php checked( $is_active ); ?>>
                        <span class="base47-tm-toggle-slider"></span>
                    </label>
                    <span class="base47-tm-toggle-label">
                        <?php echo $is_active ? 'Enabled' : 'Disabled'; ?>
                    </span>
                </div>

                <!-- Actions -->
                <div class="base47-tm-actions">
                    <button type="button"
                            class="base47-tm-btn-icon base47-tm-uninstall-btn"
                            data-theme="<?php echo esc_attr( $slug ); ?>"
                            title="Uninstall Theme">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>

            </div>

        </div> <!-- END THEME CARD -->

        <?php endforeach; ?>
    </div> <!-- END THEME CARDS GRID -->

    <!-- Footer Note -->
    <div class="base47-tm-footer-note">
        <p>💡 Pro Tip: Keep only the themes you actively use enabled. Fewer active themes = faster performance!</p>
    </div>

    <?php
}
