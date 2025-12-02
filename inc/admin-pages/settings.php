<?php
/**
 * Settings Page
 * 
 * Global plugin settings for behavior, editor, logging, and developer tools.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.4.5
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render Settings Page
 */
function base47_he_settings_page() {
    
    // Handle form submission
    if ( isset( $_POST['base47_he_save_settings'] ) ) {
        check_admin_referer( 'base47_he_settings' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions.' );
        }
        
        $new_settings = [
            // General
            'debug_mode'                  => isset( $_POST['debug_mode'] ),
            'enable_cache'                => isset( $_POST['enable_cache'] ),
            'cache_lifetime'              => absint( $_POST['cache_lifetime'] ?? 60 ),
            
            // Live Editor
            'editor_theme'                => sanitize_key( $_POST['editor_theme'] ?? 'light' ),
            'editor_font_size'            => sanitize_text_field( $_POST['editor_font_size'] ?? '14px' ),
            'line_numbers'                => isset( $_POST['line_numbers'] ),
            'line_wrap'                   => isset( $_POST['line_wrap'] ),
            'autosave_interval'           => absint( $_POST['autosave_interval'] ?? 0 ),
            
            // Logging
            'logging_enabled'             => isset( $_POST['logging_enabled'] ),
            'log_level'                   => sanitize_key( $_POST['log_level'] ?? 'warnings' ),
            'log_retention'               => absint( $_POST['log_retention'] ?? 14 ),
            'max_log_size'                => absint( $_POST['max_log_size'] ?? 5 ),
            
            // Developer Tools
            'show_file_paths'             => isset( $_POST['show_file_paths'] ),
            'show_asset_map'              => isset( $_POST['show_asset_map'] ),
            'disable_smart_loader_debug'  => isset( $_POST['disable_smart_loader_debug'] ),
            'experimental_features'       => isset( $_POST['experimental_features'] ),
            'show_performance_metrics'    => isset( $_POST['show_performance_metrics'] ),
            
            // Security
            'restrict_editor_admins'      => isset( $_POST['restrict_editor_admins'] ),
            'disable_upload_editors'      => isset( $_POST['disable_upload_editors'] ),
            'sanitize_output'             => isset( $_POST['sanitize_output'] ),
        ];
        
        if ( base47_he_update_settings( $new_settings ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Failed to save settings.</p></div>';
        }
    }
    
    $settings = base47_he_get_settings();
    
    ?>
    <div class="wrap base47-settings-page">
        <h1>Base47 HTML Editor — Settings</h1>
        <p>Configure global plugin behavior, editor preferences, logging, and developer tools.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field( 'base47_he_settings' ); ?>
            
            <table class="form-table" role="presentation">
                
                <!-- GENERAL SECTION -->
                <tr>
                    <th colspan="2">
                        <h2>General</h2>
                    </th>
                </tr>
                
                <tr>
                    <th scope="row">Debug Mode</th>
                    <td>
                        <label>
                            <input type="checkbox" name="debug_mode" value="1" <?php checked( $settings['debug_mode'] ); ?>>
                            Enable debug mode (disables caching, shows developer info)
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Enable Caching</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_cache" value="1" <?php checked( $settings['enable_cache'] ); ?>>
                            Cache theme discovery and template scans
                        </label>
                        <p class="description">Improves performance. Automatically disabled when Debug Mode is ON.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Cache Lifetime</th>
                    <td>
                        <input type="number" name="cache_lifetime" value="<?php echo esc_attr( $settings['cache_lifetime'] ); ?>" min="1" max="720" class="small-text">
                        minutes
                        <p class="description">How long to cache theme data (1-720 minutes).</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Clear Caches</th>
                    <td>
                        <button type="button" class="button" id="base47-clear-all-caches">Clear All Caches</button>
                        <p class="description">Clears theme discovery and template caches.</p>
                        <span id="base47-cache-status"></span>
                    </td>
                </tr>
                
                <!-- LIVE EDITOR SECTION -->
                <tr>
                    <th colspan="2">
                        <h2>Live Editor</h2>
                    </th>
                </tr>
                
                <tr>
                    <th scope="row">Editor Theme</th>
                    <td>
                        <select name="editor_theme">
                            <option value="light" <?php selected( $settings['editor_theme'], 'light' ); ?>>Light</option>
                            <option value="dark" <?php selected( $settings['editor_theme'], 'dark' ); ?>>Dark</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Font Size</th>
                    <td>
                        <select name="editor_font_size">
                            <option value="12px" <?php selected( $settings['editor_font_size'], '12px' ); ?>>12px</option>
                            <option value="14px" <?php selected( $settings['editor_font_size'], '14px' ); ?>>14px</option>
                            <option value="16px" <?php selected( $settings['editor_font_size'], '16px' ); ?>>16px</option>
                            <option value="18px" <?php selected( $settings['editor_font_size'], '18px' ); ?>>18px</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Show Line Numbers</th>
                    <td>
                        <label>
                            <input type="checkbox" name="line_numbers" value="1" <?php checked( $settings['line_numbers'] ); ?>>
                            Display line numbers in editor
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Line Wrapping</th>
                    <td>
                        <label>
                            <input type="checkbox" name="line_wrap" value="1" <?php checked( $settings['line_wrap'] ); ?>>
                            Wrap long lines instead of horizontal scroll
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Auto-save Interval</th>
                    <td>
                        <select name="autosave_interval">
                            <option value="0" <?php selected( $settings['autosave_interval'], 0 ); ?>>Disabled</option>
                            <option value="30" <?php selected( $settings['autosave_interval'], 30 ); ?>>30 seconds</option>
                            <option value="60" <?php selected( $settings['autosave_interval'], 60 ); ?>>60 seconds</option>
                            <option value="120" <?php selected( $settings['autosave_interval'], 120 ); ?>>120 seconds</option>
                        </select>
                        <p class="description">Automatically save changes while editing (0 = disabled).</p>
                    </td>
                </tr>
                
                <!-- LOGGING SECTION -->
                <tr>
                    <th colspan="2">
                        <h2>Logging</h2>
                    </th>
                </tr>
                
                <tr>
                    <th scope="row">Enable Logging</th>
                    <td>
                        <label>
                            <input type="checkbox" name="logging_enabled" value="1" <?php checked( $settings['logging_enabled'] ); ?>>
                            Log plugin actions and errors
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Log Level</th>
                    <td>
                        <select name="log_level">
                            <option value="errors" <?php selected( $settings['log_level'], 'errors' ); ?>>Errors Only</option>
                            <option value="warnings" <?php selected( $settings['log_level'], 'warnings' ); ?>>Warnings</option>
                            <option value="info" <?php selected( $settings['log_level'], 'info' ); ?>>Info</option>
                            <option value="debug" <?php selected( $settings['log_level'], 'debug' ); ?>>Debug</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Log Retention</th>
                    <td>
                        <input type="number" name="log_retention" value="<?php echo esc_attr( $settings['log_retention'] ); ?>" min="1" max="90" class="small-text">
                        days
                        <p class="description">Automatically delete logs older than this (1-90 days).</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Max Log Size</th>
                    <td>
                        <input type="number" name="max_log_size" value="<?php echo esc_attr( $settings['max_log_size'] ); ?>" min="1" max="50" class="small-text">
                        MB
                        <p class="description">Maximum size per log file (1-50 MB).</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Log Actions</th>
                    <td>
                        <button type="button" class="button" id="base47-clear-logs">Clear Logs</button>
                        <button type="button" class="button" id="base47-download-logs">Download Logs</button>
                        <p class="description">Clear all logs or download them as a ZIP file.</p>
                        <span id="base47-log-status"></span>
                    </td>
                </tr>
                
                <!-- DEVELOPER TOOLS SECTION -->
                <tr>
                    <th colspan="2">
                        <h2>Developer Tools</h2>
                    </th>
                </tr>
                
                <tr>
                    <th scope="row">Show File Paths in Preview</th>
                    <td>
                        <label>
                            <input type="checkbox" name="show_file_paths" value="1" <?php checked( $settings['show_file_paths'] ); ?>>
                            Display template file paths in preview overlay
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Show Loaded Assets</th>
                    <td>
                        <label>
                            <input type="checkbox" name="show_asset_map" value="1" <?php checked( $settings['show_asset_map'] ); ?>>
                            Show Smart Loader asset map in preview
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Disable Smart Loader in Debug</th>
                    <td>
                        <label>
                            <input type="checkbox" name="disable_smart_loader_debug" value="1" <?php checked( $settings['disable_smart_loader_debug'] ); ?>>
                            Serve unoptimized assets when Debug Mode is ON
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Enable Experimental Features</th>
                    <td>
                        <label>
                            <input type="checkbox" name="experimental_features" value="1" <?php checked( $settings['experimental_features'] ); ?>>
                            Enable beta features (use with caution)
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Show Performance Metrics</th>
                    <td>
                        <label>
                            <input type="checkbox" name="show_performance_metrics" value="1" <?php checked( $settings['show_performance_metrics'] ); ?>>
                            Display performance metrics in admin (experimental)
                        </label>
                    </td>
                </tr>
                
                <!-- SECURITY SECTION -->
                <tr>
                    <th colspan="2">
                        <h2>Security</h2>
                    </th>
                </tr>
                
                <tr>
                    <th scope="row">Restrict Editor to Admins</th>
                    <td>
                        <label>
                            <input type="checkbox" name="restrict_editor_admins" value="1" <?php checked( $settings['restrict_editor_admins'] ); ?>>
                            Only administrators can use Live Editor
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Disable Theme Upload for Editors</th>
                    <td>
                        <label>
                            <input type="checkbox" name="disable_upload_editors" value="1" <?php checked( $settings['disable_upload_editors'] ); ?>>
                            Only administrators can upload themes
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Sanitize Template Output</th>
                    <td>
                        <label>
                            <input type="checkbox" name="sanitize_output" value="1" <?php checked( $settings['sanitize_output'] ); ?>>
                            Sanitize template output (recommended)
                        </label>
                    </td>
                </tr>
                
            </table>
            
            <p class="submit">
                <button type="submit" name="base47_he_save_settings" class="button button-primary">Save Settings</button>
                <button type="button" id="base47-reset-settings" class="button">Reset to Defaults</button>
            </p>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        
        // Clear all caches
        $('#base47-clear-all-caches').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).text('Clearing...');
            
            $.post(ajaxurl, {
                action: 'base47_clear_all_caches',
                nonce: '<?php echo wp_create_nonce( 'base47_he' ); ?>'
            }, function(response) {
                if (response.success) {
                    $('#base47-cache-status').html('<span style="color: green;">✓ Caches cleared</span>');
                } else {
                    $('#base47-cache-status').html('<span style="color: red;">✗ Failed</span>');
                }
                btn.prop('disabled', false).text('Clear All Caches');
                setTimeout(function() {
                    $('#base47-cache-status').html('');
                }, 3000);
            });
        });
        
        // Clear logs
        $('#base47-clear-logs').on('click', function() {
            if (!confirm('Are you sure you want to delete all logs?')) return;
            
            var btn = $(this);
            btn.prop('disabled', true).text('Clearing...');
            
            $.post(ajaxurl, {
                action: 'base47_clear_logs',
                nonce: '<?php echo wp_create_nonce( 'base47_he' ); ?>'
            }, function(response) {
                if (response.success) {
                    $('#base47-log-status').html('<span style="color: green;">✓ Logs cleared</span>');
                } else {
                    $('#base47-log-status').html('<span style="color: red;">✗ Failed</span>');
                }
                btn.prop('disabled', false).text('Clear Logs');
                setTimeout(function() {
                    $('#base47-log-status').html('');
                }, 3000);
            });
        });
        
        // Download logs
        $('#base47-download-logs').on('click', function() {
            window.location.href = ajaxurl + '?action=base47_download_logs&nonce=<?php echo wp_create_nonce( 'base47_he' ); ?>';
        });
        
        // Reset settings
        $('#base47-reset-settings').on('click', function() {
            if (!confirm('Are you sure you want to reset all settings to defaults?')) return;
            
            var btn = $(this);
            btn.prop('disabled', true).text('Resetting...');
            
            $.post(ajaxurl, {
                action: 'base47_reset_settings',
                nonce: '<?php echo wp_create_nonce( 'base47_he' ); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to reset settings.');
                    btn.prop('disabled', false).text('Reset to Defaults');
                }
            });
        });
    });
    </script>
    <?php
}
