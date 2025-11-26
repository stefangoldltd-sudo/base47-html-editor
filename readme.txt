=== Mivon HTML Editor ===
Contributors: stefan-gold
Tags: html, editor, custom templates, shortcode generator, theme manager, website builder, html builder
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 2.5.2
License: GPLv2 or later

Transform any HTML template folder into reusable WordPress shortcodes —  
with live editing, automatic asset rewriting, template-set switching,  
and a full visual management UI inside WordPress.

== Description ==

Mivon HTML Editor turns any folder ending with “-templates” into a complete HTML template pack inside WordPress.

It automatically:
✔ detects template folders
✔ generates shortcodes for each HTML file
✔ loads CSS/JS only when needed
✔ rewrites asset paths (images, CSS, JS)
✔ provides a real-time Live Editor
✔ includes Theme Set Manager for multi-template setups

Perfect for:
	•	Web designers
	•	Agency site builders
	•	Elementor/Bricks/Beaver/WPBakery users
	•	Developers using static HTML templates
	•	Anyone who wants fast reusable HTML blocks with styling included

⸻

== Key Features ==

🎨 Template → Shortcode System
	•	Every HTML file becomes a shortcode automatically
	•	Shortcode names based on set + filename
	•	Works with unlimited template sets
	•	Only active sets generate shortcodes

✏️ Live HTML Editor
	•	Full-width code editor
	•	Instant preview (Full, Desktop, Tablet, Mobile)
	•	Ctrl/Cmd + S save shortcut
	•	Ctrl/Cmd + P preview shortcut
	•	Restore button for backups
	•	Real-time live preview engine

🧩 Special Widgets System (NEW in 2.5.2)
	•	Create reusable widgets using:
	•	/special-widgets/<widget-folder>/widget.json
	•	HTML + CSS + JS
	•	Auto-detected and listed in admin
	•	Shortcode: [mivon_widget slug="your-widget"]
	•	Perfect for sliders, hero blocks, contact forms, etc.

🚀 Asset Management
	•	Automatic path rewriting for images/CSS/JS
	•	Optional Manifest system for advanced asset control
	•	Loader mode for heavy template sets (Mivon, Lezar, Bfolio, Redox)
	•	Loads only what is needed — improves speed
	•	No conflicts with theme or other plugins

🗂️ Theme Set Manager
	•	Activate/deactivate entire template packs
	•	Only active sets appear in:
	•	Live Editor
	•	Shortcodes
	•	Frontend rendering

📸 Preview System
	•	Live template thumbnails in Shortcodes page
	•	Iframe sandbox preview
	•	Cache-busted assets for accurate rendering

⸻

== Installation ==
	1.	Upload plugin to /wp-content/plugins/
	2.	Activate in Plugins → Installed Plugins
	3.	Add template folders inside the plugin directory, for example:
	•	/mivon-html-editor/mivon-templates/
	•	/mivon-html-editor/beauty-templates/
	4.	Open Mivon HTML → Theme Manager and enable the sets you want
	5.	Use the auto-generated shortcodes in any page or builder

⸻

== Frequently Asked Questions ==

Can I use my own HTML files?

Yes — any folder ending in -templates is auto-detected.

Do CSS/JS load automatically?

Yes — assets inside /assets/css/ and /assets/js/ load only when needed.

Can I edit templates inside WordPress?

Yes — live editor with preview is built-in.

Where do I find shortcodes?

Under Mivon HTML → Shortcodes.

What are Special Widgets?

Reusable HTML components detected from /special-widgets/
Each widget becomes a shortcode.

⸻

== Screenshots ==
	1.	Theme Manager with activation toggles
	2.	Shortcodes overview with thumbnails
	3.	Live Editor interface
	4.	Preview modes (Desktop/Tablet/Mobile)


== Changelog ==

= 2.5.2 =

New:
	•	Special Widgets auto-discovery via widget.json
	•	Universal shortcode [mivon_widget slug="..."]
	•	New admin page listing all widgets with preview + shortcode

Improvements:
	•	Widgets load only their own CSS/JS — no conflicts
	•	Cleaner, modular widget engine
	•	Removed old hardcoded slider widget

Technical:
	•	New registry function and shortcode handler
	•	Safe path rewriting
	•	Fail-proof scanning of widget folders

⸻

= 2.5.1 =
	•	Manifest vs Loader toggle per template set
	•	Added Special Widgets system foundation
	•	Slider V1 integration
	•	Performance improvements
	•	Many bug fixes and safety improvements

⸻

= 2.5.0 – Manifest System =
	•	Introduced manifest.json asset loading
	•	Massive performance upgrade
	•	Per-set asset configuration
	•	Cleaner code and conflict prevention

= 2.1.1 =
* Critical fix: Admin CSS & JS were not being enqueued on some sites.
* Fixed: Live Editor "Save" button not responding.
* Fixed: Preview iframe not refreshing after edits.
* Fixed: Layout broken in Templates/Editor pages when admin assets failed to load.
* Improvement: Default theme detection is now stable.
* Added: Internal fallback styles remain intact.
* Added: Clean asset loading order for consistent admin UI.
* Misc code cleanup.

= 2.1.0 =
* New Theme Set Manager with activation toggles.
* Shortcodes now appear only for active sets.
* Improved template discovery logic.
* Security enhancements & nonce improvements.
* Rewrite and cleanup of preview/iframe logic.

== Upgrade Notice ==

Version 2.5.x is a major performance upgrade.
Recommended for all users using multiple template sets or heavy HTML packs

2.1.1 fixes a critical admin asset loading bug that breaks layout and disables saving.  
Updating is strongly recommended.