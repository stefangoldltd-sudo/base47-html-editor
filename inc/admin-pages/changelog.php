<?php
/**
 * Changelog Admin Page - Soft UI v3.0
 * 
 * Modern, clean changelog with better organization and filtering
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.3.2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_changelog_page() {
    $file = BASE47_HE_PATH . 'changelog.txt';
    
    // Load and parse changelog
    if ( file_exists( $file ) ) {
        $content = file_get_contents( $file );
        $versions = base47_he_parse_changelog_v3( $content );
    } else {
        $versions = base47_he_get_fallback_changelog();
    }
    
    // Get version statistics
    $stats = base47_he_get_changelog_stats( $versions );
    
    ?>
    <div class="wrap base47-changelog-soft-ui">
        
        <!-- SOFT UI HEADER -->
        <div class="base47-changelog-header-soft">
            <div class="header-content">
                <h1>
                    <span class="dashicons dashicons-update-alt"></span>
                    Version History
                </h1>
                <p>Complete changelog for Base47 HTML Editor with all updates, features, and improvements.</p>
            </div>
        </div>

        <!-- STATS CARDS -->
        <div class="base47-changelog-stats">
            <div class="base47-changelog-stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-admin-plugins"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo esc_html( BASE47_HE_VERSION ); ?></div>
                    <div class="stat-label">Current Version</div>
                </div>
            </div>
            <div class="base47-changelog-stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo count( $versions ); ?></div>
                    <div class="stat-label">Total Releases</div>
                </div>
            </div>
            <div class="base47-changelog-stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-star-filled"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $stats['features']; ?></div>
                    <div class="stat-label">New Features</div>
                </div>
            </div>
            <div class="base47-changelog-stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-admin-tools"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $stats['fixes']; ?></div>
                    <div class="stat-label">Bug Fixes</div>
                </div>
            </div>
        </div>

        <!-- FILTER TABS -->
        <div class="base47-changelog-filters">
            <button class="filter-btn active" data-filter="all">
                <span class="dashicons dashicons-list-view"></span>
                All Versions
            </button>
            <button class="filter-btn" data-filter="major">
                <span class="dashicons dashicons-star-filled"></span>
                Major Releases
            </button>
            <button class="filter-btn" data-filter="feature">
                <span class="dashicons dashicons-plus-alt"></span>
                New Features
            </button>
            <button class="filter-btn" data-filter="hotfix">
                <span class="dashicons dashicons-sos"></span>
                Hotfixes
            </button>
        </div>

        <!-- CHANGELOG TIMELINE -->
        <div class="base47-changelog-timeline">
            <?php foreach ( $versions as $index => $version ) : 
                $is_current = ( $version['version'] === BASE47_HE_VERSION );
                $type_class = 'type-' . ( $version['type'] ?? 'update' );
                $is_major = base47_he_is_major_version( $version['version'] );
            ?>
                <div class="base47-changelog-version <?php echo $is_current ? 'is-current' : ''; ?> <?php echo esc_attr( $type_class ); ?>" 
                     data-type="<?php echo esc_attr( $version['type'] ?? 'update' ); ?>"
                     data-major="<?php echo $is_major ? 'true' : 'false'; ?>">
                    
                    <!-- Timeline Dot -->
                    <div class="timeline-dot">
                        <?php if ( $is_current ) : ?>
                            <span class="dashicons dashicons-star-filled"></span>
                        <?php elseif ( $version['type'] === 'hotfix' ) : ?>
                            <span class="dashicons dashicons-sos"></span>
                        <?php elseif ( $version['type'] === 'feature' ) : ?>
                            <span class="dashicons dashicons-plus-alt"></span>
                        <?php else : ?>
                            <span class="dashicons dashicons-update"></span>
                        <?php endif; ?>
                    </div>

                    <!-- Version Card -->
                    <div class="version-card">
                        <div class="version-header" data-index="<?php echo $index; ?>">
                            <div class="version-info">
                                <div class="version-main">
                                    <span class="version-badge <?php echo esc_attr( $type_class ); ?>">
                                        v<?php echo esc_html( $version['version'] ); ?>
                                    </span>
                                    <?php if ( $is_current ) : ?>
                                        <span class="current-badge">Current</span>
                                    <?php endif; ?>
                                    <?php if ( $is_major ) : ?>
                                        <span class="major-badge">Major</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ( ! empty( $version['date'] ) ) : ?>
                                    <div class="version-date">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                        <?php echo esc_html( $version['date'] ); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ( ! empty( $version['description'] ) ) : ?>
                                    <div class="version-description">
                                        <?php echo esc_html( $version['description'] ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="version-toggle">
                                <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                                <span class="changes-count"><?php echo count( $version['changes'] ); ?> changes</span>
                            </div>
                        </div>

                        <!-- Version Content -->
                        <div class="version-content" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                            <?php if ( ! empty( $version['changes'] ) ) : ?>
                                <div class="changes-grid">
                                    <?php 
                                    $categorized = base47_he_categorize_changes( $version['changes'] );
                                    foreach ( $categorized as $category => $changes ) :
                                        if ( empty( $changes ) ) continue;
                                    ?>
                                        <div class="changes-category">
                                            <h4 class="category-title">
                                                <?php echo base47_he_get_category_icon( $category ); ?>
                                                <?php echo esc_html( ucfirst( $category ) ); ?>
                                                <span class="category-count"><?php echo count( $changes ); ?></span>
                                            </h4>
                                            <ul class="changes-list">
                                                <?php foreach ( $changes as $change ) : ?>
                                                    <li><?php echo esc_html( $change ); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="no-changes">
                                    <span class="dashicons dashicons-info"></span>
                                    <p>No detailed changes recorded for this version.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- FOOTER -->
        <div class="base47-changelog-footer">
            <div class="footer-content">
                <div class="footer-info">
                    <span class="dashicons dashicons-info"></span>
                    <div>
                        <strong>Stay Updated</strong>
                        <p>Follow our development progress and get notified of new releases.</p>
                    </div>
                </div>
                <div class="footer-actions">
                    <a href="https://47-studio.com/base47" target="_blank" class="footer-btn">
                        <span class="dashicons dashicons-external"></span>
                        Visit Website
                    </a>
                    <a href="https://47-studio.com/base47/docs" target="_blank" class="footer-btn">
                        <span class="dashicons dashicons-book"></span>
                        Documentation
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script>
    jQuery(document).ready(function($) {
        // Toggle version content
        $('.version-header').on('click', function() {
            var $version = $(this).closest('.base47-changelog-version');
            var $content = $version.find('.version-content');
            var $icon = $(this).find('.toggle-icon');
            
            $content.slideToggle(300);
            $icon.toggleClass('rotated');
            $version.toggleClass('is-open');
        });

        // Filter functionality
        $('.filter-btn').on('click', function() {
            var filter = $(this).data('filter');
            
            // Update active button
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            // Filter versions
            $('.base47-changelog-version').each(function() {
                var $version = $(this);
                var type = $version.data('type');
                var isMajor = $version.data('major');
                var show = false;
                
                switch(filter) {
                    case 'all':
                        show = true;
                        break;
                    case 'major':
                        show = (isMajor === true);
                        break;
                    case 'feature':
                        show = (type === 'feature');
                        break;
                    case 'hotfix':
                        show = (type === 'hotfix');
                        break;
                }
                
                if (show) {
                    $version.fadeIn(300);
                } else {
                    $version.fadeOut(300);
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * Parse changelog.txt into structured array (v3.0)
 * 
 * @param string $content Raw changelog content
 * @return array Parsed versions
 */
function base47_he_parse_changelog_v3( $content ) {
    $versions = [];
    $lines = explode( "\n", $content );
    $current_version = null;
    
    foreach ( $lines as $line ) {
        $line = trim( $line );
        
        // Skip empty lines and separators
        if ( empty( $line ) || strpos( $line, '=====' ) === 0 || strpos( $line, '-----' ) === 0 ) {
            continue;
        }
        
        // Check for version header (Version X.X.X or = X.X.X =)
        if ( preg_match( '/^(?:Version\s+|=\s*)(\d+\.\d+\.?\d*\.?\d*)(?:\s*[–\-—]\s*(.+?))?(?:\s*=)?$/ui', $line, $matches ) ) {
            // Save previous version
            if ( $current_version !== null ) {
                $versions[] = $current_version;
            }
            
            $version_num = $matches[1];
            $description = isset( $matches[2] ) ? $matches[2] : '';
            
            // Determine version type and extract date
            $type = 'update';
            $date = '';
            
            if ( stripos( $description, 'hotfix' ) !== false || stripos( $description, 'critical' ) !== false ) {
                $type = 'hotfix';
            } elseif ( stripos( $description, 'feature' ) !== false || stripos( $description, 'new' ) !== false || stripos( $description, 'phase' ) !== false ) {
                $type = 'feature';
            }
            
            // Extract date from description
            if ( preg_match( '/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},?\s+\d{4}\b/i', $description, $date_match ) ) {
                $date = $date_match[0];
                $description = trim( str_replace( $date_match[0], '', $description ) );
            } elseif ( preg_match( '/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{4}\b/i', $description, $date_match ) ) {
                $date = $date_match[0];
                $description = trim( str_replace( $date_match[0], '', $description ) );
            } elseif ( preg_match( '/\d{4}-\d{2}-\d{2}/', $description, $date_match ) ) {
                $date = date( 'F j, Y', strtotime( $date_match[0] ) );
                $description = trim( str_replace( $date_match[0], '', $description ) );
            }
            
            $current_version = [
                'version' => $version_num,
                'date' => $date,
                'description' => $description,
                'type' => $type,
                'changes' => []
            ];
        } elseif ( $current_version !== null ) {
            // Check for bullet points or changes
            if ( preg_match( '/^[•\-\*]\s*(.+)$/', $line, $matches ) ) {
                $current_version['changes'][] = $matches[1];
            } elseif ( !empty( $line ) && !preg_match( '/^[A-Z\s]+:$/', $line ) && !preg_match( '/^[=\-]{3,}$/', $line ) ) {
                // Add as regular change if not a section header or separator
                $current_version['changes'][] = $line;
            }
        }
    }
    
    // Add last version
    if ( $current_version !== null ) {
        $versions[] = $current_version;
    }
    
    return $versions;
}

/**
 * Get fallback changelog data
 */
function base47_he_get_fallback_changelog() {
    return [
        [
            'version' => BASE47_HE_VERSION,
            'date' => 'January 2026',
            'description' => 'Latest Release',
            'type' => 'feature',
            'changes' => [
                'Marketplace integration with 47-studio.com/base47',
                'Template duplication feature',
                'Improved Soft UI design',
                'Performance optimizations',
                'Bug fixes and stability improvements'
            ]
        ],
        [
            'version' => '2.9.7.7',
            'date' => 'December 2024',
            'description' => 'Critical Hotfix',
            'type' => 'hotfix',
            'changes' => [
                'Fixed Live Editor resizer issues',
                'Fixed preview loading problems',
                'Restored all functionality from 2.9.7.6',
                'Added minimal Soft UI integration'
            ]
        ]
    ];
}

/**
 * Get changelog statistics
 */
function base47_he_get_changelog_stats( $versions ) {
    $stats = [
        'features' => 0,
        'fixes' => 0,
        'updates' => 0
    ];
    
    foreach ( $versions as $version ) {
        switch ( $version['type'] ) {
            case 'feature':
                $stats['features']++;
                break;
            case 'hotfix':
                $stats['fixes']++;
                break;
            default:
                $stats['updates']++;
                break;
        }
    }
    
    return $stats;
}

/**
 * Check if version is major release
 */
function base47_he_is_major_version( $version ) {
    $parts = explode( '.', $version );
    if ( count( $parts ) >= 2 ) {
        // Consider X.0.0 or X.Y.0 where Y is 0 as major
        return ( isset( $parts[1] ) && $parts[1] === '0' ) || 
               ( isset( $parts[2] ) && $parts[2] === '0' && count( $parts ) === 3 );
    }
    return false;
}

/**
 * Categorize changes by type
 */
function base47_he_categorize_changes( $changes ) {
    $categorized = [
        'features' => [],
        'fixes' => [],
        'improvements' => [],
        'technical' => []
    ];
    
    foreach ( $changes as $change ) {
        $change_lower = strtolower( $change );
        
        if ( strpos( $change_lower, 'added' ) === 0 || 
             strpos( $change_lower, 'new' ) === 0 || 
             strpos( $change_lower, 'introduced' ) === 0 ) {
            $categorized['features'][] = $change;
        } elseif ( strpos( $change_lower, 'fixed' ) === 0 || 
                   strpos( $change_lower, 'fix:' ) !== false ||
                   strpos( $change_lower, 'resolved' ) === 0 ) {
            $categorized['fixes'][] = $change;
        } elseif ( strpos( $change_lower, 'improved' ) === 0 || 
                   strpos( $change_lower, 'enhanced' ) === 0 ||
                   strpos( $change_lower, 'updated' ) === 0 ||
                   strpos( $change_lower, 'optimized' ) === 0 ) {
            $categorized['improvements'][] = $change;
        } else {
            $categorized['technical'][] = $change;
        }
    }
    
    // Remove empty categories
    return array_filter( $categorized );
}

/**
 * Get category icon
 */
function base47_he_get_category_icon( $category ) {
    $icons = [
        'features' => '<span class="dashicons dashicons-plus-alt"></span>',
        'fixes' => '<span class="dashicons dashicons-admin-tools"></span>',
        'improvements' => '<span class="dashicons dashicons-chart-line"></span>',
        'technical' => '<span class="dashicons dashicons-admin-generic"></span>'
    ];
    
    return $icons[ $category ] ?? '<span class="dashicons dashicons-marker"></span>';
}
