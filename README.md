# Wiki Dynamic Heading Anchors for WordPress Menu

<p align="center">
  <img src="images/AG Logo_1.jpg" alt="Arnel Go Logo" width="200"/>
</p>

[![WordPress Plugin Version](https://img.shields.io/badge/version-1.1.0-blue.svg)](https://wordpress.org/plugins/wiki-dynamic-heading-anchors/)
[![WordPress Compatibility](https://img.shields.io/badge/wordpress-5.0%2B-green.svg)](https://wordpress.org/plugins/wiki-dynamic-heading-anchors/)
[![License](https://img.shields.io/badge/license-GPL%20v2%2B-yellow.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://www.paypal.me/arnelborresgo)

## Description

Wiki Dynamic Heading Anchors is a WordPress plugin that automatically adds heading tags from your content as submenu items in your WordPress navigation menu. This creates a dynamic table of contents for your pages and posts, making it easier for users to navigate through your content.

## Features

- ✔️ Automatically adds heading tags from content as submenu items
- 🎨 Works with any WordPress theme
- 🔧 Customizable heading tag selection (H1-H6)
- 📋 Select which post types to enable the plugin for
- 💅 Customize CSS classes for menu items
- 📏 Set scroll offset for fixed headers
- 🧹 Clean, standards-compliant code
- 🔍 Smart heading tag detection for meta fields
- 🏗️ Support for Elementor page builder
- ⚡ Optimized performance for production environments

## Installation

1. Upload the `wiki-dynamic-heading-anchors` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Wiki Dynamic Heading Anchors to configure the plugin

## Configuration

1. **Heading Tags**: Select which heading tags (H1-H6) to use for creating menu items
2. **Post Types**: Choose which post types should have dynamic heading anchors
3. **Menu Item CSS Class**: Set a custom CSS class for the menu items
4. **Scroll Offset**: Set an offset in pixels to account for fixed headers when scrolling to anchors

## How It Works

When a visitor views a page or post of a selected post type, the plugin:

1. Scans the content for heading tags (based on your settings)
2. Adds IDs to headings if they don't already have one
3. Adds these headings as submenu items under the current page in the navigation menu
4. When a visitor clicks a submenu item, they're taken to that section of the page

All changes are dynamic and don't modify your content in the database.

## Advanced Features

### Meta Field Support

The plugin intelligently detects headings in meta fields with smart tag detection:
- Fields with "sub_title", "subtitle", "sub_heading", or "subheading" are treated as H3 headings
- Fields with "main_title", "main_heading", or "page_title" are treated as H1 headings
- Other title fields default to H2 headings

### Elementor Support

The plugin automatically detects and includes headings from Elementor page builder content.

## Technical Implementation

- **JavaScript-based** dynamic heading ID generation
- Uses **MutationObserver** for handling dynamic content changes
- **jQuery smooth scrolling** implementation
- Smart content container detection
- URL hash handling and scroll offset support

## Frequently Asked Questions

### Does this plugin modify my content permanently?

No, all changes are made dynamically when the page is viewed. Your content in the database remains unchanged.

### Can I style the menu items?

Yes, you can add custom CSS classes to the menu items through the plugin settings.

### Will this work with my theme?

The plugin is designed to work with any properly coded WordPress theme that uses standard WordPress menu functions.

### Can I filter which heading tags are displayed?

Yes, you can select which heading tags (H1-H6) are included in the menu through the plugin settings.

## Support the Development

If you find this plugin useful, please consider supporting its development by making a donation.

<p align="center">
  <a href="https://www.paypal.me/arnelborresgo" target="_blank">
    <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="Donate with PayPal" />
  </a>
</p>

**PayPal Email:** arnel.b.go@gmail.com

## Changelog

### 1.1.0
- Added intelligent heading tag detection for meta fields
- Improved filtering of heading tags based on settings
- Enhanced support for Elementor page builder
- Optimized code for production environments
- Fixed issue with H3 headings appearing when only H2 is enabled

### 1.0.1
- Initial release

## License

GPL v2 or later

## Author

[Arnel Go](https://arnelgo.info/)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
