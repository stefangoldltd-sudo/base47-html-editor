<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render the Logs Page (called from main plugin menu)
 */
function base47_he_render_logs_page() {

    $logs = esc_textarea( base47_he_get_logs() );
    ?>

    <div class="wrap base47-he-wrap">
        <h1>Base47 Logs</h1>
        <p class="description">System events, installs, cache rebuilds & errors.</p>

        <textarea readonly
                  style="width:100%;height:420px;margin-top:20px;font-family:monospace;"><?php echo $logs; ?></textarea>

        <button id="base47-clear-logs" class="button button-secondary" style="margin-top:10px;">
            Clear Logs
        </button>

        <span id="base47-logs-status"
              style="margin-left:10px;font-weight:600;color:#10b981;"></span>
    </div>

    <script>
    jQuery(function($){

        $('#base47-clear-logs').on('click', function(){

            $.post(ajaxurl, {
                action: 'base47_clear_logs',
                nonce: '<?php echo wp_create_nonce("base47-he-logs"); ?>'
            }, function(response){

                if (response.success){
                    $('textarea').val('');
                    $('#base47-logs-status').text('Logs cleared ✔');
                } else {
                    $('#base47-logs-status').text('Error clearing logs ❌');
                }

            });

        });

    });
    </script>

    <?php
}

/**
 * AJAX: Clear Logs
 */
add_action( 'wp_ajax_base47_clear_logs', 'base47_he_ajax_clear_logs' );

function base47_he_ajax_clear_logs() {

    check_ajax_referer( 'base47-he-logs', 'nonce' );

    base47_he_clear_logs();

    wp_send_json_success([
        'message' => 'Logs cleared',
        'logs'    => base47_he_get_logs()
    ]);
}