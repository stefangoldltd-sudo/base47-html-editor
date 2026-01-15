<?php
/**
 * Support System AJAX Handlers
 * 
 * Handle ticket submission, replies, and status updates
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.3.13
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Register AJAX handlers
add_action( 'wp_ajax_base47_he_submit_ticket', 'base47_he_ajax_submit_ticket' );
add_action( 'wp_ajax_base47_he_reply_ticket', 'base47_he_ajax_reply_ticket' );
add_action( 'wp_ajax_base47_he_filter_tickets', 'base47_he_ajax_filter_tickets' );

/**
 * Submit new support ticket
 */
function base47_he_ajax_submit_ticket() {
    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['support_nonce'] ?? '', 'base47_he_support_ticket' ) ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Security check failed'
        ]));
    }
    
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Insufficient permissions'
        ]));
    }
    
    // Validate required fields
    $subject = sanitize_text_field( $_POST['subject'] ?? '' );
    $message = sanitize_textarea_field( $_POST['message'] ?? '' );
    $category = sanitize_text_field( $_POST['category'] ?? '' );
    $priority = sanitize_text_field( $_POST['priority'] ?? 'medium' );
    $include_system_info = isset( $_POST['include_system_info'] );
    
    if ( empty( $subject ) || empty( $message ) || empty( $category ) ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields'
        ]));
    }
    
    // Validate category and priority
    $valid_categories = ['bug', 'feature', 'question', 'installation', 'configuration', 'other'];
    $valid_priorities = ['low', 'medium', 'high', 'urgent'];
    
    if ( ! in_array( $category, $valid_categories ) ) {
        $category = 'other';
    }
    
    if ( ! in_array( $priority, $valid_priorities ) ) {
        $priority = 'medium';
    }
    
    // Collect system info if requested
    $system_info = '';
    if ( $include_system_info ) {
        $system_info = base47_he_collect_system_info();
    }
    
    // Insert ticket into database
    global $wpdb;
    $table_name = $wpdb->prefix . 'base47_support_tickets';
    
    // Ensure table exists
    base47_he_create_support_tables();
    
    $result = $wpdb->insert(
        $table_name,
        [
            'user_id' => get_current_user_id(),
            'subject' => $subject,
            'message' => $message,
            'category' => $category,
            'priority' => $priority,
            'status' => 'open',
            'system_info' => $system_info,
            'created_at' => current_time( 'mysql' )
        ],
        [
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        ]
    );
    
    if ( $result === false ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Failed to create ticket. Please try again.'
        ]));
    }
    
    $ticket_id = $wpdb->insert_id;
    
    // Send email notification (optional - for future implementation)
    base47_he_send_ticket_notification( $ticket_id, $subject, $message );
    
    wp_die( json_encode([
        'success' => true,
        'message' => 'Support ticket created successfully!',
        'ticket_id' => $ticket_id,
        'redirect' => admin_url( 'admin.php?page=base47-he-support&action=view&ticket=' . $ticket_id )
    ]));
}

/**
 * Reply to support ticket
 */
function base47_he_ajax_reply_ticket() {
    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['reply_nonce'] ?? '', 'base47_he_ticket_reply' ) ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Security check failed'
        ]));
    }
    
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Insufficient permissions'
        ]));
    }
    
    // Validate input
    $ticket_id = intval( $_POST['ticket_id'] ?? 0 );
    $reply_message = sanitize_textarea_field( $_POST['reply_message'] ?? '' );
    
    if ( ! $ticket_id || empty( $reply_message ) ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Invalid ticket ID or empty message'
        ]));
    }
    
    // Verify ticket ownership
    global $wpdb;
    $tickets_table = $wpdb->prefix . 'base47_support_tickets';
    $user_id = get_current_user_id();
    
    $ticket = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$tickets_table} WHERE id = %d AND user_id = %d",
        $ticket_id,
        $user_id
    ));
    
    if ( ! $ticket ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Ticket not found or access denied'
        ]));
    }
    
    // Check if ticket is closed
    if ( $ticket->status === 'closed' ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Cannot reply to closed ticket'
        ]));
    }
    
    // Insert reply
    $replies_table = $wpdb->prefix . 'base47_support_replies';
    
    $result = $wpdb->insert(
        $replies_table,
        [
            'ticket_id' => $ticket_id,
            'author_type' => 'user',
            'author_name' => wp_get_current_user()->display_name,
            'message' => $reply_message,
            'created_at' => current_time( 'mysql' )
        ],
        [
            '%d',
            '%s',
            '%s',
            '%s',
            '%s'
        ]
    );
    
    if ( $result === false ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Failed to send reply. Please try again.'
        ]));
    }
    
    // Update ticket status to 'open' if it was resolved
    if ( $ticket->status === 'resolved' ) {
        $wpdb->update(
            $tickets_table,
            ['status' => 'open'],
            ['id' => $ticket_id],
            ['%s'],
            ['%d']
        );
    }
    
    wp_die( json_encode([
        'success' => true,
        'message' => 'Reply sent successfully!',
        'reload' => true
    ]));
}

/**
 * Filter tickets by status
 */
function base47_he_ajax_filter_tickets() {
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( json_encode([
            'success' => false,
            'message' => 'Insufficient permissions'
        ]));
    }
    
    $status = sanitize_text_field( $_GET['status'] ?? 'all' );
    $user_id = get_current_user_id();
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'base47_support_tickets';
    
    if ( $status === 'all' ) {
        $tickets = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A );
    } else {
        $tickets = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d AND status = %s ORDER BY created_at DESC",
            $user_id,
            $status
        ), ARRAY_A );
    }
    
    wp_die( json_encode([
        'success' => true,
        'tickets' => $tickets ?: []
    ]));
}

/**
 * Collect system information
 */
function base47_he_collect_system_info() {
    $info = [];
    
    // WordPress info
    $info[] = 'WordPress Version: ' . get_bloginfo( 'version' );
    $info[] = 'Site URL: ' . get_site_url();
    $info[] = 'Home URL: ' . get_home_url();
    $info[] = 'Admin Email: ' . get_option( 'admin_email' );
    
    // Server info
    $info[] = 'PHP Version: ' . PHP_VERSION;
    $info[] = 'Server: ' . ( $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' );
    $info[] = 'User Agent: ' . ( $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown' );
    
    // Plugin info
    $info[] = 'Plugin Version: ' . BASE47_HE_VERSION;
    $info[] = 'Plugin Path: ' . BASE47_HE_PATH;
    $info[] = 'Plugin URL: ' . BASE47_HE_URL;
    
    // Theme info
    $theme = wp_get_theme();
    $info[] = 'Active Theme: ' . $theme->get( 'Name' ) . ' v' . $theme->get( 'Version' );
    $info[] = 'Theme Path: ' . get_template_directory();
    
    // Database info
    global $wpdb;
    $info[] = 'Database Version: ' . $wpdb->db_version();
    $info[] = 'Database Charset: ' . $wpdb->charset;
    
    // Memory info
    $info[] = 'PHP Memory Limit: ' . ini_get( 'memory_limit' );
    $info[] = 'PHP Max Execution Time: ' . ini_get( 'max_execution_time' );
    $info[] = 'PHP Max Input Vars: ' . ini_get( 'max_input_vars' );
    
    // Plugin settings
    $active_sets = base47_he_get_active_sets();
    $info[] = 'Active Template Sets: ' . ( empty( $active_sets ) ? 'None' : implode( ', ', $active_sets ) );
    
    $use_manifest = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );
    $info[] = 'Manifest Mode Sets: ' . ( empty( $use_manifest ) ? 'None' : implode( ', ', $use_manifest ) );
    
    $smart_loader = get_option( BASE47_HE_OPT_USE_SMART_LOADER, false );
    $info[] = 'Smart Loader: ' . ( $smart_loader ? 'Enabled' : 'Disabled' );
    
    // Error log (last 5 entries)
    $logs = base47_he_get_recent_logs( 5 );
    if ( ! empty( $logs ) ) {
        $info[] = "\nRecent Error Logs:";
        foreach ( $logs as $log ) {
            $info[] = '[' . $log['timestamp'] . '] ' . $log['level'] . ': ' . $log['message'];
        }
    }
    
    return implode( "\n", $info );
}

/**
 * Send ticket notification email
 */
function base47_he_send_ticket_notification( $ticket_id, $subject, $message ) {
    // Get support email from settings
    $settings = base47_he_get_settings();
    $support_email = $settings['support_email'] ?? 'support@47-studio.com';
    
    // Validate email
    if ( ! is_email( $support_email ) ) {
        base47_he_log( 'error', "Invalid support email address: {$support_email}" );
        return false;
    }
    
    // Get user info
    $user = wp_get_current_user();
    $user_email = $user->user_email;
    $user_name = $user->display_name;
    $site_name = get_bloginfo( 'name' );
    $site_url = get_site_url();
    
    // Email subject
    $email_subject = "[{$site_name}] New Support Ticket #{$ticket_id}: {$subject}";
    
    // Email content
    $email_message = "
New support ticket submitted on {$site_name}

Ticket Details:
- Ticket ID: #{$ticket_id}
- Subject: {$subject}
- User: {$user_name} ({$user_email})
- Site: {$site_url}
- Date: " . current_time( 'F j, Y \a\t g:i A' ) . "

Message:
{$message}

---

View ticket: {$site_url}/wp-admin/admin.php?page=base47-he-support&action=view&ticket={$ticket_id}

This is an automated message from Base47 HTML Editor Support System.
";
    
    // Email headers
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        "From: {$site_name} <noreply@" . parse_url( $site_url, PHP_URL_HOST ) . ">",
        "Reply-To: {$user_name} <{$user_email}>",
        'X-Mailer: Base47 HTML Editor Support System'
    ];
    
    // Send email
    $sent = wp_mail( $support_email, $email_subject, $email_message, $headers );
    
    // Log the result
    if ( $sent ) {
        base47_he_log( 'info', "Support ticket notification sent to {$support_email} for ticket #{$ticket_id}" );
    } else {
        base47_he_log( 'error', "Failed to send support ticket notification to {$support_email} for ticket #{$ticket_id}" );
    }
    
    return $sent;
}

/**
 * Get recent logs for system info
 */
function base47_he_get_recent_logs( $limit = 5 ) {
    // This would get recent logs from the logs system
    // For now, return empty array
    return [];
}