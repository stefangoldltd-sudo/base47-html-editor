<?php
/**
 * Marketplace Admin Page
 * 
 * Browse and install templates from base47.com marketplace
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_marketplace_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap base47-he-wrap">
        <h1>Template Marketplace</h1>
        <p>Browse and install professional templates from the Base47 marketplace at 47-studio.com.</p>
        
        <!-- Marketplace Filters -->
        <div class="base47-marketplace-filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="marketplace-search">Search Templates:</label>
                    <input type="text" id="marketplace-search" placeholder="Search by name, category, or tags..." class="regular-text">
                </div>
                
                <div class="filter-group">
                    <label for="marketplace-category">Category:</label>
                    <select id="marketplace-category">
                        <option value="">All Categories</option>
                        <option value="business">Business</option>
                        <option value="ecommerce">E-commerce</option>
                        <option value="restaurant">Restaurant</option>
                        <option value="fitness">Fitness</option>
                        <option value="realestate">Real Estate</option>
                        <option value="education">Education</option>
                        <option value="app">App Landing</option>
                        <option value="event">Event</option>
                        <option value="medical">Medical</option>
                        <option value="portfolio">Portfolio</option>
                        <option value="blog">Blog</option>
                        <option value="nonprofit">Non-Profit</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="marketplace-type">Type:</label>
                    <select id="marketplace-type">
                        <option value="">All Types</option>
                        <option value="free">Free</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="marketplace-sort">Sort By:</label>
                    <select id="marketplace-sort">
                        <option value="popular">Most Popular</option>
                        <option value="newest">Newest First</option>
                        <option value="rating">Highest Rated</option>
                        <option value="name">Name A-Z</option>
                        <option value="downloads">Most Downloads</option>
                    </select>
                </div>
                
                <button id="marketplace-apply-filters" class="button button-primary">Apply Filters</button>
                <button id="marketplace-refresh" class="button">🔄 Refresh</button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="marketplace-loading" class="marketplace-loading" style="display: none;">
            <div class="loading-spinner"></div>
            <p>Loading templates from marketplace...</p>
        </div>

        <!-- Error State -->
        <div id="marketplace-error" class="marketplace-error" style="display: none;">
            <div class="error-icon">⚠️</div>
            <h3>Unable to Connect to Marketplace</h3>
            <p>Please check your internet connection and try again.</p>
            <button id="marketplace-retry" class="button button-primary">Retry</button>
        </div>

        <!-- Templates Grid -->
        <div id="marketplace-grid" class="marketplace-grid">
            <!-- Templates will be loaded here via AJAX -->
        </div>

        <!-- Pagination -->
        <div id="marketplace-pagination" class="marketplace-pagination" style="display: none;">
            <button id="marketplace-prev" class="button" disabled>← Previous</button>
            <span id="marketplace-page-info">Page 1 of 1</span>
            <button id="marketplace-next" class="button" disabled>Next →</button>
        </div>
    </div>

    <!-- Template Preview Modal -->
    <div id="marketplace-preview-modal" class="base47-he-modal" style="display: none;">
        <div class="base47-he-modal-content marketplace-preview-content">
            <div class="base47-he-modal-header">
                <h2 id="preview-template-name">Template Preview</h2>
                <span class="base47-he-modal-close">&times;</span>
            </div>
            <div class="base47-he-modal-body">
                <div class="preview-toolbar">
                    <button class="preview-size-btn active" data-size="desktop">🖥️ Desktop</button>
                    <button class="preview-size-btn" data-size="tablet">📱 Tablet</button>
                    <button class="preview-size-btn" data-size="mobile">📱 Mobile</button>
                </div>
                <div class="preview-container">
                    <iframe id="marketplace-preview-iframe" src="" frameborder="0"></iframe>
                </div>
            </div>
            <div class="base47-he-modal-footer">
                <button id="preview-install-template" class="button button-primary">Install Template</button>
                <button class="button base47-he-modal-close">Close Preview</button>
            </div>
        </div>
    </div>

    <!-- Template Details Modal -->
    <div id="marketplace-details-modal" class="base47-he-modal" style="display: none;">
        <div class="base47-he-modal-content marketplace-details-content">
            <div class="base47-he-modal-header">
                <h2 id="details-template-name">Template Details</h2>
                <span class="base47-he-modal-close">&times;</span>
            </div>
            <div class="base47-he-modal-body">
                <div id="template-details-content">
                    <!-- Template details will be loaded here -->
                </div>
            </div>
            <div class="base47-he-modal-footer">
                <button id="details-install-template" class="button button-primary">Install Template</button>
                <button id="details-preview-template" class="button">Preview</button>
                <button class="button base47-he-modal-close">Close</button>
            </div>
        </div>
    </div>

    <style>
    /* Marketplace Styles */
    .base47-marketplace-filters {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
    }
    
    .filter-row {
        display: flex;
        gap: 15px;
        align-items: end;
        flex-wrap: wrap;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .filter-group label {
        font-weight: 600;
        font-size: 13px;
        color: #374151;
    }
    
    .filter-group input,
    .filter-group select {
        padding: 8px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    /* Loading State */
    .marketplace-loading {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #e5e7eb;
        border-top: 4px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Error State */
    .marketplace-error {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }
    
    .error-icon {
        font-size: 48px;
        margin-bottom: 20px;
    }
    
    /* Templates Grid */
    .marketplace-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .marketplace-template-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .marketplace-template-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border-color: #3b82f6;
    }
    
    .template-thumbnail {
        position: relative;
        height: 200px;
        background: #f3f4f6;
        overflow: hidden;
    }
    
    .template-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .template-type-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .template-type-badge.free {
        background: #22c55e;
        color: white;
    }
    
    .template-type-badge.premium {
        background: #f59e0b;
        color: white;
    }
    
    .template-actions {
        position: absolute;
        top: 12px;
        right: 12px;
        display: flex;
        gap: 8px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .marketplace-template-card:hover .template-actions {
        opacity: 1;
    }
    
    .template-action-btn {
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.9);
        color: #374151;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    .template-action-btn:hover {
        background: white;
        transform: scale(1.1);
    }
    
    .template-info {
        padding: 16px;
    }
    
    .template-name {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 4px;
    }
    
    .template-category {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 500;
        margin-bottom: 8px;
    }
    
    .template-description {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.4;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .template-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        font-size: 12px;
        color: #6b7280;
    }
    
    .template-rating {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .template-downloads {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .template-footer {
        display: flex;
        gap: 8px;
    }
    
    .template-footer .button {
        flex: 1;
        text-align: center;
        padding: 8px 12px;
        font-size: 13px;
    }
    
    /* Pagination */
    .marketplace-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        padding: 20px 0;
    }
    
    /* Preview Modal */
    .marketplace-preview-content {
        width: 90%;
        max-width: 1200px;
        height: 80vh;
    }
    
    .preview-toolbar {
        display: flex;
        gap: 8px;
        margin-bottom: 15px;
        padding: 0 20px;
    }
    
    .preview-size-btn {
        padding: 8px 16px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .preview-size-btn.active {
        border-color: #3b82f6;
        background: #3b82f6;
        color: white;
    }
    
    .preview-container {
        height: calc(80vh - 200px);
        padding: 0 20px;
    }
    
    .preview-container iframe {
        width: 100%;
        height: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .preview-container iframe.tablet-view {
        width: 768px;
        max-width: 100%;
        margin: 0 auto;
        display: block;
    }
    
    .preview-container iframe.mobile-view {
        width: 375px;
        max-width: 100%;
        margin: 0 auto;
        display: block;
    }
    
    /* Details Modal */
    .marketplace-details-content {
        width: 80%;
        max-width: 800px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .filter-row {
            flex-direction: column;
            align-items: stretch;
        }
        
        .marketplace-grid {
            grid-template-columns: 1fr;
        }
        
        .marketplace-preview-content {
            width: 95%;
            height: 90vh;
        }
        
        .preview-toolbar {
            flex-wrap: wrap;
        }
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Initialize marketplace
        loadMarketplaceTemplates();
        
        // Filter handlers
        $('#marketplace-apply-filters, #marketplace-refresh').on('click', function() {
            loadMarketplaceTemplates();
        });
        
        // Search on enter
        $('#marketplace-search').on('keypress', function(e) {
            if (e.which === 13) {
                loadMarketplaceTemplates();
            }
        });
        
        // Load templates function
        function loadMarketplaceTemplates() {
            const $loading = $('#marketplace-loading');
            const $error = $('#marketplace-error');
            const $grid = $('#marketplace-grid');
            
            // Show loading state
            $loading.show();
            $error.hide();
            $grid.empty();
            
            // Get filter values
            const filters = {
                search: $('#marketplace-search').val(),
                category: $('#marketplace-category').val(),
                type: $('#marketplace-type').val(),
                sort: $('#marketplace-sort').val()
            };
            
            // AJAX request to load templates
            $.post(ajaxurl, {
                action: 'base47_he_load_marketplace',
                nonce: '<?php echo wp_create_nonce('base47_he'); ?>',
                filters: filters
            }, function(response) {
                $loading.hide();
                
                if (response.success && response.data) {
                    displayTemplates(response.data.templates);
                    updatePagination(response.data.pagination);
                } else {
                    $error.show();
                }
            }).fail(function() {
                $loading.hide();
                $error.show();
            });
        }
        
        // Display templates in grid
        function displayTemplates(templates) {
            const $grid = $('#marketplace-grid');
            $grid.empty();
            
            if (templates.length === 0) {
                $grid.html('<div class="no-templates"><h3>No templates found</h3><p>Try adjusting your search filters.</p></div>');
                return;
            }
            
            templates.forEach(function(template) {
                const templateCard = createTemplateCard(template);
                $grid.append(templateCard);
            });
        }
        
        // Create template card HTML
        function createTemplateCard(template) {
            const typeClass = template.type === 'free' ? 'free' : 'premium';
            const price = template.type === 'free' ? 'Free' : '$' + template.price;
            
            return `
                <div class="marketplace-template-card" data-template-id="${template.id}">
                    <div class="template-thumbnail">
                        <img src="${template.thumbnail}" alt="${template.name}">
                        <div class="template-type-badge ${typeClass}">${template.type}</div>
                        <div class="template-actions">
                            <button class="template-action-btn preview-btn" title="Preview">👁️</button>
                            <button class="template-action-btn details-btn" title="Details">ℹ️</button>
                        </div>
                    </div>
                    <div class="template-info">
                        <div class="template-name">${template.name}</div>
                        <div class="template-category">${template.category}</div>
                        <div class="template-description">${template.description}</div>
                        <div class="template-meta">
                            <div class="template-rating">
                                <span>⭐</span>
                                <span>${template.rating} (${template.reviews})</span>
                            </div>
                            <div class="template-downloads">
                                <span>📥</span>
                                <span>${template.downloads}</span>
                            </div>
                        </div>
                        <div class="template-footer">
                            <button class="button button-primary install-btn">Install ${price}</button>
                            <button class="button preview-btn">Preview</button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Template card event handlers
        $(document).on('click', '.preview-btn', function() {
            const templateId = $(this).closest('.marketplace-template-card').data('template-id');
            openPreviewModal(templateId);
        });
        
        $(document).on('click', '.details-btn', function() {
            const templateId = $(this).closest('.marketplace-template-card').data('template-id');
            openDetailsModal(templateId);
        });
        
        $(document).on('click', '.install-btn', function() {
            const templateId = $(this).closest('.marketplace-template-card').data('template-id');
            installTemplate(templateId);
        });
        
        // Modal functions
        function openPreviewModal(templateId) {
            // Implementation for preview modal
            $('#marketplace-preview-modal').fadeIn(200);
        }
        
        function openDetailsModal(templateId) {
            // Implementation for details modal
            $('#marketplace-details-modal').fadeIn(200);
        }
        
        function installTemplate(templateId) {
            // Implementation for template installation
            if (confirm('Install this template?')) {
                // AJAX call to install template
            }
        }
        
        // Close modals
        $('.base47-he-modal-close').on('click', function() {
            $(this).closest('.base47-he-modal').fadeOut(200);
        });
        
        // Update pagination
        function updatePagination(pagination) {
            // Implementation for pagination
        }
    });
    </script>
    <?php
}
?>