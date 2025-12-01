<?php
/**
 * Dashboard Admin Page
 * 
 * Shows overview of all theme sets and their status
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_dashboard_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $sets   = base47_he_get_template_sets();
    $active = base47_he_get_active_sets();
    $all    = base47_he_get_all_templates( true );

    $counts = [];
    foreach ( $all as $item ) {
        $counts[ $item['set'] ] = ( $counts[ $item['set'] ] ?? 0 ) + 1;
    }
    ?>
    <div class="wrap base47-he-wrap">
        <h1>Base47 HTML Editor</h1>
        <p>Version: <?php echo esc_html( BASE47_HE_VERSION ); ?></p>

        <h2 style="margin-top:24px;">Theme Sets</h2>
        <div class="base47-he-grid">
            <?php foreach ( $sets as $slug => $set ) : ?>
                <div class="base47-box">
                    <h3><?php echo esc_html( $slug ); ?></h3>
                    <p class="base47-muted">
                        Status: <?php echo base47_he_is_set_active( $slug ) ? 'Active' : 'Inactive'; ?> |
                        Templates: <?php echo intval( $counts[ $slug ] ?? 0 ); ?>
                    </p>
                    <p class="base47-muted">Path: <code><?php echo esc_html( $set['path'] ); ?></code></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}
