<?php
/**
 * Changelog Admin Page - Soft UI
 * 
 * Displays plugin changelog from changelog.txt with Soft UI design
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.7.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_changelog_page() {
    $file = BASE47_HE_PATH . 'changelog.txt';
    
    // Load and parse changelog
    if ( file_exists( $file ) ) {
        $content = file_get_contents( $file );
        $versions = base47_he_parse_changelog( $content );
    } else {
        // Fallback content
        $versions = [
            [
                'version' => '2.9.7',
                'date' => 'December 2024',
                'type' => 'feature',
                'changes' => [
                    'Shortcodes page Soft UI integration',
                    'Live search functionality',
                    'Toast notifications',
                    'Modern card design'
                ]
            ],
            [
                'version' => '2.9.6.5',
                'date' => 'November 2024',
                'type' => 'feature',
                'changes' => [
                    'Theme Manager Soft UI redesign',
                    'Metadata detection engine',
                    'Upload validation system'
                ]
            ]
        ];
    }
    
    ?>
    <div class="wrap base47-changelog-soft-ui">
        
        <!-- SOFT UI HEADER -->
        <div class="base47-changelog-header-soft">
            <h1>Version History</h1>
            <p>Track all updates, improvements, and fixes across Base47 HTML Editor releases.</p>
        </div>

        <!-- VERSION COUNT -->
        <div class="base47-changelog-stats">
            <div class="base47-changelog-stat-card">
                <span class="dashicons dashicons-update"></span>
                <div>
                    <div class="stat-number"><?php echo count( $versions ); ?></div>
                    <div class="stat-label">Versions</div>
                </div>
            </div>
            <div class="base47-changelog-stat-card">
                <span class="dashicons dashicons-admin-tools"></span>
                <div>
                    <div class="stat-number"><?php echo BASE47_HE_VERSION; ?></div>
                    <div class="stat-label">Current</div>
                </div>
            </div>
        </div>

        <!-- CHANGELOG ACCORDION -->
        <div class="base47-changelog-accordion">
            <?php foreach ( $versions as $index => $version ) : 
                $is_current = ( $version['version'] === BASE47_HE_VERSION );
                $type_class = 'type-' . ( $version['type'] ?? 'update' );
            ?>
                <div class="base47-changelog-item <?php echo $is_current ? 'is-current' : ''; ?>">
                    
                    <!-- Version Header -->
                    <div class="base47-changelog-item-header" data-index="<?php echo $index; ?>">
                        <div class="version-info">
                            <span class="version-badge <?php echo esc_attr( $type_class ); ?>">
                                v<?php echo esc_html( $version['version'] ); ?>
                            </span>
                            <?php if ( $is_current ) : ?>
                                <span class="current-badge">Current</span>
                            <?php endif; ?>
                            <?php if ( ! empty( $version['date'] ) ) : ?>
                                <span class="version-date">
                                    <span class="dashicons dashicons-calendar-alt"></span>
                                    <?php echo esc_html( $version['date'] ); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>

                    <!-- Version Content -->
                    <div class="base47-changelog-item-content" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                        <?php if ( ! empty( $version['changes'] ) ) : ?>
                            <ul class="changes-list">
                                <?php foreach ( $version['changes'] as $change ) : ?>
                                    <li><?php echo esc_html( $change ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- FOOTER NOTE -->
        <div class="base47-changelog-footer">
            <p>
                <span class="dashicons dashicons-info"></span>
                For detailed technical changes, see the documentation files in the plugin folder.
            </p>
        </div>

    </div>

    <script>
    jQuery(document).ready(function($) {
        // Toggle accordion items
        $('.base47-changelog-item-header').on('click', function() {
            var $item = $(this).closest('.base47-changelog-item');
            var $content = $item.find('.base47-changelog-item-content');
            var $icon = $(this).find('.toggle-icon');
            
            // Toggle current item
            $content.slideToggle(300);
            $icon.toggleClass('rotated');
            $item.toggleClass('is-open');
        });
    });
    </script>
    <?php
}

/**
 * Parse changelog.txt into structured array
 * 
 * @param string $content Raw changelog content
 * @return array Parsed versions
 */
function base47_he_parse_changelog( $content ) {
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
            
            // Determine version type
            $type = 'update';
            if ( stripos( $description, 'hotfix' ) !== false || stripos( $description, 'fix' ) !== false ) {
                $type = 'hotfix';
            } elseif ( stripos( $description, 'feature' ) !== false || stripos( $description, 'new' ) !== false || stripos( $description, 'phase' ) !== false ) {
                $type = 'feature';
            }
            
            $current_version = [
                'version' => $version_num,
                'date' => $description,
                'type' => $type,
                'changes' => []
            ];
        } elseif ( $current_version !== null ) {
            // Check for bullet points or changes
            if ( preg_match( '/^[•\-\*]\s*(.+)$/', $line, $matches ) ) {
                $current_version['changes'][] = $matches[1];
            } elseif ( !empty( $line ) && !preg_match( '/^[A-Z\s]+:$/', $line ) ) {
                // Add as regular change if not a section header
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
