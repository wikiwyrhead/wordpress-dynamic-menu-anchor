# Wiki Dynamic Heading Anchors - Release 1.1.0

![Wiki Dynamic Heading Anchors](images/AG%20Logo_1.jpg)

## Initial Public Release

We're excited to announce the first public release of Wiki Dynamic Heading Anchors for WordPress! This plugin automatically adds heading tags from your content as submenu items in your WordPress navigation menu, creating a dynamic table of contents for your pages and posts.

## Key Features

- **Automatic Heading Detection**: Automatically adds heading tags from content as submenu items
- **Customizable Heading Selection**: Choose which heading tags (H1-H6) to include
- **Post Type Support**: Select which post types should have dynamic heading anchors
- **Custom Styling**: Add custom CSS classes to menu items
- **Fixed Header Support**: Set scroll offset for fixed headers when navigating to anchors
- **Smart Content Detection**: Intelligently detects headings in meta fields and Elementor content
- **Dynamic Processing**: All changes are made dynamically without modifying your database content

## Technical Highlights

- JavaScript-based dynamic heading ID generation
- Uses MutationObserver for handling dynamic content changes
- jQuery smooth scrolling implementation
- Smart content container detection
- URL hash handling and scroll offset support

## Installation

1. Download the latest release
2. Upload the `wiki-dynamic-heading-anchors` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > Wiki Dynamic Heading Anchors to configure the plugin

## Documentation

Complete documentation is available in the [README.md](README.md) file.

## Changelog

### 1.1.0
- Added intelligent heading tag detection for meta fields
- Improved filtering of heading tags based on settings
- Enhanced support for Elementor page builder
- Optimized code for production environments
- Fixed issue with H3 headings appearing when only H2 is enabled

### 1.0.1
- Initial release

## Support

If you encounter any issues or have feature requests, please [open an issue](https://github.com/wikiwyrhead/wordpress-dynamic-menu-anchor/issues) on GitHub.

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Acknowledgements

- Thanks to all the beta testers who provided valuable feedback
- WordPress community for continuous inspiration

---

**Author:** [Arnel Go](https://arnelgo.info/)

If you find this plugin useful, please consider [supporting its development](https://www.paypal.me/arnelborresgo).
