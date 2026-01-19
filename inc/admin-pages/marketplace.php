<?php
/***
 * Marketplace Page - Soft UI Dashboard Style
 * 
 * @package Base47_HTML_Editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/***
 * Render the Marketplace page
 */
function base47_he_marketplace_page_v2() {
	?>
	<div class="base47-dashboard-soft-ui base47-marketplace-wrap">
		
		<!-- Welcome Banner -->
		<div class="base47-welcome-banner">
			<div class="banner-bg-circle"></div>
			<div class="banner-bg-circle-sm"></div>
			<div class="banner-content">
				<div class="banner-text">
					<div class="banner-label">
						<div class="banner-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<circle cx="8" cy="21" r="1"/>
								<circle cx="19" cy="21" r="1"/>
								<path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
							</svg>
						</div>
						<span>Template Store</span>
					</div>
					<h1>Template Marketplace</h1>
					<p>Browse and install professional templates with one click. Transform your website instantly.</p>
				</div>
				<div class="banner-illustration">
					<div class="illustration-circle outer">
						<div class="illustration-circle middle">
							<div class="illustration-circle inner">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="8" cy="21" r="1"/>
									<circle cx="19" cy="21" r="1"/>
									<path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
								</svg>
							</div>
						</div>
					</div>
					<div class="floating-dot dot-1"></div>
					<div class="floating-dot dot-2"></div>
					<div class="floating-dot dot-3"></div>
				</div>
			</div>
		</div>
		
		<!-- More Templates Coming Notification -->
		<div class="base47-notification-banner">
			<div class="notification-content">
				<div class="notification-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
					</svg>
				</div>
				<div class="notification-text">
					<h3>More Templates Coming Soon!</h3>
					<p>We're constantly adding new professional templates. Currently featuring SaaS and Slider templates, with many more categories launching regularly.</p>
				</div>
				<div class="notification-badge">
					<span>New</span>
				</div>
			</div>
		</div>
		
		<!-- Stats Grid -->
		<div class="base47-stats-grid">
			<div class="stat-card">
				<div class="stat-icon gradient-primary">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<rect width="7" height="9" x="3" y="3" rx="1"/>
						<rect width="7" height="5" x="14" y="3" rx="1"/>
						<rect width="7" height="9" x="14" y="12" rx="1"/>
						<rect width="7" height="5" x="3" y="16" rx="1"/>
					</svg>
				</div>
				<div class="stat-content">
					<p class="stat-value" id="templates-count">0</p>
					<p class="stat-label">Templates</p>
				</div>
			</div>
			
			<div class="stat-card">
				<div class="stat-icon gradient-info">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="m6 14 1.5-2.9A2 2 0 0 1 9.24 10H14a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2"/>
						<path d="M14 4h6v6"/>
						<path d="m20 4-8 8"/>
					</svg>
				</div>
				<div class="stat-content">
					<p class="stat-value">12</p>
					<p class="stat-label">Categories</p>
				</div>
			</div>
			
			<div class="stat-card">
				<div class="stat-icon gradient-success">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/>
						<path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/>
						<path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>
					</svg>
				</div>
				<div class="stat-content">
					<p class="stat-value">Free & Pro</p>
					<p class="stat-label">Available</p>
				</div>
			</div>
			
			<div class="stat-card">
				<div class="stat-icon gradient-purple">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
						<polyline points="7 10 12 15 17 10"/>
						<line x1="12" x2="12" y1="15" y2="3"/>
					</svg>
				</div>
				<div class="stat-content">
					<p class="stat-value">1-Click</p>
					<p class="stat-label">Install</p>
				</div>
			</div>
		</div>
		
		<!-- Filter Section -->
		<div class="dashboard-card filter-card">
			<div class="card-header">
				<div class="header-icon gradient-primary">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
					</svg>
				</div>
				<h2>Filter Templates</h2>
			</div>
			<div class="card-body">
				<div class="filter-grid">
					<div class="filter-search">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="11" cy="11" r="8"/>
							<path d="m21 21-4.3-4.3"/>
						</svg>
						<input type="text" id="template-search" placeholder="Search templates...">
					</div>
					
					<select id="template-category" class="filter-select">
						<option value="all">All Categories</option>
						<option value="business">Business</option>
						<option value="portfolio">Portfolio</option>
						<option value="ecommerce">E-Commerce</option>
						<option value="blog">Blog</option>
						<option value="landing">Landing Page</option>
						<option value="agency">Agency</option>
						<option value="restaurant">Restaurant</option>
						<option value="medical">Medical</option>
						<option value="education">Education</option>
						<option value="real-estate">Real Estate</option>
						<option value="fitness">Fitness</option>
					</select>
					
					<select id="template-type" class="filter-select">
						<option value="all">All Types</option>
						<option value="free">Free</option>
						<option value="premium">Premium</option>
					</select>
					
					<div class="filter-actions">
						<select id="template-sort" class="filter-select">
							<option value="popular">Most Popular</option>
							<option value="newest">Newest</option>
							<option value="rating">Top Rated</option>
							<option value="downloads">Most Downloads</option>
						</select>
						
						<button type="button" id="refresh-templates" class="btn-icon" title="Refresh">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
								<path d="M3 3v5h5"/>
								<path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
								<path d="M16 16h5v5"/>
							</svg>
						</button>
					</div>
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
				<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/>
					<path d="m7.5 4.27 9 5.15"/>
					<polyline points="3.29 7 12 12 20.71 7"/>
					<line x1="12" x2="12" y1="22" y2="12"/>
					<circle cx="18.5" cy="15.5" r="2.5"/>
					<path d="M20.27 17.27 22 19"/>
				</svg>
			</div>
			<h3>No templates found</h3>
			<p>Try adjusting your filters or search query.</p>
		</div>
		
		<!-- Installation Guide -->
		<div class="marketplace-install-guide">
			<div class="install-guide-header">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="12" r="10"/>
					<path d="M12 16v-4"/>
					<path d="M12 8h.01"/>
				</svg>
				<h3>How to Install Templates</h3>
			</div>
			<div class="install-guide-steps">
				<div class="install-step">
					<div class="step-number">1</div>
					<div class="step-content">
						<h4>Download Template</h4>
						<p>Click the "Install" button on any template card. The ZIP file will download to your computer.</p>
					</div>
				</div>
				<div class="install-step">
					<div class="step-number">2</div>
					<div class="step-content">
						<h4>Go to Theme Manager</h4>
						<p>Navigate to <strong>Base47 HTML → Theme Manager</strong> in your WordPress admin menu.</p>
					</div>
				</div>
				<div class="install-step">
					<div class="step-number">3</div>
					<div class="step-content">
						<h4>Upload ZIP File</h4>
						<p>Click "Choose ZIP File" and select the downloaded template. Then click "Upload & Install".</p>
					</div>
				</div>
				<div class="install-step">
					<div class="step-number">4</div>
					<div class="step-content">
						<h4>Start Using</h4>
						<p>The template will be added to your collection and ready to use in the Live Editor!</p>
					</div>
				</div>
			</div>
			<div class="install-guide-note">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
				</svg>
				<p><strong>Note:</strong> Make sure the ZIP file contains a folder ending with "-templates" (e.g., "agency-templates"). If you get an error, check the ZIP structure.</p>
			</div>
		</div>
		
	</div>
	
	<!-- Preview Modal -->
	<div id="preview-modal" class="preview-modal" style="display: none;">
		<div class="preview-backdrop"></div>
		<div class="preview-container">
			<div class="preview-header">
				<div class="preview-title">
					<h2 id="preview-template-name">Template Name</h2>
					<span id="preview-template-badge" class="badge badge-free">Free</span>
				</div>
				<div class="device-switcher">
					<button type="button" class="device-btn active" data-device="desktop">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<rect width="20" height="14" x="2" y="3" rx="2"/>
							<line x1="8" x2="16" y1="21" y2="21"/>
							<line x1="12" x2="12" y1="17" y2="21"/>
						</svg>
						<span>Desktop</span>
					</button>
					<button type="button" class="device-btn" data-device="tablet">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
							<line x1="12" x2="12.01" y1="18" y2="18"/>
						</svg>
						<span>Tablet</span>
					</button>
					<button type="button" class="device-btn" data-device="mobile">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<rect width="14" height="20" x="5" y="2" rx="2" ry="2"/>
							<path d="M12 18h.01"/>
						</svg>
						<span>Mobile</span>
					</button>
				</div>
				<div class="preview-actions">
					<button type="button" id="preview-install-btn" class="btn btn-primary">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
							<polyline points="7 10 12 15 17 10"/>
							<line x1="12" x2="12" y1="15" y2="3"/>
						</svg>
						Install Template
					</button>
					<button type="button" id="preview-close-btn" class="btn-icon btn-close">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M18 6 6 18"/>
							<path d="m6 6 12 12"/>
						</svg>
					</button>
				</div>
			</div>
			<div class="preview-body">
				<div id="preview-frame-wrapper" class="preview-frame-wrapper device-desktop">
					<iframe id="preview-iframe" src="about:blank"></iframe>
				</div>
			</div>
		</div>
	</div>
	<?php
}
