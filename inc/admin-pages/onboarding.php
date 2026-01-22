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
                <h2>Transform Your HTML Workflow</h2>
                <p class="hero-description">
                    Base47 HTML Editor turns any HTML template into a WordPress shortcode, 
                    with live editing, professional templates, and powerful features.
                </p>
            </div>
            
            <div class="feature-highlights">
                <div class="highlight-grid">
                    <div class="highlight-item">
                        <div class="highlight-icon">
                            <span class="dashicons dashicons-edit"></span>
                        </div>
                        <h3>Live HTML Editor</h3>
                        <p>Edit HTML with real-time preview and syntax highlighting</p>
                    </div>
                    
                    <div class="highlight-item">
                        <div class="highlight-icon">
                            <span class="dashicons dashicons-layout"></span>
                        </div>
                        <h3>47 Pro Templates</h3>
                        <p>Professional templates for agencies, e-commerce, restaurants & more</p>
                    </div>
                    
                    <div class="highlight-item">
                        <div class="highlight-icon">
                            <span class="dashicons dashicons-shortcode"></span>
                        </div>
                        <h3>Instant Shortcodes</h3>
                        <p>Any template becomes a shortcode automatically</p>
                    </div>
                    
                    <div class="highlight-item">
                        <div class="highlight-icon">
                            <span class="dashicons dashicons-smartphone"></span>
                        </div>
                        <h3>Responsive Design</h3>
                        <p>All templates are mobile-friendly and responsive</p>
                    </div>
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
 * Step 2: Editor Introduction
 */
function base47_he_onboarding_step_editor() {
    ?>
    <div class="onboarding-step step-editor">
        <div class="step-content">
            <div class="step-header">
                <h2>Meet Your HTML Editor</h2>
                <p>Powerful editing tools designed for modern web development</p>
            </div>
            
            <div class="editor-demo">
                <div class="demo-screenshot">
                    <img src="<?php echo BASE47_HE_URL; ?>admin-assets/images/editor-screenshot.png" alt="HTML Editor" class="screenshot">
                    <div class="demo-overlay">
                        <div class="play-button" onclick="base47PlayEditorDemo()">
                            <span class="dashicons dashicons-controls-play"></span>
                        </div>
                    </div>
                </div>
                
                <div class="editor-features">
                    <h3>Editor Features</h3>
                    <ul class="feature-list">
                        <li>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <strong>Syntax Highlighting</strong> - Color-coded HTML, CSS, and JavaScript
                        </li>
                        <li>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <strong>Live Preview</strong> - See changes instantly as you type
                        </li>
                        <li>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <strong>Auto-Complete</strong> - Smart suggestions for tags and attributes
                        </li>
                        <li>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <strong>Error Detection</strong> - Catch mistakes before publishing
                        </li>
                        <?php if ( base47_he_is_pro_active() ) : ?>
                        <li class="pro-feature">
                            <span class="dashicons dashicons-star-filled"></span>
                            <strong>Monaco Editor</strong> - VS Code experience in WordPress
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="quick-tip">
                <div class="tip-icon">
                    <span class="dashicons dashicons-lightbulb"></span>
                </div>
                <div class="tip-content">
                    <h4>Pro Tip</h4>
                    <p>Use <kbd>Ctrl+S</kbd> (or <kbd>Cmd+S</kbd> on Mac) to save your work quickly while editing.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Step 3: Templates Overview
 */
function base47_he_onboarding_step_templates() {
    ?>
    <div class="onboarding-step step-templates">
        <div class="step-content">
            <div class="step-header">
                <h2>Discover Professional Templates</h2>
                <p>Choose from 47 professionally designed templates across 9 categories</p>
            </div>
            
            <div class="template-categories">
                <div class="category-grid">
                    <?php
                    $categories = array(
                        'agency' => array( 'name' => 'Agency', 'icon' => 'building', 'count' => 6 ),
                        'ecommerce' => array( 'name' => 'E-commerce', 'icon' => 'cart', 'count' => 5 ),
                        'restaurant' => array( 'name' => 'Restaurant', 'icon' => 'food', 'count' => 4 ),
                        'fitness' => array( 'name' => 'Fitness', 'icon' => 'heart', 'count' => 4 ),
                        'realestate' => array( 'name' => 'Real Estate', 'icon' => 'admin-home', 'count' => 6 ),
                        'education' => array( 'name' => 'Education', 'icon' => 'welcome-learn-more', 'count' => 5 ),
                        'app' => array( 'name' => 'App Landing', 'icon' => 'smartphone', 'count' => 7 ),
                        'event' => array( 'name' => 'Events', 'icon' => 'calendar-alt', 'count' => 5 ),
                        'medical' => array( 'name' => 'Medical', 'icon' => 'plus-alt', 'count' => 5 ),
                    );
                    
                    foreach ( $categories as $slug => $category ) :
                    ?>
                        <div class="category-card">
                            <div class="category-icon">
                                <span class="dashicons dashicons-<?php echo $category['icon']; ?>"></span>
                            </div>
                            <h3><?php echo $category['name']; ?></h3>
                            <p class="template-count"><?php echo $category['count']; ?> Templates</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="template-preview">
                <h3>How Templates Work</h3>
                <div class="workflow-steps">
                    <div class="workflow-step">
                        <div class="step-number">1</div>
                        <div class="step-info">
                            <h4>Browse & Install</h4>
                            <p>Choose from our marketplace of professional templates</p>
                        </div>
                    </div>
                    <div class="workflow-arrow">→</div>
                    <div class="workflow-step">
                        <div class="step-number">2</div>
                        <div class="step-info">
                            <h4>Customize</h4>
                            <p>Edit HTML, CSS, and content to match your brand</p>
                        </div>
                    </div>
                    <div class="workflow-arrow">→</div>
                    <div class="workflow-step">
                        <div class="step-number">3</div>
                        <div class="step-info">
                            <h4>Use Anywhere</h4>
                            <p>Insert with shortcodes in posts, pages, or widgets</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="cta-section">
                <a href="<?php echo admin_url( 'admin.php?page=base47-he-marketplace' ); ?>" class="button button-primary button-large">
                    <span class="dashicons dashicons-download"></span>
                    Browse Templates
                </a>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Step 4: Features Overview
 */
function base47_he_onboarding_step_features() {
    ?>
    <div class="onboarding-step step-features">
        <div class="step-content">
            <div class="step-header">
                <h2>Powerful Features at Your Fingertips</h2>
                <p>Discover what makes Base47 HTML Editor special</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-section">
                    <h3>
                        <span class="dashicons dashicons-heart"></span>
                        Free Features
                    </h3>
                    <ul class="feature-list">
                        <li>Live HTML Editor with syntax highlighting</li>
                        <li>Real-time preview</li>
                        <li>Template discovery system</li>
                        <li>Shortcode generation</li>
                        <li>Basic template pack included</li>
                        <li>Community support</li>
                    </ul>
                </div>
                
                <?php if ( base47_he_is_pro_active() ) : ?>
                <div class="feature-section pro-features">
                    <h3>
                        <span class="dashicons dashicons-star-filled"></span>
                        Pro Features (Active)
                    </h3>
                    <ul class="feature-list">
                        <li>Monaco Editor (VS Code experience)</li>
                        <li>47 professional templates</li>
                        <li>Template marketplace</li>
                        <li>Advanced preview modes</li>
                        <li>Auto-backups & restore</li>
                        <li>White label system</li>
                        <li>Analytics dashboard</li>
                        <li>Priority support</li>
                    </ul>
                </div>
                <?php else : ?>
                <div class="feature-section pro-features">
                    <h3>
                        <span class="dashicons dashicons-star-filled"></span>
                        Pro Features
                    </h3>
                    <ul class="feature-list">
                        <li>Monaco Editor (VS Code experience)</li>
                        <li>47 professional templates</li>
                        <li>Template marketplace</li>
                        <li>Advanced preview modes</li>
                        <li>Auto-backups & restore</li>
                        <li>White label system</li>
                        <li>Analytics dashboard</li>
                        <li>Priority support</li>
                    </ul>
                    <div class="upgrade-cta">
                        <a href="<?php echo admin_url( 'admin.php?page=base47-he-upgrade' ); ?>" class="button button-primary">
                            Upgrade to Pro
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="help-section">
                <h3>Need Help?</h3>
                <div class="help-options">
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-support' ); ?>" class="help-option">
                        <span class="dashicons dashicons-sos"></span>
                        <div>
                            <strong>Support Center</strong>
                            <p>Get help with any questions</p>
                        </div>
                    </a>
                    
                    <a href="https://47-studio.com/base47/docs/" target="_blank" class="help-option">
                        <span class="dashicons dashicons-book"></span>
                        <div>
                            <strong>Documentation</strong>
                            <p>Detailed guides and tutorials</p>
                        </div>
                    </a>
                    
                    <a href="https://www.youtube.com/channel/UC47Studio" target="_blank" class="help-option">
                        <span class="dashicons dashicons-video-alt3"></span>
                        <div>
                            <strong>Video Tutorials</strong>
                            <p>Watch step-by-step guides</p>
                        </div>
                    </a>
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