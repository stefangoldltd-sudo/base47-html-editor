<?php
/**
 * Settings Page - Soft UI
 * 
 * Global plugin settings with Soft UI design
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.7.1
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
            'editor_mode'                 => sanitize_key( $_POST['editor_mode'] ?? 'advanced' ),
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
            // Log settings save
            $user = wp_get_current_user();
            $username = $user->user_login ?? 'Unknown';
            $changed_settings = [];
            $old_settings = base47_he_get_settings();
            foreach ( $new_settings as $key => $value ) {
                if ( isset( $old_settings[$key] ) && $old_settings[$key] !== $value ) {
                    $changed_settings[] = $key;
                }
            }
            if ( ! empty( $changed_settings ) ) {
                base47_he_log( "Settings updated: " . implode( ', ', $changed_settings ) . " by {$username}", 'info' );
            }
            
            echo '<div class="base47-notice base47-notice-success"><span class="dashicons dashicons-yes-alt"></span> Settings saved successfully.</div>';
        } else {
            echo '<div class="base47-notice base47-notice-error"><span class="dashicons dashicons-warning"></span> Failed to save settings.</div>';
        }
    }
    
    $settings = base47_he_get_settings();
    
    ?>
    <div class="wrap base47-settings-soft-ui">
        
        <!-- SOFT UI HEADER -->
        <div class="base47-settings-header-soft">
            <h1>Settings</h1>
            <p>Configure plugin behavior, editor preferences, logging, and advanced options.</p>
        </div>
        
        <form method="post" action="" class="base47-settings-form">
            <?php wp_nonce_field( 'base47_he_settings' ); ?>
            
            <div class="base47-settings-grid">
                
                <!-- GENERAL SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <h2>General</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Debug Mode -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Debug Mode</label>
                                <p class="description">Disables caching and shows developer information</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="debug_mode" value="1" <?php checked( $settings['debug_mode'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Enable Caching -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Enable Caching</label>
                                <p class="description">Cache theme discovery and template scans</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="enable_cache" value="1" <?php checked( $settings['enable_cache'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Cache Lifetime -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Cache Lifetime</label>
                                <p class="description">How long to cache theme data</p>
                            </div>
                            <div class="setting-control">
                                <div class="input-group">
                                    <input type="number" name="cache_lifetime" value="<?php echo esc_attr( $settings['cache_lifetime'] ); ?>" min="1" max="720" class="form-control">
                                    <span class="input-suffix">minutes</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Clear Caches -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Clear Caches</label>
                                <p class="description">Remove all cached data</p>
                            </div>
                            <div class="setting-control">
                                <button type="button" class="btn-soft-secondary" id="base47-clear-all-caches">
                                    <span class="dashicons dashicons-trash"></span>
                                    Clear All Caches
                                </button>
                                <span id="base47-cache-status" class="status-message"></span>
                            </div>
                        </div>
                        
                        <!-- Smart Loader++ (Pro) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('smart_loader') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Smart Loader++ <?php echo base47_he_get_feature_badge('smart_loader'); ?></label>
                                <p class="description">Advanced asset optimization and loading</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="smart_loader_enabled" value="1" <?php disabled(!base47_he_has_feature('smart_loader')); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Manifest Loader (Pro) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('manifest_loader') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Manifest Loader <?php echo base47_he_get_feature_badge('manifest_loader'); ?></label>
                                <p class="description">Load assets from manifest.json files</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="manifest_loader_enabled" value="1" <?php disabled(!base47_he_has_feature('manifest_loader')); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <!-- LIVE EDITOR SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-edit"></span>
                        <h2>Live Editor</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Default Editor Mode -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Default Editor Mode</label>
                                <p class="description">Which editor loads by default</p>
                            </div>
                            <div class="setting-control">
                                <?php if ( base47_he_has_feature( 'monaco_editor' ) ) : ?>
                                    <select name="editor_mode" class="form-select">
                                        <option value="advanced" <?php selected( $settings['editor_mode'], 'advanced' ); ?>>Advanced (Monaco)</option>
                                        <option value="classic" <?php selected( $settings['editor_mode'], 'classic' ); ?>>Classic (Textarea)</option>
                                    </select>
                                <?php else : ?>
                                    <select name="editor_mode" class="form-select" disabled>
                                        <option value="classic">Classic (Textarea) - Free</option>
                                    </select>
                                    <span style="margin-left: 8px;"><?php echo base47_he_get_feature_badge( 'monaco_editor' ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Line Wrapping (Free) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Line Wrapping</label>
                                <p class="description">Wrap long lines instead of scroll</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="line_wrap" value="1" <?php checked( $settings['line_wrap'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Auto-save Interval (Basic in Free) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Auto-save Interval</label>
                                <p class="description">Automatically save changes</p>
                            </div>
                            <div class="setting-control">
                                <select name="autosave_interval" class="form-select">
                                    <option value="0" <?php selected( $settings['autosave_interval'], 0 ); ?>>Disabled</option>
                                    <option value="30" <?php selected( $settings['autosave_interval'], 30 ); ?>>30 seconds</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Editor Theme (Pro - Monaco only) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('monaco_editor') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Editor Theme <?php echo base47_he_get_feature_badge('monaco_editor'); ?></label>
                                <p class="description">Visual theme for Monaco editor</p>
                            </div>
                            <div class="setting-control">
                                <select name="editor_theme" class="form-select" <?php disabled(!base47_he_has_feature('monaco_editor')); ?>>
                                    <option value="light" <?php selected( $settings['editor_theme'], 'light' ); ?>>Light</option>
                                    <option value="dark" <?php selected( $settings['editor_theme'], 'dark' ); ?>>Dark</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Font Size (Pro - Monaco only) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('monaco_editor') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Font Size <?php echo base47_he_get_feature_badge('monaco_editor'); ?></label>
                                <p class="description">Monaco editor text size</p>
                            </div>
                            <div class="setting-control">
                                <select name="editor_font_size" class="form-select" <?php disabled(!base47_he_has_feature('monaco_editor')); ?>>
                                    <option value="12px" <?php selected( $settings['editor_font_size'], '12px' ); ?>>12px</option>
                                    <option value="14px" <?php selected( $settings['editor_font_size'], '14px' ); ?>>14px</option>
                                    <option value="16px" <?php selected( $settings['editor_font_size'], '16px' ); ?>>16px</option>
                                    <option value="18px" <?php selected( $settings['editor_font_size'], '18px' ); ?>>18px</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Line Numbers (Pro - Monaco only) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('monaco_editor') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Show Line Numbers <?php echo base47_he_get_feature_badge('monaco_editor'); ?></label>
                                <p class="description">Display line numbers in Monaco</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="line_numbers" value="1" <?php checked( $settings['line_numbers'] ); ?> <?php disabled(!base47_he_has_feature('monaco_editor')); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <!-- LOGGING SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-media-text"></span>
                        <h2>Logging</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Enable Logging (Free) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Enable Logging</label>
                                <p class="description">Log plugin actions and errors</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="logging_enabled" value="1" <?php checked( $settings['logging_enabled'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Log Level (Free: Errors/Warnings, Pro: Info/Debug) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Log Level</label>
                                <p class="description">Minimum severity to log</p>
                            </div>
                            <div class="setting-control">
                                <select name="log_level" class="form-select">
                                    <option value="errors" <?php selected( $settings['log_level'], 'errors' ); ?>>Errors Only</option>
                                    <option value="warnings" <?php selected( $settings['log_level'], 'warnings' ); ?>>Warnings</option>
                                    <option value="info" <?php selected( $settings['log_level'], 'info' ); ?> <?php disabled(!base47_he_has_feature('advanced_logs')); ?>>Info <?php echo !base47_he_has_feature('advanced_logs') ? base47_he_get_feature_badge('advanced_logs') : ''; ?></option>
                                    <option value="debug" <?php selected( $settings['log_level'], 'debug' ); ?> <?php disabled(!base47_he_has_feature('advanced_logs')); ?>>Debug <?php echo !base47_he_has_feature('advanced_logs') ? base47_he_get_feature_badge('advanced_logs') : ''; ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Log Retention (Free: 7/14 days, Pro: up to 90) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Log Retention</label>
                                <p class="description">Auto-delete old logs</p>
                            </div>
                            <div class="setting-control">
                                <?php if ( base47_he_has_feature( 'advanced_logs' ) ) : ?>
                                    <div class="input-group">
                                        <input type="number" name="log_retention" value="<?php echo esc_attr( $settings['log_retention'] ); ?>" min="1" max="90" class="form-control">
                                        <span class="input-suffix">days</span>
                                    </div>
                                <?php else : ?>
                                    <select name="log_retention" class="form-select">
                                        <option value="7" <?php selected( $settings['log_retention'], 7 ); ?>>7 days</option>
                                        <option value="14" <?php selected( $settings['log_retention'], 14 ); ?>>14 days</option>
                                    </select>
                                    <span style="margin-left: 8px;"><?php echo base47_he_get_feature_badge( 'advanced_logs' ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Max Log Size (Free: fixed, Pro: configurable) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Max Log Size</label>
                                <p class="description">Maximum size per log file</p>
                            </div>
                            <div class="setting-control">
                                <?php if ( base47_he_has_feature( 'advanced_logs' ) ) : ?>
                                    <div class="input-group">
                                        <input type="number" name="max_log_size" value="<?php echo esc_attr( $settings['max_log_size'] ); ?>" min="1" max="50" class="form-control">
                                        <span class="input-suffix">MB</span>
                                    </div>
                                <?php else : ?>
                                    <div class="input-group">
                                        <input type="number" name="max_log_size" value="5" class="form-control" disabled>
                                        <span class="input-suffix">MB</span>
                                    </div>
                                    <span style="margin-left: 8px;"><?php echo base47_he_get_feature_badge( 'advanced_logs' ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Log Actions -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Log Actions</label>
                                <p class="description">Manage log files</p>
                            </div>
                            <div class="setting-control">
                                <div class="button-group">
                                    <button type="button" class="btn-soft-secondary" id="base47-clear-logs">
                                        <span class="dashicons dashicons-trash"></span>
                                        Clear Logs
                                    </button>
                                    <button type="button" class="btn-soft-secondary" id="base47-download-logs">
                                        <span class="dashicons dashicons-download"></span>
                                        Download
                                    </button>
                                </div>
                                <span id="base47-log-status" class="status-message"></span>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <!-- DEVELOPER TOOLS SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-admin-tools"></span>
                        <h2>Developer Tools</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Disable Smart Loader (Free - for safety) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Disable Smart Loader</label>
                                <p class="description">Serve unoptimized assets in debug</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="disable_smart_loader_debug" value="1" <?php checked( $settings['disable_smart_loader_debug'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Show File Paths (Pro) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('advanced_logs') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Show File Paths <?php echo base47_he_get_feature_badge('advanced_logs'); ?></label>
                                <p class="description">Display template paths in preview</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="show_file_paths" value="1" <?php checked( $settings['show_file_paths'] ); ?> <?php disabled(!base47_he_has_feature('advanced_logs')); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Show Loaded Assets (Pro) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('advanced_logs') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Show Loaded Assets <?php echo base47_he_get_feature_badge('advanced_logs'); ?></label>
                                <p class="description">Show Smart Loader asset map</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="show_asset_map" value="1" <?php checked( $settings['show_asset_map'] ); ?> <?php disabled(!base47_he_has_feature('advanced_logs')); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Experimental Features (Pro) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('advanced_logs') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Experimental Features <?php echo base47_he_get_feature_badge('advanced_logs'); ?></label>
                                <p class="description">Enable beta features (use with caution)</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="experimental_features" value="1" <?php checked( $settings['experimental_features'] ); ?> <?php disabled(!base47_he_has_feature('advanced_logs')); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Performance Metrics (Pro) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('advanced_logs') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Performance Metrics <?php echo base47_he_get_feature_badge('advanced_logs'); ?></label>
                                <p class="description">Display performance data in admin</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="show_performance_metrics" value="1" <?php checked( $settings['show_performance_metrics'] ); ?> <?php disabled(!base47_he_has_feature('advanced_logs')); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <!-- SECURITY SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-shield"></span>
                        <h2>Security</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Restrict Editor to Admins -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Restrict Editor to Admins</label>
                                <p class="description">Only administrators can use Live Editor</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="restrict_editor_admins" value="1" <?php checked( $settings['restrict_editor_admins'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Disable Theme Upload for Editors -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Disable Theme Upload</label>
                                <p class="description">Only administrators can upload themes</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="disable_upload_editors" value="1" <?php checked( $settings['disable_upload_editors'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Sanitize Template Output -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Sanitize Output</label>
                                <p class="description">Sanitize template output (recommended)</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="sanitize_output" value="1" <?php checked( $settings['sanitize_output'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <!-- ADVANCED SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <h2>Advanced</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Reset Settings (Free) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Reset to Defaults</label>
                                <p class="description">Restore all settings to default values</p>
                            </div>
                            <div class="setting-control">
                                <button type="button" id="base47-reset-settings" class="btn-soft-danger">
                                    <span class="dashicons dashicons-undo"></span>
                                    Reset to Defaults
                                </button>
                            </div>
                        </div>
                        
                        <!-- Export Settings (Pro) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('advanced_logs') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Export Settings <?php echo base47_he_get_feature_badge('advanced_logs'); ?></label>
                                <p class="description">Download settings as JSON file</p>
                            </div>
                            <div class="setting-control">
                                <?php if ( base47_he_has_feature( 'advanced_logs' ) ) : ?>
                                    <button type="button" id="base47-export-settings" class="btn-soft-secondary">
                                        <span class="dashicons dashicons-download"></span>
                                        Export Settings
                                    </button>
                                <?php else : ?>
                                    <button type="button" class="btn-soft-secondary" disabled>
                                        <span class="dashicons dashicons-download"></span>
                                        Export Settings
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Import Settings (Pro) -->
                        <div class="setting-row <?php echo !base47_he_has_feature('advanced_logs') ? 'base47-pro-disabled' : ''; ?>">
                            <div class="setting-label">
                                <label>Import Settings <?php echo base47_he_get_feature_badge('advanced_logs'); ?></label>
                                <p class="description">Upload previously exported settings</p>
                            </div>
                            <div class="setting-control">
                                <?php if ( base47_he_has_feature( 'advanced_logs' ) ) : ?>
                                    <input type="file" id="base47-import-file" accept=".json" style="display:none;">
                                    <button type="button" id="base47-import-settings" class="btn-soft-secondary">
                                        <span class="dashicons dashicons-upload"></span>
                                        Import Settings
                                    </button>
                                    <span id="base47-import-status" class="status-message"></span>
                                <?php else : ?>
                                    <button type="button" class="btn-soft-secondary" disabled>
                                        <span class="dashicons dashicons-upload"></span>
                                        Import Settings
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </div>
            
            <!-- SAVE BUTTON -->
            <div class="base47-settings-footer">
                <button type="submit" name="base47_he_save_settings" class="btn-soft-primary btn-large">
                    <span class="dashicons dashicons-yes"></span>
                    Save All Settings
                </button>
            </div>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        
        // Clear all caches
        $('#base47-clear-all-caches').on('click', function() {
            var btn = $(this);
            var originalText = btn.html();
            btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Clearing...');
            
            $.post(ajaxurl, {
                action: 'base47_clear_all_caches',
                nonce: '<?php echo wp_create_nonce( 'base47_he' ); ?>'
            }, function(response) {
                if (response.success) {
                    $('#base47-cache-status').html('<span class="status-success">✓ Cleared</span>');
                } else {
                    $('#base47-cache-status').html('<span class="status-error">✗ Failed</span>');
                }
                btn.prop('disabled', false).html(originalText);
                setTimeout(function() {
                    $('#base47-cache-status').html('');
                }, 3000);
            });
        });
        
        // Clear logs
        $('#base47-clear-logs').on('click', function() {
            if (!confirm('Are you sure you want to delete all logs?')) return;
            
            var btn = $(this);
            var originalText = btn.html();
            btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Clearing...');
            
            $.post(ajaxurl, {
                action: 'base47_clear_logs',
                nonce: '<?php echo wp_create_nonce( 'base47_he' ); ?>'
            }, function(response) {
                if (response.success) {
                    $('#base47-log-status').html('<span class="status-success">✓ Cleared</span>');
                } else {
                    $('#base47-log-status').html('<span class="status-error">✗ Failed</span>');
                }
                btn.prop('disabled', false).html(originalText);
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
            if (!confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) return;
            
            var btn = $(this);
            var originalText = btn.html();
            btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Resetting...');
            
            $.post(ajaxurl, {
                action: 'base47_reset_settings',
                nonce: '<?php echo wp_create_nonce( 'base47_he' ); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to reset settings.');
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Export settings
        $('#base47-export-settings').on('click', function() {
            window.location.href = ajaxurl + '?action=base47_export_settings&nonce=<?php echo wp_create_nonce( 'base47_he' ); ?>';
        });
        
        // Import settings
        $('#base47-import-settings').on('click', function() {
            $('#base47-import-file').click();
        });
        
        $('#base47-import-file').on('change', function() {
            var file = this.files[0];
            if (!file) return;
            
            if (!confirm('Import settings from "' + file.name + '"? This will overwrite your current settings.')) {
                $(this).val('');
                return;
            }
            
            var formData = new FormData();
            formData.append('action', 'base47_import_settings');
            formData.append('nonce', '<?php echo wp_create_nonce( 'base47_he' ); ?>');
            formData.append('settings_file', file);
            
            $('#base47-import-status').html('<span class="status-info">Importing...</span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#base47-import-status').html('<span class="status-success">✓ Imported!</span>');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#base47-import-status').html('<span class="status-error">✗ ' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    $('#base47-import-status').html('<span class="status-error">✗ Import failed</span>');
                }
            });
            
            $(this).val('');
        });
    });
    </script>
    <?php
}
