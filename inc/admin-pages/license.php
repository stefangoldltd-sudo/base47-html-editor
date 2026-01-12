<?php
/**
 * License Activation Page - Soft UI v3.0
 * 
 * Modern, professional license activation with enhanced UX
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_license_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    $is_pro = base47_he_is_pro_active();
    $license_key = get_option( 'base47_he_license_key', '' );
    $license_status = get_option( 'base47_he_license_status', 'inactive' );
    $license_data = get_option( 'base47_he_license_data', [] );
    
    $pro_url = base47_he_get_pro_url();
    
    // Helper function to mask license key
    $mask_license_key = function( $key ) {
        if ( strlen( $key ) <= 8 ) {
            return $key;
        }
        $parts = explode( '-', $key );
        if ( count( $parts ) >= 4 ) {
            return $parts[0] . '-****-****-' . end($parts);
        }
        return substr( $key, 0, 4 ) . '-****-****-' . substr( $key, -4 );
    };
    ?>
    <div class="wrap base47-license-soft-ui">
        
        <!-- SOFT UI HEADER -->
        <div class="base47-license-header-soft">
            <div class="header-content">
                <h1>
                    <span class="dashicons dashicons-star-filled"></span>
                    License Management
                </h1>
                <p>Activate your Pro license to unlock all premium features and get priority support.</p>
            </div>
        </div>

        <?php if ( $is_pro && $license_status === 'active' ) : ?>
            
            <!-- ACTIVE LICENSE SECTION -->
            <div class="license-status-section">
                
                <!-- Status Hero Card -->
                <div class="license-hero-card license-active">
                    <div class="hero-icon">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </div>
                    <div class="hero-content">
                        <h2>License Active</h2>
                        <p>Your Pro license is active and all premium features are unlocked.</p>
                        <div class="hero-badge">
                            <span class="dashicons dashicons-shield-alt"></span>
                            Pro License
                        </div>
                    </div>
                    <div class="hero-animation">
                        <div class="pulse-ring"></div>
                        <div class="pulse-ring pulse-ring-delay"></div>
                    </div>
                </div>

                <!-- License Details Card -->
                <div class="license-details-card">
                    <h3>
                        <span class="dashicons dashicons-admin-network"></span>
                        License Information
                    </h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <div class="detail-label">License Key</div>
                            <div class="detail-value">
                                <code class="license-key-display"><?php echo esc_html( $mask_license_key( $license_key ) ); ?></code>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="status-badge status-active">
                                    <span class="dashicons dashicons-yes"></span>
                                    Active
                                </span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Plan</div>
                            <div class="detail-value"><?php echo esc_html( ucfirst( $license_data['plan'] ?? 'Pro' ) ); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Sites Allowed</div>
                            <div class="detail-value">
                                <?php 
                                $max = $license_data['max_activations'] ?? 1;
                                echo esc_html( $max == -1 ? 'Unlimited' : $max );
                                ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Expires</div>
                            <div class="detail-value"><?php echo esc_html( $license_data['expires'] ?? 'Never' ); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Activated</div>
                            <div class="detail-value"><?php echo esc_html( $license_data['activated_at'] ?? 'Unknown' ); ?></div>
                        </div>
                    </div>
                </div>

                <!-- License Actions -->
                <div class="license-actions-card">
                    <div class="actions-content">
                        <h4>License Management</h4>
                        <p>Need to move your license to another site or having issues?</p>
                    </div>
                    <div class="actions-buttons">
                        <button type="button" class="action-btn btn-secondary" id="base47-deregister-license">
                            <span class="dashicons dashicons-dismiss"></span>
                            Deactivate License
                        </button>
                        <a href="<?php echo esc_url( $pro_url ); ?>/account" target="_blank" class="action-btn btn-primary">
                            <span class="dashicons dashicons-admin-users"></span>
                            Manage Account
                        </a>
                    </div>
                </div>
            </div>
            
        <?php else : ?>
            
            <!-- INACTIVE LICENSE SECTION -->
            <div class="license-activation-section">
                
                <!-- Activation Hero Card -->
                <div class="license-hero-card license-inactive">
                    <div class="hero-icon">
                        <span class="dashicons dashicons-lock"></span>
                    </div>
                    <div class="hero-content">
                        <h2>Activate Your License</h2>
                        <p>Enter your license key to unlock all Pro features and get priority support.</p>
                    </div>
                </div>

                <!-- Activation Form -->
                <div class="license-form-card">
                    <form id="base47-license-form">
                        <div class="form-header">
                            <h3>
                                <span class="dashicons dashicons-admin-network"></span>
                                License Activation
                            </h3>
                            <p>Enter the license key you received after purchase.</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="base47-license-key-input">License Key</label>
                            <div class="input-wrapper">
                                <input 
                                    type="text" 
                                    id="base47-license-key-input" 
                                    name="license_key" 
                                    class="license-input" 
                                    placeholder="B47-XXXX-XXXX-XXXX-XXXX"
                                    value="<?php echo esc_attr( $license_key ); ?>"
                                >
                                <span class="input-icon">
                                    <span class="dashicons dashicons-admin-network"></span>
                                </span>
                            </div>
                            <div class="input-help">
                                <span class="dashicons dashicons-info"></span>
                                Your license key is in your purchase confirmation email
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="activate-btn">
                                <span class="btn-icon">
                                    <span class="dashicons dashicons-yes"></span>
                                </span>
                                <span class="btn-text">Activate License</span>
                            </button>
                        </div>
                        
                        <div id="license-message" class="license-message"></div>
                    </form>
                </div>

                <!-- Purchase License Card -->
                <div class="purchase-license-card">
                    <div class="purchase-content">
                        <div class="purchase-icon">
                            <span class="dashicons dashicons-cart"></span>
                        </div>
                        <div class="purchase-info">
                            <h3>Need a License?</h3>
                            <p>Get instant access to all Pro features with a one-time purchase.</p>
                            <div class="purchase-price">
                                <span class="price-amount">$67</span>
                                <span class="price-period">one-time</span>
                            </div>
                        </div>
                        <div class="purchase-action">
                            <a href="<?php echo esc_url( $pro_url ); ?>" class="purchase-btn" target="_blank">
                                <span class="dashicons dashicons-external"></span>
                                Purchase License
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
        
        <!-- PRO FEATURES SHOWCASE -->
        <div class="pro-features-section">
            <div class="features-header">
                <h2>
                    <span class="dashicons dashicons-star-filled"></span>
                    Pro Features
                </h2>
                <p>Unlock powerful features that will transform your workflow and boost productivity.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-editor-code"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Monaco Editor</h3>
                        <p>VS Code experience with IntelliSense, multi-cursor editing, and advanced code folding.</p>
                        <div class="feature-badge">Premium</div>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-smartphone"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Responsive Preview</h3>
                        <p>Test your templates instantly on desktop, tablet, and mobile views with live switching.</p>
                        <div class="feature-badge">Premium</div>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-admin-page"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Unlimited Templates</h3>
                        <p>Install unlimited template packs from the marketplace and create custom designs.</p>
                        <div class="feature-badge">Premium</div>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-admin-customizer"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Advanced Widgets</h3>
                        <p>Access premium widgets and build custom components with the widget system.</p>
                        <div class="feature-badge">Premium</div>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-performance"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Smart Loader++</h3>
                        <p>Intelligent asset loading with dependency management and performance optimization.</p>
                        <div class="feature-badge">Premium</div>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-backup"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Auto-Backups</h3>
                        <p>Automatic backups before every save with one-click restore and version history.</p>
                        <div class="feature-badge">Premium</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TESTIMONIALS SECTION -->
        <div class="testimonials-section">
            <div class="testimonials-header">
                <h2>What Our Users Say</h2>
                <p>Join thousands of satisfied customers who trust Base47 HTML Editor.</p>
            </div>
            
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <p>"The Monaco editor is a game-changer. It's like having VS Code right in WordPress!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <span class="dashicons dashicons-admin-users"></span>
                        </div>
                        <div class="author-info">
                            <div class="author-name">Sarah Johnson</div>
                            <div class="author-role">Web Developer</div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <p>"Best investment for my agency. The template system saves hours of work every week."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <span class="dashicons dashicons-businessman"></span>
                        </div>
                        <div class="author-info">
                            <div class="author-name">Mike Chen</div>
                            <div class="author-role">Agency Owner</div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <p>"Professional quality templates and excellent support. Highly recommended!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <span class="dashicons dashicons-admin-users"></span>
                        </div>
                        <div class="author-info">
                            <div class="author-name">Lisa Rodriguez</div>
                            <div class="author-role">Freelancer</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SUPPORT SECTION -->
        <div class="support-section">
            <div class="support-content">
                <div class="support-info">
                    <span class="dashicons dashicons-sos"></span>
                    <div>
                        <strong>Need Help?</strong>
                        <p>Our support team is here to help you get the most out of Base47 HTML Editor.</p>
                    </div>
                </div>
                <div class="support-actions">
                    <a href="https://47-studio.com/base47/docs" target="_blank" class="support-btn">
                        <span class="dashicons dashicons-book"></span>
                        Documentation
                    </a>
                    <a href="https://47-studio.com/base47/support" target="_blank" class="support-btn">
                        <span class="dashicons dashicons-email"></span>
                        Get Support
                    </a>
                </div>
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
            var $btn = $('.activate-btn');
            var $btnText = $btn.find('.btn-text');
            var $btnIcon = $btn.find('.btn-icon .dashicons');
            
            if (!licenseKey) {
                showMessage('error', 'Please enter a license key');
                return;
            }
            
            // Update button state
            $btn.prop('disabled', true).addClass('loading');
            $btnText.text('Activating...');
            $btnIcon.removeClass('dashicons-yes').addClass('dashicons-update');
            
            showMessage('info', 'Validating license key...');
            
            $.post(ajaxurl, {
                action: 'base47_activate_license',
                license_key: licenseKey,
                nonce: '<?php echo wp_create_nonce('base47_he'); ?>'
            }, function(response) {
                if (response.success) {
                    showMessage('success', '✓ License activated successfully! Reloading page...');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('error', '✗ ' + response.data.message);
                    resetButton();
                }
            }).fail(function() {
                showMessage('error', '✗ Network error. Please try again.');
                resetButton();
            });
            
            function resetButton() {
                $btn.prop('disabled', false).removeClass('loading');
                $btnText.text('Activate License');
                $btnIcon.removeClass('dashicons-update').addClass('dashicons-yes');
            }
            
            function showMessage(type, text) {
                $message.removeClass('success error info').addClass(type).html(text).show();
            }
        });
        
        // Deregister License
        $('#base47-deregister-license').on('click', function() {
            if (!confirm('Are you sure you want to deactivate this license key? All Pro features will be disabled.')) {
                return;
            }
            
            var $btn = $(this);
            var originalText = $btn.html();
            
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Deactivating...');
            
            $.post(ajaxurl, {
                action: 'base47_deactivate_license',
                nonce: '<?php echo wp_create_nonce('base47_he'); ?>'
            }, function(response) {
                if (response.success) {
                    alert('License deactivated successfully!');
                    location.reload();
                } else {
                    alert('Failed to deactivate license: ' + response.data.message);
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
    </script>
    <?php
}
