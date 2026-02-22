<?php
/**
 * White Label Admin Page - Pro Feature
 * 
 * Allows Pro users to customize branding, remove Base47 branding,
 * add custom logo and colors for agency/reseller use
 * 
 * @package Base47_HTML_Editor
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_white_label_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    // Check if Pro is active
    if ( ! base47_he_is_pro_active() ) {
        base47_he_show_pro_required_page( 'White Label', 'Customize branding and remove Base47 branding for your agency or clients.' );
        return;
    }
    
    // Handle form submission
    if ( isset( $_POST['save_white_label'] ) && wp_verify_nonce( $_POST['white_label_nonce'], 'base47_he_white_label' ) ) {
        $settings = [
            'enabled' => isset( $_POST['white_label_enabled'] ),
            'remove_branding' => isset( $_POST['remove_branding'] ),
            'custom_logo' => sanitize_url( $_POST['custom_logo'] ?? '' ),
            'custom_name' => sanitize_text_field( $_POST['custom_name'] ?? '' ),
            'custom_description' => sanitize_textarea_field( $_POST['custom_description'] ?? '' ),
            'custom_author' => sanitize_text_field( $_POST['custom_author'] ?? '' ),
            'custom_author_url' => sanitize_url( $_POST['custom_author_url'] ?? '' ),
            'custom_primary_color' => sanitize_hex_color( $_POST['custom_primary_color'] ?? '#f97316' ),
            'custom_secondary_color' => sanitize_hex_color( $_POST['custom_secondary_color'] ?? '#3b82f6' ),
            'hide_changelog' => isset( $_POST['hide_changelog'] ),
            'hide_support_links' => isset( $_POST['hide_support_links'] ),
            'custom_footer_text' => sanitize_text_field( $_POST['custom_footer_text'] ?? '' )
        ];
        
        update_option( 'base47_he_white_label', $settings );
        
        echo '<div class="notice notice-success"><p>White label settings saved successfully!</p></div>';
    }
    
    // Get current settings
    $settings = get_option( 'base47_he_white_label', [
        'enabled' => false,
        'remove_branding' => false,
        'custom_logo' => '',
        'custom_name' => '',
        'custom_description' => '',
        'custom_author' => '',
        'custom_author_url' => '',
        'custom_primary_color' => '#f97316',
        'custom_secondary_color' => '#3b82f6',
        'hide_changelog' => false,
        'hide_support_links' => false,
        'custom_footer_text' => ''
    ] );
    
    ?>
    <div class="wrap base47-white-label-soft-ui">
        
        <!-- Header -->
        <div class="base47-page-header">
            <div class="header-content">
                <div class="header-text">
                    <h1>
                        <span class="dashicons dashicons-admin-appearance"></span>
                        Agency Branding
                    </h1>
                    <p>Customize the plugin appearance for your agency or clients</p>
                </div>
                <div class="header-badge">
                    <span class="pro-badge">
                        <span class="dashicons dashicons-star-filled"></span>
                        Pro Feature
                    </span>
                </div>
            </div>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field( 'base47_he_white_label', 'white_label_nonce' ); ?>
            
            <div class="base47-settings-grid">
                
                <!-- Main Settings -->
                <div class="settings-main">
                    
                    <!-- Enable White Label -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>
                                <span class="dashicons dashicons-admin-tools"></span>
                                Branding Control
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="setting-row">
                                <label class="setting-toggle">
                                    <input type="checkbox" name="white_label_enabled" <?php checked( $settings['enabled'] ); ?>>
                                    <span class="toggle-slider"></span>
                                    <div class="toggle-content">
                                        <strong>Enable Custom Branding</strong>
                                        <p>Use your own branding instead of default plugin branding</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="setting-row">
                                <label class="setting-toggle">
                                    <input type="checkbox" name="remove_branding" <?php checked( $settings['remove_branding'] ); ?>>
                                    <span class="toggle-slider"></span>
                                    <div class="toggle-content">
                                        <strong>Use Clean Interface</strong>
                                        <p>Remove developer credits and use minimal interface</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Custom Branding -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>
                                <span class="dashicons dashicons-format-image"></span>
                                Your Branding
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="setting-row">
                                <label for="custom_logo">Company Logo URL</label>
                                <input type="url" id="custom_logo" name="custom_logo" value="<?php echo esc_attr( $settings['custom_logo'] ); ?>" placeholder="https://yourcompany.com/logo.png">
                                <p class="setting-description">Your company logo (recommended: 200x60px)</p>
                            </div>
                            
                            <div class="setting-row">
                                <label for="custom_name">Plugin Display Name</label>
                                <input type="text" id="custom_name" name="custom_name" value="<?php echo esc_attr( $settings['custom_name'] ); ?>" placeholder="Your HTML Editor">
                                <p class="setting-description">How the plugin appears in the admin menu</p>
                            </div>
                            
                            <div class="setting-row">
                                <label for="custom_description">Plugin Description</label>
                                <textarea id="custom_description" name="custom_description" rows="3" placeholder="Professional HTML template management system"><?php echo esc_textarea( $settings['custom_description'] ); ?></textarea>
                                <p class="setting-description">Brief description of what the plugin does</p>
                            </div>
                            
                            <div class="setting-row">
                                <label for="custom_author">Developer/Agency Name</label>
                                <input type="text" id="custom_author" name="custom_author" value="<?php echo esc_attr( $settings['custom_author'] ); ?>" placeholder="Your Company Name">
                                <p class="setting-description">Your company name for plugin credits</p>
                            </div>
                            
                            <div class="setting-row">
                                <label for="custom_author_url">Company Website</label>
                                <input type="url" id="custom_author_url" name="custom_author_url" value="<?php echo esc_attr( $settings['custom_author_url'] ); ?>" placeholder="https://yourcompany.com">
                                <p class="setting-description">Your company website URL</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Custom Colors -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>
                                <span class="dashicons dashicons-art"></span>
                                Custom Colors
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="color-grid">
                                <div class="color-setting">
                                    <label for="custom_primary_color">Primary Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" id="custom_primary_color" name="custom_primary_color" value="<?php echo esc_attr( $settings['custom_primary_color'] ); ?>">
                                        <input type="text" class="color-text" value="<?php echo esc_attr( $settings['custom_primary_color'] ); ?>" readonly>
                                    </div>
                                    <p class="setting-description">Main accent color for buttons and highlights</p>
                                </div>
                                
                                <div class="color-setting">
                                    <label for="custom_secondary_color">Secondary Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" id="custom_secondary_color" name="custom_secondary_color" value="<?php echo esc_attr( $settings['custom_secondary_color'] ); ?>">
                                        <input type="text" class="color-text" value="<?php echo esc_attr( $settings['custom_secondary_color'] ); ?>" readonly>
                                    </div>
                                    <p class="setting-description">Secondary color for links and accents</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Advanced Options -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>
                                <span class="dashicons dashicons-admin-generic"></span>
                                Interface Options
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="setting-row">
                                <label class="setting-toggle">
                                    <input type="checkbox" name="hide_changelog" <?php checked( $settings['hide_changelog'] ); ?>>
                                    <span class="toggle-slider"></span>
                                    <div class="toggle-content">
                                        <strong>Hide Version History</strong>
                                        <p>Remove the changelog page from admin menu</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="setting-row">
                                <label class="setting-toggle">
                                    <input type="checkbox" name="hide_support_links" <?php checked( $settings['hide_support_links'] ); ?>>
                                    <span class="toggle-slider"></span>
                                    <div class="toggle-content">
                                        <strong>Hide External Links</strong>
                                        <p>Remove external support and documentation links</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="setting-row">
                                <label for="custom_footer_text">Footer Credit Text</label>
                                <input type="text" id="custom_footer_text" name="custom_footer_text" value="<?php echo esc_attr( $settings['custom_footer_text'] ); ?>" placeholder="Developed by Your Company">
                                <p class="setting-description">Custom footer text for plugin pages</p>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Sidebar -->
                <div class="settings-sidebar">
                    
                    <!-- Preview -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>
                                <span class="dashicons dashicons-visibility"></span>
                                Live Preview
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="white-label-preview">
                                <div class="preview-header">
                                    <div class="preview-logo">
                                        <?php if ( $settings['custom_logo'] ) : ?>
                                            <img src="<?php echo esc_url( $settings['custom_logo'] ); ?>" alt="Custom Logo" style="max-height: 40px;">
                                        <?php else : ?>
                                            <span class="dashicons dashicons-admin-appearance"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="preview-text">
                                        <h4><?php echo esc_html( $settings['custom_name'] ?: 'Base47 HTML Editor' ); ?></h4>
                                        <p><?php echo esc_html( $settings['custom_description'] ?: 'Turn HTML templates into shortcodes' ); ?></p>
                                    </div>
                                </div>
                                
                                <div class="preview-colors">
                                    <div class="color-sample primary" style="background-color: <?php echo esc_attr( $settings['custom_primary_color'] ); ?>">
                                        Primary
                                    </div>
                                    <div class="color-sample secondary" style="background-color: <?php echo esc_attr( $settings['custom_secondary_color'] ); ?>">
                                        Secondary
                                    </div>
                                </div>
                                
                                <div class="preview-footer">
                                    <?php if ( $settings['remove_branding'] && $settings['custom_footer_text'] ) : ?>
                                        <small><?php echo esc_html( $settings['custom_footer_text'] ); ?></small>
                                    <?php elseif ( ! $settings['remove_branding'] ) : ?>
                                        <small>Powered by Base47</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- White Label Benefits -->
                    <div class="settings-card">
                        <div class="card-header">
                            <h3>
                                <span class="dashicons dashicons-star-filled"></span>
                                Agency Benefits
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="benefits-list">
                                <div class="benefit-item">
                                    <span class="dashicons dashicons-yes"></span>
                                    <span>Use your company branding</span>
                                </div>
                                <div class="benefit-item">
                                    <span class="dashicons dashicons-yes"></span>
                                    <span>Professional client presentation</span>
                                </div>
                                <div class="benefit-item">
                                    <span class="dashicons dashicons-yes"></span>
                                    <span>Custom colors and logo</span>
                                </div>
                                <div class="benefit-item">
                                    <span class="dashicons dashicons-yes"></span>
                                    <span>Clean, minimal interface</span>
                                </div>
                                <div class="benefit-item">
                                    <span class="dashicons dashicons-yes"></span>
                                    <span>Perfect for client work</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Save Button -->
                    <div class="settings-card">
                        <div class="card-body">
                            <button type="submit" name="save_white_label" class="btn-save-settings">
                                <span class="dashicons dashicons-saved"></span>
                                Save Branding Settings
                            </button>
                            
                            <p class="save-note">
                                <span class="dashicons dashicons-info"></span>
                                Changes apply immediately after saving
                            </p>
                        </div>
                    </div>
                    
                </div>
                
            </div>
            
        </form>
        
    </div>
    
    <?php
}

/**
 * Helper function to check if white label is enabled
 */
function base47_he_is_white_label_enabled() {
    if ( ! base47_he_is_pro_active() ) return false;
    
    $settings = get_option( 'base47_he_white_label', [] );
    return ! empty( $settings['enabled'] );
}

/**
 * Helper function to get white label settings
 */
function base47_he_get_white_label_settings() {
    if ( ! base47_he_is_pro_active() ) return [];
    
    return get_option( 'base47_he_white_label', [
        'enabled' => false,
        'remove_branding' => false,
        'custom_logo' => '',
        'custom_name' => '',
        'custom_description' => '',
        'custom_author' => '',
        'custom_author_url' => '',
        'custom_primary_color' => '#f97316',
        'custom_secondary_color' => '#3b82f6',
        'hide_changelog' => false,
        'hide_support_links' => false,
        'custom_footer_text' => ''
    ] );
}

/**
 * Get custom plugin name
 */
function base47_he_get_plugin_name() {
    if ( ! base47_he_is_white_label_enabled() ) return 'Base47 HTML Editor';
    
    $settings = base47_he_get_white_label_settings();
    return $settings['custom_name'] ?: 'Base47 HTML Editor';
}

/**
 * Get custom plugin description
 */
function base47_he_get_plugin_description() {
    if ( ! base47_he_is_white_label_enabled() ) return 'Turn HTML templates into shortcodes, edit them live, and manage which theme-sets are active via toggle switches.';
    
    $settings = base47_he_get_white_label_settings();
    return $settings['custom_description'] ?: 'Turn HTML templates into shortcodes, edit them live, and manage which theme-sets are active via toggle switches.';
}