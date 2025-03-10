=== Wiki Dynamic Heading Anchors ===
Contributors: arnelgo
Donate link: https://www.paypal.me/arnelborresgo
Tags: menu, navigation, headings, anchors, table of contents, toc
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.1.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically adds IDs to headings and creates dynamic menu items in WordPress navigation menus.

== Description ==

Wiki Dynamic Heading Anchors is a WordPress plugin that automatically adds heading tags from your content as submenu items in your WordPress navigation menu. This creates a dynamic table of contents for your pages and posts, making it easier for users to navigate through your content.

### Key Features

* Automatically adds heading tags as submenu items
* Configurable heading tags (H1-H6)
* Selective post type support
* Customizable CSS classes
* Adjustable scroll offset for fixed headers
* Dynamic content modification (no database changes)
* Clean, standards-compliant code
* Smart heading tag detection
* Support for Elementor and other page builders
* Optimized performance
* Enhanced security measures
* Improved code efficiency

### How It Works

When a visitor views a page or post of a selected post type, the plugin:

1. Scans the content for heading tags (based on your settings)
2. Adds IDs to headings if they don't already have one
3. Adds these headings as submenu items under the current page in the navigation menu
4. When a visitor clicks a submenu item, they're taken to that section of the page

All changes are dynamic and don't modify your content in the database.

### Use Cases

* Create a wiki-style navigation for documentation sites
* Build a table of contents for long-form content
* Improve navigation for knowledge bases
* Enhance user experience for tutorial websites
* Create better navigation for legal documents or terms pages

== Installation ==

1. Upload the `wiki-dynamic-heading-anchors` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Wiki Dynamic Heading Anchors to configure the plugin
4. Select which heading tags (H1-H6) to process
5. Choose which post types should have the dynamic heading anchors feature
6. Set a custom CSS class for menu items if desired
7. Adjust the scroll offset value if you have a fixed header

== Frequently Asked Questions ==

= Does this plugin modify my content permanently? =

No, all changes are made dynamically when the page is viewed. Your content in the database remains unchanged.

= Can I style the menu items? =

Yes, you can add custom CSS classes to the menu items through the plugin settings.

= Will this work with my theme? =

The plugin is designed to work with any properly coded WordPress theme that uses standard WordPress menu functions.

= Can I filter which heading tags are displayed? =

Yes, you can select which heading tags (H1-H6) are included in the menu through the plugin settings.

= Does it work with page builders like Elementor? =

Yes, the plugin is compatible with Elementor and other popular page builders.

= How do I add headings from a specific post to a menu? =

When editing a post, you'll see a metabox called "Dynamic Menu Anchors" where you can add the post title to a menu and select specific headings to add as anchor links.

= Can I control which menu the headings are added to? =

Yes, in the post editor metabox, you can select which menu to add the headings to.

= Does it support multilingual sites? =

Yes, the plugin is translation-ready and works with multilingual plugins.

= What is the scroll offset setting used for? =

The scroll offset setting allows you to adjust the vertical position when scrolling to an anchor. This is particularly useful if your site has a fixed header that would otherwise cover the heading when navigating to an anchor link.

== Screenshots ==

1. Plugin settings page
2. Post metabox for adding headings to menus
3. Example of dynamic menu items in action

== Changelog ==

= 1.1.1 =
* Added enhanced security measures
* Improved code efficiency and reduced JavaScript file size
* Added customizable scroll offset for fixed headers
* Standardized code formatting for better maintainability
* Fixed minor bugs and improved compatibility with various themes
* Optimized MutationObserver implementation for better performance

= 1.1.0 =
* Added intelligent heading tag detection for meta fields
* Improved filtering of heading tags based on settings
* Enhanced support for Elementor page builder
* Optimized code for production environments
* Fixed issue with H3 headings appearing when only H2 is enabled

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.1 =
This update includes enhanced security measures, improved code efficiency, customizable scroll offset for fixed headers, and minor bug fixes.

== Additional Information ==

= Technical Implementation =
* Uses WordPress hooks for menu modification
* JavaScript-based dynamic heading ID generation
* Uses MutationObserver for handling dynamic content changes
* jQuery smooth scrolling implementation
* Smart content container detection
* Optimized code structure for better performance

= Credits =
* Developed by [Arnel Go](https://arnelgo.info/)
