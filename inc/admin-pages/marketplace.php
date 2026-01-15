<?php
/**
 * Marketplace Admin Page - Modern Design
 * 
 * Template marketplace with orange gradient banner, filter system, and template cards
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_marketplace_page_v2() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap base47-marketplace-wrap">
        
        <!-- Welcome Banner -->
        <div class="base47-welcome-banner">
            <div class="banner-bg-circle"></div>
            <div class="banner-content">
                <div class="banner-text">
                    <div class="banner-label">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <span>TEMPLATE STORE</span>
                    </div>
                    <h1>Template Marketplace</h1>
                    <p>Browse and install professional templates with one click. Transform your website instantly.</p>
                </div>
                <div class="banner-illustration">
                    <div class="illustration-circle outer"></div>
                    <div class="illustration-circle middle"></div>
                    <div class="illustration-circle inner">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    </div>
                </div>
            </div>
            <div class="floating-dot dot-1"></div>
            <div class="floating-dot dot-2"></div>
            <div class="floating-dot dot-3"></div>
        </div>
        
        <!-- Stats Grid -->
        <div class="base47-stats-grid">
            <div class="stat-card">
                <div class="stat-icon gradient-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value" id="templates-count">12</p>
                    <p class="stat-label">Templates</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon gradient-info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">12</p>
                    <p class="stat-label">Categories</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon gradient-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">Free & Pro</p>
                    <p class="stat-label">Available</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon gradient-purple">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
                <div class="stat-content">
                    <p class="stat-value">1-Click</p>
                    <p class="stat-label">Install</p>
                </div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="base47-filter-section">
            <div class="filter-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                <span>Filter Templates</span>
            </div>
            
            <div class="filter-grid">
                <div class="filter-search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="template-search" placeholder="Search templates...">
                </div>
                
                <select id="template-category" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="business">Business/Corporate</option>
                    <option value="portfolio">Portfolio</option>
                    <option value="blog">Blog/Magazine</option>
                    <option value="landing">Landing Page</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="medical">Medical</option>
                    <option value="realestate">Real Estate</option>
                    <option value="fitness">Fitness</option>
                </select>
                
                <select id="template-type" class="filter-select">
                    <option value="">All Types</option>
                    <option value="free">Free</option>
                    <option value="premium">Premium</option>
                </select>
                
                <div class="filter-actions">
                    <select id="template-sort" class="filter-select">
                        <option value="popular">Most Popular</option>
                        <option value="newest">Newest/Recent</option>
                        <option value="downloads">Most Downloads</option>
                    </select>
                    
                    <button type="button" id="refresh-templates" class="btn-refresh" title="Refresh">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Templates Grid -->
        <div id="templates-grid" class="templates-grid">
            <!-- Templates will be loaded here via AJAX -->
        </div>
        
        <!-- Loading State -->
        <div id="templates-loading" class="templates-loading" style="display: none;">
            <div class="loading-spinner"></div>
            <p>Loading templates...</p>
        </div>
        
        <!-- Empty State -->
        <div id="templates-empty" class="templates-empty" style="display: none;">
            <div class="empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <p class="empty-text">No templates found</p>
            <p class="empty-subtext">Try adjusting your filters or search term.</p>
        </div>
        
    </div>
    
    <!-- Preview Modal -->
    <div id="preview-modal" class="preview-modal" style="display: none;">
        <div class="preview-modal-overlay"></div>
        <div class="preview-container">
            <div class="preview-header">
                <div class="preview-title">
                    <h3 id="preview-template-name">Template Preview</h3>
                </div>
                <div class="device-switcher">
                    <button type="button" class="device-btn active" data-device="desktop">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    </button>
                    <button type="button" class="device-btn" data-device="tablet">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    </button>
                    <button type="button" class="device-btn" data-device="mobile">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="2" width="10" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    </button>
                </div>
                <button type="button" class="btn-close" id="preview-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="preview-body">
                <iframe id="preview-frame-wrapper" class="preview-iframe" src=""></iframe>
            </div>
            <div class="preview-actions">
                <button type="button" id="preview-install-btn" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Install Template
                </button>
                <button type="button" class="btn-secondary" id="preview-close-btn">Close</button>
            </div>
        </div>
    </div>
    
    <?php
}
