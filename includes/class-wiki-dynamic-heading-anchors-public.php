<?php
/**
 * Public-facing functionality
 *
 * @package    WikiDynamicHeadingAnchors
 * @subpackage WikiDynamicHeadingAnchors/includes
 * @since      1.0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wiki_Dynamic_Heading_Anchors_Public {
    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
        $this->init_hooks();
    }

    private function init_hooks() {
        // Add filter to modify menu items
        add_filter( 'wp_nav_menu_objects', array( $this, 'add_heading_anchors_to_menu' ), 10, 2 );
        
        // Add scroll margin for smooth scrolling
        add_action( 'wp_head', array( $this, 'add_scroll_margin_style' ) );
        
        // Add JavaScript for adding IDs to headings and smooth scrolling
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function add_scroll_margin_style() {
        $scroll_offset = isset( $this->settings['scroll_offset'] ) ? absint( $this->settings['scroll_offset'] ) : 100;
        $heading_tags = isset( $this->settings['heading_tags'] ) ? (array) $this->settings['heading_tags'] : array( 'h2' );
        
        if ( $scroll_offset > 0 ) {
            echo '<style>';
            foreach ( $heading_tags as $tag ) {
                echo esc_html( $tag ) . ' { scroll-margin-top: ' . esc_html( $scroll_offset ) . 'px; }' . "\n";
            }
            echo '.elementor-menu-anchor { scroll-margin-top: ' . esc_html( $scroll_offset ) . 'px; }';
            echo '</style>';
        }
    }

    public function enqueue_scripts() {
        // First ensure jQuery is loaded
        wp_enqueue_script( 'jquery' );
        
        // Register and enqueue our heading ID script
        wp_enqueue_script(
            'wiki-dynamic-heading-anchors-js',
            plugins_url( 'js/wiki-dynamic-heading-anchors.js', dirname( plugin_basename( __DIR__ ) ) ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __DIR__ ) . 'js/wiki-dynamic-heading-anchors.js' ),
            true
        );
        
        // Pass settings to the script
        wp_localize_script(
            'wiki-dynamic-heading-anchors-js',
            'wikiDynamicHeadingAnchorsSettings',
            array(
                'headingTag' => $this->settings['heading_tags'],
                'scrollOffset' => absint( $this->settings['scroll_offset'] ),
                'menuClass' => $this->settings['menu_class']
            )
        );
    }

    public function add_heading_anchors_to_menu( $menu_items, $args ) {
        // Only run on singular posts of the enabled types
        if ( !is_singular( $this->settings['post_types'] ) ) {
            return $menu_items;
        }

        global $post;
        $heading_tags = isset( $this->settings['heading_tags'] ) ? (array) $this->settings['heading_tags'] : array( 'h2' );
        $menu_class = isset( $this->settings['menu_class'] ) ? $this->settings['menu_class'] : 'heading-anchor';
        $current_url = get_permalink();
        
        // Get content from post and ACF fields if available
        $content = $post->post_content;
        
        // Add ACF content if available
        if ( function_exists( 'get_fields' ) ) {
            $acf_fields = get_fields();
            if ( is_array( $acf_fields ) ) {
                foreach ( $acf_fields as $field ) {
                    if ( is_string( $field ) ) {
                        $content .= $field;
                    }
                }
            }
        }
        
        // Find the parent menu item for the current page
        $parent_id = 0;
        $parent_exists = false;
        
        foreach ( $menu_items as $item ) {
            if ( trailingslashit($item->url) === trailingslashit($current_url) ) {
                $parent_id = $item->ID;
                $parent_exists = true;
                break;
            }
        }
        
        // If parent doesn't exist in menu, don't add submenu items
        if ( !$parent_exists ) {
            return $menu_items;
        }
        
        // Process each heading tag
        $new_items = array();
        $menu_order = count( $menu_items ) + 1;
        
        foreach ( $heading_tags as $tag ) {
            // Extract all headings of current tag from content
            $pattern = '/<' . $tag . '[^>]*>(.*?)<\/' . $tag . '>/is';
            preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER );
            
            if ( empty( $matches ) ) {
                continue;
            }
            
            // Process each heading match
            foreach ( $matches as $index => $match ) {
                // Get the heading text
                $text = isset( $match[1] ) ? $match[1] : '';
                
                // Clean the heading text
                $text = wp_strip_all_tags( $text );
                $text = trim( $text );
                
                if ( empty( $text ) ) {
                    continue;
                }
                
                // Generate ID from text (same logic as in JavaScript)
                $id = $this->generate_id_from_content( $text );
                
                // Create menu item object
                $new_item = new stdClass();
                $new_item->ID = -1 * ( $index + 1000 );
                $new_item->db_id = $new_item->ID;
                $new_item->title = $text;
                $new_item->url = $current_url . '#' . $id;
                $new_item->menu_order = $menu_order++;
                $new_item->menu_item_parent = $parent_id;
                $new_item->type = 'custom';
                $new_item->object = 'custom';
                $new_item->object_id = $new_item->ID;
                $new_item->classes = array( $menu_class );
                $new_item->target = '';
                $new_item->attr_title = '';
                $new_item->description = '';
                $new_item->xfn = '';
                $new_item->status = '';
                
                $new_items[] = $new_item;
            }
        }
        
        // Add new items to the menu
        return array_merge( $menu_items, $new_items );
    }

    private function generate_id_from_content( $content ) {
        // Strip HTML tags
        $text = wp_strip_all_tags( $content );
        
        // Convert to lowercase
        $text = strtolower( $text );
        
        // Replace spaces with hyphens
        $text = preg_replace( '/\s+/', '-', $text );
        
        // Remove non-alphanumeric characters
        $text = preg_replace( '/[^\w-]/', '', $text );
        
        return $text;
    }
}
