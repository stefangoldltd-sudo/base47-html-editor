/***
 * Marketplace JavaScript - Soft UI Dashboard
 * 
 * @package Base47_HTML_Editor
 */

(function($) {
    'use strict';
    
    // State
    let templates = [];
    let currentFilters = {
        search: '',
        category: 'all',
        type: 'all',
        sort: 'popular'
    };
    let previewTemplateId = null;
    
    // DOM Elements
    const $templatesGrid = $('#templates-grid');
    const $templatesLoading = $('#templates-loading');
    const $templatesEmpty = $('#templates-empty');
    const $templatesCount = $('#templates-count');
    const $searchInput = $('#template-search');
    const $categorySelect = $('#template-category');
    const $typeSelect = $('#template-type');
    const $sortSelect = $('#template-sort');
    const $refreshBtn = $('#refresh-templates');
    const $previewModal = $('#preview-modal');
    
    /***
     * Initialize the marketplace
     */
    function init() {
        bindEvents();
        loadTemplates();
    }
    
    /***
     * Bind event listeners
     */
    function bindEvents() {
        // Filter events
        $searchInput.on('input', debounce(function() {
            currentFilters.search = $(this).val();
            filterAndRenderTemplates();
        }, 300));
        
        $categorySelect.on('change', function() {
            currentFilters.category = $(this).val();
            filterAndRenderTemplates();
        });
        
        $typeSelect.on('change', function() {
            currentFilters.type = $(this).val();
            filterAndRenderTemplates();
        });
        
        $sortSelect.on('change', function() {
            currentFilters.sort = $(this).val();
            filterAndRenderTemplates();
        });
        
        $refreshBtn.on('click', function() {
            loadTemplates();
        });
        
        // Template card events (delegated)
        $templatesGrid.on('click', '.btn-install', function() {
            const templateId = $(this).data('template-id');
            installTemplate(templateId, $(this));
        });
        
        $templatesGrid.on('click', '.btn-download', function() {
            const templateId = $(this).data('template-id');
            const $btn = $(this);
            const originalHtml = $btn.html();
            
            // Show loading state
            $btn.addClass('loading').html(`
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
            `);
            
            // Get download URL via AJAX
            $.ajax({
                url: base47HeAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'base47_he_download_marketplace_template',
                    template_id: templateId,
                    nonce: base47HeAdmin.nonce
                },
                success: function(response) {
                    $btn.removeClass('loading').html(originalHtml);
                    
                    if (response.success && response.data.download_url) {
                        // Create a temporary link and trigger download
                        const link = document.createElement('a');
                        link.href = response.data.download_url;
                        link.download = response.data.file_name || '';
                        link.target = '_blank';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        
                        showNotification('success', `Download started: ${response.data.file_name} (${formatFileSize(response.data.file_size)})`);
                    } else {
                        showNotification('error', response.data.message || 'Download failed');
                    }
                },
                error: function(xhr, status, error) {
                    $btn.removeClass('loading').html(originalHtml);
                    showNotification('error', 'Download failed: ' + error);
                }
            });
        });
        
        $templatesGrid.on('click', '.btn-preview', function() {
            const templateId = $(this).data('template-id');
            openPreview(templateId);
        });
        
        // Preview modal events
        $previewModal.find('.preview-backdrop, #preview-close-btn').on('click', closePreview);
        
        $previewModal.find('.device-btn').on('click', function() {
            const device = $(this).data('device');
            switchDevice(device);
        });
        
        $('#preview-install-btn').on('click', function() {
            if (previewTemplateId) {
                installTemplate(previewTemplateId, $(this));
            }
        });
        
        // Close modal on ESC
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $previewModal.is(':visible')) {
                closePreview();
            }
        });
    }
    
    /***
     * Load templates via AJAX
     */
    function loadTemplates() {
        showLoading();
        
        $.ajax({
            url: base47HeAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'base47_he_load_marketplace',
                nonce: base47HeAdmin.nonce,
                filters: currentFilters
            },
            success: function(response) {
                if (response.success && response.data.templates) {
                    templates = response.data.templates;
                    $templatesCount.text(templates.length);
                    filterAndRenderTemplates();
                } else {
                    showError('Failed to load templates');
                }
            },
            error: function() {
                showError('Network error. Please try again.');
            }
        });
    }
    
    /***
     * Filter and render templates
     */
    function filterAndRenderTemplates() {
        let filtered = [...templates];
        
        // Search filter
        if (currentFilters.search) {
            const search = currentFilters.search.toLowerCase();
            filtered = filtered.filter(t => 
                t.name.toLowerCase().includes(search) ||
                t.description.toLowerCase().includes(search) ||
                t.category.toLowerCase().includes(search)
            );
        }
        
        // Category filter
        if (currentFilters.category !== 'all') {
            filtered = filtered.filter(t => 
                t.category.toLowerCase().replace(/[- ]/g, '') === currentFilters.category.replace('-', '')
            );
        }
        
        // Type filter
        if (currentFilters.type !== 'all') {
            filtered = filtered.filter(t => t.type === currentFilters.type);
        }
        
        // Sort
        switch (currentFilters.sort) {
            case 'newest':
                filtered.reverse();
                break;
            case 'rating':
                filtered.sort((a, b) => b.rating - a.rating);
                break;
            case 'downloads':
                filtered.sort((a, b) => b.downloads - a.downloads);
                break;
            case 'popular':
            default:
                filtered.sort((a, b) => (b.downloads * b.rating) - (a.downloads * a.rating));
                break;
        }
        
        renderTemplates(filtered);
    }
    
    /***
     * Render templates to the grid
     */
    function renderTemplates(templateList) {
        hideLoading();
        
        if (templateList.length === 0) {
            $templatesGrid.hide();
            $templatesEmpty.show();
            return;
        }
        
        $templatesEmpty.hide();
        $templatesGrid.show();
        
        const html = templateList.map(template => createTemplateCard(template)).join('');
        $templatesGrid.html(html);
    }
    
    /***
     * Get placeholder SVG based on category
     */
    function getPlaceholderSvg(category) {
        const colors = {
            'business': '#667eea',
            'ecommerce': '#f97316',
            'fooddining': '#ef4444',
            'healthfitness': '#10b981',
            'realestate': '#f59e0b',
            'education': '#3b82f6',
            'technology': '#8b5cf6',
            'portfolio': '#ec4899'
        };
        
        const categoryKey = category.toLowerCase().replace(/[- &]/g, '');
        const color = colors[categoryKey] || '#667eea';
        const categoryName = category.replace(/[- ]/g, ' ');
        
        return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" style="width:100%;height:100%;">
            <defs>
                <linearGradient id="grad-${categoryKey}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:${color};stop-opacity:1" />
                    <stop offset="100%" style="stop-color:${color};stop-opacity:0.6" />
                </linearGradient>
            </defs>
            <rect width="400" height="300" fill="url(#grad-${categoryKey})"/>
            <text x="200" y="140" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="white" text-anchor="middle" opacity="0.9">
                ${escapeHtml(categoryName)}
            </text>
            <text x="200" y="170" font-family="Arial, sans-serif" font-size="16" fill="white" text-anchor="middle" opacity="0.7">
                Template Preview
            </text>
        </svg>`;
    }
    
    /***
     * Create HTML for a template card
     */
    function createTemplateCard(template) {
        const badgeClass = template.type === 'free' ? 'badge-free' : 'badge-premium';
        const badgeText = template.type === 'free' ? 'Free' : template.price;
        
        // Handle thumbnail with fallback
        let thumbnailHtml;
        if (template.thumbnail && template.thumbnail.startsWith('<svg')) {
            thumbnailHtml = template.thumbnail;
        } else if (template.thumbnail && template.thumbnail.trim() !== '') {
            thumbnailHtml = `<img src="${escapeHtml(template.thumbnail)}" alt="${escapeHtml(template.name)}" onerror="this.parentElement.innerHTML='${getPlaceholderSvg(template.category)}'">`;
        } else {
            thumbnailHtml = getPlaceholderSvg(template.category);
        }
        
        return `
            <div class="template-card" data-template-id="${escapeHtml(template.id)}">
                <div class="template-thumbnail">
                    ${thumbnailHtml}
                    <span class="badge ${badgeClass}">${escapeHtml(badgeText)}</span>
                    <div class="template-overlay">
                        <button type="button" class="btn btn-preview" data-template-id="${escapeHtml(template.id)}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            Preview
                        </button>
                    </div>
                </div>
                <div class="template-content">
                    <p class="template-category">${escapeHtml(template.category)}</p>
                    <h3 class="template-name">${escapeHtml(template.name)}</h3>
                    <p class="template-description">${escapeHtml(template.description)}</p>
                    <div class="template-stats">
                        <div class="template-rating">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            <span>${template.rating}</span>
                            <span class="reviews">(${template.reviews})</span>
                        </div>
                        <div class="template-downloads">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" x2="12" y1="15" y2="3"/>
                            </svg>
                            <span>${formatNumber(template.downloads)}</span>
                        </div>
                    </div>
                    <div class="template-actions">
                        <button type="button" class="btn btn-primary btn-install" data-template-id="${escapeHtml(template.id)}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" x2="12" y1="15" y2="3"/>
                            </svg>
                            Install
                        </button>
                        <button type="button" class="btn btn-secondary btn-download" data-template-id="${escapeHtml(template.id)}" title="Download ZIP file">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" x2="12" y1="15" y2="3"/>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-secondary btn-preview" data-template-id="${escapeHtml(template.id)}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    /***
     * Install a template
     */
    function installTemplate(templateId, $button) {
        const $btn = $button;
        const originalHtml = $btn.html();
        
        // Find the template
        const template = templates.find(t => t.id === templateId);
        if (!template) {
            showNotification('error', 'Template not found');
            return;
        }
        
        // Show loading state
        $btn.addClass('loading').html(`
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            Installing...
        `);
        
        // Install template via AJAX
        $.ajax({
            url: base47HeAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'base47_he_install_marketplace_template',
                template_id: templateId,
                nonce: base47HeAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $btn.removeClass('loading').html(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Installed!
                    `);
                    
                    showNotification('success', response.data.message || 'Template installed successfully!');
                    
                    // Reset button after 3 seconds
                    setTimeout(function() {
                        $btn.removeClass('loading').html(originalHtml);
                    }, 3000);
                } else {
                    $btn.removeClass('loading').html(originalHtml);
                    
                    let errorMessage = 'Installation failed';
                    if (response.data) {
                        if (typeof response.data === 'string') {
                            errorMessage = response.data;
                        } else if (response.data.message) {
                            errorMessage = response.data.message;
                        }
                        
                        // Log debug info to console for troubleshooting
                        if (response.data.debug) {
                            console.log('Installation Debug Info:', response.data.debug);
                        }
                    }
                    
                    showNotification('error', errorMessage);
                }
            },
            error: function(xhr, status, error) {
                $btn.removeClass('loading').html(originalHtml);
                
                let errorMessage = 'Installation failed: ' + error;
                
                // Try to get more detailed error from response
                if (xhr.responseJSON && xhr.responseJSON.data) {
                    if (typeof xhr.responseJSON.data === 'string') {
                        errorMessage = xhr.responseJSON.data;
                    } else if (xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    }
                    
                    // Log debug info
                    if (xhr.responseJSON.data.debug) {
                        console.log('Installation Error Debug:', xhr.responseJSON.data.debug);
                    }
                }
                
                showNotification('error', errorMessage);
            }
        });
    }
    
    /***
     * Open preview modal
     */
    function openPreview(templateId) {
        const template = templates.find(t => t.id === templateId);
        if (!template) return;
        
        // Show download message instead of preview
        showNotification('info', 'Preview not available. Download the template to view it locally.');
        
        // Optionally, trigger download automatically
        // installTemplate(templateId, $('.btn-install[data-template-id="' + templateId + '"]').first());
    }
    
    /***
     * Load preview URL via AJAX
     */
    function loadPreviewUrl(templateId) {
        const $iframe = $('#preview-iframe');
        $iframe.attr('src', 'about:blank');
        
        $.ajax({
            url: base47HeAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'base47_he_get_template_preview',
                nonce: base47HeAdmin.nonce,
                template_id: templateId
            },
            success: function(response) {
                if (response.success && response.data.preview_url) {
                    $iframe.attr('src', response.data.preview_url);
                }
            }
        });
    }
    
    /***
     * Close preview modal
     */
    function closePreview() {
        $previewModal.fadeOut(200);
        $('body').css('overflow', '');
        previewTemplateId = null;
        $('#preview-iframe').attr('src', 'about:blank');
    }
    
    /***
     * Switch preview device
     */
    function switchDevice(device) {
        const $wrapper = $('#preview-frame-wrapper');
        const $buttons = $previewModal.find('.device-btn');
        
        $buttons.removeClass('active');
        $buttons.filter(`[data-device="${device}"]`).addClass('active');
        
        $wrapper.removeClass('device-desktop device-tablet device-mobile');
        $wrapper.addClass(`device-${device}`);
    }
    
    /***
     * Show loading state
     */
    function showLoading() {
        $templatesGrid.hide();
        $templatesEmpty.hide();
        $templatesLoading.show();
    }
    
    /***
     * Hide loading state
     */
    function hideLoading() {
        $templatesLoading.hide();
    }
    
    /***
     * Show error message
     */
    function showError(message) {
        hideLoading();
        showNotification('error', message);
    }
    
    /***
     * Show notification as center modal
     */
    function showNotification(type, message) {
        // Remove any existing notifications
        $('.base47-notification-modal').remove();
        
        const iconSvg = type === 'error' ? 
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>' :
            type === 'success' ? 
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/></svg>' :
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>';
        
        const bgColor = type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#3b82f6';
        
        const modal = $(`
            <div class="base47-notification-modal" style="
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 999999;
                animation: fadeIn 0.3s ease;
            ">
                <div style="
                    background: white;
                    border-radius: 16px;
                    padding: 32px;
                    max-width: 400px;
                    margin: 20px;
                    box-shadow: 0 25px 50px rgba(0,0,0,0.25);
                    text-align: center;
                    animation: slideUp 0.3s ease;
                ">
                    <div style="
                        width: 64px;
                        height: 64px;
                        border-radius: 50%;
                        background: ${bgColor};
                        color: white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 20px;
                    ">
                        ${iconSvg}
                    </div>
                    <h3 style="
                        margin: 0 0 12px;
                        font-size: 20px;
                        font-weight: 600;
                        color: #1f2937;
                    ">${type === 'success' ? 'Success!' : type === 'error' ? 'Error' : 'Info'}</h3>
                    <p style="
                        margin: 0 0 24px;
                        color: #6b7280;
                        line-height: 1.5;
                    ">${escapeHtml(message)}</p>
                    <button class="notification-close-btn" style="
                        background: ${bgColor};
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.2s ease;
                    ">OK</button>
                </div>
            </div>
        `);
        
        // Add CSS animations
        if (!$('#base47-notification-styles').length) {
            $('head').append(`
                <style id="base47-notification-styles">
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    @keyframes slideUp {
                        from { transform: translateY(20px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                    .notification-close-btn:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    }
                </style>
            `);
        }
        
        $('body').append(modal);
        
        // Close on button click or backdrop click
        modal.find('.notification-close-btn').on('click', function() {
            modal.fadeOut(200, function() {
                $(this).remove();
            });
        });
        
        modal.on('click', function(e) {
            if (e.target === this) {
                modal.fadeOut(200, function() {
                    $(this).remove();
                });
            }
        });
        
        // Auto-close after 5 seconds for success/info
        if (type !== 'error') {
            setTimeout(function() {
                if (modal.is(':visible')) {
                    modal.fadeOut(200, function() {
                        $(this).remove();
                    });
                }
            }, 5000);
        }
    }
    
    /***
     * Utility: Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /***
     * Utility: Escape HTML
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /***
     * Utility: Format file size (bytes to human readable)
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    /***
     * Utility: Format number (1250 -> 1.2K)
     */
    function formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }
    
    // Initialize on document ready
    $(document).ready(init);
    
})(jQuery);
