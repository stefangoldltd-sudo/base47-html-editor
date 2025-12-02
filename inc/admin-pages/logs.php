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
                nonce: '<?php echo wp_create_nonce("base47_he"); ?>'
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