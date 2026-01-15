<?php
/**
 * Tooltips & Help System
 * 
 * Contextual help tooltips throughout the plugin
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.3.14
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get tooltip content for specific context
 */
function base47_he_get_tooltip( $key ) {
    $tooltips = base47_he_get_all_tooltips();
    return $tooltips[ $key ] ?? null;
}

/**
 * Render tooltip HTML
 */
function base47_he_tooltip( $key, $position = 'top' ) {
    $tooltip = base47_he_get_tooltip( $key );
    if ( ! $tooltip ) return '';
    
    return sprintf(
        '<span class="base47-tooltip-trigger" data-tippy-content="%s" data-tippy-placement="%s">
            <span class="dashicons dashicons-editor-help"></span>
        </span>',
        esc_attr( $tooltip['content'] ),
        esc_attr( $position )
    );
}

/**
 * Render help box (larger help sections)
 */
function base47_he_help_box( $key ) {
    $tooltip = base47_he_get_tooltip( $key );
    if ( ! $tooltip || empty( $tooltip['help_box'] ) ) return '';
    
    return sprintf(
        '<div class="base47-help-box">
            <div class="help-box-header">
                <span class="dashicons dashicons-info"></span>
                <h4>%s</h4>
            </div>
            <div class="help-box-content">
                %s
            </div>
        </div>',
        esc_html( $tooltip['title'] ),
        wp_kses_post( $tooltip['help_box'] )
    );
}

/**
 * All tooltip content organized by context
 */
function base47_he_get_all_tooltips() {
    return [
        
        // ========================================
        // DASHBOARD PAGE
        // ========================================
        
        'dashboard_stats' => [
            'title' => 'Dashboard Statistics',
            'content' => 'Overview of your template sets, total templates, active sets, and special widgets.',
            'help_box' => '<p>These statistics give you a quick overview of your Base47 HTML Editor setup:</p>
                          <ul>
                            <li><strong>Theme Sets:</strong> Number of template collections installed</li>
                            <li><strong>Total Templates:</strong> All HTML templates across all sets</li>
                            <li><strong>Active Sets:</strong> Currently enabled template sets</li>
                            <li><strong>Special Widgets:</strong> Reusable components available</li>
                          </ul>'
        ],
        
        'dashboard_quick_actions' => [
            'title' => 'Quick Actions',
            'content' => 'Fast access to commonly used features like Live Editor, Theme Manager, and Shortcodes.',
        ],
        
        'dashboard_system_info' => [
            'title' => 'System Information',
            'content' => 'Important system details including WordPress version, PHP version, and plugin version.',
        ],
        
        // ========================================
        // LIVE EDITOR PAGE
        // ========================================
        
        'editor_template_selector' => [
            'title' => 'Template Selector',
            'content' => 'Choose which template to edit. Templates are organized by theme sets.',
            'help_box' => '<p>Select a template to edit from the dropdown menu. Templates are grouped by their theme sets.</p>
                          <p><strong>Tips:</strong></p>
                          <ul>
                            <li>Use the search box to quickly find templates</li>
                            <li>Recently edited templates appear at the top</li>
                            <li>Inactive theme sets are shown in gray</li>
                          </ul>'
        ],
        
        'editor_monaco' => [
            'title' => 'Monaco Editor',
            'content' => 'Professional code editor with syntax highlighting, auto-completion, and error detection.',
            'help_box' => '<p>The Monaco Editor provides a VS Code-like editing experience:</p>
                          <ul>
                            <li><strong>Syntax Highlighting:</strong> HTML, CSS, and JavaScript support</li>
                            <li><strong>Auto-completion:</strong> Intelligent code suggestions</li>
                            <li><strong>Error Detection:</strong> Real-time syntax error highlighting</li>
                            <li><strong>Keyboard Shortcuts:</strong> Ctrl+S to save, Ctrl+Z to undo</li>
                          </ul>'
        ],
        
        'editor_preview' => [
            'title' => 'Live Preview',
            'content' => 'See your changes in real-time. Preview updates automatically as you type.',
        ],
        
        'editor_save' => [
            'title' => 'Save Template',
            'content' => 'Save your changes to the template file. Changes are applied immediately.',
        ],
        
        'editor_duplicate' => [
            'title' => 'Duplicate Template',
            'content' => 'Create a copy of this template with a new name. Perfect for creating variations.',
        ],
        
        'editor_canvas_mode' => [
            'title' => 'Canvas Mode',
            'content' => 'Toggle between full page preview and isolated template preview.',
            'help_box' => '<p>Canvas Mode controls how your template is previewed:</p>
                          <ul>
                            <li><strong>Full Page:</strong> Shows template within your WordPress theme</li>
                            <li><strong>Canvas Only:</strong> Shows just the template HTML without theme wrapper</li>
                          </ul>
                          <p>Use Canvas Mode when you want to see the pure template output.</p>'
        ],
        
        // ========================================
        // SHORTCODES PAGE
        // ========================================
        
        'shortcodes_list' => [
            'title' => 'Shortcodes List',
            'content' => 'All available shortcodes generated from your templates. Click to copy to clipboard.',
        ],
        
        'shortcodes_search' => [
            'title' => 'Search Shortcodes',
            'content' => 'Quickly find shortcodes by template name, theme set, or shortcode text.',
        ],
        
        'shortcodes_copy' => [
            'title' => 'Copy Shortcode',
            'content' => 'Click any shortcode to copy it to your clipboard for use in posts and pages.',
        ],
        
        'shortcodes_preview' => [
            'title' => 'Preview Template',
            'content' => 'Click the eye icon to preview how the template looks before using it.',
        ],
        
        // ========================================
        // THEME MANAGER PAGE
        // ========================================
        
        'theme_manager_upload' => [
            'title' => 'Upload Theme Set',
            'content' => 'Upload a ZIP file containing HTML templates to create a new theme set.',
            'help_box' => '<p>Upload theme sets as ZIP files with this structure:</p>
                          <pre>theme-name/
├── index.html
├── about.html
├── contact.html
├── theme.json
└── assets/
    ├── css/
    └── js/</pre>
                          <p>The theme.json file should contain metadata about your theme set.</p>'
        ],
        
        'theme_manager_toggle' => [
            'title' => 'Activate/Deactivate Theme Set',
            'content' => 'Enable or disable theme sets. Only active sets generate shortcodes.',
        ],
        
        'theme_manager_delete' => [
            'title' => 'Delete Theme Set',
            'content' => 'Permanently remove a theme set and all its templates. This action cannot be undone.',
        ],
        
        'theme_manager_manifest' => [
            'title' => 'Manifest Mode',
            'content' => 'Advanced loading mode that optimizes performance by loading only required assets.',
            'help_box' => '<p>Manifest Mode provides better performance by:</p>
                          <ul>
                            <li>Loading only the CSS/JS files each template needs</li>
                            <li>Reducing page load times</li>
                            <li>Preventing asset conflicts</li>
                          </ul>
                          <p>Enable this for theme sets that include a manifest.json file.</p>'
        ],
        
        // ========================================
        // MARKETPLACE PAGE
        // ========================================
        
        'marketplace_browse' => [
            'title' => 'Browse Templates',
            'content' => 'Discover and install new template sets from the Base47 marketplace.',
        ],
        
        'marketplace_categories' => [
            'title' => 'Template Categories',
            'content' => 'Filter templates by category: Business, E-commerce, Portfolio, Landing Pages, etc.',
        ],
        
        'marketplace_install' => [
            'title' => 'Install Template',
            'content' => 'One-click installation of template sets directly from the marketplace.',
        ],
        
        'marketplace_preview' => [
            'title' => 'Preview Template',
            'content' => 'See a live preview of the template before installing it.',
        ],
        
        // ========================================
        // SETTINGS PAGE
        // ========================================
        
        'settings_editor_mode' => [
            'title' => 'Editor Mode',
            'content' => 'Choose between Basic (textarea) and Advanced (Monaco) editor modes.',
            'help_box' => '<p>Editor Mode Options:</p>
                          <ul>
                            <li><strong>Basic:</strong> Simple textarea editor, faster loading</li>
                            <li><strong>Advanced:</strong> Monaco editor with syntax highlighting and auto-completion</li>
                          </ul>
                          <p>Advanced mode requires more resources but provides a better editing experience.</p>'
        ],
        
        'settings_smart_loader' => [
            'title' => 'Smart Loader',
            'content' => 'Intelligent asset loading system that optimizes CSS and JavaScript delivery.',
            'help_box' => '<p>Smart Loader optimizes your site by:</p>
                          <ul>
                            <li>Loading assets only when needed</li>
                            <li>Combining multiple CSS/JS files</li>
                            <li>Minifying assets for faster loading</li>
                            <li>Caching processed assets</li>
                          </ul>
                          <p>Recommended for production sites.</p>'
        ],
        
        'settings_cache' => [
            'title' => 'Template Cache',
            'content' => 'Cache processed templates for better performance. Clear cache after making changes.',
        ],
        
        'settings_backup' => [
            'title' => 'Auto Backup',
            'content' => 'Automatically backup templates before editing. Helps prevent data loss.',
        ],
        
        // ========================================
        // SUPPORT PAGE
        // ========================================
        
        'support_ticket_category' => [
            'title' => 'Ticket Category',
            'content' => 'Choose the category that best describes your issue for faster support.',
            'help_box' => '<p>Category Guidelines:</p>
                          <ul>
                            <li><strong>Bug Report:</strong> Something is broken or not working</li>
                            <li><strong>Feature Request:</strong> Suggest new functionality</li>
                            <li><strong>Question:</strong> General questions about usage</li>
                            <li><strong>Installation:</strong> Help with plugin installation</li>
                            <li><strong>Configuration:</strong> Help with plugin setup</li>
                          </ul>'
        ],
        
        'support_priority' => [
            'title' => 'Priority Level',
            'content' => 'Set the urgency of your support request. Higher priority tickets get faster response.',
        ],
        
        'support_system_info' => [
            'title' => 'Include System Information',
            'content' => 'Include technical details about your setup to help diagnose issues faster.',
        ],
        
        // ========================================
        // SPECIAL WIDGETS PAGE
        // ========================================
        
        'widgets_create' => [
            'title' => 'Create Special Widget',
            'content' => 'Create reusable HTML components that can be used across multiple templates.',
        ],
        
        'widgets_shortcode' => [
            'title' => 'Widget Shortcode',
            'content' => 'Use this shortcode to insert the widget into any post, page, or template.',
        ],
        
        // ========================================
        // LICENSE PAGE
        // ========================================
        
        'license_key' => [
            'title' => 'License Key',
            'content' => 'Enter your Base47 HTML Editor Pro license key to unlock premium features.',
        ],
        
        'license_status' => [
            'title' => 'License Status',
            'content' => 'Shows whether your license is active, expired, or invalid.',
        ],
        
        // ========================================
        // UPGRADE PAGE
        // ========================================
        
        'upgrade_features' => [
            'title' => 'Pro Features',
            'content' => 'Premium features available in Base47 HTML Editor Pro version.',
        ],
        
        'upgrade_pricing' => [
            'title' => 'Pricing Plans',
            'content' => 'Choose the plan that best fits your needs. All plans include lifetime updates.',
        ],
        
        // ========================================
        // GENERAL TOOLTIPS
        // ========================================
        
        'pro_feature' => [
            'title' => 'Pro Feature',
            'content' => 'This feature is available in Base47 HTML Editor Pro. Upgrade to unlock.',
        ],
        
        'coming_soon' => [
            'title' => 'Coming Soon',
            'content' => 'This feature is in development and will be available in a future update.',
        ],
        
        'experimental' => [
            'title' => 'Experimental Feature',
            'content' => 'This feature is experimental and may change in future versions.',
        ],
        
        'performance_tip' => [
            'title' => 'Performance Tip',
            'content' => 'This setting affects site performance. Test thoroughly before using on production.',
        ],
        
        'security_note' => [
            'title' => 'Security Note',
            'content' => 'This setting affects site security. Only enable if you understand the implications.',
        ],
        
    ];
}

/**
 * Get contextual help for specific pages
 */
function base47_he_get_page_help( $page ) {
    $help_content = [
        
        'dashboard' => [
            'title' => 'Dashboard Help',
            'content' => '
                <h3>Welcome to Base47 HTML Editor</h3>
                <p>Your dashboard provides an overview of your template system and quick access to key features.</p>
                
                <h4>Getting Started</h4>
                <ol>
                    <li><strong>Install Templates:</strong> Go to Theme Manager to upload template sets</li>
                    <li><strong>Activate Sets:</strong> Enable the template sets you want to use</li>
                    <li><strong>Edit Templates:</strong> Use Live Editor to customize your templates</li>
                    <li><strong>Use Shortcodes:</strong> Copy shortcodes from the Shortcodes page</li>
                </ol>
                
                <h4>Quick Actions</h4>
                <p>Use the quick action buttons to jump directly to commonly used features:</p>
                <ul>
                    <li><strong>Live Editor:</strong> Edit templates with syntax highlighting</li>
                    <li><strong>Theme Manager:</strong> Install and manage template sets</li>
                    <li><strong>Shortcodes:</strong> Browse and copy shortcodes</li>
                    <li><strong>Settings:</strong> Configure plugin options</li>
                </ul>
            '
        ],
        
        'editor' => [
            'title' => 'Live Editor Help',
            'content' => '
                <h3>Live Editor</h3>
                <p>Edit your HTML templates with a professional code editor and live preview.</p>
                
                <h4>Editor Features</h4>
                <ul>
                    <li><strong>Syntax Highlighting:</strong> HTML, CSS, and JavaScript support</li>
                    <li><strong>Auto-completion:</strong> Intelligent code suggestions</li>
                    <li><strong>Error Detection:</strong> Real-time syntax checking</li>
                    <li><strong>Live Preview:</strong> See changes as you type</li>
                </ul>
                
                <h4>Keyboard Shortcuts</h4>
                <ul>
                    <li><strong>Ctrl+S:</strong> Save template</li>
                    <li><strong>Ctrl+Z:</strong> Undo changes</li>
                    <li><strong>Ctrl+Y:</strong> Redo changes</li>
                    <li><strong>Ctrl+F:</strong> Find text</li>
                    <li><strong>Ctrl+H:</strong> Find and replace</li>
                </ul>
                
                <h4>Canvas Mode</h4>
                <p>Toggle Canvas Mode to switch between:</p>
                <ul>
                    <li><strong>Full Page:</strong> Template within WordPress theme</li>
                    <li><strong>Canvas Only:</strong> Pure template HTML</li>
                </ul>
            '
        ],
        
        'shortcodes' => [
            'title' => 'Shortcodes Help',
            'content' => '
                <h3>Shortcodes</h3>
                <p>Browse and copy shortcodes for all your active templates.</p>
                
                <h4>Using Shortcodes</h4>
                <ol>
                    <li><strong>Find Template:</strong> Browse or search for the template you want</li>
                    <li><strong>Copy Shortcode:</strong> Click the shortcode to copy it</li>
                    <li><strong>Paste in Content:</strong> Add to any post, page, or widget</li>
                </ol>
                
                <h4>Shortcode Format</h4>
                <p>Shortcodes follow this pattern:</p>
                <code>[base47_template set="theme-name" template="template-name"]</code>
                
                <h4>Tips</h4>
                <ul>
                    <li>Use the search box to quickly find templates</li>
                    <li>Preview templates before using them</li>
                    <li>Only active theme sets show shortcodes</li>
                </ul>
            '
        ],
        
        'theme-manager' => [
            'title' => 'Theme Manager Help',
            'content' => '
                <h3>Theme Manager</h3>
                <p>Install, activate, and manage your HTML template sets.</p>
                
                <h4>Installing Theme Sets</h4>
                <ol>
                    <li><strong>Prepare ZIP:</strong> Create a ZIP file with your templates</li>
                    <li><strong>Upload:</strong> Use the upload form to install</li>
                    <li><strong>Activate:</strong> Enable the theme set to generate shortcodes</li>
                </ol>
                
                <h4>Theme Set Structure</h4>
                <pre>theme-name/
├── index.html
├── about.html
├── contact.html
├── theme.json (metadata)
└── assets/
    ├── css/style.css
    └── js/main.js</pre>
                
                <h4>Manifest Mode</h4>
                <p>Enable Manifest Mode for theme sets that include manifest.json for:</p>
                <ul>
                    <li>Better performance</li>
                    <li>Selective asset loading</li>
                    <li>Reduced conflicts</li>
                </ul>
            '
        ],
        
        'settings' => [
            'title' => 'Settings Help',
            'content' => '
                <h3>Settings</h3>
                <p>Configure Base47 HTML Editor options and performance settings.</p>
                
                <h4>Editor Settings</h4>
                <ul>
                    <li><strong>Editor Mode:</strong> Choose Basic or Advanced (Monaco) editor</li>
                    <li><strong>Theme:</strong> Light or dark editor theme</li>
                    <li><strong>Auto-save:</strong> Automatically save changes</li>
                </ul>
                
                <h4>Performance Settings</h4>
                <ul>
                    <li><strong>Smart Loader:</strong> Optimize asset loading</li>
                    <li><strong>Cache:</strong> Cache processed templates</li>
                    <li><strong>Minification:</strong> Minify CSS and JavaScript</li>
                </ul>
                
                <h4>Backup Settings</h4>
                <ul>
                    <li><strong>Auto Backup:</strong> Backup before editing</li>
                    <li><strong>Backup Retention:</strong> How long to keep backups</li>
                </ul>
            '
        ],
        
    ];
    
    return $help_content[ $page ] ?? null;
}

/**
 * Render contextual help sidebar
 */
function base47_he_render_help_sidebar( $page ) {
    $help = base47_he_get_page_help( $page );
    if ( ! $help ) return '';
    
    return sprintf(
        '<div class="base47-help-sidebar" id="base47-help-sidebar">
            <div class="help-sidebar-header">
                <h3>%s</h3>
                <button class="help-sidebar-close" onclick="base47ToggleHelpSidebar()">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="help-sidebar-content">
                %s
            </div>
        </div>
        <div class="help-sidebar-overlay" id="help-sidebar-overlay" onclick="base47ToggleHelpSidebar()"></div>',
        esc_html( $help['title'] ),
        wp_kses_post( $help['content'] )
    );
}

/**
 * Render help button for pages
 */
function base47_he_help_button( $page ) {
    $help = base47_he_get_page_help( $page );
    if ( ! $help ) return '';
    
    return '<button class="base47-help-button" onclick="base47ToggleHelpSidebar()" title="Show Help">
                <span class="dashicons dashicons-editor-help"></span>
                Help
            </button>';
}