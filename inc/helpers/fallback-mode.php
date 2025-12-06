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
     * Render fallback mode notice
     */
    public static function render_notice() {
        
        if ( ! self::is_active() ) {
            return;
        }
        
        $info = self::get_info();
        
        ?>
        <div class="notice notice-error is-dismissible">
            <h3>⚠️ Base47 HTML Editor - Fallback Mode Active</h3>
            <p><strong>Reason:</strong> <?php echo esc_html( $info['reason'] ); ?></p>
            <p><strong>Time:</strong> <?php echo esc_html( $info['time'] ); ?></p>
            <p>
                The plugin is running in safe mode with limited functionality. 
                Some features may be disabled to prevent errors.
            </p>
            <p>
                <a href="<?php echo admin_url( 'admin.php?page=base47-he-settings&action=deactivate_fallback' ); ?>" 
                   class="button button-primary">
                    Deactivate Fallback Mode
                </a>
                <a href="<?php echo admin_url( 'admin.php?page=base47-he-logs' ); ?>" 
                   class="button">
                    View Logs
                </a>
            </p>
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
    
    wp_redirect( admin_url( 'admin.php?page=base47-he-settings&fallback_deactivated=1' ) );
    exit;
});

