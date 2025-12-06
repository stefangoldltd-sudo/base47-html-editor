<?php
/**
 * WooCommerce Compatibility
 * 
 * Detects and handles WooCommerce integration
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.8
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WooCommerce Compatibility Class
 */
class Base47_WooCommerce_Compat {
    
    /**
     * Check if WooCommerce is active
     */
    public static function is_active() {
        return class_exists( 'WooCommerce' );
    }
    
    /**
     * Get WooCommerce version
     */
    public static function get_version() {
        
        if ( ! self::is_active() ) {
            return null;
        }
        
        if ( defined( 'WC_VERSION' ) ) {
            return WC_VERSION;
        }
        
        return null;
    }
    
    /**
     * Check if on WooCommerce page
     */
    public static function is_woocommerce_page() {
        
        if ( ! self::is_active() ) {
            return false;
        }
        
        if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
            return true;
        }
        
        if ( function_exists( 'is_shop' ) && is_shop() ) {
            return true;
        }
        
        if ( function_exists( 'is_product' ) && is_product() ) {
            return true;
        }
        
        if ( function_exists( 'is_cart' ) && is_cart() ) {
            return true;
        }
        
        if ( function_exists( 'is_checkout' ) && is_checkout() ) {
            return true;
        }
        
        if ( function_exists( 'is_account_page' ) && is_account_page() ) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get WooCommerce info
     */
    public static function get_info() {
        
        if ( ! self::is_active() ) {
            return [
                'active'  => false,
                'version' => null,
                'message' => 'WooCommerce is not installed or activated',
            ];
        }
        
        return [
            'active'  => true,
            'version' => self::get_version(),
            'message' => 'WooCommerce detected and compatible',
        ];
    }
    
    /**
     * Check compatibility with current WooCommerce version
     */
    public static function check_compatibility() {
        
        if ( ! self::is_active() ) {
            return true; // No WooCommerce, no problem
        }
        
        $version = self::get_version();
        
        if ( ! $version ) {
            return true; // Can't determine version, assume compatible
        }
        
        // Require WooCommerce 3.0+
        if ( version_compare( $version, '3.0', '<' ) ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Add WooCommerce compatibility notice
     */
    public static function render_compatibility_notice() {
        
        if ( ! self::is_active() ) {
            return;
        }
        
        if ( self::check_compatibility() ) {
            return;
        }
        
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Base47 HTML Editor:</strong> 
                Your WooCommerce version (<?php echo esc_html( self::get_version() ); ?>) 
                may not be fully compatible. Please update to WooCommerce 3.0 or higher.
            </p>
        </div>
        <?php
    }
    
    /**
     * Prevent asset conflicts with WooCommerce
     */
    public static function prevent_conflicts() {
        
        if ( ! self::is_active() ) {
            return;
        }
        
        if ( ! self::is_woocommerce_page() ) {
            return;
        }
        
        // Dequeue conflicting scripts/styles if needed
        // This is a placeholder for future conflict resolution
        
        // Example:
        // wp_dequeue_script( 'base47-conflicting-script' );
    }
    
    /**
     * Get WooCommerce template compatibility
     */
    public static function get_template_compatibility() {
        
        if ( ! self::is_active() ) {
            return null;
        }
        
        return [
            'shop_page'     => self::is_template_compatible( 'shop' ),
            'product_page'  => self::is_template_compatible( 'product' ),
            'cart_page'     => self::is_template_compatible( 'cart' ),
            'checkout_page' => self::is_template_compatible( 'checkout' ),
            'account_page'  => self::is_template_compatible( 'account' ),
        ];
    }
    
    /**
     * Check if specific template type is compatible
     */
    private static function is_template_compatible( $template_type ) {
        
        // Check if Base47 templates can work with WooCommerce pages
        // This is a basic check - can be expanded based on needs
        
        switch ( $template_type ) {
            case 'shop':
            case 'product':
                // Product pages usually work fine with custom templates
                return true;
                
            case 'cart':
            case 'checkout':
                // Cart/checkout may have conflicts - use with caution
                return 'partial';
                
            case 'account':
                // Account pages usually work fine
                return true;
                
            default:
                return 'unknown';
        }
    }
    
    /**
     * Add WooCommerce info to system info
     */
    public static function add_to_system_info( $info = [] ) {
        
        $info['woocommerce'] = self::get_info();
        
        if ( self::is_active() ) {
            $info['woocommerce']['compatibility'] = self::check_compatibility();
            $info['woocommerce']['templates'] = self::get_template_compatibility();
        }
        
        return $info;
    }
}

/**
 * Initialize WooCommerce compatibility
 */
add_action( 'plugins_loaded', function() {
    
    // Check compatibility on admin pages
    if ( is_admin() ) {
        add_action( 'admin_notices', [ 'Base47_WooCommerce_Compat', 'render_compatibility_notice' ] );
    }
    
    // Prevent conflicts on front-end
    add_action( 'wp_enqueue_scripts', [ 'Base47_WooCommerce_Compat', 'prevent_conflicts' ], 999 );
    
}, 20 );

/**
 * Helper function to check if WooCommerce is active
 */
function base47_he_is_woocommerce_active() {
    return Base47_WooCommerce_Compat::is_active();
}

/**
 * Helper function to check if on WooCommerce page
 */
function base47_he_is_woocommerce_page() {
    return Base47_WooCommerce_Compat::is_woocommerce_page();
}

