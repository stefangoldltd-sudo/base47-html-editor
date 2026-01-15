/**
 * Marketplace JavaScript
 * 
 * Handles template loading, filtering, preview, and installation
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.4
 */

(function($) {
    'use strict';
    
    let allTemplates = [];
    let currentTemplate = null;
    
    // Initialize on document ready
    $(document).ready(function() {
        initMarketplace();
    });
    
    function initMarketplace() {
        // Load templates
        loadTemplates();
        
        // Filter events
        $('#template-search').on('input', filterTemplates);
        $('#template-category').on('change', filterTemplates);
        $('#template-type').on('change', filterTemplates);
        $('#template-sort').on('change', filterTemplates);
        $('#refresh-templates').on('click', loadTemplates);
        
        // Preview modal events
        $(document).on('click', '.btn-preview', handlePreview);
        $(document).on('click', '.btn-install', handleInstall);
        $('#preview-close, #preview-close-btn, .preview-modal-overlay').on('click', closePreview);
        
        // Device switcher
        $(document).on('click', '.device-btn', handleDeviceSwitch);
        
        // Preview install button
        $('#preview-install-btn').on('click', function() {
            if (currentTemplate) {
                installTemplate(currentTemplate);
            }
        });
    }
    
    function loadTemplates() {
        showLoading();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'base47_he_load_marketplace',
                nonce: base47HeAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    allTemplates = response.data;
                    updateTemplateCount(allTemplates.length);
                    filterTemplates();
                } else {
                    showError();
                }
            },
            error: function() {
                showError();
            }
        });
    }
    
    function filterTemplates() {
        const search = $('#template-search').val().toLowerCase();
        const category = $('#template-category').val();
        const type = $('#template-type').val();
        const sort = $('#template-sort').val();
        
        let filtered = allTemplates.filter(template => {
            const matchesSearch = !search || 
                template.name.toLowerCase().includes(search) ||
                template.description.toLowerCase().includes(search);
            const matchesCategory = !category || template.category === category;
            const matchesType = !type || template.type === type;
            
            return matchesSearch && matchesCategory && matchesType;
        });
        
        // Sort templates
        filtered = sortTemplates(filtered, sort);
        
        renderTemplates(filtered);
    }
    
    function sortTemplates(templates, sortBy) {
        const sorted = [...templates];
        
        switch(sortBy) {
            case 'popular':
                sorted.sort((a, b) => (b.downloads || 0) - (a.downloads || 0));
                break;
            case 'newest':
                sorted.sort((a, b) => (b.id || 0) - (a.id || 0));
                break;
            case 'downloads':
                sorted.sort((a, b) => (b.downloads || 0) - (a.downloads || 0));
                break;
            case 'name':
                sorted.sort((a, b) => a.name.localeCompare(b.name));
                break;
        }
        
        return sorted;
    }
    
    function renderTemplates(templates) {
        const $grid = $('#templates-grid');
        $grid.empty();
        
        hideLoading();
        
        if (templates.length === 0) {
            showEmpty();
            return;
        }
        
        hideEmpty();
        
        templates.forEach(template => {
            const card = createTemplateCard(template);
            $grid.append(card);
        });
    }
    
    function createTemplateCard(template) {
        const isFree = template.type === 'free';
        const price = isFree ? 'Free' : (template.price || '$49');
        const rating = template.rating || 4.5;
        const downloads = template.downloads || 0;
        const stars = generateStars(rating);
        
        return `
            <div class="template-card" data-template-id="${template.id}">
                <div class="template-thumbnail">
                    <img src="${template.thumbnail || 'https://via.placeholder.com/400x300'}" alt="${template.name}">
                    <span class="template-badge ${isFree ? 'badge-free' : 'badge-premium'}">${price}</span>
                    <div class="template-overlay">
                        <button class="btn-preview" data-template-id="${template.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Preview
                        </button>
                    </div>
                </div>
                <div class="template-content">
                    <div class="template-category">${template.category || 'General'}</div>
                    <h4 class="template-name">${template.name}</h4>
                    <p class="template-description">${template.description || 'Professional template for your website'}</p>
                    <div class="template-rating">
                        <div class="rating-stars">${stars}</div>
                        <span class="rating-count">${rating} (${template.reviews || 312})</span>
                    </div>
                    <div class="template-downloads">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        ${formatNumber(downloads)} downloads
                    </div>
                    <div class="template-actions">
                        <button class="btn-install" data-template-id="${template.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Install
                        </button>
                        <button class="btn-preview" data-template-id="${template.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Preview
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    function generateStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        let stars = '';
        
        for (let i = 0; i < fullStars; i++) {
            stars += '★';
        }
        
        if (hasHalfStar) {
            stars += '☆';
        }
        
        const emptyStars = 5 - Math.ceil(rating);
        for (let i = 0; i < emptyStars; i++) {
            stars += '☆';
        }
        
        return stars;
    }
    
    function formatNumber(num) {
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'k';
        }
        return num.toString();
    }
    
    function handlePreview(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const templateId = $(this).data('template-id');
        const template = allTemplates.find(t => t.id == templateId);
        
        if (!template) return;
        
        currentTemplate = template;
        
        // Update modal
        $('#preview-template-name').text(template.name);
        $('#preview-frame-wrapper').attr('src', template.preview_url || 'about:blank');
        
        // Show modal
        $('#preview-modal').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }
    
    function closePreview(e) {
        if (e.target !== e.currentTarget && !$(e.target).hasClass('btn-close')) return;
        
        $('#preview-modal').fadeOut(300);
        $('body').css('overflow', '');
        $('#preview-frame-wrapper').attr('src', 'about:blank');
        currentTemplate = null;
    }
    
    function handleDeviceSwitch() {
        const device = $(this).data('device');
        
        $('.device-btn').removeClass('active');
        $(this).addClass('active');
        
        const $iframe = $('#preview-frame-wrapper');
        $iframe.removeClass('tablet mobile');
        
        if (device === 'tablet') {
            $iframe.addClass('tablet');
        } else if (device === 'mobile') {
            $iframe.addClass('mobile');
        }
    }
    
    function handleInstall(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const templateId = $(this).data('template-id');
        const template = allTemplates.find(t => t.id == templateId);
        
        if (!template) return;
        
        installTemplate(template);
    }
    
    function installTemplate(template) {
        const $btn = $(`.btn-install[data-template-id="${template.id}"]`);
        const originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Installing...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'base47_he_install_marketplace_template',
                template_id: template.id,
                nonce: base47HeAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $btn.html('<span class="dashicons dashicons-yes"></span> Installed!');
                    
                    // Show success message
                    showNotice('success', 'Template installed successfully!');
                    
                    // Redirect if provided
                    if (response.data && response.data.redirect_url) {
                        setTimeout(() => {
                            window.location.href = response.data.redirect_url;
                        }, 1500);
                    }
                } else {
                    $btn.prop('disabled', false).html(originalText);
                    showNotice('error', response.data || 'Installation failed. Please try again.');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(originalText);
                showNotice('error', 'Installation failed. Please try again.');
            }
        });
    }
    
    function showLoading() {
        $('#templates-loading').show();
        $('#templates-grid').hide();
        $('#templates-empty').hide();
    }
    
    function hideLoading() {
        $('#templates-loading').hide();
        $('#templates-grid').show();
    }
    
    function showEmpty() {
        $('#templates-empty').show();
        $('#templates-grid').hide();
    }
    
    function hideEmpty() {
        $('#templates-empty').hide();
    }
    
    function showError() {
        hideLoading();
        showNotice('error', 'Failed to load templates. Please try again.');
    }
    
    function updateTemplateCount(count) {
        $('#templates-count').text(count);
    }
    
    function showNotice(type, message) {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const notice = $(`
            <div class="notice ${noticeClass} is-dismissible" style="margin: 1rem 0;">
                <p>${message}</p>
            </div>
        `);
        
        $('.base47-marketplace-wrap').prepend(notice);
        
        setTimeout(() => {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
})(jQuery);
