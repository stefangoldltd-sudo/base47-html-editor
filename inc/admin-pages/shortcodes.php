<?php
/**
 * Shortcodes Admin Page
 * 
 * Lists all available shortcodes from active themes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_templates_page() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $active = base47_he_get_active_sets();
    $sets   = base47_he_get_template_sets();

    if ( empty( $active ) ) {
        echo '<div class="wrap"><h1>Shortcodes</h1><p>No active themes. Go to <strong>Theme Manager</strong> to enable one.</p></div>';
        return;
    }

    // Group templates by set
    $by_set = [];
    foreach ( base47_he_get_all_templates( false ) as $item ) {
        $by_set[ $item['set'] ][] = $item['file'];
    }

    ?>

    <div class="wrap base47-sc-soft-ui">
        
        <!-- SOFT UI HEADER -->
        <div class="base47-sc-header-soft">
            <h1>Shortcodes</h1>
            <p>Browse and manage your template shortcodes. Copy, preview, and edit with ease.</p>
        </div>

        <!-- SEARCH BAR -->
        <div class="base47-sc-search-wrapper">
            <div class="base47-sc-search-box">
                <span class="dashicons dashicons-search"></span>
                <input type="text" 
                       id="base47-sc-search" 
                       placeholder="Search templates by name..."
                       autocomplete="off">
            </div>
        </div>

    <?php foreach ( $active as $set_slug ) : ?>

        <?php 
        $files = $by_set[ $set_slug ] ?? []; 
        $theme_label = $sets[ $set_slug ]['label'] ?? $set_slug;
        ?>

        <div class="base47-sc-theme-section" data-theme="<?php echo esc_attr( $set_slug ); ?>">
            <h2 class="base47-sc-theme-title">
                <span class="dashicons dashicons-admin-appearance"></span>
                <?php echo esc_html( $theme_label ); ?>
            </h2>

        <?php if ( empty( $files ) ) : ?>

            <p class="base47-sc-empty">No templates found in this theme.</p>

        <?php else : ?>

            <div class="base47-he-template-grid">

                <?php foreach ( $files as $file ) :

$slug = base47_he_filename_to_slug( $file );

// Unified shortcode naming for ALL themes
$set_clean = str_replace( [ '-templates', '-templetes' ], '', $set_slug );
$shortcode = '[base47-' . $set_clean . '-' . $slug . ']';

                    // Classic preview
                    $preview_url = admin_url(
    'admin-ajax.php?action=base47_he_preview'
    . '&file=' . rawurlencode( $file )
    . '&set=' . rawurlencode( $set_slug )
    . '&_wpnonce=' . wp_create_nonce( 'base47_he' )
);

                    // Live editor
                    $editor_url = admin_url(
                        'admin.php?page=base47-he-editor&set=' . rawurlencode( $set_slug ) .
                        '&file=' . rawurlencode( $file )
                    );
                    ?>

                    <div class="base47-sc-card" data-template-name="<?php echo esc_attr( strtolower( $file ) ); ?>">

                        <!-- Card Header -->
                        <div class="base47-sc-card-header">
                            <h3 class="base47-sc-template-name"><?php echo esc_html( $file ); ?></h3>
                            <span class="base47-sc-theme-badge"><?php echo esc_html( $theme_label ); ?></span>
                        </div>

                        <!-- Shortcode Display -->
                        <div class="base47-sc-shortcode-display">
                            <code><?php echo esc_html( $shortcode ); ?></code>
                        </div>

                        <!-- Preview Panel -->
                        <div class="base47-sc-preview-panel">
                            <div class="base47-sc-preview-empty">
                                <span class="dashicons dashicons-visibility"></span>
                                <p>Click "Load Preview" to see template</p>
                            </div>
                            <iframe class="base47-he-template-iframe"
                                    src="about:blank"
                                    loading="lazy"
                                    style="display:none;"></iframe>
                        </div>

                        <!-- Card Actions -->
                        <div class="base47-sc-card-actions">

                            <button type="button"
                                    class="base47-sc-btn base47-sc-btn-primary base47-load-preview-btn"
                                    data-file="<?php echo esc_attr( $file ); ?>"
                                    data-set="<?php echo esc_attr( $set_slug ); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                                Load Preview
                            </button>

                            <button type="button"
                                    class="base47-sc-btn base47-sc-btn-secondary base47-he-copy"
                                    data-shortcode="<?php echo esc_attr( $shortcode ); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                                Copy Shortcode
                            </button>

                            <a class="base47-sc-btn base47-sc-btn-secondary" 
                               href="<?php echo esc_url( $editor_url ); ?>">
                                <span class="dashicons dashicons-edit"></span>
                                Edit
                            </a>

                            <a class="base47-sc-btn base47-sc-btn-secondary" 
                               target="_blank"
                               href="<?php echo esc_url( $preview_url ); ?>">
                                <span class="dashicons dashicons-external"></span>
                                Preview
                            </a>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        </div> <!-- END THEME SECTION -->

    <?php endforeach; ?>

    </div> <!-- END WRAP -->

    <?php
}
