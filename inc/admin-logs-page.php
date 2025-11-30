<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Logs page to sidebar
 */
add_action( 'admin_menu', 'base47_he_register_logs_page' );
function base47_he_register_logs_page() {
    add_submenu_page(
        'base47-html-editor',
        'Logs',
        'Logs',
        'manage_options',
        'base47-logs',
        'base47_he_render_logs_page'
    );
}

/**
 * Render Logs UI
 */
function base47_he_render_logs_page() {

    $logs = esc_textarea( base47_he_get_logs() );
    ?>

    <div class="base47-he-wrap">
        <h1 class="base47-tm-title">Base47 Logs</h1>
        <p class="base47-tm-subtitle">System events, installs, cache operations & errors.</p>

        <div class="base47-logs-container">
            <textarea readonly class="base47-logs-box"><?php echo $logs; ?></textarea>
        </div>

        <button id="base47-clear-logs" class="button button-danger">
            Clear Logs
        </button>

        <span id="base47-logs-status" style="margin-left: 10px; color: #6ee7b7;"></span>
    </div>

    <?php
}


/**
 * AJAX → Clear logs
 */
add_action( 'wp_ajax_base47_clear_logs', 'base47_he_ajax_clear_logs' );
function base47_he_ajax_clear_logs() {

    base47_he_clear_logs();

    wp_send_json_success([
        'message' => 'Logs cleared',
        'logs'    => base47_he_get_logs()
    ]);
}