/**
 * Tooltips & Help System JavaScript
 * 
 * Initialize tooltips and help sidebar functionality
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.3.14
 */

// Global help sidebar state
let helpSidebarActive = false;

jQuery(document).ready(function($) {
    
    // Initialize tooltips system
    initTooltipsSystem();
    
    function initTooltipsSystem() {
        // Load Tippy.js if not already loaded
        loadTippyJS(function() {
            initTooltips();
        });
        
        // Initialize help sidebar
        initHelpSidebar();
        
        // Initialize keyboard shortcuts
        initHelpKeyboardShortcuts();
        
        // Initialize contextual help
        initContextualHelp();
    }
    
    /**
     * Load Tippy.js library
     */
    function loadTippyJS(callback) {
        // Check if Tippy is already loaded
        if (typeof tippy !== 'undefined') {
            callback();
            return;
        }
        
        // Load Tippy.js CSS
        if (!$('link[href*="tippy"]').length) {
            $('<link>')
                .attr('rel', 'stylesheet')
                .attr('href', 'https://unpkg.com/tippy.js@6/dist/tippy.css')
                .appendTo('head');
        }
        
        // Load Tippy.js JavaScript
        if (!window.tippy) {
            $.getScript('https://unpkg.com/@popperjs/core@2')
                .done(function() {
                    $.getScript('https://unpkg.com/tippy.js@6')
                        .done(function() {
                            callback();
                        })
                        .fail(function() {
                            console.warn('Failed to load Tippy.js');
                            // Fallback to native tooltips
                            initNativeTooltips();
                        });
                })
                .fail(function() {
                    console.warn('Failed to load Popper.js');
                    initNativeTooltips();
                });
        } else {
            callback();
        }
    }
    
    /**
     * Initialize Tippy.js tooltips
     */
    function initTooltips() {
        // Initialize tooltips with Base47 theme
        tippy('.base47-tooltip-trigger', {
            theme: 'base47',
            placement: 'top',
            arrow: true,
            animation: 'fade',
            duration: [200, 150],
            delay: [500, 0],
            interactive: false,
            maxWidth: 300,
            zIndex: 999999,
            appendTo: document.body,
            onShow(instance) {
                // Track tooltip usage for analytics
                trackTooltipUsage(instance.reference);
            }
        });
        
        // Initialize tooltips for elements with data-tippy-content
        tippy('[data-tippy-content]', {
            theme: 'base47',
            placement: 'top',
            arrow: true,
            animation: 'fade',
            duration: [200, 150],
            delay: [500, 0],
            interactive: false,
            maxWidth: 300,
            zIndex: 999999,
            appendTo: document.body
        });
        
        // Initialize tooltips for Pro features
        tippy('.pro-feature', {
            content: 'This feature is available in Base47 HTML Editor Pro. Upgrade to unlock premium features.',
            theme: 'base47',
            placement: 'top',
            arrow: true,
            animation: 'fade',
            duration: [200, 150],
            delay: [500, 0],
            maxWidth: 250
        });
        
        // Initialize tooltips for coming soon features
        tippy('.coming-soon', {
            content: 'This feature is coming soon in a future update.',
            theme: 'base47',
            placement: 'top',
            arrow: true,
            animation: 'fade',
            duration: [200, 150],
            delay: [500, 0],
            maxWidth: 200
        });
        
        console.log('Base47 tooltips initialized with Tippy.js');
    }
    
    /**
     * Fallback to native tooltips if Tippy.js fails to load
     */
    function initNativeTooltips() {
        $('.base47-tooltip-trigger').each(function() {
            const $trigger = $(this);
            const content = $trigger.attr('data-tippy-content');
            
            if (content) {
                $trigger.attr('title', content);
            }
        });
        
        console.log('Base47 tooltips initialized with native tooltips');
    }
    
    /**
     * Initialize help sidebar
     */
    function initHelpSidebar() {
        // Create help sidebar if it doesn't exist
        if (!$('#base47-help-sidebar').length) {
            // Help sidebar will be rendered by PHP when needed
            return;
        }
        
        // Handle sidebar close button
        $(document).on('click', '.help-sidebar-close', function(e) {
            e.preventDefault();
            base47ToggleHelpSidebar();
        });
        
        // Handle overlay click
        $(document).on('click', '.help-sidebar-overlay', function(e) {
            e.preventDefault();
            base47ToggleHelpSidebar();
        });
        
        // Handle escape key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27 && helpSidebarActive) { // Escape key
                base47ToggleHelpSidebar();
            }
        });
    }
    
    /**
     * Initialize keyboard shortcuts for help
     */
    function initHelpKeyboardShortcuts() {
        $(document).on('keydown', function(e) {
            // F1 key or Ctrl+? to toggle help
            if (e.keyCode === 112 || (e.ctrlKey && e.shiftKey && e.keyCode === 191)) {
                e.preventDefault();
                base47ToggleHelpSidebar();
            }
            
            // Ctrl+H for help (if not in editor)
            if (e.ctrlKey && e.keyCode === 72 && !$(e.target).closest('.monaco-editor').length) {
                e.preventDefault();
                base47ToggleHelpSidebar();
            }
        });
    }
    
    /**
     * Initialize contextual help
     */
    function initContextualHelp() {
        // Add help indicators to complex forms
        addHelpIndicators();
        
        // Initialize smart help suggestions
        initSmartHelp();
        
        // Initialize help search
        initHelpSearch();
    }
    
    /**
     * Add help indicators to complex elements
     */
    function addHelpIndicators() {
        // Add indicators to settings that affect performance
        $('.performance-setting').addClass('base47-help-indicator');
        
        // Add indicators to Pro features
        $('.pro-only').addClass('base47-help-indicator');
        
        // Add indicators to experimental features
        $('.experimental').addClass('base47-help-indicator');
    }
    
    /**
     * Initialize smart help suggestions
     */
    function initSmartHelp() {
        // Show contextual help based on user actions
        
        // Help for first-time users
        if (isFirstTimeUser()) {
            showWelcomeHelp();
        }
        
        // Help for common issues
        detectCommonIssues();
        
        // Help for advanced features
        suggestAdvancedFeatures();
    }
    
    /**
     * Initialize help search functionality
     */
    function initHelpSearch() {
        // Add search box to help sidebar if it exists
        const $helpContent = $('.help-sidebar-content');
        if ($helpContent.length) {
            const searchBox = `
                <div class="help-search-box" style="margin-bottom: 1rem;">
                    <input type="text" id="help-search" placeholder="Search help content..." 
                           style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.5rem;">
                </div>
            `;
            $helpContent.prepend(searchBox);
            
            // Handle search
            $('#help-search').on('input', function() {
                const query = $(this).val().toLowerCase();
                searchHelpContent(query);
            });
        }
    }
    
    /**
     * Search help content
     */
    function searchHelpContent(query) {
        const $content = $('.help-sidebar-content');
        const $sections = $content.find('h3, h4, p, li');
        
        if (!query) {
            $sections.show();
            return;
        }
        
        $sections.each(function() {
            const $section = $(this);
            const text = $section.text().toLowerCase();
            
            if (text.includes(query)) {
                $section.show();
                // Highlight matching text
                highlightText($section, query);
            } else {
                $section.hide();
            }
        });
    }
    
    /**
     * Highlight matching text
     */
    function highlightText($element, query) {
        const text = $element.text();
        const regex = new RegExp(`(${query})`, 'gi');
        const highlighted = text.replace(regex, '<mark>$1</mark>');
        $element.html(highlighted);
    }
    
    /**
     * Check if user is first-time user
     */
    function isFirstTimeUser() {
        return !localStorage.getItem('base47_help_seen');
    }
    
    /**
     * Show welcome help for first-time users
     */
    function showWelcomeHelp() {
        // Mark as seen
        localStorage.setItem('base47_help_seen', 'true');
        
        // Show welcome tooltip or modal
        setTimeout(function() {
            if (typeof tippy !== 'undefined') {
                const welcomeTooltip = tippy(document.body, {
                    content: `
                        <div style="text-align: center;">
                            <h4 style="margin: 0 0 0.5rem 0;">Welcome to Base47 HTML Editor!</h4>
                            <p style="margin: 0 0 1rem 0;">Click the help button (?) on any page for contextual assistance.</p>
                            <button onclick="this.closest('.tippy-box')._tippy.hide()" 
                                    style="background: #f97316; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer;">
                                Got it!
                            </button>
                        </div>
                    `,
                    theme: 'base47-light',
                    placement: 'bottom',
                    arrow: true,
                    interactive: true,
                    showOnCreate: true,
                    hideOnClick: true,
                    duration: [300, 200],
                    maxWidth: 300
                });
                
                // Auto-hide after 10 seconds
                setTimeout(function() {
                    welcomeTooltip.hide();
                }, 10000);
            }
        }, 2000);
    }
    
    /**
     * Detect common issues and suggest help
     */
    function detectCommonIssues() {
        // Check for empty template sets
        if ($('.theme-set-empty').length) {
            showHelpSuggestion('empty-templates', 'No templates found. Upload template sets in Theme Manager.');
        }
        
        // Check for inactive sets
        if ($('.theme-set-inactive').length > $('.theme-set-active').length) {
            showHelpSuggestion('inactive-sets', 'Most of your template sets are inactive. Activate them to use their shortcodes.');
        }
        
        // Check for performance issues
        if ($('.performance-warning').length) {
            showHelpSuggestion('performance', 'Performance issues detected. Check Settings for optimization options.');
        }
    }
    
    /**
     * Suggest advanced features
     */
    function suggestAdvancedFeatures() {
        // Suggest Pro features to free users
        if (!$('body').hasClass('base47-pro-active')) {
            setTimeout(function() {
                showHelpSuggestion('pro-features', 'Unlock advanced features with Base47 HTML Editor Pro.');
            }, 30000); // Show after 30 seconds
        }
        
        // Suggest marketplace to users with few templates
        if ($('.template-count').text() < 10) {
            setTimeout(function() {
                showHelpSuggestion('marketplace', 'Discover more templates in the Marketplace.');
            }, 60000); // Show after 1 minute
        }
    }
    
    /**
     * Show help suggestion
     */
    function showHelpSuggestion(type, message) {
        // Don't show if already dismissed
        if (localStorage.getItem(`base47_help_dismissed_${type}`)) {
            return;
        }
        
        const $suggestion = $(`
            <div class="base47-help-suggestion" data-type="${type}" style="
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 0.5rem;
                padding: 1rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                max-width: 300px;
                z-index: 999999;
                animation: slideInUp 0.3s ease;
            ">
                <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                    <span class="dashicons dashicons-lightbulb" style="color: #f97316; margin-top: 0.125rem;"></span>
                    <div style="flex: 1;">
                        <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">${message}</p>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="help-suggestion-dismiss" style="
                                background: none;
                                border: 1px solid #ddd;
                                padding: 0.25rem 0.5rem;
                                border-radius: 0.25rem;
                                font-size: 0.75rem;
                                cursor: pointer;
                            ">Dismiss</button>
                            <button class="help-suggestion-learn" style="
                                background: #f97316;
                                color: white;
                                border: none;
                                padding: 0.25rem 0.5rem;
                                border-radius: 0.25rem;
                                font-size: 0.75rem;
                                cursor: pointer;
                            ">Learn More</button>
                        </div>
                    </div>
                    <button class="help-suggestion-close" style="
                        background: none;
                        border: none;
                        font-size: 1rem;
                        cursor: pointer;
                        padding: 0;
                        color: #999;
                    ">&times;</button>
                </div>
            </div>
        `);
        
        $('body').append($suggestion);
        
        // Handle dismiss
        $suggestion.find('.help-suggestion-dismiss, .help-suggestion-close').on('click', function() {
            localStorage.setItem(`base47_help_dismissed_${type}`, 'true');
            $suggestion.fadeOut(function() {
                $(this).remove();
            });
        });
        
        // Handle learn more
        $suggestion.find('.help-suggestion-learn').on('click', function() {
            base47ToggleHelpSidebar();
            $suggestion.fadeOut(function() {
                $(this).remove();
            });
        });
        
        // Auto-hide after 15 seconds
        setTimeout(function() {
            $suggestion.fadeOut(function() {
                $(this).remove();
            });
        }, 15000);
    }
    
    /**
     * Track tooltip usage for analytics
     */
    function trackTooltipUsage(element) {
        // Track which tooltips are most used
        const tooltipKey = $(element).closest('[data-tooltip-key]').attr('data-tooltip-key') || 'unknown';
        
        // Store in localStorage for now (could send to analytics service)
        const usage = JSON.parse(localStorage.getItem('base47_tooltip_usage') || '{}');
        usage[tooltipKey] = (usage[tooltipKey] || 0) + 1;
        localStorage.setItem('base47_tooltip_usage', JSON.stringify(usage));
    }
    
    /**
     * Add CSS animations
     */
    function addAnimations() {
        const animations = `
            <style>
                @keyframes slideInUp {
                    from {
                        transform: translateY(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                @keyframes pulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                }
            </style>
        `;
        $('head').append(animations);
    }
    
    // Initialize animations
    addAnimations();
    
    // Refresh tooltips when content changes
    $(document).on('base47ContentUpdated', function() {
        if (typeof tippy !== 'undefined') {
            // Destroy existing tooltips
            $('.base47-tooltip-trigger').each(function() {
                if (this._tippy) {
                    this._tippy.destroy();
                }
            });
            
            // Reinitialize tooltips
            setTimeout(initTooltips, 100);
        }
    });
    
});

/**
 * Global function to toggle help sidebar
 */
function base47ToggleHelpSidebar() {
    const sidebar = document.getElementById('base47-help-sidebar');
    const overlay = document.getElementById('help-sidebar-overlay');
    
    if (!sidebar) return;
    
    helpSidebarActive = !helpSidebarActive;
    
    if (helpSidebarActive) {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Global function to show specific help topic
 */
function base47ShowHelp(topic) {
    // Open help sidebar and scroll to topic
    if (!helpSidebarActive) {
        base47ToggleHelpSidebar();
    }
    
    setTimeout(function() {
        const element = document.querySelector(`[data-help-topic="${topic}"]`);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }, 300);
}