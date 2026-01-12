<?php
/**
 * Changelog Page - Clean & Simple Design
 * 
 * Header + Stats + Simple Version Rows
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.3.12
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_changelog_page() {
    $versions = base47_he_get_changelog_versions();
    $stats = base47_he_calculate_stats( $versions );
    ?>
    <div class="wrap base47-changelog-clean">
        
        <!-- HEADER -->
        <div class="changelog-header">
            <div class="header-content">
                <h1>
                    <span class="dashicons dashicons-update-alt"></span>
                    Version History
                </h1>
                <p>Complete changelog for Base47 HTML Editor with all updates, features, and improvements.</p>
            </div>
        </div>

        <!-- STATS BUBBLES -->
        <div class="changelog-stats">
            <div class="stat-bubble">
                <div class="stat-icon">
                    <span class="dashicons dashicons-admin-plugins"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo esc_html( BASE47_HE_VERSION ); ?></div>
                    <div class="stat-label">Current Version</div>
                </div>
            </div>
            
            <div class="stat-bubble">
                <div class="stat-icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo count( $versions ); ?></div>
                    <div class="stat-label">Total Releases</div>
                </div>
            </div>
            
            <div class="stat-bubble">
                <div class="stat-icon">
                    <span class="dashicons dashicons-star-filled"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $stats['features']; ?></div>
                    <div class="stat-label">New Features</div>
                </div>
            </div>
            
            <div class="stat-bubble">
                <div class="stat-icon">
                    <span class="dashicons dashicons-admin-tools"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $stats['fixes']; ?></div>
                    <div class="stat-label">Bug Fixes</div>
                </div>
            </div>
        </div>

        <!-- VERSION ROWS -->
        <div class="changelog-versions">
            <?php foreach ( $versions as $version ) : 
                $is_current = ( $version['version'] === BASE47_HE_VERSION );
            ?>
                <div class="version-row <?php echo $is_current ? 'current-version' : ''; ?>">
                    <div class="version-header">
                        <div class="version-info">
                            <h3 class="version-number">v<?php echo esc_html( $version['version'] ); ?></h3>
                            <?php if ( $is_current ) : ?>
                                <span class="current-badge">Current</span>
                            <?php endif; ?>
                            <span class="version-date"><?php echo esc_html( $version['date'] ); ?></span>
                        </div>
                        <div class="version-title">
                            <h4><?php echo esc_html( $version['title'] ); ?></h4>
                        </div>
                    </div>
                    
                    <div class="version-content">
                        <p class="version-summary"><?php echo esc_html( $version['summary'] ); ?></p>
                        
                        <div class="changes-list">
                            <?php foreach ( $version['changes'] as $change ) : ?>
                                <div class="change-item">
                                    <span class="change-bullet">•</span>
                                    <span class="change-text"><?php echo esc_html( $change ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
    <?php
}

/**
 * Get changelog versions data
 */
function base47_he_get_changelog_versions() {
    return [
        [
            'version' => '2.9.9.3.12',
            'date' => 'January 12, 2026',
            'title' => 'Clean Changelog Design',
            'summary' => 'Simple, clean changelog design with header, stats bubbles, and version rows.',
            'changes' => [
                'Clean header with title and description',
                'Stats bubbles showing key metrics',
                'Simple version rows with changes',
                'Current version highlighting',
                'Easy to read layout'
            ]
        ],
        [
            'version' => '2.9.9.3.9',
            'date' => 'January 12, 2026',
            'title' => 'Critical Bug Fixes',
            'summary' => 'Major bug fixes and UI improvements across all admin pages.',
            'changes' => [
                'Fixed changelog page showing empty rows',
                'Added missing marketplace.css file',
                'Enhanced dashboard support system',
                'Professional admin page designs',
                'Improved responsive layouts',
                'Better error handling and user feedback'
            ]
        ],
        [
            'version' => '2.9.9.3.5',
            'date' => 'January 12, 2026',
            'title' => 'Pro Upgrade Page Redesign',
            'summary' => 'Complete redesign of the Pro upgrade experience with conversion optimization.',
            'changes' => [
                'Conversion-optimized layout',
                'Animated hero illustrations',
                'Professional pricing cards',
                'Customer testimonials',
                'Interactive FAQ system',
                'Trust badges and guarantees',
                'Fully responsive design'
            ]
        ],
        [
            'version' => '2.9.9.3.4',
            'date' => 'January 12, 2026',
            'title' => 'License Page Redesign',
            'summary' => 'Modern license management with professional design and better UX.',
            'changes' => [
                'Hero status cards with animations',
                'Professional license details',
                'Enhanced activation form',
                'Customer testimonials',
                'Integrated support section',
                'Pulse animations for active licenses'
            ]
        ],
        [
            'version' => '2.9.9.3.1',
            'date' => 'January 11, 2026',
            'title' => 'Marketplace Launch',
            'summary' => 'Revolutionary template marketplace with one-click installations.',
            'changes' => [
                'Complete marketplace interface',
                'Browse templates from 47-studio.com/base47',
                'Advanced filtering system',
                'One-click template installation',
                'Live preview system',
                'Template rating and reviews',
                'Professional UI design'
            ]
        ],
        [
            'version' => '2.9.9.3.0',
            'date' => 'January 10, 2026',
            'title' => 'Template Duplication',
            'summary' => 'Powerful template duplication system for rapid development.',
            'changes' => [
                'Duplicate button in Live Editor',
                'Modal interface for naming',
                'Complete template duplication',
                'Automatic shortcode generation',
                'Preserves unsaved edits',
                'Filename validation'
            ]
        ],
        [
            'version' => '2.9.8',
            'date' => 'November 2025',
            'title' => 'Dashboard Revolution',
            'summary' => 'Professional dashboard redesign with Soft UI components.',
            'changes' => [
                'Soft UI dashboard design',
                'Welcome banner system',
                'Statistics overview cards',
                'Quick actions grid',
                'System information display',
                'Ecosystem products showcase'
            ]
        ],
        [
            'version' => '2.9.0',
            'date' => 'July 2025',
            'title' => 'Base47 Transformation',
            'summary' => 'Complete rebranding and major feature overhaul.',
            'changes' => [
                'Complete Mivon to Base47 rebrand',
                'Live HTML editor with Monaco',
                'Template management system',
                'Professional branding',
                'Enhanced security features',
                'WordPress 6.2+ compatibility'
            ]
        ],
        [
            'version' => '2.5.0',
            'date' => 'November 17, 2024',
            'title' => 'Manifest System',
            'summary' => 'Revolutionary asset management system for optimal performance.',
            'changes' => [
                'Smart asset loading system',
                'Per-template configuration',
                'Massive performance boost',
                'Better memory usage',
                'Flexible asset structure',
                'Enhanced code maintainability'
            ]
        ],
        [
            'version' => '2.0.0',
            'date' => 'November 2024',
            'title' => 'Genesis',
            'summary' => 'The beginning of Base47 HTML Editor journey.',
            'changes' => [
                'Initial public release',
                'Core shortcode system',
                'Basic admin interface',
                'Template rendering foundation'
            ]
        ]
    ];
}

/**
 * Calculate statistics from versions
 */
function base47_he_calculate_stats( $versions ) {
    $features = 0;
    $fixes = 0;
    
    foreach ( $versions as $version ) {
        foreach ( $version['changes'] as $change ) {
            $change_lower = strtolower( $change );
            if ( strpos( $change_lower, 'added' ) === 0 || 
                 strpos( $change_lower, 'new' ) === 0 || 
                 strpos( $change_lower, 'introduced' ) === 0 ||
                 strpos( $change_lower, 'launched' ) !== false ) {
                $features++;
            } elseif ( strpos( $change_lower, 'fixed' ) === 0 || 
                       strpos( $change_lower, 'resolved' ) === 0 ||
                       strpos( $change_lower, 'bug' ) !== false ) {
                $fixes++;
            }
        }
    }
    
    return [
        'features' => $features,
        'fixes' => $fixes
    ];
}
