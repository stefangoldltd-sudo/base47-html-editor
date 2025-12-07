<?php
/**
 * Upgrade Page - Marketing & Pricing
 * 
 * Beautiful upgrade page with pricing plans and feature comparison
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.2.6
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_upgrade_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    $pro_url = base47_he_get_pro_url();
    ?>
    <div class="wrap base47-upgrade-page">
        
        <!-- Hero Section -->
        <div class="base47-upgrade-hero">
            <div class="hero-content">
                <h1>🚀 Unlock the Full Power of Base47 HTML Editor</h1>
                <p class="hero-subtitle">Get Monaco Editor, unlimited templates, auto-backups, and premium features</p>
                <div class="hero-actions">
                    <a href="#pricing" class="btn-hero-primary">View Pricing Plans</a>
                    <a href="<?php echo admin_url('admin.php?page=base47-he-license'); ?>" class="btn-hero-secondary">
                        Already have a license? Activate it here
                    </a>
                </div>
            </div>
            <div class="hero-illustration">
                <span class="dashicons dashicons-editor-code"></span>
            </div>
        </div>
        
        <!-- Feature Comparison -->
        <div class="base47-comparison-section">
            <h2>Free vs Pro - What's Included?</h2>
            <p class="section-subtitle">See exactly what you get with each version</p>
            
            <table class="base47-comparison-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Free</th>
                        <th>Pro</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Classic Editor</strong></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                    </tr>
                    <tr>
                        <td><strong>Monaco Editor (VS Code)</strong></td>
                        <td><span class="dashicons dashicons-no feature-no"></span></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                    </tr>
                    <tr>
                        <td><strong>Template Packs</strong></td>
                        <td>1 Pack</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td><strong>Special Widgets</strong></td>
                        <td>1 Widget</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td><strong>Smart Loader++</strong></td>
                        <td><span class="dashicons dashicons-no feature-no"></span></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                    </tr>
                    <tr>
                        <td><strong>Manifest Loader</strong></td>
                        <td><span class="dashicons dashicons-no feature-no"></span></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                    </tr>
                    <tr>
                        <td><strong>Auto-Backups & Restore</strong></td>
                        <td><span class="dashicons dashicons-no feature-no"></span></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                    </tr>
                    <tr>
                        <td><strong>Responsive Preview</strong></td>
                        <td><span class="dashicons dashicons-no feature-no"></span></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                    </tr>
                    <tr>
                        <td><strong>Advanced Logging</strong></td>
                        <td><span class="dashicons dashicons-no feature-no"></span></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                    </tr>
                    <tr>
                        <td><strong>Export/Import Settings</strong></td>
                        <td><span class="dashicons dashicons-no feature-no"></span></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                    </tr>
                    <tr>
                        <td><strong>Priority Support</strong></td>
                        <td><span class="dashicons dashicons-no feature-no"></span></td>
                        <td><span class="dashicons dashicons-yes feature-yes"></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pricing Plans -->
        <div class="base47-pricing-section" id="pricing">
            <h2>Choose Your Plan</h2>
            <p class="section-subtitle">All plans include 1 year of updates and support</p>
            
            <div class="pricing-grid">
                
                <!-- Single Site -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Single Site</h3>
                        <div class="pricing-price">
                            <span class="currency">$</span>
                            <span class="amount">49</span>
                            <span class="period">/year</span>
                        </div>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><span class="dashicons dashicons-yes"></span> 1 Website</li>
                            <li><span class="dashicons dashicons-yes"></span> All Pro Features</li>
                            <li><span class="dashicons dashicons-yes"></span> 1 Year Updates</li>
                            <li><span class="dashicons dashicons-yes"></span> 1 Year Support</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="<?php echo esc_url($pro_url); ?>" class="btn-pricing" target="_blank">Buy Now</a>
                    </div>
                </div>
                
                <!-- 5-Site -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>5-Site</h3>
                        <div class="pricing-price">
                            <span class="currency">$</span>
                            <span class="amount">99</span>
                            <span class="period">/year</span>
                        </div>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><span class="dashicons dashicons-yes"></span> 5 Websites</li>
                            <li><span class="dashicons dashicons-yes"></span> All Pro Features</li>
                            <li><span class="dashicons dashicons-yes"></span> 1 Year Updates</li>
                            <li><span class="dashicons dashicons-yes"></span> 1 Year Support</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="<?php echo esc_url($pro_url); ?>" class="btn-pricing" target="_blank">Buy Now</a>
                    </div>
                </div>
                
                <!-- Unlimited (Most Popular) -->
                <div class="pricing-card pricing-popular">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="pricing-header">
                        <h3>Unlimited</h3>
                        <div class="pricing-price">
                            <span class="currency">$</span>
                            <span class="amount">199</span>
                            <span class="period">/year</span>
                        </div>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><span class="dashicons dashicons-yes"></span> Unlimited Websites</li>
                            <li><span class="dashicons dashicons-yes"></span> All Pro Features</li>
                            <li><span class="dashicons dashicons-yes"></span> 1 Year Updates</li>
                            <li><span class="dashicons dashicons-yes"></span> Priority Support</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="<?php echo esc_url($pro_url); ?>" class="btn-pricing btn-pricing-popular" target="_blank">Buy Now</a>
                    </div>
                </div>
                
                <!-- Developer -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Developer</h3>
                        <div class="pricing-price">
                            <span class="currency">$</span>
                            <span class="amount">299</span>
                            <span class="period">/lifetime</span>
                        </div>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><span class="dashicons dashicons-yes"></span> Unlimited Websites</li>
                            <li><span class="dashicons dashicons-yes"></span> All Pro Features</li>
                            <li><span class="dashicons dashicons-yes"></span> Lifetime Updates</li>
                            <li><span class="dashicons dashicons-yes"></span> Resale Rights</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="<?php echo esc_url($pro_url); ?>" class="btn-pricing" target="_blank">Buy Now</a>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="base47-faq-section">
            <h2>Frequently Asked Questions</h2>
            
            <div class="faq-grid">
                <div class="faq-item">
                    <h4>What happens after I purchase?</h4>
                    <p>You'll receive a license key via email immediately. Simply activate it on your WordPress site to unlock all Pro features.</p>
                </div>
                
                <div class="faq-item">
                    <h4>Can I upgrade my plan later?</h4>
                    <p>Yes! You can upgrade from Single to 5-Site or Unlimited at any time. Just contact support for upgrade pricing.</p>
                </div>
                
                <div class="faq-item">
                    <h4>Do you offer refunds?</h4>
                    <p>Yes, we offer a 30-day money-back guarantee. If you're not satisfied, we'll refund your purchase.</p>
                </div>
                
                <div class="faq-item">
                    <h4>What's included in support?</h4>
                    <p>All plans include email support. Unlimited and Developer plans get priority support with faster response times.</p>
                </div>
            </div>
        </div>
        
        <!-- Final CTA -->
        <div class="base47-final-cta">
            <h2>Ready to Upgrade?</h2>
            <p>Join thousands of developers using Base47 HTML Editor Pro</p>
            <div class="cta-actions">
                <a href="<?php echo esc_url($pro_url); ?>" class="btn-cta-primary" target="_blank">Get Pro Now</a>
                <a href="<?php echo admin_url('admin.php?page=base47-he-license'); ?>" class="btn-cta-secondary">
                    Already purchased? Activate your license
                </a>
            </div>
        </div>
        
    </div>
    <?php
}
