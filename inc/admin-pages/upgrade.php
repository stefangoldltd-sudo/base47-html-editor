<?php
/**
 * Upgrade Page - Soft UI v3.0
 * 
 * Modern, conversion-optimized upgrade page with enhanced UX
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.3.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_upgrade_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    $pro_url = base47_he_get_pro_url();
    ?>
    <div class="wrap base47-upgrade-soft-ui">
        
        <!-- HERO SECTION -->
        <div class="upgrade-hero-section">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="dashicons dashicons-star-filled"></span>
                    Upgrade to Pro
                </div>
                <h1>Unlock the Full Power of Base47 HTML Editor</h1>
                <p class="hero-subtitle">Transform your workflow with Monaco Editor, unlimited templates, auto-backups, and premium features that save hours every week.</p>
                
                <div class="hero-features">
                    <div class="hero-feature">
                        <span class="dashicons dashicons-editor-code"></span>
                        <span>Monaco Editor</span>
                    </div>
                    <div class="hero-feature">
                        <span class="dashicons dashicons-admin-page"></span>
                        <span>Unlimited Templates</span>
                    </div>
                    <div class="hero-feature">
                        <span class="dashicons dashicons-backup"></span>
                        <span>Auto-Backups</span>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <a href="#pricing" class="hero-btn-primary">
                        <span class="dashicons dashicons-cart"></span>
                        View Pricing Plans
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=base47-he-license'); ?>" class="hero-btn-secondary">
                        <span class="dashicons dashicons-admin-network"></span>
                        Already have a license?
                    </a>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="hero-illustration">
                    <div class="illustration-bg"></div>
                    <span class="dashicons dashicons-editor-code"></span>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">5,000+</div>
                        <div class="stat-label">Happy Users</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">4.9★</div>
                        <div class="stat-label">Rating</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TRUST BADGES -->
        <div class="trust-badges-section">
            <div class="trust-badge">
                <span class="dashicons dashicons-users"></span>
                <span>5,000+ Happy Users</span>
            </div>
            <div class="trust-badge">
                <span class="dashicons dashicons-update"></span>
                <span>1 Year of Free Updates</span>
            </div>
            <div class="trust-badge">
                <span class="dashicons dashicons-sos"></span>
                <span>Priority Support Included</span>
            </div>
        </div>

        <!-- PRICING SECTION -->
        <div class="pricing-section" id="pricing">
            <div class="pricing-header">
                <h2>Choose Your Perfect Plan</h2>
                <p>All plans include 1 year of updates and support. Upgrade or downgrade anytime.</p>
            </div>
            
            <div class="pricing-toggle">
                <span class="toggle-label">Monthly</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="pricing-toggle" checked>
                    <span class="toggle-slider"></span>
                </label>
                <span class="toggle-label">Yearly <span class="save-badge">Save 20%</span></span>
            </div>
            
            <div class="pricing-grid">
                
                <!-- Single Site -->
                <div class="pricing-card">
                    <div class="pricing-header-card">
                        <h3>Single Site</h3>
                        <p>Perfect for individual developers</p>
                    </div>
                    <div class="pricing-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount" data-monthly="7" data-yearly="67">67</span>
                        <span class="price-period">/year</span>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><span class="dashicons dashicons-yes"></span> 1 Website License</li>
                            <li><span class="dashicons dashicons-yes"></span> All Pro Features</li>
                            <li><span class="dashicons dashicons-yes"></span> Monaco Editor</li>
                            <li><span class="dashicons dashicons-yes"></span> Unlimited Templates</li>
                            <li><span class="dashicons dashicons-yes"></span> Auto-Backups</li>
                            <li><span class="dashicons dashicons-yes"></span> Email Support</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="<?php echo esc_url($pro_url); ?>" class="pricing-btn" target="_blank">
                            Get Started
                        </a>
                    </div>
                </div>
                
                <!-- Professional (Most Popular) -->
                <div class="pricing-card pricing-popular">
                    <div class="popular-badge">
                        <span class="dashicons dashicons-star-filled"></span>
                        Most Popular
                    </div>
                    <div class="pricing-header-card">
                        <h3>Professional</h3>
                        <p>Best for agencies and freelancers</p>
                    </div>
                    <div class="pricing-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount" data-monthly="15" data-yearly="147">147</span>
                        <span class="price-period">/year</span>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><span class="dashicons dashicons-yes"></span> 5 Website Licenses</li>
                            <li><span class="dashicons dashicons-yes"></span> All Pro Features</li>
                            <li><span class="dashicons dashicons-yes"></span> Monaco Editor</li>
                            <li><span class="dashicons dashicons-yes"></span> Unlimited Templates</li>
                            <li><span class="dashicons dashicons-yes"></span> Auto-Backups</li>
                            <li><span class="dashicons dashicons-yes"></span> Priority Support</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="<?php echo esc_url($pro_url); ?>" class="pricing-btn pricing-btn-popular" target="_blank">
                            Get Started
                        </a>
                    </div>
                </div>
                
                <!-- Agency -->
                <div class="pricing-card">
                    <div class="pricing-header-card">
                        <h3>Agency</h3>
                        <p>For teams and large projects</p>
                    </div>
                    <div class="pricing-price">
                        <span class="price-currency">$</span>
                        <span class="price-amount" data-monthly="25" data-yearly="247">247</span>
                        <span class="price-period">/year</span>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <li><span class="dashicons dashicons-yes"></span> Unlimited Websites</li>
                            <li><span class="dashicons dashicons-yes"></span> All Pro Features</li>
                            <li><span class="dashicons dashicons-yes"></span> Monaco Editor</li>
                            <li><span class="dashicons dashicons-yes"></span> Unlimited Templates</li>
                            <li><span class="dashicons dashicons-yes"></span> Auto-Backups</li>
                            <li><span class="dashicons dashicons-yes"></span> Priority Support</li>
                            <li><span class="dashicons dashicons-yes"></span> White Label Option</li>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="<?php echo esc_url($pro_url); ?>" class="pricing-btn" target="_blank">
                            Get Started
                        </a>
                    </div>
                </div>
                
            </div>
            
            <div class="pricing-guarantee">
                <span class="dashicons dashicons-star-filled"></span>
                <strong>Premium Support Included</strong> - Get priority support and faster response times with your Pro license.
            </div>
        </div>

        <!-- FEATURES SHOWCASE -->
        <div class="features-showcase-section">
            <div class="features-header">
                <h2>Why Upgrade to Pro?</h2>
                <p>Unlock powerful features that will transform your workflow and boost productivity.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-showcase-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-editor-code"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Monaco Editor</h3>
                        <p>Get the full VS Code experience with IntelliSense, multi-cursor editing, code folding, and syntax highlighting.</p>
                        <div class="feature-highlight">Most Requested Feature</div>
                    </div>
                </div>
                
                <div class="feature-showcase-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-smartphone"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Responsive Preview</h3>
                        <p>Test your templates instantly on desktop, tablet, and mobile views with live device switching.</p>
                        <div class="feature-highlight">Save Hours of Testing</div>
                    </div>
                </div>
                
                <div class="feature-showcase-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-backup"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Auto-Backups</h3>
                        <p>Never lose your work again. Automatic backups before every save with one-click restore functionality.</p>
                        <div class="feature-highlight">Peace of Mind</div>
                    </div>
                </div>
                
                <div class="feature-showcase-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-admin-page"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Unlimited Templates</h3>
                        <p>Access unlimited template packs from the marketplace and create custom designs without restrictions.</p>
                        <div class="feature-highlight">Endless Possibilities</div>
                    </div>
                </div>
                
                <div class="feature-showcase-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-performance"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Smart Loader++</h3>
                        <p>Intelligent asset loading with dependency management and performance optimization for faster sites.</p>
                        <div class="feature-highlight">Better Performance</div>
                    </div>
                </div>
                
                <div class="feature-showcase-card">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-sos"></span>
                    </div>
                    <div class="feature-content">
                        <h3>Priority Support</h3>
                        <p>Get faster response times and direct access to our development team for technical questions.</p>
                        <div class="feature-highlight">Expert Help</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COMPARISON TABLE -->
        <div class="comparison-section">
            <div class="comparison-header">
                <h2>Free vs Pro Comparison</h2>
                <p>See exactly what you get with each version</p>
            </div>
            
            <div class="comparison-table-wrapper">
                <table class="comparison-table">
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
                            <td><span class="feature-check feature-yes"><span class="dashicons dashicons-yes"></span></span></td>
                            <td><span class="feature-check feature-yes"><span class="dashicons dashicons-yes"></span></span></td>
                        </tr>
                        <tr>
                            <td><strong>Monaco Editor (VS Code)</strong></td>
                            <td><span class="feature-check feature-no"><span class="dashicons dashicons-no"></span></span></td>
                            <td><span class="feature-check feature-yes"><span class="dashicons dashicons-yes"></span></span></td>
                        </tr>
                        <tr>
                            <td><strong>Template Packs</strong></td>
                            <td><span class="feature-limit">1 Pack</span></td>
                            <td><span class="feature-unlimited">Unlimited</span></td>
                        </tr>
                        <tr>
                            <td><strong>Responsive Preview</strong></td>
                            <td><span class="feature-check feature-no"><span class="dashicons dashicons-no"></span></span></td>
                            <td><span class="feature-check feature-yes"><span class="dashicons dashicons-yes"></span></span></td>
                        </tr>
                        <tr>
                            <td><strong>Auto-Backups & Restore</strong></td>
                            <td><span class="feature-check feature-no"><span class="dashicons dashicons-no"></span></span></td>
                            <td><span class="feature-check feature-yes"><span class="dashicons dashicons-yes"></span></span></td>
                        </tr>
                        <tr>
                            <td><strong>Smart Loader++</strong></td>
                            <td><span class="feature-check feature-no"><span class="dashicons dashicons-no"></span></span></td>
                            <td><span class="feature-check feature-yes"><span class="dashicons dashicons-yes"></span></span></td>
                        </tr>
                        <tr>
                            <td><strong>Priority Support</strong></td>
                            <td><span class="feature-check feature-no"><span class="dashicons dashicons-no"></span></span></td>
                            <td><span class="feature-check feature-yes"><span class="dashicons dashicons-yes"></span></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TESTIMONIALS -->
        <div class="testimonials-section">
            <div class="testimonials-header">
                <h2>What Our Pro Users Say</h2>
                <p>Join thousands of satisfied customers who upgraded to Pro</p>
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
                        <p>"The Monaco editor alone is worth the upgrade. It's like having VS Code right in WordPress!"</p>
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
                        <p>"Best investment for my agency. The unlimited templates and auto-backups save hours every week."</p>
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
                        <p>"The responsive preview feature is a game-changer. No more switching between devices to test!"</p>
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

        <!-- FAQ SECTION -->
        <div class="faq-section">
            <div class="faq-header">
                <h2>Frequently Asked Questions</h2>
                <p>Everything you need to know about upgrading to Pro</p>
            </div>
            
            <div class="faq-accordion">
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>What happens after I purchase?</h4>
                        <span class="faq-toggle"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
                    </div>
                    <div class="faq-answer">
                        <p>You'll receive a license key via email immediately after purchase. Simply go to the License page in your WordPress admin and activate it to unlock all Pro features instantly.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Can I upgrade my plan later?</h4>
                        <span class="faq-toggle"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
                    </div>
                    <div class="faq-answer">
                        <p>Absolutely! You can upgrade from Single to Professional or Agency at any time. Just contact our support team and we'll help you with the upgrade process and pricing.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Do you offer refunds?</h4>
                        <span class="faq-toggle"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
                    </div>
                    <div class="faq-answer">
                        <p>We provide comprehensive support through our support center, documentation, and email support for Pro users.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>What's included in support?</h4>
                        <span class="faq-toggle"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
                    </div>
                    <div class="faq-answer">
                        <p>All Pro plans include email support with our expert team. Professional and Agency plans get priority support with faster response times and direct access to our developers.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>How long do I get updates?</h4>
                        <span class="faq-toggle"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
                    </div>
                    <div class="faq-answer">
                        <p>All plans include 1 year of free updates. After that, you can renew at a discounted rate to continue receiving updates and support, or keep using your current version forever.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h4>Can I use it on multiple sites?</h4>
                        <span class="faq-toggle"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
                    </div>
                    <div class="faq-answer">
                        <p>It depends on your plan. Single Site allows 1 website, Professional allows 5 websites, and Agency allows unlimited websites. You can always upgrade if you need more sites.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FINAL CTA -->
        <div class="final-cta-section">
            <div class="cta-content">
                <h2>Ready to Transform Your Workflow?</h2>
                <p>Join over 5,000 developers who trust Base47 HTML Editor Pro for their projects</p>
                
                <div class="cta-features">
                    <div class="cta-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <span>Premium Support Included</span>
                    </div>
                    <div class="cta-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <span>Instant Access After Purchase</span>
                    </div>
                    <div class="cta-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <span>1 Year of Free Updates</span>
                    </div>
                </div>
                
                <div class="cta-actions">
                    <a href="<?php echo esc_url($pro_url); ?>" class="cta-btn-primary" target="_blank">
                        <span class="dashicons dashicons-cart"></span>
                        Get Pro Now - Starting at $67/year
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=base47-he-license'); ?>" class="cta-btn-secondary">
                        <span class="dashicons dashicons-admin-network"></span>
                        Already purchased? Activate your license
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script>
    jQuery(document).ready(function($) {
        // Pricing toggle
        $('#pricing-toggle').on('change', function() {
            var isYearly = $(this).is(':checked');
            $('.price-amount').each(function() {
                var $this = $(this);
                var monthlyPrice = $this.data('monthly');
                var yearlyPrice = $this.data('yearly');
                $this.text(isYearly ? yearlyPrice : monthlyPrice);
            });
            $('.price-period').text(isYearly ? '/year' : '/month');
        });
        
        // FAQ accordion
        $('.faq-question').on('click', function() {
            var $item = $(this).closest('.faq-item');
            var $answer = $item.find('.faq-answer');
            var $toggle = $(this).find('.faq-toggle .dashicons');
            
            // Close other items
            $('.faq-item').not($item).removeClass('active').find('.faq-answer').slideUp(300);
            $('.faq-toggle .dashicons').not($toggle).removeClass('rotated');
            
            // Toggle current item
            $item.toggleClass('active');
            $answer.slideToggle(300);
            $toggle.toggleClass('rotated');
        });
        
        // Smooth scroll to pricing
        $('a[href="#pricing"]').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('#pricing').offset().top - 50
            }, 800);
        });
    });
    </script>
    <?php
}
