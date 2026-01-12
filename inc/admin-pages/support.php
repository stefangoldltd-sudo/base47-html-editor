<?php
/**
 * Support Admin Page - Built-in Ticket System
 * 
 * Complete support ticket system with submission, tracking, and history
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.3.13
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_support_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $action = $_GET['action'] ?? 'list';
    $ticket_id = $_GET['ticket'] ?? null;

    switch ( $action ) {
        case 'new':
            base47_he_support_new_ticket_form();
            break;
        case 'view':
            base47_he_support_view_ticket( $ticket_id );
            break;
        default:
            base47_he_support_tickets_list();
            break;
    }
}

/**
 * Support tickets list view
 */
function base47_he_support_tickets_list() {
    $tickets = base47_he_get_support_tickets();
    ?>
    <div class="wrap base47-support-system">
        
        <!-- Header -->
        <div class="support-header">
            <div class="header-content">
                <h1>
                    <span class="dashicons dashicons-sos"></span>
                    Support Center
                </h1>
                <p>Get help from our expert support team. Submit tickets, track progress, and get answers fast.</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo admin_url( 'admin.php?page=base47-he-support&action=new' ); ?>" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt"></span>
                    New Ticket
                </a>
            </div>
        </div>

        <!-- Support Stats -->
        <div class="support-stats-grid">
            <div class="support-stat-card">
                <div class="stat-icon stat-icon-primary">
                    <span class="dashicons dashicons-tickets"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo count( $tickets ); ?></div>
                    <div class="stat-label">Total Tickets</div>
                </div>
            </div>
            
            <div class="support-stat-card">
                <div class="stat-icon stat-icon-warning">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo base47_he_count_tickets_by_status( $tickets, 'open' ); ?></div>
                    <div class="stat-label">Open Tickets</div>
                </div>
            </div>
            
            <div class="support-stat-card">
                <div class="stat-icon stat-icon-info">
                    <span class="dashicons dashicons-admin-tools"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo base47_he_count_tickets_by_status( $tickets, 'in_progress' ); ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
            </div>
            
            <div class="support-stat-card">
                <div class="stat-icon stat-icon-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo base47_he_count_tickets_by_status( $tickets, 'resolved' ); ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>
        </div>

        <!-- Quick Help Section -->
        <div class="quick-help-section">
            <h2>Quick Help</h2>
            <div class="quick-help-grid">
                <div class="help-item">
                    <div class="help-icon">
                        <span class="dashicons dashicons-book"></span>
                    </div>
                    <div class="help-content">
                        <h3>Documentation</h3>
                        <p>Find answers in our comprehensive guides</p>
                        <a href="https://47-studio.com/base47/docs" target="_blank" class="help-link">Browse Docs →</a>
                    </div>
                </div>
                
                <div class="help-item">
                    <div class="help-icon">
                        <span class="dashicons dashicons-video-alt3"></span>
                    </div>
                    <div class="help-content">
                        <h3>Video Tutorials</h3>
                        <p>Watch step-by-step video guides</p>
                        <a href="https://47-studio.com/base47/tutorials" target="_blank" class="help-link">Watch Videos →</a>
                    </div>
                </div>
                
                <div class="help-item">
                    <div class="help-icon">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="help-content">
                        <h3>Community Forum</h3>
                        <p>Get help from other users</p>
                        <a href="https://47-studio.com/base47/community" target="_blank" class="help-link">Join Forum →</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets List -->
        <div class="tickets-section">
            <div class="section-header">
                <h2>Your Support Tickets</h2>
                <div class="tickets-filter">
                    <select id="ticket-status-filter">
                        <option value="all">All Tickets</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>
            
            <?php if ( empty( $tickets ) ) : ?>
                <div class="no-tickets">
                    <div class="no-tickets-icon">
                        <span class="dashicons dashicons-tickets"></span>
                    </div>
                    <h3>No Support Tickets Yet</h3>
                    <p>You haven't submitted any support tickets. Need help? Create your first ticket!</p>
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-support&action=new' ); ?>" class="button button-primary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        Create First Ticket
                    </a>
                </div>
            <?php else : ?>
                <div class="tickets-list">
                    <?php foreach ( $tickets as $ticket ) : ?>
                        <div class="ticket-item" data-status="<?php echo esc_attr( $ticket['status'] ); ?>">
                            <div class="ticket-status">
                                <span class="status-badge status-<?php echo esc_attr( $ticket['status'] ); ?>">
                                    <?php echo base47_he_get_status_label( $ticket['status'] ); ?>
                                </span>
                            </div>
                            
                            <div class="ticket-content">
                                <div class="ticket-header">
                                    <h3 class="ticket-title">
                                        <a href="<?php echo admin_url( 'admin.php?page=base47-he-support&action=view&ticket=' . $ticket['id'] ); ?>">
                                            <?php echo esc_html( $ticket['subject'] ); ?>
                                        </a>
                                    </h3>
                                    <div class="ticket-meta">
                                        <span class="ticket-id">#<?php echo $ticket['id']; ?></span>
                                        <span class="ticket-category"><?php echo esc_html( $ticket['category'] ); ?></span>
                                        <span class="ticket-priority priority-<?php echo esc_attr( $ticket['priority'] ); ?>">
                                            <?php echo ucfirst( $ticket['priority'] ); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="ticket-excerpt">
                                    <p><?php echo esc_html( wp_trim_words( $ticket['message'], 20 ) ); ?></p>
                                </div>
                                
                                <div class="ticket-footer">
                                    <div class="ticket-date">
                                        Created: <?php echo date( 'M j, Y \a\t g:i A', strtotime( $ticket['created_at'] ) ); ?>
                                    </div>
                                    <div class="ticket-actions">
                                        <a href="<?php echo admin_url( 'admin.php?page=base47-he-support&action=view&ticket=' . $ticket['id'] ); ?>" class="ticket-action-btn">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
    <?php
}

/**
 * New ticket form
 */
function base47_he_support_new_ticket_form() {
    ?>
    <div class="wrap base47-support-new-ticket">
        
        <!-- Header -->
        <div class="support-header">
            <div class="header-content">
                <h1>
                    <span class="dashicons dashicons-plus-alt"></span>
                    Submit New Support Ticket
                </h1>
                <p>Describe your issue in detail and we'll get back to you as soon as possible.</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo admin_url( 'admin.php?page=base47-he-support' ); ?>" class="button">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    Back to Tickets
                </a>
            </div>
        </div>

        <!-- New Ticket Form -->
        <div class="ticket-form-container">
            <form id="new-support-ticket-form" class="ticket-form">
                <?php wp_nonce_field( 'base47_he_support_ticket', 'support_nonce' ); ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ticket-subject">Subject *</label>
                        <input type="text" id="ticket-subject" name="subject" required 
                               placeholder="Brief description of your issue">
                    </div>
                </div>
                
                <div class="form-row form-row-split">
                    <div class="form-group">
                        <label for="ticket-category">Category *</label>
                        <select id="ticket-category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="bug">Bug Report</option>
                            <option value="feature">Feature Request</option>
                            <option value="question">General Question</option>
                            <option value="installation">Installation Help</option>
                            <option value="configuration">Configuration Help</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ticket-priority">Priority *</label>
                        <select id="ticket-priority" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ticket-message">Message *</label>
                        <textarea id="ticket-message" name="message" rows="8" required 
                                  placeholder="Please describe your issue in detail. Include steps to reproduce if it's a bug."></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="include-system-info" name="include_system_info" checked>
                            Include system information (recommended)
                        </label>
                        <p class="form-help">This helps us diagnose issues faster</p>
                    </div>
                </div>
                
                <!-- System Info Preview -->
                <div id="system-info-preview" class="system-info-section">
                    <h3>System Information</h3>
                    <div class="system-info-grid">
                        <div class="info-item">
                            <span class="info-label">WordPress Version:</span>
                            <span class="info-value"><?php echo get_bloginfo( 'version' ); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">PHP Version:</span>
                            <span class="info-value"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Plugin Version:</span>
                            <span class="info-value"><?php echo BASE47_HE_VERSION; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Active Theme:</span>
                            <span class="info-value"><?php echo wp_get_theme()->get( 'Name' ); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Server:</span>
                            <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">User Agent:</span>
                            <span class="info-value"><?php echo $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-email"></span>
                        Submit Ticket
                    </button>
                    <a href="<?php echo admin_url( 'admin.php?page=base47-he-support' ); ?>" class="button button-large">
                        Cancel
                    </a>
                </div>
                
            </form>
        </div>

    </div>
    <?php
}

/**
 * View single ticket
 */
function base47_he_support_view_ticket( $ticket_id ) {
    if ( ! $ticket_id ) {
        wp_redirect( admin_url( 'admin.php?page=base47-he-support' ) );
        exit;
    }

    $ticket = base47_he_get_support_ticket( $ticket_id );
    if ( ! $ticket ) {
        wp_redirect( admin_url( 'admin.php?page=base47-he-support' ) );
        exit;
    }

    $replies = base47_he_get_ticket_replies( $ticket_id );
    ?>
    <div class="wrap base47-support-ticket-view">
        
        <!-- Header -->
        <div class="support-header">
            <div class="header-content">
                <h1>
                    <span class="dashicons dashicons-tickets"></span>
                    Ticket #<?php echo $ticket['id']; ?>: <?php echo esc_html( $ticket['subject'] ); ?>
                </h1>
                <div class="ticket-meta-header">
                    <span class="status-badge status-<?php echo esc_attr( $ticket['status'] ); ?>">
                        <?php echo base47_he_get_status_label( $ticket['status'] ); ?>
                    </span>
                    <span class="priority-badge priority-<?php echo esc_attr( $ticket['priority'] ); ?>">
                        <?php echo ucfirst( $ticket['priority'] ); ?> Priority
                    </span>
                    <span class="category-badge">
                        <?php echo esc_html( $ticket['category'] ); ?>
                    </span>
                </div>
            </div>
            <div class="header-actions">
                <a href="<?php echo admin_url( 'admin.php?page=base47-he-support' ); ?>" class="button">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    Back to Tickets
                </a>
            </div>
        </div>

        <!-- Ticket Content -->
        <div class="ticket-conversation">
            
            <!-- Original Message -->
            <div class="message-item message-user">
                <div class="message-header">
                    <div class="message-author">
                        <span class="dashicons dashicons-admin-users"></span>
                        You
                    </div>
                    <div class="message-date">
                        <?php echo date( 'M j, Y \a\t g:i A', strtotime( $ticket['created_at'] ) ); ?>
                    </div>
                </div>
                <div class="message-content">
                    <p><?php echo nl2br( esc_html( $ticket['message'] ) ); ?></p>
                    
                    <?php if ( ! empty( $ticket['system_info'] ) ) : ?>
                        <div class="system-info-attachment">
                            <h4>System Information</h4>
                            <pre><?php echo esc_html( $ticket['system_info'] ); ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Replies -->
            <?php foreach ( $replies as $reply ) : ?>
                <div class="message-item message-<?php echo $reply['author_type']; ?>">
                    <div class="message-header">
                        <div class="message-author">
                            <?php if ( $reply['author_type'] === 'support' ) : ?>
                                <span class="dashicons dashicons-admin-tools"></span>
                                Base47 Support Team
                            <?php else : ?>
                                <span class="dashicons dashicons-admin-users"></span>
                                You
                            <?php endif; ?>
                        </div>
                        <div class="message-date">
                            <?php echo date( 'M j, Y \a\t g:i A', strtotime( $reply['created_at'] ) ); ?>
                        </div>
                    </div>
                    <div class="message-content">
                        <p><?php echo nl2br( esc_html( $reply['message'] ) ); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Reply Form -->
            <?php if ( $ticket['status'] !== 'closed' ) : ?>
                <div class="reply-form-section">
                    <h3>Add Reply</h3>
                    <form id="ticket-reply-form" class="reply-form">
                        <?php wp_nonce_field( 'base47_he_ticket_reply', 'reply_nonce' ); ?>
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                        
                        <div class="form-group">
                            <textarea name="reply_message" rows="5" required 
                                      placeholder="Type your reply here..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="button button-primary">
                                <span class="dashicons dashicons-email"></span>
                                Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            <?php else : ?>
                <div class="ticket-closed-notice">
                    <span class="dashicons dashicons-lock"></span>
                    This ticket has been closed. If you need further assistance, please create a new ticket.
                </div>
            <?php endif; ?>

        </div>

    </div>
    <?php
}

/**
 * Get support tickets for current user
 */
function base47_he_get_support_tickets() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'base47_support_tickets';
    
    // Create table if it doesn't exist
    base47_he_create_support_tables();
    
    $user_id = get_current_user_id();
    
    $tickets = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
        $user_id
    ), ARRAY_A );
    
    return $tickets ?: [];
}

/**
 * Get single support ticket
 */
function base47_he_get_support_ticket( $ticket_id ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'base47_support_tickets';
    $user_id = get_current_user_id();
    
    $ticket = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE id = %d AND user_id = %d",
        $ticket_id,
        $user_id
    ), ARRAY_A );
    
    return $ticket;
}

/**
 * Get ticket replies
 */
function base47_he_get_ticket_replies( $ticket_id ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'base47_support_replies';
    
    $replies = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE ticket_id = %d ORDER BY created_at ASC",
        $ticket_id
    ), ARRAY_A );
    
    return $replies ?: [];
}

/**
 * Count tickets by status
 */
function base47_he_count_tickets_by_status( $tickets, $status ) {
    return count( array_filter( $tickets, function( $ticket ) use ( $status ) {
        return $ticket['status'] === $status;
    }));
}

/**
 * Get status label
 */
function base47_he_get_status_label( $status ) {
    $labels = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed'
    ];
    
    return $labels[ $status ] ?? 'Unknown';
}

/**
 * Create support database tables
 */
function base47_he_create_support_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Support tickets table
    $tickets_table = $wpdb->prefix . 'base47_support_tickets';
    $tickets_sql = "CREATE TABLE IF NOT EXISTS {$tickets_table} (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL,
        subject varchar(255) NOT NULL,
        message text NOT NULL,
        category varchar(50) NOT NULL,
        priority varchar(20) NOT NULL DEFAULT 'medium',
        status varchar(20) NOT NULL DEFAULT 'open',
        system_info text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY status (status)
    ) {$charset_collate};";
    
    // Support replies table
    $replies_table = $wpdb->prefix . 'base47_support_replies';
    $replies_sql = "CREATE TABLE IF NOT EXISTS {$replies_table} (
        id int(11) NOT NULL AUTO_INCREMENT,
        ticket_id int(11) NOT NULL,
        author_type varchar(20) NOT NULL DEFAULT 'user',
        author_name varchar(255),
        message text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY ticket_id (ticket_id)
    ) {$charset_collate};";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $tickets_sql );
    dbDelta( $replies_sql );
}