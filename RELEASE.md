# Wiki Dynamic Heading Anchors - Release 1.1.1

![Wiki Dynamic Heading Anchors](images/AG%20Logo_1.jpg)

## Security and Performance Update

We're excited to announce the latest update to Wiki Dynamic Heading Anchors for WordPress! This release focuses on security enhancements, code optimization, and improved user experience with the addition of a customizable scroll offset feature.

## Key Features

- **Automatic Heading Detection**: Automatically adds heading tags from content as submenu items
- **Customizable Heading Selection**: Choose which heading tags (H1-H6) to include
- **Post Type Support**: Select which post types should have dynamic heading anchors
- **Custom Styling**: Add custom CSS classes to menu items
- **Fixed Header Support**: Set scroll offset for fixed headers when navigating to anchors
- **Smart Content Detection**: Intelligently detects headings in meta fields and Elementor content
- **Dynamic Processing**: All changes are made dynamically without modifying your database content
- **Enhanced Security**: Improved security measures to protect your WordPress site
- **Optimized Code**: Streamlined JavaScript for better performance

## Technical Highlights

- JavaScript-based dynamic heading ID generation
- Optimized MutationObserver implementation for handling dynamic content changes
- jQuery smooth scrolling implementation with customizable offset
- Smart content container detection
- URL hash handling and scroll offset support
- Standardized code formatting for better maintainability

## Installation

1. Download the latest release
2. Upload the `wiki-dynamic-heading-anchors` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > Wiki Dynamic Heading Anchors to configure the plugin
5. Adjust the scroll offset value if you have a fixed header

## Documentation

Complete documentation is available in the [README.md](README.md) file.

## Changelog

### 1.1.1
- Added enhanced security measures
- Improved code efficiency and reduced JavaScript file size
- Added customizable scroll offset for fixed headers
- Standardized code formatting for better maintainability
- Fixed minor bugs and improved compatibility with various themes
- Optimized MutationObserver implementation for better performance

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
