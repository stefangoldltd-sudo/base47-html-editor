<?php
/**
 * Logs Admin Page - Soft UI
 * 
 * Displays system logs with Soft UI design
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.7.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render the Logs Page (called from main plugin menu)
 */
function base47_he_render_logs_page() {

    // Add a test log entry if logs are empty (for demonstration)
    $raw_logs = base47_he_get_logs();
    if ( empty( $raw_logs ) ) {
        base47_he_log( 'Logs page accessed - logging system initialized', 'info' );
        base47_he_log( 'Welcome to Base47 HTML Editor logging system', 'info' );
        $raw_logs = base47_he_get_logs();
    }
    
    $log_entries = base47_he_parse_logs( $raw_logs );
    $log_file = base47_he_get_log_file();
    $log_size = file_exists( $log_file ) ? size_format( filesize( $log_file ) ) : '0 B';
    $log_count = count( $log_entries );
    
    ?>

    <div class="wrap base47-logs-soft-ui">
        
        <!-- SOFT UI HEADER -->
        <div class="base47-logs-header-soft">
            <h1>System Logs</h1>
            <p>Monitor plugin activity, errors, and system events in real-time.</p>
        </div>

        <!-- STATS CARDS -->
        <div class="base47-logs-stats">
            <div class="base47-logs-stat-card">
                <span class="dashicons dashicons-media-text"></span>
                <div>
                    <div class="stat-number"><?php echo $log_count; ?></div>
                    <div class="stat-label">Log Entries</div>
                </div>
            </div>
            <div class="base47-logs-stat-card">
                <span class="dashicons dashicons-database"></span>
                <div>
                    <div class="stat-number"><?php echo $log_size; ?></div>
                    <div class="stat-label">Log Size</div>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="base47-logs-actions">
            <button type="button" id="base47-clear-logs" class="base47-logs-btn base47-logs-btn-danger">
                <span class="dashicons dashicons-trash"></span>
                Clear Logs
            </button>
            <button type="button" id="base47-download-logs" class="base47-logs-btn base47-logs-btn-secondary">
                <span class="dashicons dashicons-download"></span>
                Download Logs
            </button>
            <span id="base47-logs-status" class="base47-logs-status"></span>
        </div>

        <!-- LOGS TABLE -->
        <?php if ( empty( $log_entries ) ) : ?>
            
            <div class="base47-logs-empty">
                <span class="dashicons dashicons-info"></span>
                <h3>No Logs Available</h3>
                <p>System logs will appear here as events occur.</p>
            </div>

        <?php else : ?>

            <div class="base47-logs-table-wrapper">
                <table class="base47-logs-table">
                    <thead>
                        <tr>
                            <th class="col-time">Time</th>
                            <th class="col-level">Level</th>
                            <th class="col-message">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( array_reverse( $log_entries ) as $entry ) : ?>
                            <tr class="log-row log-<?php echo esc_attr( strtolower( $entry['level'] ) ); ?>">
                                <td class="col-time">
                                    <span class="log-time"><?php echo esc_html( $entry['time'] ); ?></span>
                                </td>
                                <td class="col-level">
                                    <span class="log-badge log-badge-<?php echo esc_attr( strtolower( $entry['level'] ) ); ?>">
                                        <?php echo esc_html( $entry['level'] ); ?>
                                    </span>
                                </td>
                                <td class="col-message">
                                    <?php echo esc_html( $entry['message'] ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

        <!-- FOOTER NOTE -->
        <div class="base47-logs-footer">
            <p>
                <span class="dashicons dashicons-info"></span>
                Logs are stored in <code><?php echo esc_html( str_replace( ABSPATH, '', $log_file ) ); ?></code> and persist through plugin updates.
            </p>
        </div>

    </div>

    <script>
    jQuery(document).ready(function($) {
        
        // Clear logs
        $('#base47-clear-logs').on('click', function() {
            if (!confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
                return;
            }
            
            var btn = $(this);
            var originalText = btn.html();
            btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Clearing...');
            
            $.post(ajaxurl, {
                action: 'base47_clear_logs',
                nonce: '<?php echo wp_create_nonce("base47_he"); ?>'
            }, function(response) {
                if (response.success) {
                    $('#base47-logs-status').html('<span class="status-success">✓ Logs cleared successfully</span>');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $('#base47-logs-status').html('<span class="status-error">✗ Failed to clear logs</span>');
                    btn.prop('disabled', false).html(originalText);
                }
            }).fail(function() {
                $('#base47-logs-status').html('<span class="status-error">✗ Network error</span>');
                btn.prop('disabled', false).html(originalText);
            });
        });
        
        // Download logs
        $('#base47-download-logs').on('click', function() {
            window.location.href = ajaxurl + '?action=base47_download_logs&nonce=<?php echo wp_create_nonce("base47_he"); ?>';
        });
        
    });
    </script>

    <?php
}

/**
 * Parse raw logs into structured array
 * 
 * @param string $raw_logs Raw log content
 * @return array Parsed log entries
 */
function base47_he_parse_logs( $raw_logs ) {
    if ( empty( $raw_logs ) ) {
        return [];
    }
    
    $entries = [];
    $lines = explode( "\n", $raw_logs );
    
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }
        
        // Parse format: [2024-12-04 10:30:45] [INFO] Message here
        if ( preg_match( '/^\[([^\]]+)\]\s*\[([^\]]+)\]\s*(.+)$/', $line, $matches ) ) {
            $entries[] = [
                'time' => $matches[1],
                'level' => strtoupper( trim( $matches[2] ) ),
                'message' => trim( $matches[3] )
            ];
        } else {
            // Fallback for non-standard format
            $entries[] = [
                'time' => date( 'Y-m-d H:i:s' ),
                'level' => 'INFO',
                'message' => $line
            ];
        }
    }
    
    return $entries;
}