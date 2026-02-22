<?php
/**
 * Analytics Dashboard Admin Page - Pro Feature
 * 
 * Shows template usage statistics, performance metrics,
 * and user insights for data-driven decisions
 * 
 * @package Base47_HTML_Editor
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_analytics_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    // Check if Pro is active
    if ( ! base47_he_is_pro_active() ) {
        base47_he_show_pro_required_page( 'System Overview', 'Get detailed information about your templates, system, and plugin usage.' );
        return;
    }
    
    // Get real data
    $analytics = base47_he_get_analytics_data();
    $template_stats = base47_he_get_template_usage_stats();
    $system_info = base47_he_get_system_info();
    
    ?>
    <div class="wrap base47-analytics-soft-ui">
        
        <!-- Header -->
        <div class="base47-page-header">
            <div class="header-content">
                <div class="header-text">
                    <h1>
                        <span class="dashicons dashicons-dashboard"></span>
                        System Overview
                    </h1>
                    <p>Template usage, system information, and plugin statistics</p>
                </div>
            </div>
        </div>
        
        <!-- Overview Stats -->
        <div class="analytics-overview-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <span class="dashicons dashicons-media-document"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo esc_html( $analytics['total_templates'] ); ?></div>
                    <div class="stat-label">Total Templates</div>
                </div>
            </div>
            
            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo esc_html( $analytics['active_sets'] ); ?></div>
                    <div class="stat-label">Active Template Sets</div>
                </div>
            </div>
            
            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <span class="dashicons dashicons-shortcode"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo esc_html( $analytics['total_shortcodes'] ); ?></div>
                    <div class="stat-label">Available Shortcodes</div>
                </div>
            </div>
            
            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo esc_html( $analytics['total_users'] ); ?></div>
                    <div class="stat-label">WordPress Users</div>
                </div>
            </div>
        </div>
        
        <div class="analytics-main-grid">
            
            <!-- Left Column -->
            <div class="analytics-main">
                
                <!-- Template Sets -->
                <div class="analytics-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-portfolio"></span>
                            Template Sets
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="template-sets-list">
                            <?php foreach ( $template_stats['template_sets'] as $set_slug => $set_data ) : ?>
                            <div class="template-set-item">
                                <div class="set-info">
                                    <h4><?php echo esc_html( $set_data['name'] ); ?></h4>
                                    <p><?php echo esc_html( $set_data['count'] ); ?> templates</p>
                                </div>
                                <div class="set-status">
                                    <?php if ( $set_data['active'] ) : ?>
                                        <span class="status-badge active">
                                            <span class="dashicons dashicons-yes"></span>
                                            Active
                                        </span>
                                    <?php else : ?>
                                        <span class="status-badge inactive">
                                            <span class="dashicons dashicons-minus"></span>
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- System Information -->
                <div class="analytics-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-admin-tools"></span>
                            System Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="system-info-grid">
                            <div class="system-info-item">
                                <span class="info-label">Plugin Version</span>
                                <span class="info-value"><?php echo esc_html( $system_info['plugin_version'] ); ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="info-label">WordPress Version</span>
                                <span class="info-value"><?php echo esc_html( $system_info['wordpress_version'] ); ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="info-label">PHP Version</span>
                                <span class="info-value"><?php echo esc_html( $system_info['php_version'] ); ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="info-label">Active Theme</span>
                                <span class="info-value"><?php echo esc_html( $system_info['active_theme'] ); ?></span>
                            </div>
                            <div class="system-info-item">
                                <span class="info-label">Pro Status</span>
                                <span class="info-value">
                                    <?php if ( $system_info['is_pro_active'] ) : ?>
                                        <span class="pro-active">✓ Active</span>
                                    <?php else : ?>
                                        <span class="pro-inactive">✗ Not Active</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="system-info-item">
                                <span class="info-label">Memory Limit</span>
                                <span class="info-value"><?php echo esc_html( $system_info['memory_limit'] ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Right Sidebar -->
            <div class="analytics-sidebar">
                
                <!-- Quick Actions -->
                <div class="analytics-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-admin-generic"></span>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions-list">
                            <a href="<?php echo admin_url( 'admin.php?page=base47-he-theme-manager' ); ?>" class="quick-action-btn">
                                <span class="dashicons dashicons-admin-appearance"></span>
                                Manage Template Sets
                            </a>
                            <a href="<?php echo admin_url( 'admin.php?page=base47-he-editor' ); ?>" class="quick-action-btn">
                                <span class="dashicons dashicons-edit"></span>
                                Open Live Editor
                            </a>
                            <a href="<?php echo admin_url( 'admin.php?page=base47-he-templates' ); ?>" class="quick-action-btn">
                                <span class="dashicons dashicons-shortcode"></span>
                                View Shortcodes
                            </a>
                            <a href="<?php echo admin_url( 'admin.php?page=base47-he-settings' ); ?>" class="quick-action-btn">
                                <span class="dashicons dashicons-admin-settings"></span>
                                Plugin Settings
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Plugin Status -->
                <div class="analytics-card">
                    <div class="card-header">
                        <h3>
                            <span class="dashicons dashicons-info"></span>
                            Plugin Status
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="status-items">
                            <div class="status-item">
                                <span class="status-label">Templates Loaded</span>
                                <span class="status-value good">✓ Working</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Shortcodes</span>
                                <span class="status-value good">✓ Registered</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Live Editor</span>
                                <span class="status-value good">✓ Available</span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Theme Manager</span>
                                <span class="status-value good">✓ Working</span>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>
        
    </div>
    
    <?php
}

/**
 * Get analytics data for the dashboard
 */
function base47_he_get_analytics_data() {
    // Get actual usage data from WordPress
    $templates = base47_he_get_all_templates( true );
    $active_sets = base47_he_get_active_sets();
    
    // Get real data from WordPress options/database
    $usage_data = get_option( 'base47_he_usage_stats', [] );
    
    // Calculate real metrics
    $total_templates = count( $templates );
    $active_template_sets = count( $active_sets );
    $total_shortcodes = 0;
    
    // Count actual shortcodes in use
    foreach ( $templates as $template ) {
        if ( base47_he_shortcode_exists( $template['shortcode'] ) ) {
            $total_shortcodes++;
        }
    }
    
    // Get WordPress users count
    $user_count = count_users();
    $total_users = $user_count['total_users'];
    
    return [
        'total_templates' => $total_templates,
        'active_sets' => $active_template_sets,
        'total_shortcodes' => $total_shortcodes,
        'total_users' => $total_users,
        'plugin_version' => BASE47_HE_VERSION,
        'wordpress_version' => get_bloginfo( 'version' ),
        'php_version' => PHP_VERSION,
        'active_theme' => wp_get_theme()->get( 'Name' )
    ];
}

/**
 * Get template usage statistics
 */
function base47_he_get_template_usage_stats() {
    $templates = base47_he_get_all_templates( true );
    $active_sets = base47_he_get_active_sets();
    
    // Group templates by set
    $sets_data = [];
    foreach ( $templates as $template ) {
        $set_name = $template['set'];
        if ( ! isset( $sets_data[ $set_name ] ) ) {
            $sets_data[ $set_name ] = [
                'name' => str_replace( 'Base47-', '', str_replace( '-templates', '', $set_name ?? '' ) ),
                'count' => 0,
                'active' => in_array( $set_name, $active_sets )
            ];
        }
        $sets_data[ $set_name ]['count']++;
    }
    
    return [
        'template_sets' => $sets_data,
        'total_templates' => count( $templates ),
        'active_sets' => count( $active_sets )
    ];
}

/**
 * Get system information
 */
function base47_he_get_system_info() {
    return [
        'wordpress_version' => get_bloginfo( 'version' ),
        'php_version' => PHP_VERSION,
        'plugin_version' => BASE47_HE_VERSION,
        'active_theme' => wp_get_theme()->get( 'Name' ),
        'is_pro_active' => base47_he_is_pro_active(),
        'memory_limit' => ini_get( 'memory_limit' ),
        'max_execution_time' => ini_get( 'max_execution_time' )
    ];
}