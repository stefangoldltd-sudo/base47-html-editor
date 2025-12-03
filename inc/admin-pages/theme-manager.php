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
    <div class="wrap base47-he-wrap">
        <h1>Theme Manager</h1>

        <?php
        // NOTICES
        foreach ( $notices as $notice ) {
            $class = $notice['type'] === 'error' ? 'notice notice-error' : 'notice notice-success';
            echo '<div class="' . esc_attr( $class ) . '"><p>' . wp_kses_post( $notice['msg'] ) . '</p></div>';
        }
        ?>

        <!-- TOP ACTION BAR: INSTALL + SCAN -->
        <div style="margin:20px 0;padding:15px;border:1px solid #ddd;background:#fff;border-radius:6px;">
            <h2 style="margin-top:0;">Theme Actions</h2>

            <!-- Install ZIP -->
            <form method="post" enctype="multipart/form-data" style="margin-bottom:12px;">
                <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
                <input type="hidden" name="base47_he_theme_action" value="install_theme">
                <label for="base47_theme_zip" style="display:inline-block;margin-right:8px;">
                    <strong>Install Theme (ZIP):</strong>
                </label>
                <input type="file" name="base47_theme_zip" id="base47_theme_zip" accept=".zip">
                <button type="submit" class="button button-primary" style="margin-left:6px;">
                    Upload &amp; Install
                </button>
                <p class="description" style="margin-top:6px;">
                    ZIP must contain a folder like <code>lezar-templates/</code> or <code>bfolio-templates/</code>.
                </p>
            </form>

            <!-- Scan themes -->
            <form method="post" style="margin-top:10px;">
                  <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
				<input type="hidden" name="base47_he_theme_action" value="scan_themes">
                <button type="submit" class="button">
                    Scan Themes
                </button>
                <span class="description" style="margin-left:8px;">
                    Refresh the list after uploading theme folders via FTP.
                </span>
            </form>
        </div>
		
		<!-- Rebuild all caches -->
<form method="post" style="margin-top:10px;">
     <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
    <button type="button"
            id="base47-rebuild-caches-btn"
            class="button button-secondary">
        Rebuild All Caches
    </button>
    <span class="description" style="margin-left:8px;">
        Clears and regenerates all template + theme caches.
    </span>
</form>

        <!-- GLASS THEME MANAGER -->
        <?php base47_he_render_theme_manager_section(); ?>

    </div>
    <?php
}

/**
 * Render Theme Manager Glass UI Section
 */
function base47_he_render_theme_manager_section() {

    $themes        = base47_he_get_template_sets();       // real uploaded sets
    $active_themes = get_option( 'base47_active_themes', [] );

    if ( ! is_array( $active_themes ) ) {
        $active_themes = [];
    }
    ?>
    <div class="base47-he-wrap base47-tm-wrap">

        <div class="base47-tm-header">
            <h2 class="base47-tm-title">Theme Manager</h2>
            <p class="base47-tm-subtitle">
                Choose which theme sets are active. Only active themes load templates and assets.
            </p>
        </div>

		<?php
// Default Theme Selector
$default_theme = get_option('base47_default_theme', array_key_first($themes));
?>

<div class="base47-default-theme-row" style="margin-bottom:20px;">
    <label for="base47_default_theme" style="font-weight:600; margin-right:10px;">
        Default Theme:
    </label>

    <select id="base47_default_theme" style="padding:6px 10px; border-radius:6px;">
        <?php foreach ( $themes as $slug => $t ) : ?>
            <option value="<?php echo esc_attr($slug); ?>"
                <?php selected( $slug, $default_theme ); ?>>
                <?php echo esc_html( $t['label'] ); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
		
        <div class="base47-tm-grid">
            <?php foreach ( $themes as $slug => $theme ) :

                 $info = [
                          'label'       => $theme['label']       ?? $slug,
                          'version'     => $theme['version']     ?? '1.0.0',
                          'description' => $theme['description'] ?? '',
                          'accent'      => $theme['accent']      ?? '#7C5CFF',
                          'thumbnail'   => $theme['thumbnail']   ?? '',
                       ];

                $is_active    = in_array( $slug, $active_themes, true );
                $templates    = base47_he_count_theme_templates( $slug );
                $accent       = $info['accent'];
                $first_letter = strtoupper( mb_substr( $slug, 0, 1 ) );
                
                // Check if theme has proper metadata
                $has_metadata = true;
                if ( empty( $theme['label'] ) || $theme['label'] === $slug ||
                     empty( $theme['description'] ) || $theme['description'] === 'Auto-generated theme' ||
                     empty( $theme['version'] ) || $theme['version'] === '1.0.0' ) {
                    $has_metadata = false;
                }
                
                // Generate unique color for avatar
                $avatar_hue = crc32( $slug ) % 360;
                
                // Use default thumbnail if theme doesn't have one
                $thumbnail_url = '';
                if ( ! empty( $info['thumbnail'] ) && file_exists( $theme['path'] . $info['thumbnail'] ) ) {
                    $thumbnail_url = $theme['url'] . $info['thumbnail'];
                } else {
                    // Use plugin's default thumbnail
                    $thumbnail_url = BASE47_HE_URL . 'admin-assets/default-thumbnail.png';
                }
                ?>
                
<div class="base47-tm-card <?php echo $is_active ? 'is-active' : 'is-inactive'; ?>"
     data-theme="<?php echo esc_attr( $slug ); ?>"
     data-active="<?php echo $is_active ? '1' : '0'; ?>"
     style="--base47-tm-accent: <?php echo esc_attr( $accent ); ?>;">

    <div class="base47-tm-card-bg"></div>

    <div class="base47-tm-card-inner">

        <!-- Badge -->
        <div class="base47-tm-badge">
            <span class="base47-tm-badge-dot"></span>
            <span class="base47-tm-badge-text">
                <?php echo $is_active ? 'Active' : 'Disabled'; ?>
            </span>
        </div>
        
        <!-- No Metadata Badge -->
        <?php if ( ! $has_metadata ) : ?>
            <div class="base47-tm-badge" style="background: rgba(217, 119, 6, 0.1); border-color: rgba(217, 119, 6, 0.3);">
                <span style="color: #d97706;">⚠</span>
                <span class="base47-tm-badge-text" style="color: #d97706;">No metadata</span>
            </div>
        <?php endif; ?>

        <!-- MAIN INFO ROW -->
        <div class="base47-tm-card-main">

            <div class="base47-tm-logo" style="background: hsl(<?php echo esc_attr( $avatar_hue ); ?>, 70%, 50%);">
                <span class="base47-tm-logo-inner">
                    <?php echo esc_html( $first_letter ); ?>
                </span>
            </div>

            <div class="base47-tm-text">
                <h3 class="base47-tm-name"><?php echo esc_html( $info['label'] ); ?></h3>

                <div class="base47-tm-meta">
                    <span class="base47-tm-meta-item">
                        <span class="dashicons dashicons-admin-appearance"></span>
                        Version <?php echo esc_html( $info['version'] ); ?>
                    </span>

                    <span class="base47-tm-meta-sep">•</span>

                    <span class="base47-tm-meta-item">
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        <?php echo esc_html( $templates ); ?> templates
                    </span>
                </div>

                <?php if ( ! empty( $info['description'] ) ) : ?>
                    <p class="base47-tm-description">
                        <?php echo esc_html( $info['description'] ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- THUMBNAIL -->
        <?php if ( ! empty( $thumbnail_url ) ) : ?>
            <div class="base47-tm-thumb">
                <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="">
            </div>
        <?php endif; ?>

        <!-- FOOTER -->
        <div class="base47-tm-footer">

            <!-- Toggle -->
            <label class="base47-tm-toggle">
                <input type="checkbox"
                       class="base47-tm-toggle-input"
                       data-theme="<?php echo esc_attr( $slug ); ?>"
                       <?php checked( $is_active ); ?> />

                <span class="base47-tm-toggle-track">
                    <span class="base47-tm-toggle-thumb"></span>
                </span>

                <span class="base47-tm-toggle-label">
                    <?php echo $is_active ? 'Enabled' : 'Disabled'; ?>
                </span>
            </label>

            <div class="base47-tm-footer-right">

                <!-- Uninstall -->
                <button type="button"
                        class="button-link-delete base47-tm-uninstall-btn"
                        data-theme="<?php echo esc_attr( $slug ); ?>">
                    Uninstall
                </button>

                <!-- Coming soon -->
                <button type="button" class="button button-secondary base47-tm-details-btn" disabled>
                    <span class="dashicons dashicons-visibility"></span>
                    Coming soon
                </button>
            </div>

        </div>

  <!-- ASSET MODES -->
<div class="base47-tm-asset-modes">

    <?php
        // Existing options
        $use_manifest_arr = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );

        // NEW: Smart Loader++
        $smart_loader_arr = get_option( BASE47_HE_OPT_USE_SMART_LOADER, [] );

        $use_manifest = in_array( $slug, $use_manifest_arr, true );
        $use_smart    = in_array( $slug, $smart_loader_arr, true );

        $manifest_path = trailingslashit( $theme['path'] ) . 'manifest.json';
        $has_manifest  = file_exists( $manifest_path );
    ?>

    <!-- 1) Classic Loader (default) -->
    <label class="tm-mode">
        <input type="radio"
               name="asset_mode_<?php echo esc_attr( $slug ); ?>"
               value="loader"
               <?php checked( ! $use_manifest && ! $use_smart ); ?>>
        <span>Loader (default)</span>
    </label>

    <!-- 2) Manifest -->
    <label class="tm-mode">
        <input type="radio"
               name="asset_mode_<?php echo esc_attr( $slug ); ?>"
               value="manifest"
               <?php checked( $use_manifest ); ?>
               <?php disabled( ! $has_manifest ); ?>>
        <span>Manifest</span>
    </label>

    <!-- 3) Smart Loader++ -->
    <label class="tm-mode">
        <input type="radio"
               name="asset_mode_<?php echo esc_attr( $slug ); ?>"
               value="smart"
               <?php checked( $use_smart ); ?>>
        <span>Smart Loader++</span>
    </label>

    <!-- Hidden save fields -->

    <!-- Manifest save -->
    <input type="checkbox"
           class="tm-hidden-manifest"
           name="base47_use_manifest[]"
           value="<?php echo esc_attr( $slug ); ?>"
           <?php checked( $use_manifest ); ?>>

    <!-- Smart Loader save -->
    <input type="checkbox"
           class="tm-hidden-smart"
           name="base47_he_use_smart_loader[]"
           value="<?php echo esc_attr( $slug ); ?>"
           <?php checked( $use_smart ); ?>>
</div>
		
		
    </div> <!-- END card inner -->

</div> <!-- END card -->

            <?php endforeach; ?>
        </div>

        <div class="base47-tm-footer-note">
            <p>Tip: keep only the themes you use enabled. Fewer active themes = faster Base47.</p>
        </div>

    </div>
    <?php
}
