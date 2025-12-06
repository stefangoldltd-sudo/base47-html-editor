<?php
/**
 * Error Handling Framework
 * 
 * Centralized error handling and logging system
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.8
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Error Handler Class
 */
class Base47_Error_Handler {
    
    /**
     * Error levels
     */
    const LEVEL_DEBUG   = 'debug';
    const LEVEL_INFO    = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR   = 'error';
    const LEVEL_CRITICAL = 'critical';
    
    /**
     * Initialize error handler
     */
    public static function init() {
        
        // Register shutdown function to catch fatal errors
        register_shutdown_function( [ __CLASS__, 'handle_shutdown' ] );
        
        // Set custom error handler
        if ( WP_DEBUG ) {
            set_error_handler( [ __CLASS__, 'handle_error' ] );
        }
    }
    
    /**
     * Handle PHP errors
     */
    public static function handle_error( $errno, $errstr, $errfile, $errline ) {
        
        // Don't handle suppressed errors (@)
        if ( ! ( error_reporting() & $errno ) ) {
            return false;
        }
        
        $level = self::LEVEL_ERROR;
        
        switch ( $errno ) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $level = self::LEVEL_CRITICAL;
                break;
                
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $level = self::LEVEL_WARNING;
                break;
                
            case E_NOTICE:
            case E_USER_NOTICE:
                $level = self::LEVEL_INFO;
                break;
                
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $level = self::LEVEL_DEBUG;
                break;
        }
        
        $message = sprintf(
            'PHP %s: %s in %s on line %d',
            strtoupper( $level ),
            $errstr,
            $errfile,
            $errline
        );
        
        self::log( $message, $level );
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    /**
     * Handle fatal errors on shutdown
     */
    public static function handle_shutdown() {
        
        $error = error_get_last();
        
        if ( $error && in_array( $error['type'], [ E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE ] ) ) {
            
            $message = sprintf(
                'FATAL ERROR: %s in %s on line %d',
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            self::log( $message, self::LEVEL_CRITICAL );
            
            // Try to activate fallback mode
            self::activate_fallback_mode();
        }
    }
    
    /**
     * Log error message
     */
    public static function log( $message, $level = self::LEVEL_ERROR ) {
        
        // Use existing logging system if available
        if ( function_exists( 'base47_he_log' ) ) {
            base47_he_log( $message, $level );
            return;
        }
        
        // Fallback to error_log
        if ( WP_DEBUG && WP_DEBUG_LOG ) {
            error_log( '[Base47 HTML Editor] ' . $message );
        }
    }
    
    /**
     * Activate fallback mode
     */
    public static function activate_fallback_mode() {
        
        update_option( 'base47_he_fallback_mode', true );
        update_option( 'base47_he_fallback_reason', 'Fatal error detected' );
        update_option( 'base47_he_fallback_time', current_time( 'mysql' ) );
    }
    
    /**
     * Check if in fallback mode
     */
    public static function is_fallback_mode() {
        return (bool) get_option( 'base47_he_fallback_mode', false );
    }
    
    /**
     * Deactivate fallback mode
     */
    public static function deactivate_fallback_mode() {
        delete_option( 'base47_he_fallback_mode' );
        delete_option( 'base47_he_fallback_reason' );
        delete_option( 'base47_he_fallback_time' );
    }
    
    /**
     * Get fallback mode info
     */
    public static function get_fallback_info() {
        
        if ( ! self::is_fallback_mode() ) {
            return null;
        }
        
        return [
            'active' => true,
            'reason' => get_option( 'base47_he_fallback_reason', 'Unknown' ),
            'time'   => get_option( 'base47_he_fallback_time', '' ),
        ];
    }
}

/**
 * Safe execution wrapper
 * 
 * Executes a callback with error handling
 */
function base47_he_safe_execute( $callback, $fallback = null, $context = '' ) {
    
    try {
        
        if ( ! is_callable( $callback ) ) {
            Base47_Error_Handler::log( 
                'Invalid callback provided to safe_execute: ' . $context, 
                Base47_Error_Handler::LEVEL_ERROR 
            );
            return $fallback;
        }
        
        return call_user_func( $callback );
        
    } catch ( Exception $e ) {
        
        Base47_Error_Handler::log( 
            sprintf( 
                'Exception in %s: %s (File: %s, Line: %d)', 
                $context, 
                $e->getMessage(), 
                $e->getFile(), 
                $e->getLine() 
            ),
            Base47_Error_Handler::LEVEL_ERROR
        );
        
        return $fallback;
        
    } catch ( Error $e ) {
        
        Base47_Error_Handler::log( 
            sprintf( 
                'Fatal error in %s: %s (File: %s, Line: %d)', 
                $context, 
                $e->getMessage(), 
                $e->getFile(), 
                $e->getLine() 
            ),
            Base47_Error_Handler::LEVEL_CRITICAL
        );
        
        Base47_Error_Handler::activate_fallback_mode();
        
        return $fallback;
    }
}

/**
 * Validate required functions exist
 */
function base47_he_validate_requirements() {
    
    $required_functions = [
        'file_get_contents',
        'file_put_contents',
        'glob',
        'is_dir',
        'is_file',
        'scandir',
    ];
    
    $missing = [];
    
    foreach ( $required_functions as $func ) {
        if ( ! function_exists( $func ) ) {
            $missing[] = $func;
        }
    }
    
    if ( ! empty( $missing ) ) {
        Base47_Error_Handler::log( 
            'Missing required PHP functions: ' . implode( ', ', $missing ), 
            Base47_Error_Handler::LEVEL_CRITICAL 
        );
        Base47_Error_Handler::activate_fallback_mode();
        return false;
    }
    
    return true;
}

/**
 * Check system compatibility
 */
function base47_he_check_compatibility() {
    
    $issues = [];
    
    // Check PHP version
    if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
        $issues[] = 'PHP version 7.0 or higher required (current: ' . PHP_VERSION . ')';
    }
    
    // Check WordPress version
    global $wp_version;
    if ( version_compare( $wp_version, '5.0', '<' ) ) {
        $issues[] = 'WordPress version 5.0 or higher required (current: ' . $wp_version . ')';
    }
    
    // Check write permissions
    $upload_dir = wp_upload_dir();
    if ( ! wp_is_writable( $upload_dir['basedir'] ) ) {
        $issues[] = 'Upload directory is not writable: ' . $upload_dir['basedir'];
    }
    
    if ( ! empty( $issues ) ) {
        foreach ( $issues as $issue ) {
            Base47_Error_Handler::log( 'Compatibility issue: ' . $issue, Base47_Error_Handler::LEVEL_WARNING );
        }
        return false;
    }
    
    return true;
}

// Initialize error handler
Base47_Error_Handler::init();

