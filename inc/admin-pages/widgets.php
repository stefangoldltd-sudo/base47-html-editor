<?php
/**
 * Special Widgets Admin Page - Soft UI
 * 
 * Lists all special widgets with shortcodes
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.7.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_special_widgets_page() {
    $widgets = base47_he_get_special_widgets_registry();
    $widget_count = count( $widgets );
    ?>
    <div class="wrap base47-widgets-soft-ui">
        
        <!-- Page Header -->
        <div class="base47-widgets-header-soft">
            <h1>Special Widgets</h1>
            <p>Reusable components ready to insert into your templates</p>
        </div>

        <!-- Stats Card -->
        <div class="base47-widgets-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-admin-plugins"></span>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo esc_html( $widget_count ); ?></div>
                    <div class="stat-label">Available Widgets</div>
                </div>
            </div>
        </div>

        <?php if ( empty( $widgets ) ) : ?>

            <!-- Empty State -->
            <div class="base47-widgets-empty">
                <div class="empty-icon">
                    <span class="dashicons dashicons-admin-plugins"></span>
                </div>
                <h3>No Special Widgets Found</h3>
                <p>To add a widget, create a folder in <code>special-widgets/</code> with a <code>widget.json</code> file.</p>
                <div class="empty-example">
                    <strong>Example structure:</strong>
                    <pre>special-widgets/
  my-widget/
    widget.json
    my-widget.html
    style.css
    script.js</pre>
                </div>
            </div>

        <?php else : ?>

            <!-- Widgets Grid -->
            <div class="base47-widgets-grid">
                <?php
                $plugin_url = plugin_dir_url( BASE47_HE_PATH . 'base47-html-editor.php' );
                foreach ( $widgets as $w ) :
                    $folder    = $w['folder'];
                    $html      = $w['html'];
                    $name      = $w['name'];
                    $desc      = $w['description'];
                    $slug      = $w['slug'];
                    $shortcode = '[base47_widget slug="' . esc_attr( $slug ) . '"]';
                    $preview   = $plugin_url . 'special-widgets/' . $folder . '/' . $html;
                    
                    // Determine widget type/category from name or slug
                    $type = 'Component';
                    if ( stripos( $name, 'hero' ) !== false || stripos( $slug, 'hero' ) !== false ) {
                        $type = 'Hero';
                    } elseif ( stripos( $name, 'slider' ) !== false || stripos( $slug, 'slider' ) !== false ) {
                        $type = 'Slider';
                    } elseif ( stripos( $name, 'contact' ) !== false || stripos( $slug, 'contact' ) !== false ) {
                        $type = 'Contact';
                    } elseif ( stripos( $name, 'form' ) !== false || stripos( $slug, 'form' ) !== false ) {
                        $type = 'Form';
                    }
                ?>
                    <div class="widget-card">
                        <div class="widget-header">
                            <div class="widget-icon">
                                <span class="dashicons dashicons-admin-customizer"></span>
                            </div>
                            <div class="widget-type-badge"><?php echo esc_html( $type ); ?></div>
                        </div>
                        
                        <div class="widget-body">
                            <h3 class="widget-name"><?php echo esc_html( $name ); ?></h3>
                            <p class="widget-description">
                                <?php echo esc_html( $desc ? $desc : 'A reusable widget component for your templates.' ); ?>
                            </p>
                            
                            <div class="widget-shortcode">
                                <label>Shortcode:</label>
                                <div class="shortcode-display">
                                    <code><?php echo esc_html( $shortcode ); ?></code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="widget-footer">
                            <button class="btn-widget-copy" data-shortcode="<?php echo esc_attr( $shortcode ); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                                Copy Shortcode
                            </button>
                            <a href="<?php echo esc_url( $preview ); ?>" 
                               target="_blank" 
                               class="btn-widget-preview">
                                <span class="dashicons dashicons-visibility"></span>
                                Open Example
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer Note -->
            <div class="base47-widgets-footer-note">
                <span class="dashicons dashicons-info"></span>
                Widgets are auto-discovered from <code>special-widgets/</code> folder. Only folders with <code>widget.json</code> are shown.
            </div>

        <?php endif; ?>

    </div>

    <script>
    jQuery(document).ready(function($) {
        // Copy shortcode to clipboard
        $('.btn-widget-copy').on('click', function(e) {
            e.preventDefault();
            
            var shortcode = $(this).data('shortcode');
            var $btn = $(this);
            
            // Create temporary input
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(shortcode).select();
            document.execCommand('copy');
            $temp.remove();
            
            // Show success feedback
            var originalHtml = $btn.html();
            $btn.html('<span class="dashicons dashicons-yes"></span> Copied!');
            $btn.addClass('copied');
            
            setTimeout(function() {
                $btn.html(originalHtml);
                $btn.removeClass('copied');
            }, 2000);
            
            // Show toast notification
            showToast('Shortcode Copied!', 'success');
        });
        
        // Toast notification function
        function showToast(message, type) {
            var toast = $('<div class="base47-toast base47-toast-' + type + '">' + message + '</div>');
            $('body').append(toast);
            
            setTimeout(function() {
                toast.addClass('show');
            }, 100);
            
            setTimeout(function() {
                toast.removeClass('show');
                setTimeout(function() {
                    toast.remove();
                }, 300);
            }, 2000);
        }
    });
    </script>
    <?php
}
