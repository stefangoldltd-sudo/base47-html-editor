<?php
/**
 * License Activation Page
 * 
 * Slider Revolution-style license activation
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.2.6
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_license_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    $is_pro = base47_he_is_pro_active();
    $license_key = get_option( 'base47_he_license_key', '' );
    $license_status = get_option( 'base47_he_license_status', 'inactive' );
    $license_data = get_option( 'base47_he_license_data', [] );
    
    $pro_url = base47_he_get_pro_url();
    ?>
    <div class="wrap base47-license-page">
        
        <h1>License Activation</h1>
        
        <?php if ( $is_pro && $license_status === 'active' ) : ?>
            
            <!-- REGISTERED STATE (Like Slider Revolution) -->
            <div class="base47-license-registered">
                <div class="license-status-card">
                    <h2>Registered License Key</h2>
                    
                    <div class="license-status-tabs">
                        <button class="license-tab active">
                            <span class="dashicons dashicons-yes"></span>
                            Registered
                        </button>
                        <button class="license-tab" disabled>
                            <span class="dashicons dashicons-admin-network"></span>
                            Find My Key
                        </button>
                    </div>
                    
                    <div class="license-key-display">
                        <?php 
                        // Mask license key
                        $masked_key = '';
                        if ( $license_key ) {
                            $parts = explode( '-', $license_key );
                            $masked_key = 'XXXX-XXXX-XXXX-' . end($parts);
                        }
                        ?>
                        <input type="text" value="<?php echo esc_attr( $masked_key ); ?>" readonly class="license-key-input">
                    </div>
                    
                    <div class="license-info">
                        <?php if ( ! empty( $license_data ) ) : ?>
                            <p><strong>Plan:</strong> <?php echo esc_html( ucfirst( $license_data['plan'] ?? 'Pro' ) ); ?></p>
                            <p><strong>Expires:</strong> <?php echo esc_html( $license_data['expires'] ?? 'Never' ); ?></p>
                            <p><strong>Status:</strong> <span class="status-active">Active</span></p>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="base47-deregister-license" class="btn-deregister">
                        Deregister this License Key
                    </button>
                </div>
            </div>
            
        <?php else : ?>
            
            <!-- NOT REGISTERED STATE -->
            <div class="base47-license-not-registered">
                <div class="license-activation-card">
                    <h2>Activate Your License</h2>
                    <p>Enter your license key to unlock all Pro features</p>
                    
                    <form id="base47-license-form">
                        <div class="license-input-group">
                            <input type="text" 
                                   id="base47-license-key-input" 
                                   name="license_key" 
                                   placeholder="XXXX-XXXX-XXXX-XXXX" 
                                   class="license-key-input"
                                   value="<?php echo esc_attr( $license_key ); ?>">
                        </div>
                        
                        <div class="license-actions">
                            <button type="submit" class="btn-activate-license">
                                <span class="dashicons dashicons-yes"></span>
                                Activate License
                            </button>
                            <a href="<?php echo esc_url( $pro_url ); ?>" class="btn-find-key" target="_blank">
                                <span class="dashicons dashicons-admin-network"></span>
                                Find My Key
                            </a>
                        </div>
                        
                        <div id="license-message" class="license-message"></div>
                    </form>
                </div>
            </div>
            
        <?php endif; ?>
        
        <!-- License Info Box (Always Show) -->
        <div class="base47-license-info-box">
            <div class="info-box-icon">
                <span class="dashicons dashicons-info"></span>
            </div>
            <div class="info-box-content">
                <h3>1 License Key per Website</h3>
                <p>If you want to use Base47 HTML Editor on another domain, you need to use a different license key.</p>
                <?php if ( ! $is_pro ) : ?>
                    <a href="<?php echo esc_url( $pro_url ); ?>" class="btn-buy-license" target="_blank">
                        Buy License Key
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Manage Licenses Section -->
        <div class="base47-manage-licenses">
            <div class="manage-licenses-card">
                <h3>
                    Manage Your Licenses
                    <span class="badge-new">NEW</span>
                </h3>
                <p>Switch license key registrations, download plugins and get discounts!</p>
                <a href="<?php echo esc_url( $pro_url . '/my-account/' ); ?>" class="btn-dashboard" target="_blank">
                    Go To My Dashboard
                </a>
                <p class="help-text">
                    <a href="<?php echo esc_url( $pro_url . '/docs/license-activation/' ); ?>" target="_blank">
                        I don't have a login. How to get access?
                    </a>
                </p>
            </div>
        </div>
        
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Activate License
        $('#base47-license-form').on('submit', function(e) {
            e.preventDefault();
            
            var licenseKey = $('#base47-license-key-input').val().trim();
            var $message = $('#license-message');
            var $btn = $('.btn-activate-license');
            
            if (!licenseKey) {
                $message.html('<span class="error">Please enter a license key</span>').show();
                return;
            }
            
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Activating...');
            $message.html('<span class="info">Validating license key...</span>').show();
            
            $.post(ajaxurl, {
                action: 'base47_activate_license',
                license_key: licenseKey,
                nonce: '<?php echo wp_create_nonce('base47_he'); ?>'
            }, function(response) {
                if (response.success) {
                    $message.html('<span class="success">✓ License activated successfully! Reloading...</span>');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $message.html('<span class="error">✗ ' + response.data.message + '</span>');
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Activate License');
                }
            }).fail(function() {
                $message.html('<span class="error">✗ Network error. Please try again.</span>');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Activate License');
            });
        });
        
        // Deregister License
        $('#base47-deregister-license').on('click', function() {
            if (!confirm('Are you sure you want to deregister this license key? All Pro features will be disabled.')) {
                return;
            }
            
            var $btn = $(this);
            $btn.prop('disabled', true).html('Deregistering...');
            
            $.post(ajaxurl, {
                action: 'base47_deactivate_license',
                nonce: '<?php echo wp_create_nonce('base47_he'); ?>'
            }, function(response) {
                if (response.success) {
                    alert('License deregistered successfully!');
                    location.reload();
                } else {
                    alert('Failed to deregister license: ' + response.data.message);
                    $btn.prop('disabled', false).html('Deregister this License Key');
                }
            });
        });
    });
    </script>
    <?php
}
