<?php
/**
 * Dashboard Admin Page - Soft UI Dashboard 2.0
 * 
 * Professional dashboard with welcome banner, stats, system overview,
 * suggested products, and quick actions
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.8
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_dashboard_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $sets   = base47_he_get_template_sets();
    $active = base47_he_get_active_sets();
    $all    = base47_he_get_all_templates( true );
    
    // Get special widgets count
    $widgets = base47_he_get_special_widgets_registry();
    $widget_count = count( $widgets );

    $counts = [];
    foreach ( $all as $item ) {
        $counts[ $item['set'] ] = ( $counts[ $item['set'] ] ?? 0 ) + 1;
    }
    
    // Check for updates (placeholder for future)
    $current_version = BASE47_HE_VERSION;
    $is_latest = true; // Will be dynamic in future
    
    ?>
    <div class="wrap base47-dashboard-soft-ui">
        
        <!-- Welcome Banner -->
        <div class="base47-welcome-banner">
            <div class="banner-content">
                <div class="banner-text">
                    <h1>Welcome to Base47 HTML Editor</h1>
                    <p>Your powerful HTML template management system for WordPress</p>
                    <div class="banner-actions">
                        <a href="<?php echo admin_url( 'admin.php?page=base47-he-editor' ); ?>" class="btn-banner-primary">
                            <span class="dashicons dashicons-edit"></span>
                            Open Live Editor
                        </a>
                        <a href="https://base47.com/docs" target="_blank" class="btn-banner-secondary">
                            <span class="dashicons dashicons-book"></span>
                            Documentation
                        </a>
                    </div>
                </div>
                <div class="banner-illustration">
                    <div class="illustration-circle"></div>
                    <span class="dashicons dashicons-layout"></span>
                </div>
            </div>
        </div>
        
        <!-- Stats Overview -->
        <div class="base47-stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <span class="dashicons dashicons-portfolio"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo count( $sets ); ?></div>
                    <div class="stat-label">Theme Sets</div>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <span class="dashicons dashicons-media-document"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo count( $all ); ?></div>
                    <div class="stat-label">Total Templates</div>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo count( $active ); ?></div>
                    <div class="stat-label">Active Sets</div>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <span class="dashicons dashicons-admin-plugins"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $widget_count; ?></div>
                    <div class="stat-label">Special Widgets</div>
                </div>
            </div>
        </div>
        
        <?php if ( ! base47_he_is_pro_active() ) : ?>
        <!-- Pro Upgrade CTA -->
        <div class="base47-upgrade-cta">
            <h3>🚀 Upgrade to Base47 HTML Editor Pro</h3>
            <p>Unlock Monaco Editor, unlimited templates, auto-backups, and more premium features!</p>
            <div class="base47-upgrade-features">
                <div class="upgrade-feature-item">
                    <span class="dashicons dashicons-yes"></span>
                    <span>Monaco Editor (VS Code)</span>
                </div>
                <div class="upgrade-feature-item">
                    <span class="dashicons dashicons-yes"></span>
                    <span>Unlimited Templates</span>
                </div>
                <div class="upgrade-feature-item">
                    <span class="dashicons dashicons-yes"></span>
                    <span>Auto-Backups & Restore</span>
                </div>
                <div class="upgrade-feature-item">
                    <span class="dashicons dashicons-yes"></span>
                    <span>Priority Support</span>
                </div>
            </div>
            <a href="<?php echo esc_url( base47_he_get_pro_url() ); ?>" class="button button-hero" target="_blank">
                Get Pro Now
                <span class="dashicons dashicons-external"></span>
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Main Content Grid -->
        <div class="base47-dashboard-grid">
            
            <!-- Left Column -->
            <div class="dashboard-main">
                
                <!-- Quick Actions -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-admin-tools"></span>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions-grid">
                            <a href="<?php echo admin_url( 'admin.php?page=base47-he-editor' ); ?>" class="quick-action-item">
                                <div class="action-icon action-primary">
                                    <span class="dashicons dashicons-edit"></span>
                                </div>
                                <div class="action-content">
                                    <h4>Live Editor</h4>
                                    <p>Edit templates with Monaco</p>
                                </div>
                            </a>
                            
                            <a href="<?php echo admin_url( 'admin.php?page=base47-he-theme-manager' ); ?>" class="quick-action-item">
                                <div class="action-icon action-purple">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                </div>
                                <div class="action-content">
                                    <h4>Theme Manager</h4>
                                    <p>Install & manage themes</p>
                                </div>
                            </a>
                            
                            <a href="<?php echo admin_url( 'admin.php?page=base47-he-templates' ); ?>" class="quick-action-item">
                                <div class="action-icon action-blue">
                                    <span class="dashicons dashicons-shortcode"></span>
                                </div>
                                <div class="action-content">
                                    <h4>Shortcodes</h4>
                                    <p>Browse & copy shortcodes</p>
                                </div>
                            </a>
                            
                            <a href="<?php echo admin_url( 'admin.php?page=base47-special-widgets' ); ?>" class="quick-action-item">
                                <div class="action-icon action-orange">
                                    <span class="dashicons dashicons-admin-plugins"></span>
                                </div>
                                <div class="action-content">
                                    <h4>Special Widgets</h4>
                                    <p>Reusable components</p>
                                </div>
                            </a>
                            
                            <a href="<?php echo admin_url( 'admin.php?page=base47-he-settings' ); ?>" class="quick-action-item">
                                <div class="action-icon action-green">
                                    <span class="dashicons dashicons-admin-settings"></span>
                                </div>
                                <div class="action-content">
                                    <h4>Settings</h4>
                                    <p>Configure plugin options</p>
                                </div>
                            </a>
                            
                            <a href="<?php echo admin_url( 'admin.php?page=base47-he-logs' ); ?>" class="quick-action-item">
                                <div class="action-icon action-info">
                                    <span class="dashicons dashicons-list-view"></span>
                                </div>
                                <div class="action-content">
                                    <h4>Logs</h4>
                                    <p>View system logs</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- System Overview -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-dashboard"></span>
                            System Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="system-info-grid">
                            <div class="system-info-item">
                                <span class="info-label">WordPress Version</span>
                                <span class="info-value"><?php echo get_bloginfo( 'version' ); ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="info-label">PHP Version</span>
                                <span class="info-value"><?php echo PHP_VERSION; ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="info-label">Plugin Version</span>
                                <span class="info-value"><?php echo BASE47_HE_VERSION; ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="info-label">Active Theme</span>
                                <span class="info-value"><?php echo wp_get_theme()->get( 'Name' ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recommended Products -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-download"></span>
                            Recommended Products
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="install-products-grid">
                            
                            <!-- Base47 Shell Theme -->
                            <div class="install-product-item">
                                <div class="install-product-icon">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                </div>
                                <div class="install-product-info">
                                    <h4>Base47 Shell Theme</h4>
                                    <p>Lightweight, fast WordPress theme optimized for Base47 HTML Editor</p>
                                </div>
                                <div class="install-product-action">
                                    <?php
                                    $shell_theme = wp_get_theme( 'base47-shell' );
                                    if ( $shell_theme->exists() ) :
                                    ?>
                                        <button class="btn-install btn-installed" disabled>
                                            <span class="dashicons dashicons-yes"></span>
                                            Installed
                                        </button>
                                    <?php else : ?>
                                        <button class="btn-install btn-install-primary" data-product="shell-theme">
                                            <span class="dashicons dashicons-download"></span>
                                            Install
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Base47 Lead Form -->
                            <div class="install-product-item">
                                <div class="install-product-icon">
                                    <span class="dashicons dashicons-feedback"></span>
                                </div>
                                <div class="install-product-info">
                                    <h4>Base47 Lead Form</h4>
                                    <p>Advanced contact form plugin with lead management and integrations</p>
                                </div>
                                <div class="install-product-action">
                                    <?php
                                    $lead_form_active = is_plugin_active( 'base47-lead-form/base47-lead-form.php' );
                                    if ( $lead_form_active ) :
                                    ?>
                                        <button class="btn-install btn-installed" disabled>
                                            <span class="dashicons dashicons-yes"></span>
                                            Installed
                                        </button>
                                    <?php else : ?>
                                        <button class="btn-install btn-install-primary" data-product="lead-form">
                                            <span class="dashicons dashicons-download"></span>
                                            Install
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        </div>
                        <p class="install-note">
                            <span class="dashicons dashicons-info"></span>
                            Installation feature coming soon. For now, download from <a href="https://base47.com" target="_blank">base47.com</a>
                        </p>
                    </div>
                </div>
                
            </div>
            
            <!-- Right Sidebar -->
            <div class="dashboard-sidebar">
                
                <!-- Update Status -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-update"></span>
                            Update Status
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if ( $is_latest ) : ?>
                            <div class="update-status update-success">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <div>
                                    <strong>You're up to date!</strong>
                                    <p>Version <?php echo esc_html( $current_version ); ?></p>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="update-status update-warning">
                                <span class="dashicons dashicons-warning"></span>
                                <div>
                                    <strong>Update Available</strong>
                                    <p>New version available</p>
                                </div>
                            </div>
                            <a href="#" class="btn-update">Update Now</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Base47 Ecosystem -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-star-filled"></span>
                            Base47 Ecosystem
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="ecosystem-products">
                            <div class="product-item">
                                <div class="product-icon">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                </div>
                                <div class="product-info">
                                    <h4>Base47 Shell Theme</h4>
                                    <p>Lightweight WordPress theme</p>
                                    <a href="https://base47.com/shell-theme" target="_blank" class="product-link">Learn More →</a>
                                </div>
                            </div>
                            
                            <div class="product-item">
                                <div class="product-icon">
                                    <span class="dashicons dashicons-feedback"></span>
                                </div>
                                <div class="product-info">
                                    <h4>Base47 Lead Form</h4>
                                    <p>Advanced contact forms</p>
                                    <a href="https://base47.com/lead-form" target="_blank" class="product-link">Learn More →</a>
                                </div>
                            </div>
                            
                            <div class="product-item">
                                <div class="product-icon">
                                    <span class="dashicons dashicons-layout"></span>
                                </div>
                                <div class="product-info">
                                    <h4>Base47 Template Packs</h4>
                                    <p>Premium template collections</p>
                                    <a href="https://base47.com/templates" target="_blank" class="product-link">Learn More →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Help & Support -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-sos"></span>
                            Help & Support
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="support-links">
                            <a href="https://base47.com/docs" target="_blank" class="support-link">
                                <span class="dashicons dashicons-book"></span>
                                Documentation
                            </a>
                            <a href="https://base47.com/tutorials" target="_blank" class="support-link">
                                <span class="dashicons dashicons-video-alt3"></span>
                                Video Tutorials
                            </a>
                            <a href="https://base47.com/support" target="_blank" class="support-link">
                                <span class="dashicons dashicons-email"></span>
                                Contact Support
                            </a>
                            <a href="https://base47.com/community" target="_blank" class="support-link">
                                <span class="dashicons dashicons-groups"></span>
                                Community Forum
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Rate Us -->
                <div class="dashboard-card card-rating">
                    <div class="card-body">
                        <div class="rating-content">
                            <h3>Enjoying Base47?</h3>
                            <p>Help us grow by leaving a 5-star review!</p>
                            <div class="rating-stars">
                                ⭐⭐⭐⭐⭐
                            </div>
                            <a href="https://wordpress.org/support/plugin/base47-html-editor/reviews/#new-post" target="_blank" class="btn-rate">
                                Rate on WordPress.org
                            </a>
                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>
        
    </div>
    <?php
}
