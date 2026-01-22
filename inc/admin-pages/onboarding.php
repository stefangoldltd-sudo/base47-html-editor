<?php
/**
 * Onboarding Wizard
 * 
 * Welcome new users and guide them through key features
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.8
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Onboarding page
 */
function base47_he_onboarding_page() {
    // Check if user has completed onboarding
    $completed = get_user_meta( get_current_user_id(), 'base47_he_onboarding_completed', true );
    $current_step = isset( $_GET['step'] ) ? intval( $_GET['step'] ) : 1;
    
    ?>
    <div class="wrap base47-onboarding-wrap">
        <div class="base47-onboarding-container">
            
            <!-- Header -->
            <div class="onboarding-header">
                <div class="logo-section">
                    <img src="<?php echo BASE47_HE_URL; ?>admin-assets/images/base47-logo.png" alt="Base47" class="onboarding-logo">
                    <h1>Welcome to Base47 HTML Editor!</h1>
                    <p>Let's get you started with the most powerful HTML editing experience for WordPress</p>
                </div>
                
                <!-- Progress Bar -->
                <div class="progress-bar">
                    <div class="progress-steps">
                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                            <div class="step <?php echo $i <= $current_step ? 'active' : ''; ?> <?php echo $i < $current_step ? 'completed' : ''; ?>">
                                <span class="step-number"><?php echo $i; ?></span>
                                <span class="step-label">
                                    <?php
                                    $labels = array( 1 => 'Welcome', 2 => 'Editor', 3 => 'Templates', 4 => 'Features', 5 => 'Complete' );
                                    echo $labels[$i];
                                    ?>
                                </span>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="progress-line">
                        <div class="progress-fill" style="width: <?php echo ( ( $current_step - 1 ) / 4 ) * 100; ?>%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Step Content -->
            <div class="onboarding-content">
                <?php
                switch ( $current_step ) {
                    case 1:
                        base47_he_onboarding_step_welcome();
                        break;
                    case 2:
                        base47_he_onboarding_step_editor();
                        break;
                    case 3:
                        base47_he_onboarding_step_templates();
                        break;
                    case 4:
                        base47_he_onboarding_step_features();
                        break;
                    case 5:
                        base47_he_onboarding_step_complete();
                        break;
                    default:
                        base47_he_onboarding_step_welcome();
                }
                ?>
            </div>
            
            <!-- Navigation -->
            <div class="onboarding-navigation">
                <?php if ( $current_step > 1 ) : ?>
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-onboarding&step=' . ( $current_step - 1 ) ); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                        Previous
                    </a>
                <?php endif; ?>
                
                <div class="nav-spacer"></div>
                
                <?php if ( $current_step < 5 ) : ?>
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-onboarding&step=' . ( $current_step + 1 ) ); ?>" class="button button-primary">
                        Next
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo admin_url( 'admin.php?page=base47-he-dashboard' ); ?>" class="button button-link skip-onboarding">
                    Skip Tour
                </a>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Step 1: Welcome
 */
function base47_he_onboarding_step_welcome() {
    ?>
    <div class="onboarding-step step-welcome">
        <div class="step-content">
            <div class="welcome-hero">
                <div class="hero-icon">
                    <span class="dashicons dashicons-welcome-learn-more"></span>
                </div>
                <h2>Welcome to Base47 HTML Editor!</h2>
                <p class="hero-description">
                    Let's get you started in just 5 simple steps. You'll learn how to install templates, 
                    edit them, and use them on your website.
                </p>
            </div>
            
            <div class="getting-started-video">
                <div class="video-container">
                    <div class="video-placeholder">
                        <div class="play-button">
                            <span class="dashicons dashicons-controls-play"></span>
                        </div>
                        <div class="video-info">
                            <h3>Quick Start Guide (2 minutes)</h3>
                            <p>Watch this quick overview to see how Base47 HTML Editor works</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="quick-stats">
                <div class="stat-item">
                    <div class="stat-number">47</div>
                    <div class="stat-label">Pro Templates</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">5,000+</div>
                    <div class="stat-label">Happy Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">9</div>
                    <div class="stat-label">Categories</div>
                </div>
            </div>
            
            <div class="version-info">
                <div class="version-badge">
                    <span class="version-label">Version</span>
                    <span class="version-number"><?php echo BASE47_HE_VERSION; ?></span>
                </div>
                
                <?php if ( base47_he_is_pro_active() ) : ?>
                    <div class="pro-badge">
                        <span class="dashicons dashicons-star-filled"></span>
                        Pro Active
                    </div>
                <?php else : ?>
                    <div class="free-badge">
                        <span class="dashicons dashicons-heart"></span>
                        Free Version
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Step 2: Install Template
 */
function base47_he_onboarding_step_editor() {
    ?>
    <div class="onboarding-step step-editor">
        <div class="step-content">
            <div class="step-header">
                <h2>Step 1: Install a Template</h2>
                <p>Let's start by installing your first professional template</p>
            </div>
            
            <div class="step-instructions">
                <div class="instruction-card">
                    <div class="instruction-number">1</div>
                    <div class="instruction-content">
                        <h3>Go to Marketplace</h3>
                        <p>Click on "Marketplace" in the Base47 HTML menu to browse templates</p>
                        <a href="<?php echo admin_url( 'admin.php?page=base47-he-marketplace' ); ?>" class="instruction-button" target="_blank">
                            <span class="dashicons dashicons-external"></span>
                            Open Marketplace
                        </a>
                    </div>
                </div>
                
                <div class="instruction-card">
                    <div class="instruction-number">2</div>
                    <div class="instruction-content">
                        <h3>Choose a Template</h3>
                        <p>Browse through 47 professional templates across 9 categories</p>
                        <div class="template-categories-mini">
                            <span class="category-tag">Agency</span>
                            <span class="category-tag">E-commerce</span>
                            <span class="category-tag">Restaurant</span>
                            <span class="category-tag">+6 more</span>
                        </div>
                    </div>
                </div>
                
                <div class="instruction-card">
                    <div class="instruction-number">3</div>
                    <div class="instruction-content">
                        <h3>Install Template</h3>
                        <p>Click "Install" on any template you like - it will be added to your website instantly</p>
                    </div>
                </div>
            </div>
            
            <div class="step-tip">
                <div class="tip-icon">
                    <span class="dashicons dashicons-lightbulb"></span>
                </div>
                <div class="tip-content">
                    <h4>Pro Tip</h4>
                    <p>Start with an Agency or App template - they're versatile and work great for most websites!</p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Step 3: Activate Template
 */
function base47_he_onboarding_step_templates() {
    ?>
    <div class="onboarding-step step-templates">
        <div class="step-content">
            <div class="step-header">
                <h2>Step 2: Activate Your Template</h2>
                <p>Now let's activate the template you installed so you can use it</p>
            </div>
            
            <div class="step-instructions">
                <div class="instruction-card">
                    <div class="instruction-number">1</div>
                    <div class="instruction-content">
                        <h3>Go to Theme Manager</h3>
                        <p>Click on "Theme Manager" in the Base47 HTML menu</p>
                        <a href="<?php echo admin_url( 'admin.php?page=base47-he-theme-manager' ); ?>" class="instruction-button" target="_blank">
                            <span class="dashicons dashicons-external"></span>
                            Open Theme Manager
                        </a>
                    </div>
                </div>
                
                <div class="instruction-card">
                    <div class="instruction-number">2</div>
                    <div class="instruction-content">
                        <h3>Find Your Template</h3>
                        <p>You'll see your installed template in the list. It might be inactive (gray toggle)</p>
                    </div>
                </div>
                
                <div class="instruction-card">
                    <div class="instruction-number">3</div>
                    <div class="instruction-content">
                        <h3>Activate Template</h3>
                        <p>Click the toggle switch next to your template to activate it (it will turn green)</p>
                        <div class="toggle-example">
                            <span class="toggle-off">OFF</span>
                            <span class="arrow">→</span>
                            <span class="toggle-on">ON</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="step-tip">
                <div class="tip-icon">
                    <span class="dashicons dashicons-info"></span>
                </div>
                <div class="tip-content">
                    <h4>Important</h4>
                    <p>Only activated templates can be used on your website. Inactive templates won't generate shortcodes.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Step 4: Edit and Use Template
 */
function base47_he_onboarding_step_features() {
    ?>
    <div class="onboarding-step step-features">
        <div class="step-content">
            <div class="step-header">
                <h2>Step 3: Edit & Use Your Template</h2>
                <p>Now let's customize your template and add it to a page</p>
            </div>
            
            <div class="step-instructions">
                <div class="instruction-card">
                    <div class="instruction-number">1</div>
                    <div class="instruction-content">
                        <h3>Edit in Live Editor</h3>
                        <p>Go to "Live Editor" and select your template to customize it</p>
                        <a href="<?php echo admin_url( 'admin.php?page=base47-he-editor' ); ?>" class="instruction-button" target="_blank">
                            <span class="dashicons dashicons-external"></span>
                            Open Live Editor
                        </a>
                    </div>
                </div>
                
                <div class="instruction-card">
                    <div class="instruction-number">2</div>
                    <div class="instruction-content">
                        <h3>Copy the Shortcode</h3>
                        <p>After editing, copy the shortcode from the "Shortcodes" page</p>
                        <a href="<?php echo admin_url( 'admin.php?page=base47-he-templates' ); ?>" class="instruction-button" target="_blank">
                            <span class="dashicons dashicons-external"></span>
                            View Shortcodes
                        </a>
                        <div class="shortcode-example">
                            <code>[base47_template name="your-template"]</code>
                        </div>
                    </div>
                </div>
                
                <div class="instruction-card">
                    <div class="instruction-number">3</div>
                    <div class="instruction-content">
                        <h3>Add to Page</h3>
                        <p>Create a new page/post and paste the shortcode where you want the template to appear</p>
                        <a href="<?php echo admin_url( 'post-new.php?post_type=page' ); ?>" class="instruction-button" target="_blank">
                            <span class="dashicons dashicons-external"></span>
                            Create New Page
                        </a>
                    </div>
                </div>
                
                <div class="instruction-card">
                    <div class="instruction-number">4</div>
                    <div class="instruction-content">
                        <h3>Save & View</h3>
                        <p>Save your page and view it on the frontend - your template is now live!</p>
                    </div>
                </div>
            </div>
            
            <div class="step-tip">
                <div class="tip-icon">
                    <span class="dashicons dashicons-star-filled"></span>
                </div>
                <div class="tip-content">
                    <h4>That's It!</h4>
                    <p>You can use the same shortcode on multiple pages, and any changes you make in the Live Editor will update everywhere automatically.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Step 5: Complete
 */
function base47_he_onboarding_step_complete() {
    // Mark onboarding as completed
    update_user_meta( get_current_user_id(), 'base47_he_onboarding_completed', true );
    
    ?>
    <div class="onboarding-step step-complete">
        <div class="step-content">
            <div class="completion-hero">
                <div class="success-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <h2>You're All Set!</h2>
                <p class="hero-description">
                    Welcome to the Base47 HTML Editor family. You're ready to create amazing HTML experiences!
                </p>
            </div>
            
            <div class="next-steps">
                <h3>What's Next?</h3>
                <div class="steps-grid">
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-editor' ); ?>" class="next-step-card">
                        <div class="step-icon">
                            <span class="dashicons dashicons-edit"></span>
                        </div>
                        <h4>Start Editing</h4>
                        <p>Jump into the HTML editor and create your first template</p>
                    </a>
                    
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-marketplace' ); ?>" class="next-step-card">
                        <div class="step-icon">
                            <span class="dashicons dashicons-download"></span>
                        </div>
                        <h4>Browse Templates</h4>
                        <p>Explore our collection of professional templates</p>
                    </a>
                    
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-dashboard' ); ?>" class="next-step-card">
                        <div class="step-icon">
                            <span class="dashicons dashicons-dashboard"></span>
                        </div>
                        <h4>View Dashboard</h4>
                        <p>See your stats and manage your HTML templates</p>
                    </a>
                </div>
            </div>
            
            <div class="completion-actions">
                <a href="<?php echo admin_url( 'admin.php?page=base47-he-dashboard' ); ?>" class="button button-primary button-hero">
                    Go to Dashboard
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </a>
                
                <div class="social-follow">
                    <p>Stay connected for updates and tips:</p>
                    <div class="social-links">
                        <a href="https://twitter.com/47Studio" target="_blank" class="social-link">
                            <span class="dashicons dashicons-twitter"></span>
                        </a>
                        <a href="https://www.youtube.com/channel/UC47Studio" target="_blank" class="social-link">
                            <span class="dashicons dashicons-video-alt3"></span>
                        </a>
                        <a href="https://47-studio.com/blog/" target="_blank" class="social-link">
                            <span class="dashicons dashicons-rss"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Check if user should see onboarding
 */
function base47_he_should_show_onboarding() {
    // Don't show if user has completed onboarding
    if ( get_user_meta( get_current_user_id(), 'base47_he_onboarding_completed', true ) ) {
        return false;
    }
    
    // Don't show if user has dismissed onboarding
    if ( get_user_meta( get_current_user_id(), 'base47_he_onboarding_dismissed', true ) ) {
        return false;
    }
    
    // Show for new users (plugin activated less than 7 days ago)
    $activation_date = get_option( 'base47_he_activation_date', '' );
    if ( empty( $activation_date ) ) {
        return true;
    }
    
    $days_since_activation = ( time() - strtotime( $activation_date ) ) / ( 24 * 60 * 60 );
    return $days_since_activation <= 7;
}

/**
 * Dismiss onboarding
 */
function base47_he_dismiss_onboarding() {
    update_user_meta( get_current_user_id(), 'base47_he_onboarding_dismissed', true );
}