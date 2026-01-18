<?php
/**
 * Fallback Safety Mode
 * 
 * Provides a safe mode when critical errors occur
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.8
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fallback Mode Manager
 */
class Base47_Fallback_Mode {
    
    /**
     * Check if fallback mode is active
     */
    public static function is_active() {
        return (bool) get_option( 'base47_he_fallback_mode', false );
    }
    
    /**
     * Activate fallback mode
     */
    public static function activate( $reason = 'Unknown error' ) {
        
        update_option( 'base47_he_fallback_mode', true );
        update_option( 'base47_he_fallback_reason', $reason );
        update_option( 'base47_he_fallback_time', current_time( 'mysql' ) );
        
        // Log activation
        if ( function_exists( 'base47_he_log' ) ) {
            base47_he_log( 'Fallback mode activated: ' . $reason, 'critical' );
        }
        
        // Send admin notification
        self::notify_admin( $reason );
    }
    
    /**
     * Deactivate fallback mode
     */
    public static function deactivate() {
        
        delete_option( 'base47_he_fallback_mode' );
        delete_option( 'base47_he_fallback_reason' );
        delete_option( 'base47_he_fallback_time' );
        
        // Log deactivation
        if ( function_exists( 'base47_he_log' ) ) {
            base47_he_log( 'Fallback mode deactivated', 'info' );
        }
    }
    
    /**
     * Get fallback mode info
     */
    public static function get_info() {
        
        if ( ! self::is_active() ) {
            return null;
        }
        
        return [
            'active' => true,
            'reason' => get_option( 'base47_he_fallback_reason', 'Unknown' ),
            'time'   => get_option( 'base47_he_fallback_time', '' ),
        ];
    }
    
    /**
     * Render fallback mode notice (Soft UI Style)
     */
    public static function render_notice() {
        
        if ( ! self::is_active() ) {
            return;
        }
        
        $info = self::get_info();
        
        ?>
        <style>
            .base47-fallback-notice {
                background: linear-gradient(135deg, #ea0606 0%, #ff6b6b 100%);
                border: none;
                border-radius: 1rem;
                padding: 1.5rem 2rem;
                margin: 1rem 20px 1rem 2px;
                box-shadow: 0 8px 32px rgba(234, 6, 6, 0.3);
                color: #fff;
                position: relative;
                overflow: hidden;
            }
            
            .base47-fallback-notice::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
                background-size: cover;
                opacity: 0.3;
            }
            
            .base47-fallback-header {
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1rem;
                position: relative;
                z-index: 1;
            }
            
            .base47-fallback-icon {
                width: 48px;
                height: 48px;
                background: rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(10px);
                border-radius: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                flex-shrink: 0;
            }
            
            .base47-fallback-title {
                font-size: 1.25rem;
                font-weight: 700;
                margin: 0;
                color: #fff;
            }
            
            .base47-fallback-content {
                position: relative;
                z-index: 1;
            }
            
            .base47-fallback-info {
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(10px);
                border-radius: 0.75rem;
                padding: 1rem 1.25rem;
                margin-bottom: 1rem;
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .base47-fallback-info-row {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 0.5rem;
            }
            
            .base47-fallback-info-row:last-child {
                margin-bottom: 0;
            }
            
            .base47-fallback-info-label {
                font-weight: 700;
                color: #fff;
            }
            
            .base47-fallback-info-value {
                color: rgba(255, 255, 255, 0.95);
            }
            
            .base47-fallback-message {
                font-size: 0.9375rem;
                line-height: 1.6;
                margin: 0 0 1.25rem 0;
                color: rgba(255, 255, 255, 0.95);
            }
            
            .base47-fallback-actions {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
            }
            
            .base47-fallback-btn {
                background: #fff;
                color: #ea0606;
                border: none;
                padding: 0.625rem 1.25rem;
                border-radius: 0.5rem;
                font-weight: 600;
                font-size: 0.875rem;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                transition: all 0.2s ease;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
            
            .base47-fallback-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
                color: #ea0606;
            }
            
            .base47-fallback-btn-secondary {
                background: rgba(255, 255, 255, 0.2);
                color: #fff;
                backdrop-filter: blur(10px);
            }
            
            .base47-fallback-btn-secondary:hover {
                background: rgba(255, 255, 255, 0.3);
                color: #fff;
            }
            
            .notice.base47-fallback-notice .notice-dismiss {
                display: none;
            }
        </style>
        
        <div class="notice base47-fallback-notice">
            <div class="base47-fallback-header">
                <div class="base47-fallback-icon">⚠️</div>
                <h3 class="base47-fallback-title">Base47 HTML Editor - Fallback Mode Active</h3>
            </div>
            
            <div class="base47-fallback-content">
                <div class="base47-fallback-info">
                    <div class="base47-fallback-info-row">
                        <span class="base47-fallback-info-label">Reason:</span>
                        <span class="base47-fallback-info-value"><?php echo esc_html( $info['reason'] ); ?></span>
                    </div>
                    <div class="base47-fallback-info-row">
                        <span class="base47-fallback-info-label">Time:</span>
                        <span class="base47-fallback-info-value"><?php echo esc_html( $info['time'] ); ?></span>
                    </div>
                </div>
                
                <p class="base47-fallback-message">
                    The plugin is running in safe mode with limited functionality. 
                    Some features may be disabled to prevent errors. Please check the logs for more details.
                </p>
                
                <div class="base47-fallback-actions">
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-settings&action=deactivate_fallback' ); ?>" 
                       class="base47-fallback-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Deactivate Fallback Mode
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-logs' ); ?>" 
                       class="base47-fallback-btn base47-fallback-btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" x2="8" y1="13" y2="13"/>
                            <line x1="16" x2="8" y1="17" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        View Logs
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Notify admin about fallback mode
     */
    private static function notify_admin( $reason ) {
        
        // Get admin email
        $admin_email = get_option( 'admin_email' );
        
        if ( ! $admin_email ) {
            return;
        }
        
        $subject = '[' . get_bloginfo( 'name' ) . '] Base47 HTML Editor - Fallback Mode Activated';
        
        $message = sprintf(
            "Base47 HTML Editor has activated fallback mode due to an error.\n\n" .
            "Reason: %s\n" .
            "Time: %s\n\n" .
            "The plugin is now running in safe mode with limited functionality.\n\n" .
            "Please check the logs at: %s\n\n" .
            "To deactivate fallback mode, visit: %s",
            $reason,
            current_time( 'mysql' ),
            admin_url( 'admin.php?page=base47-he-logs' ),
            admin_url( 'admin.php?page=base47-he-settings' )
        );
        
        // Send email (non-blocking)
        wp_mail( $admin_email, $subject, $message );
    }
    
    /**
     * Safe render template (fallback mode compatible)
     */
    public static function safe_render_template( $filename, $set_slug = '' ) {
        
        if ( ! self::is_active() ) {
            // Normal rendering
            if ( function_exists( 'base47_he_render_template' ) ) {
                return base47_he_render_template( $filename, $set_slug );
            }
        }
        
        // Fallback rendering (minimal, safe)
        $sets = base47_he_get_template_sets();
        
        if ( empty( $set_slug ) ) {
            $default = get_option( 'base47_default_theme', '' );
            if ( $default ) {
                $set_slug = $default;
            }
        }
        
        if ( empty( $set_slug ) || ! isset( $sets[ $set_slug ] ) ) {
            return '<!-- Base47: Fallback mode - template not available -->';
        }
        
        $full = $sets[ $set_slug ]['path'] . $filename;
        
        if ( ! file_exists( $full ) ) {
            return '<!-- Base47: Fallback mode - file not found -->';
        }
        
        // Simple file read (no processing)
        $html = @file_get_contents( $full );
        
        if ( false === $html ) {
            return '<!-- Base47: Fallback mode - read error -->';
        }
        
        // Minimal asset rewriting
        $base_url = trailingslashit( $sets[ $set_slug ]['url'] );
        $html = str_replace( 'src="assets/', 'src="' . $base_url . 'assets/', $html );
        $html = str_replace( 'href="assets/', 'href="' . $base_url . 'assets/', $html );
        
        return $html;
    }
}

/**
 * Add admin notice for fallback mode
 */
add_action( 'admin_notices', [ 'Base47_Fallback_Mode', 'render_notice' ] );

/**
 * Handle fallback mode deactivation
 */
add_action( 'admin_init', function() {
    
    if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'deactivate_fallback' ) {
        return;
    }
    
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'base47-he-settings' ) {
        return;
    }
    
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    Base47_Fallback_Mode::deactivate();
    
    // Redirect back to dashboard instead of settings
    wp_redirect( admin_url( 'admin.php?page=base47-he-dashboard&fallback_deactivated=1' ) );
    exit;
});

