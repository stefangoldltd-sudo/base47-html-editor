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

    <h1>Shortcodes</h1>
    <p>
        Only <strong>active</strong> theme sets are listed.<br>
        Previews are now <strong>lazy-loaded</strong> – click <em>Load preview</em>.
    </p>

    <?php foreach ( $active as $set_slug ) : ?>

        <?php $files = $by_set[ $set_slug ] ?? []; ?>

        <h2><?php echo esc_html( $set_slug ); ?></h2>

        <?php if ( empty( $files ) ) : ?>

            <p class="base47-muted">No templates found in this set.</p>

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

                    <div class="base47-he-template-box">

                        <strong><?php echo esc_html( $file ); ?></strong>
                        <code><?php echo esc_html( $shortcode ); ?></code>

                        <div class="base47-he-template-thumb">
                            <iframe class="base47-he-template-iframe"
                                    src="about:blank"
                                    loading="lazy"></iframe>
                        </div>

                        <div class="base47-he-template-actions">

                            <a class="button" target="_blank"
                               href="<?php echo esc_url( $preview_url ); ?>">
                                Preview
                            </a>

                            <button type="button"
                                    class="button base47-he-copy"
                                    data-shortcode="<?php echo esc_attr( $shortcode ); ?>">
                                Copy shortcode
                            </button>

                            <a class="button" href="<?php echo esc_url( $editor_url ); ?>">
                                Edit
                            </a>

                            <button type="button"
                                    class="button button-secondary base47-load-preview-btn"
                                    data-file="<?php echo esc_attr( $file ); ?>"
                                    data-set="<?php echo esc_attr( $set_slug ); ?>">
                                Load preview
                            </button>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    <?php endforeach; ?>

    <?php
}
