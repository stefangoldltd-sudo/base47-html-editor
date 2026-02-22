=== Base47 HTML Editor ===
Contributors: stefangold
Tags: html editor, template manager, shortcodes, live editor, code editor, html templates
Requires at least: 5.0
Tested up to: 6.9.1
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Transform any HTML template folder into WordPress shortcodes with a professional Monaco-based live editor, theme-set manager, smart asset loading, and a beautiful Soft UI dashboard.

== Description ==

Base47 HTML Editor allows developers and designers to use static HTML templates inside WordPress by converting them into shortcodes with controlled asset loading and a live editor.

The plugin automatically converts HTML files into shortcodes, loads their CSS/JS only when needed, and provides a full Monaco-powered live editor with real-time preview. Manage multiple template sets, special widgets, and assets with ease — all inside a modern Soft UI dashboard.

= Highlights =

* Live HTML editor powered by Monaco (VS Code engine)
* Automatic shortcode creation for every HTML file
* Real-time live preview with responsive modes
* Theme-set management (activate/deactivate template packs)
* Smart asset loading (Manifest, Smart Loader++, Fallback)
* Automatic rewriting of image/CSS/JS URLs
* Special Widgets system with auto-discovery
* Soft UI admin dashboard for a premium user experience
* Clean uninstall and full logging system
* Tested with Elementor, Bricks, Gutenberg, and WooCommerce

= Main Features =

**Live HTML Editor**
* Monaco editor (syntax highlighting, IntelliSense, shortcuts)
* Real-time preview inside iframe
* Backup + restore of previous versions

**Template & Shortcode System**
* Every HTML file becomes a shortcode `[base47-theme-template]`
* Nested shortcodes supported
* Works with unlimited template folders ending in `-templates`

**Theme Manager**
* Activate/deactivate template sets
* Set default theme
* Per-theme asset loading mode

**Smart Asset Loader**
* Loads only the CSS/JS required by the specific template
* Manifest.json support for advanced libraries
* Prevents conflicts between template sets and theme CSS

**Special Widgets**
* Add reusable HTML components such as sliders, hero sections
* Auto-detection from `special-widgets/` folder
* Shortcode usage: `[base47_widget slug="widget-name"]`

== Installation ==

1. Upload the plugin or install via **Plugins → Add New**.
2. Activate the plugin.
3. Place your template folders inside:
   - `wp-content/uploads/base47-themes/` (recommended)
   - Or use the built-in template installer to add and manage template sets
4. Visit **Base47 → Theme Manager** and activate your template sets.
5. Use the generated shortcodes anywhere in your pages or page builders.

== External Services & Privacy ==

This plugin does not contact any external services or APIs by default. All template processing, editing, and management happens locally on your WordPress installation.

Data Collection:
- No personal data is collected or transmitted to external servers
- All user data remains on your WordPress installation
- No tracking, analytics, or phone home functionality

Third-Party Libraries:
This plugin bundles the following third-party libraries (see THIRD-PARTY-LICENSES.md for full details):
- Monaco Editor v0.45.0 (MIT License) - Code editor component
- Font Awesome Free v6.5.1 (Font Awesome Free License) - Icon library
- jQuery v3.7.1 (MIT License) - JavaScript library

All bundled libraries are GPL-compatible and properly licensed.

== Frequently Asked Questions ==

= Can I use my own HTML templates? =

Yes. Any folder ending with `-templates` is detected automatically.

= Do CSS and JS load automatically? =

Yes. Assets inside `assets/css/` and `assets/js/` load only when the shortcode is used.

= Can I edit templates? =

Yes. The built-in Monaco editor provides full live editing and preview.

= Is it compatible with page builders? =

Yes. Tested with Elementor, Bricks, Beaver Builder, Gutenberg, and WPBakery.

= Does this plugin work with WooCommerce? =

Yes. The asset loader is designed to minimize conflicts with WooCommerce styling.

= Where do I find my shortcodes? =

Go to **Base47 → Shortcodes** to browse all available shortcodes with live previews.

= What are Special Widgets? =

Reusable HTML components stored inside each template set under `/special-widgets/`. Each widget can be used with `[base47_widget slug="widget-name"]`.

= Can I manage multiple template sets? =

Yes. Use the Theme Manager to activate/deactivate unlimited template sets.

= Does it create backups? =

Yes. The Live Editor automatically creates backups before each save. Use the "Restore" button to view previous versions.

= What happens when I uninstall? =

On uninstall, all plugin options and generated data are removed. Template files stored in wp-content/uploads/base47-themes/ are preserved and can be manually deleted if desired.

== Screenshots ==

1. Dashboard overview
2. Live HTML Editor
3. Theme Manager
4. Shortcodes preview
5. Special Widgets
6. Settings panel
7. Logs view
8. Changelog page

== Changelog ==

= 3.0.0 – February 6, 2026 =
* Initial WordPress.org release
* Complete internationalization (i18n) support with translation files
* Enhanced security with proper escaping, sanitization, and nonce validation
* Improved file deletion security with capability checks and path validation
* Updated compatibility testing with WordPress 6.9.1
* Enhanced documentation and privacy disclosures
* Third-party library licensing documentation
* Removed external update mechanisms for WordPress.org compliance
* Professional Monaco-powered live HTML editor
* Automatic shortcode generation for HTML templates
* Smart asset loading system (Manifest, Smart Loader, Fallback modes)
* Theme-set management with activation/deactivation
* Special Widgets system with auto-discovery
* Real-time preview with responsive modes
* Backup and restore functionality
* Soft UI admin dashboard
* Complete WordPress coding standards compliance
* Clean uninstall procedures
* Comprehensive logging system
* Mobile-responsive admin interface

== Upgrade Notice ==

= 3.0.0 =
Initial WordPress.org release with full internationalization, enhanced security, and comprehensive HTML template management features.

== License ==

GPL v2 or later.
