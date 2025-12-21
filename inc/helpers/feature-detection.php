<?php
/**
 * Feature Detection System
 * 
 * Detects whether Pro plugin is installed and active,
 * and provides feature gating for Free vs Pro features.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if Pro plugin is installed (regardless of license status)
 * 
 * @return bool
 */
function base47_he_is_pro_installed() {
    // Check if Pro plugin constant is defined
    if ( defined( 'BASE47_HE_PRO_VERSION' ) ) {
        return true;
    }
    
    // Check if Pro plugin class exists
    if ( class_exists( 'Base47_HTML_Editor_Pro' ) ) {
        return true;
    }
    
    return false;
}

/**
 * Check if Pro plugin is installed and active
 * 
 * @return bool
 */
function base47_he_is_pro_active() {
    // Check if Pro plugin constant is defined
    if ( defined( 'BASE47_HE_PRO_VERSION' ) ) {
        // Pro plugin is installed - now check if license is active
        $license_status = get_option( 'base47_he_license_status', 'inactive' );
        return ( $license_status === 'active' );
    }
    
    // Check if Pro plugin class exists
    if ( class_exists( 'Base47_HTML_Editor_Pro' ) ) {
        // Pro plugin is installed - now check if license is active
        $license_status = get_option( 'base47_he_license_status', 'inactive' );
        return ( $license_status === 'active' );
    }
    
    // Allow Pro plugin to register itself via filter
    return apply_filters( 'base47_he_is_pro_active', false );
}

/**
 * Check if a specific feature is available
 * 
 * @param string $feature Feature slug
 * @return bool
 */
function base47_he_has_feature( $feature ) {
    $is_pro = base47_he_is_pro_active();
    
    // Define Pro-only features
    $pro_features = array(
        'monaco_editor',
        'editor_switcher',
        'advanced_preview',
        'unlimited_templates',
        'template_marketplace',
        'template_export_import',
        'unlimited_widgets',
        'widget_marketplace',
        'widget_builder',
        'smart_loader',
        'manifest_loader',
        'auto_backups',
        'manual_backups',
        'restore',
        'performance_settings',
        'woocommerce_settings',
        'advanced_logs',
        'debug_mode',
        'priority_support',
        'license_management',
        'auto_updates',
    );
    
    // If Pro is active, all features are available
    if ( $is_pro ) {
        return true;
    }
    
    // If feature is Pro-only and Pro is not active, return false
    if ( in_array( $feature, $pro_features, true ) ) {
        return false;
    }
    
    // Free features are always available
    return true;
}

/**
 * Get Pro upgrade URL
 * 
 * @return string
 */
function base47_he_get_pro_url() {
    return apply_filters( 'base47_he_pro_url', 'https://47-studio.com/base47/' );
}

/**
 * Get feature label (Free/Pro badge)
 * 
 * @param string $feature Feature slug
 * @return string HTML badge
 */
function base47_he_get_feature_badge( $feature ) {
    if ( base47_he_has_feature( $feature ) ) {
        return '';
    }
    
    return '<span class="base47-pro-badge">PRO</span>';
}

/**
 * Display Pro upgrade notice
 * 
 * @param string $feature Feature name
 * @param string $description Feature description
 */
function base47_he_pro_upgrade_notice( $feature, $description = '' ) {
    if ( base47_he_is_pro_active() ) {
        return;
    }
    
    $pro_url = base47_he_get_pro_url();
    ?>
    <div class="base47-pro-notice">
        <div class="pro-notice-icon">
            <span class="dashicons dashicons-lock"></span>
        </div>
        <div class="pro-notice-content">
            <h4><?php echo esc_html( $feature ); ?> is a Pro Feature</h4>
            <?php if ( $description ) : ?>
                <p><?php echo esc_html( $description ); ?></p>
            <?php endif; ?>
            <a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary" target="_blank">
                Upgrade to Pro
                <span class="dashicons dashicons-external"></span>
            </a>
        </div>
    </div>
    <?php
}

/**
 * Get Free version limits
 * 
 * @return array
 */
function base47_he_get_free_limits() {
    return array(
        'template_packs' => 1,  // Base47 Minimal only
        'widgets' => 1,         // Hero Slider V1 only
        'backups' => 0,         // No backups in Free
        'preview_modes' => 1,   // Basic preview only
    );
}

/**
 * Check if limit is reached for Free version
 * 
 * @param string $limit_type Limit type (template_packs, widgets, etc.)
 * @param int $current_count Current count
 * @return bool
 */
function base47_he_is_limit_reached( $limit_type, $current_count ) {
    if ( base47_he_is_pro_active() ) {
        return false; // No limits in Pro
    }
    
    $limits = base47_he_get_free_limits();
    
    if ( ! isset( $limits[ $limit_type ] ) ) {
        return false;
    }
    
    return $current_count >= $limits[ $limit_type ];
}

/**
 * Get feature comparison data for Free vs Pro
 * 
 * @return array
 */
function base47_he_get_feature_comparison() {
    return array(
        'Editor' => array(
            array(
                'name' => 'Classic Textarea Editor',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Monaco Editor (VS Code)',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Editor Mode Switcher',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Basic Live Preview',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Advanced Preview (Responsive)',
                'free' => false,
                'pro' => true,
            ),
        ),
        'Templates' => array(
            array(
                'name' => 'Template Discovery',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Included Template Packs',
                'free' => '1 (Base47 Minimal)',
                'pro' => 'Unlimited',
            ),
            array(
                'name' => 'Template Marketplace',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Export/Import Sets',
                'free' => false,
                'pro' => true,
            ),
        ),
        'Widgets' => array(
            array(
                'name' => 'Widget Discovery',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Included Widgets',
                'free' => '1 (Hero Slider)',
                'pro' => 'Unlimited',
            ),
            array(
                'name' => 'Widget Marketplace',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Custom Widget Builder',
                'free' => false,
                'pro' => true,
            ),
        ),
        'Asset Loading' => array(
            array(
                'name' => 'Fallback Loader',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Smart Loader++',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Manifest Loader',
                'free' => false,
                'pro' => true,
            ),
        ),
        'Backups' => array(
            array(
                'name' => 'Auto-Backups',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Manual Backups',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'One-Click Restore',
                'free' => false,
                'pro' => true,
            ),
        ),
        'Support' => array(
            array(
                'name' => 'Community Support',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Priority Support',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Email Support',
                'free' => false,
                'pro' => true,
            ),
        ),
    );
}
