<?php
/**
 * Plugin Name: Wiki Dynamic Heading Anchors
 * Description: Automatically adds IDs to headings and creates dynamic menu items in WordPress navigation menus
 * Version: 1.1.0
 * Author: Arnel Go
 * Author URI: https://arnelgo.info/
 * Plugin URI: https://github.com/wikiwyrhead/wordpress-dynamic-menu-anchor
 * GitHub Plugin URI: https://github.com/wikiwyrhead/wordpress-dynamic-menu-anchor
 * Text Domain: wiki-dynamic-heading-anchors
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.0
 *
 * @package Wiki_Dynamic_Heading_Anchors
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

class Wiki_Dynamic_Heading_Anchors {
    /**
     * The single instance of this class.
     *
     * @since 1.0.0
     * @var Wiki_Dynamic_Heading_Anchors
     */
    private static $instance = null;

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     * @var array
     */
    private $settings;

    /**
     * Returns the single instance of this class.
     *
     * @since 1.0.0
     * @return Wiki_Dynamic_Heading_Anchors The single instance.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        // Initialize settings with defaults.
        $this->settings = get_option(
            'wiki_dynamic_heading_anchors_settings',
            array(
                'heading_tags' => array( 'h2', 'h3', 'h4', 'h5', 'h6' ),
                'post_types'   => array( 'post', 'page' ),
                'menu_class'   => 'wiki-dynamic-heading-anchor',
            )
        );

        // Add settings link to plugins page
        $plugin_basename = plugin_basename( __FILE__ );
        add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_settings_link' ) );

        // Frontend scripts.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        
        // Admin scripts and styles.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        
        // Add meta box for menu creation.
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        
        // Add settings page.
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Handle form submission for menu creation.
        add_action( 'admin_post_wiki_dynamic_heading_anchors_add_to_menu', array( $this, 'handle_add_to_menu_form_submission' ) );
        add_action( 'admin_post_nopriv_wiki_dynamic_heading_anchors_add_to_menu', array( $this, 'handle_add_to_menu_form_submission' ) );
        
        // Add hooks for post title menu functionality.
        add_action( 'save_post', array( $this, 'add_post_title_to_menu' ), 10, 3 );
        
        // Add AJAX handler for listing post headings.
        add_action( 'wp_ajax_wiki_dynamic_heading_anchors_list_headings', array( $this, 'ajax_list_post_headings' ) );
        
        // Add AJAX handler for saving title menu.
        add_action( 'wp_ajax_wiki_dynamic_heading_anchors_save_title_menu', array( $this, 'ajax_save_title_menu' ) );
        
        // Add AJAX handler for adding headings to menu.
        add_action( 'wp_ajax_wiki_dynamic_heading_anchors_add_headings_to_menu', array( $this, 'ajax_add_headings_to_menu' ) );
        
        // Register shortcodes.
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        
        // Add permalink button to TinyMCE editor.
        add_action( 'init', array( $this, 'add_permalink_button' ) );
        
        // Register AJAX actions.
        add_action( 'init', array( $this, 'register_ajax_actions' ) );
        
        // Enqueue frontend scripts.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
        
        // Modify plugin row meta links.
        add_filter( 'plugin_row_meta', array( $this, 'modify_plugin_row_meta' ), 10, 2 );
    }

    /**
     * Plugin activation hook.
     *
     * @since 1.0.0
     */
    public function activate_plugin() {
        // Initialize default settings if not exist.
        if ( ! get_option( 'wiki_dynamic_heading_anchors_settings' ) ) {
            update_option( 'wiki_dynamic_heading_anchors_settings', $this->settings );
        }
    }

    /**
     * Plugin deactivation hook.
     *
     * @since 1.0.0
     */
    public function deactivate_plugin() {
        // Cleanup if needed.
    }

    /**
     * Add admin menu item.
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'Wiki Dynamic Heading Anchors Settings', 'wiki-dynamic-heading-anchors' ),
            __( 'Wiki Dynamic Heading Anchors', 'wiki-dynamic-heading-anchors' ),
            'manage_options',
            'wiki-dynamic-heading-anchors',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register plugin settings.
     *
     * @since 1.0.0
     */
    public function register_settings() {
        register_setting(
            'wiki_dynamic_heading_anchors_settings',
            'wiki_dynamic_heading_anchors_settings',
            array( $this, 'sanitize_settings' )
        );
    }

    /**
     * Sanitize settings.
     *
     * @since 1.0.0
     * @param array $input The input array to sanitize.
     * @return array The sanitized array.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();
        
        // Sanitize heading tags.
        $valid_heading_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
        $sanitized['heading_tags'] = isset( $input['heading_tags'] ) && is_array( $input['heading_tags'] )
            ? array_intersect( $input['heading_tags'], $valid_heading_tags )
            : array( 'h2' );
        
        // Sanitize post types.
        $valid_post_types = array_keys( get_post_types( array( 'public' => true ) ) );
        $sanitized['post_types'] = isset( $input['post_types'] ) && is_array( $input['post_types'] )
            ? array_intersect( $input['post_types'], $valid_post_types )
            : array( 'post', 'page' );
        
        // Sanitize menu class.
        $sanitized['menu_class'] = isset( $input['menu_class'] ) ? sanitize_html_class( $input['menu_class'] ) : 'wiki-dynamic-heading-anchor';
        
        return $sanitized;
    }

    /**
     * Render the settings page.
     *
     * @since 1.0.0
     */
    public function render_settings_page() {
        ?>
        <div class="wrap wiki-dynamic-heading-anchors-settings">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'wiki_dynamic_heading_anchors_settings' );
                do_settings_sections( 'wiki_dynamic_heading_anchors_settings' );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Heading Tags', 'wiki-dynamic-heading-anchors' ); ?></th>
                        <td>
                            <div class="checkbox-container">
                                <?php foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $tag ) : ?>
                                    <label>
                                        <input type="checkbox" name="wiki_dynamic_heading_anchors_settings[heading_tags][]" 
                                               value="<?php echo esc_attr( $tag ); ?>"
                                               <?php checked( in_array( $tag, $this->settings['heading_tags'], true ) ); ?>
                                        >
                                        <?php echo esc_html( $tag ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="description"><?php esc_html_e( 'Select which heading tags should be processed by the plugin.', 'wiki-dynamic-heading-anchors' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Post Types', 'wiki-dynamic-heading-anchors' ); ?></th>
                        <td>
                            <div class="checkbox-container">
                                <?php foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) : ?>
                                    <label>
                                        <input type="checkbox" name="wiki_dynamic_heading_anchors_settings[post_types][]" 
                                               value="<?php echo esc_attr( $post_type->name ); ?>"
                                               <?php checked( in_array( $post_type->name, $this->settings['post_types'], true ) ); ?>
                                        >
                                        <?php echo esc_html( $post_type->label ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="description"><?php esc_html_e( 'Select which post types should have the dynamic heading anchors feature.', 'wiki-dynamic-heading-anchors' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Menu Item CSS Class', 'wiki-dynamic-heading-anchors' ); ?></th>
                        <td>
                            <input type="text" name="wiki_dynamic_heading_anchors_settings[menu_class]" 
                                   value="<?php echo esc_attr( $this->settings['menu_class'] ); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e( 'CSS class to add to menu items created by this plugin.', 'wiki-dynamic-heading-anchors' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <!-- Donation Section -->
            <div class="wiki-dynamic-heading-anchors-donation">
                <img src="<?php echo esc_url( plugins_url( 'images/AG Logo_1.jpg', __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Arnel Go', 'wiki-dynamic-heading-anchors' ); ?>" class="logo">
                <h3><?php esc_html_e( 'Support the Development', 'wiki-dynamic-heading-anchors' ); ?></h3>
                <p>
                    <?php esc_html_e( 'If you find this plugin useful, please consider making a donation to support continued development and maintenance. Your contribution helps keep this plugin updated and compatible with the latest WordPress versions.', 'wiki-dynamic-heading-anchors' ); ?>
                </p>
                <a href="https://www.paypal.me/arnelborresgo" target="_blank" class="paypal-button">
                    <?php esc_html_e( 'Donate with PayPal', 'wiki-dynamic-heading-anchors' ); ?>
                </a>
                <div class="paypal-email">
                    <?php esc_html_e( 'PayPal Email:', 'wiki-dynamic-heading-anchors' ); ?> arnel.b.go@gmail.com
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue scripts for the frontend.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() {
        // Enqueue our custom script.
        wp_enqueue_script(
            'wiki-dynamic-heading-anchors-js',
            plugins_url( 'js/wiki-dynamic-heading-anchors.js', __FILE__ ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'js/wiki-dynamic-heading-anchors.js' ),
            true
        );
        
        // Pass settings to our script.
        wp_localize_script(
            'wiki-dynamic-heading-anchors-js',
            'wikiDynamicHeadingAnchorsSettings',
            array(
                'headingTags' => $this->settings['heading_tags'],
                'menuClass'   => $this->settings['menu_class']
            )
        );
        
        // Get current post ID
        global $post;
        $post_id = $post ? $post->ID : 0;
        
        if ($post_id) {
            // Get headings for current post and pass to JavaScript
            $headings = $this->list_post_headings_for_menu($post_id);
            
            // Get post content for debugging
            $content_preview = $post ? substr($post->post_content, 0, 100) : '';
            
            wp_localize_script(
                'wiki-dynamic-heading-anchors-js',
                'wikiDynamicHeadingAnchorsData',
                array(
                    'postId' => $post_id,
                    'headings' => $headings,
                    'postType' => $post ? $post->post_type : '',
                    'postContent' => $content_preview
                )
            );
        }
    }

    public function enqueue_admin_scripts() {
        // Enqueue admin CSS
        wp_enqueue_style(
            'wiki-dynamic-heading-anchors-admin',
            plugins_url('css/admin-style.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'css/admin-style.css')
        );
    }

    public function add_meta_boxes() {
        // Add meta box for all supported post types
        foreach ($this->settings['post_types'] as $post_type) {
            add_meta_box(
                'wiki_dynamic_heading_anchors_metabox',
                'Dynamic Menu Anchors',
                array($this, 'meta_box_callback'),
                $post_type,
                'side',
                'high'
            );
        }
    }

    public function meta_box_callback($post) {
        wp_nonce_field('wiki_dynamic_heading_anchors_add_to_menu', 'wiki_dynamic_heading_anchors_add_to_menu_nonce');
        
        // Get available menus
        $menus = wp_get_nav_menus();
        if (empty($menus)) {
            echo '<div class="notice notice-warning inline"><p>No menus found. <a href="' . admin_url('nav-menus.php') . '">Create a menu</a> first.</p></div>';
            return;
        }
        
        // Check if post type is supported
        if (!in_array($post->post_type, $this->settings['post_types'])) {
            echo '<div class="notice notice-warning inline"><p>This post type is not supported in plugin settings.</p></div>';
            return;
        }
        
        // Get post headings - these are already filtered by the get_post_headings function
        $headings = $this->get_post_headings($post);
        
        // Additional filtering to ensure only enabled heading tags are shown
        $filtered_headings = array();
        foreach ($headings as $heading) {
            // Convert both to lowercase for case-insensitive comparison
            if (in_array(strtolower($heading['tag']), array_map('strtolower', $this->settings['heading_tags']))) {
                $filtered_headings[] = $heading;
            }
        }
        
        // Get selected menu for headings
        $selected_headings_menu_id = get_post_meta($post->ID, '_wiki_dynamic_heading_anchors_headings_menu_id', true);
        ?>
        <div class="wiki-heading-anchors-box">
            <!-- Post Title to Menu Section -->
            <div class="post-title-to-menu-section">
                <h4><?php _e('Add Post Title to Menu', 'wiki-dynamic-heading-anchors'); ?></h4>
                <p class="description"><?php _e('Add this post\'s title as a menu item to the primary menu.', 'wiki-dynamic-heading-anchors'); ?></p>
                
                <p>
                    <button type="button" class="button" id="wiki_add_title_to_menu">
                        <?php _e('Add Title to Menu', 'wiki-dynamic-heading-anchors'); ?>
                    </button>
                </p>
            </div>
            
            <hr>
            
            <!-- Headings to Menu Section -->
            <div class="headings-to-menu-section">
                <h4><?php _e('Add Headings as Anchor Links', 'wiki-dynamic-heading-anchors'); ?></h4>
                
                <?php if (!empty($filtered_headings)) : ?>
                    <p class="description"><?php _e('Select headings to add as anchor links to a menu.', 'wiki-dynamic-heading-anchors'); ?></p>
                    
                    <select name="wiki_dynamic_heading_anchors_headings_menu_id" id="wiki_dynamic_heading_anchors_headings_menu_id">
                        <option value=""><?php _e('-- Select Menu --', 'wiki-dynamic-heading-anchors'); ?></option>
                        <?php foreach ($menus as $menu) : ?>
                            <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($selected_headings_menu_id, $menu->term_id); ?>>
                                <?php echo esc_html($menu->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="heading-list" style="margin-top: 10px; max-height: 200px; overflow-y: auto;">
                        <?php foreach ($filtered_headings as $heading) : ?>
                            <label>
                                <input type="checkbox" name="wiki_dynamic_heading_anchors_headings[]" value="<?php echo esc_attr($heading['id']); ?>" data-text="<?php echo esc_attr($heading['text']); ?>">
                                <?php echo esc_html($heading['text']); ?> <small>(<?php echo esc_html(strtoupper($heading['tag'])); ?>)</small>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                    
                    <p>
                        <button type="button" class="button" id="wiki_add_headings_to_menu">
                            <?php _e('Add Selected Headings to Menu', 'wiki-dynamic-heading-anchors'); ?>
                        </button>
                    </p>
                <?php else : ?>
                    <p class="description"><?php _e('No headings found in this post that match your settings.', 'wiki-dynamic-heading-anchors'); ?></p>
                <?php endif; ?>
            </div>
            
            <div id="wiki_heading_anchors_message"></div>
            
            <script>
                jQuery(document).ready(function($) {
                    // Add post title to menu
                    $('#wiki_add_title_to_menu').on('click', function() {
                        // Add the post title to the primary menu
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'wiki_dynamic_heading_anchors_save_title_menu',
                                post_id: <?php echo $post->ID; ?>,
                                nonce: '<?php echo wp_create_nonce('wiki_dynamic_heading_anchors_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#wiki_heading_anchors_message').html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                                    
                                    // Check if the menu already exists in the dropdown
                                    var menuExists = false;
                                    var $select = $('#wiki_dynamic_heading_anchors_headings_menu_id');
                                    
                                    $select.find('option').each(function() {
                                        if ($(this).val() == response.data.menu_id) {
                                            menuExists = true;
                                            return false; // break the loop
                                        }
                                    });
                                    
                                    // If the menu doesn't exist in the dropdown, add it
                                    if (!menuExists && response.data.menu_id && response.data.menu_name) {
                                        var $option = $('<option></option>')
                                            .val(response.data.menu_id)
                                            .text(response.data.menu_name);
                                        $select.append($option);
                                    }
                                    
                                    // Select the menu in the dropdown
                                    if (response.data.menu_id) {
                                        $select.val(response.data.menu_id);
                                    }
                                } else {
                                    $('#wiki_heading_anchors_message').html('<div class="notice notice-error inline" style="color: red"><p>' + response.data + '</p></div>');
                                }
                            }
                        });
                    });
                    
                    // Add headings to menu
                    $('#wiki_add_headings_to_menu').on('click', function() {
                        var menuId = $('#wiki_dynamic_heading_anchors_headings_menu_id').val();
                        var selectedHeadings = [];
                        
                        if (!menuId) {
                            alert('<?php _e("Please select a menu first.", "wiki-dynamic-heading-anchors"); ?>');
                            return;
                        }
                        
                        $('input[name="wiki_dynamic_heading_anchors_headings[]"]:checked').each(function() {
                            selectedHeadings.push({
                                id: $(this).val(),
                                text: $(this).data('text')
                            });
                        });
                        
                        if (selectedHeadings.length === 0) {
                            alert('<?php _e("Please select at least one heading.", "wiki-dynamic-heading-anchors"); ?>');
                            return;
                        }
                        
                        // Save the selected menu ID as post meta and add headings to menu
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'wiki_dynamic_heading_anchors_add_headings_to_menu',
                                post_id: <?php echo $post->ID; ?>,
                                menu_id: menuId,
                                headings: selectedHeadings,
                                nonce: '<?php echo wp_create_nonce('wiki_dynamic_heading_anchors_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#wiki_heading_anchors_message').html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                                } else {
                                    $('#wiki_heading_anchors_message').html('<div class="notice notice-error inline" style="color: red"><p>' + response.data + '</p></div>');
                                }
                            }
                        });
                    });
                });
            </script>
        </div>
        <?php
    }

    public function handle_add_to_menu_form_submission() {
        // Verify nonce and check if this is our form submission
        if (!isset($_POST['action']) || $_POST['action'] !== 'wiki_dynamic_heading_anchors_add_to_menu' || 
            !isset($_POST['wiki_dynamic_heading_anchors_add_to_menu_nonce']) || 
            !wp_verify_nonce($_POST['wiki_dynamic_heading_anchors_add_to_menu_nonce'], 'wiki_dynamic_heading_anchors_add_to_menu')) {
            wp_die('Invalid request');
        }

        // Get and validate form data
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $menu_id = isset($_POST['menu_id']) ? absint($_POST['menu_id']) : 0;
        $create_menu_item = isset($_POST['create_menu_item']);
        $add_headings = isset($_POST['add_headings']);

        // Validate post
        $post = get_post($post_id);
        if (!$post) {
            wp_die('Invalid post.');
        }

        // Validate menu
        if (!$menu_id || !is_nav_menu($menu_id)) {
            wp_die('Please select a valid menu.');
        }

        $parent_id = 0;
        $message = '';

        // Add or update the post as a menu item
        if ($create_menu_item) {
            $menu_item_data = array(
                'menu-item-title'     => $post->post_title,
                'menu-item-object-id' => $post->ID,
                'menu-item-object'    => $post->post_type,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish'
            );

            $parent_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
            
            if (is_wp_error($parent_id)) {
                wp_die($parent_id->get_error_message());
            }
            
            $message .= 'Added post title to menu. ';
        }

        // Add headings as sub-items
        if ($add_headings) {
            $headings = $this->get_post_headings($post);
            $added_count = 0;
            
            foreach ($headings as $heading) {
                $menu_item_data = array(
                    'menu-item-title'      => wp_strip_all_tags($heading['text']),
                    'menu-item-url'        => get_permalink($post->ID) . '#' . $heading['id'],
                    'menu-item-type'       => 'custom',
                    'menu-item-status'     => 'publish',
                    'menu-item-parent-id'  => $parent_id,
                    'menu-item-classes'    => $this->settings['menu_class']
                );
                
                $item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
                
                if (!is_wp_error($item_id)) {
                    $added_count++;
                }
            }
            
            if ($added_count > 0) {
                $message .= sprintf('%d heading(s) added as sub-items.', $added_count);
            }
        }

        // Redirect back to the post edit screen with message
        wp_redirect(add_query_arg('message', urlencode($message), get_edit_post_link($post_id, 'url')));
        exit;
    }

    public function get_post_headings($post) {
        $headings = array();
        $content = $post->post_content;
        
        // Check if post type is supported
        if (!in_array($post->post_type, $this->settings['post_types'])) {
            return array();
        }
        
        // Check for headings in post content
        if (!empty($content)) {
            // Get all configured heading tags
            foreach ($this->settings['heading_tags'] as $tag) {
                preg_match_all('/<' . $tag . '[^>]*>(.*?)<\/' . $tag . '>/i', $content, $matches);
                
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $heading_text) {
                        $text = strip_tags($heading_text);
                        if (!empty($text)) {
                            $id = sanitize_title($text);
                            $headings[] = array(
                                'text' => $text,
                                'id' => $id,
                                'tag' => $tag,
                                'source' => 'post_content'
                            );
                        }
                    }
                }
            }
        }
        
        // Check for headings in meta fields (ACF, etc.)
        $meta_keys = get_post_custom_keys($post->ID);
        if ($meta_keys) {
            foreach ($meta_keys as $key) {
                // Skip internal meta keys and ACF field keys
                if (substr($key, 0, 1) === '_' || strpos($key, 'field_') === 0) {
                    continue;
                }
                
                $meta_value = get_post_meta($post->ID, $key, true);
                
                // Skip empty values
                if (empty($meta_value)) {
                    continue;
                }
                
                // Check for Elementor data
                if ($key === '_elementor_data' && is_string($meta_value)) {
                    // Try to decode JSON
                    $elementor_data = json_decode($meta_value, true);
                    if (is_array($elementor_data)) {
                        $this->extract_elementor_headings_with_filter($elementor_data, $headings);
                    }
                }
                
                // Process section titles and other meta fields that might contain headings
                if (is_string($meta_value) && 
                    (strpos($key, 'section_title') !== false || 
                     strpos($key, 'heading') !== false || 
                     strpos($key, 'title') !== false) && 
                    strlen($meta_value) > 3 && 
                    strlen($meta_value) < 100) {
                    
                    // Determine the appropriate heading tag based on the field name
                    $tag = $this->determine_heading_tag_from_field_name($key);
                    
                    // Only include if the tag is in the settings
                    if (in_array(strtolower($tag), array_map('strtolower', $this->settings['heading_tags']))) {
                        $id = sanitize_title($meta_value);
                        $headings[] = array(
                            'text' => $meta_value,
                            'id' => $id,
                            'tag' => $tag,
                            'source' => 'meta:' . $key
                        );
                    }
                }
            }
        }
        
        // Remove duplicates
        $unique_headings = array();
        $ids = array();
        
        foreach ($headings as $heading) {
            if (!in_array($heading['id'], $ids)) {
                $ids[] = $heading['id'];
                $unique_headings[] = $heading;
            }
        }
        
        return $unique_headings;
    }
    
    /**
     * Extract headings from Elementor data with filtering based on settings
     *
     * @param array $elementor_data Elementor data array
     * @param array &$headings Headings array to populate
     */
    private function extract_elementor_headings_with_filter($elementor_data, &$headings) {
        if (!is_array($elementor_data)) {
            return;
        }
        
        foreach ($elementor_data as $element) {
            if (!isset($element['elType'])) {
                continue;
            }
            
            // Check for heading widgets
            if ($element['elType'] === 'widget' && isset($element['widgetType']) && $element['widgetType'] === 'heading') {
                if (isset($element['settings']['title'])) {
                    $title = $element['settings']['title'];
                    $tag = isset($element['settings']['header_size']) ? $element['settings']['header_size'] : 'h2';
                    
                    // Only include headings with tags that are in settings (case-insensitive comparison)
                    if (in_array(strtolower($tag), array_map('strtolower', $this->settings['heading_tags']))) {
                        $id = sanitize_title($title);
                        $headings[] = array(
                            'text' => $title,
                            'id' => $id,
                            'tag' => $tag,
                            'source' => 'elementor'
                        );
                    }
                }
            }
            
            // Process inner elements recursively
            if (isset($element['elements']) && is_array($element['elements'])) {
                $this->extract_elementor_headings_with_filter($element['elements'], $headings);
            }
        }
    }
    
    /**
     * Determine the appropriate heading tag based on the field name
     *
     * @param string $field_name The field name to analyze
     * @return string The heading tag (h1, h2, h3, etc.)
     */
    private function determine_heading_tag_from_field_name($field_name) {
        // Default to h2 for most fields
        $tag = 'h2';
        
        // Check for specific patterns in field names that indicate heading level
        if (strpos($field_name, 'sub_title') !== false || 
            strpos($field_name, 'subtitle') !== false || 
            strpos($field_name, 'sub_heading') !== false || 
            strpos($field_name, 'subheading') !== false) {
            $tag = 'h3';
        } else if (strpos($field_name, 'main_title') !== false || 
                 strpos($field_name, 'main_heading') !== false || 
                 strpos($field_name, 'page_title') !== false) {
            $tag = 'h1';
        }
        
        return $tag;
    }

    public function list_post_headings_for_menu($post_id) {
        // Get post
        $post = get_post($post_id);
        
        if (!$post) {
            return array();
        }
        
        // Check if post type is supported
        if (!in_array($post->post_type, $this->settings['post_types'])) {
            return array();
        }
        
        // Get all headings
        $headings = array();
        
        // Check for headings in post meta (ACF fields, etc.)
        $meta_keys = get_post_custom_keys($post_id);
        
        if ($meta_keys) {
            foreach ($meta_keys as $key) {
                // Skip internal meta keys and ACF field keys
                if (substr($key, 0, 1) === '_' || strpos($key, 'field_') === 0) {
                    continue;
                }
                
                $meta_value = get_post_meta($post_id, $key, true);
                
                // Skip empty values and ACF field keys
                if (empty($meta_value) || (is_string($meta_value) && strpos($meta_value, 'field_') === 0)) {
                    continue;
                }
                
                // Skip serialized data that doesn't contain actual content
                if (is_string($meta_value) && $this->looks_like_serialized($meta_value)) {
                    $unserialized = maybe_unserialize($meta_value);
                    
                    // Only process if it's an array with actual text content
                    if (is_array($unserialized)) {
                        $this->extract_headings_from_array($unserialized, $headings);
                    }
                    continue;
                }
                
                // Only process text fields that look like headings (not too long, not too short)
                if (is_string($meta_value) && strlen($meta_value) > 3 && strlen($meta_value) < 100) {
                    // Skip ACF field keys and other non-heading content
                    if ((is_string($meta_value) && strpos($meta_value, 'field_') === 0) || (is_string($meta_value) && strpos($meta_value, 'a:') === 0)) {
                        continue;
                    }
                    
                    $headings[] = array(
                        'id' => sanitize_title($meta_value),
                        'text' => $meta_value,
                        'tag' => 'h2', // Default to h2 for meta headings
                        'source' => 'meta:' . $key
                    );
                }
            }
        }
        
        // Check for Smart Custom Fields
        global $wpdb;
        $scf_meta = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key, meta_value FROM $wpdb->postmeta 
                WHERE post_id = %d AND meta_key LIKE %s",
                $post_id,
                'smart-custom-fields-%'
            )
        );
        
        if ($scf_meta) {
            foreach ($scf_meta as $meta) {
                // Only process text fields that look like headings
                if (is_string($meta->meta_value) && strlen($meta->meta_value) > 3 && strlen($meta->meta_value) < 100) {
                    // Skip ACF field keys and other non-heading content
                    if ((is_string($meta->meta_value) && strpos($meta->meta_value, 'field_') === 0) || (is_string($meta->meta_value) && strpos($meta->meta_value, 'a:') === 0)) {
                        continue;
                    }
                    
                    $headings[] = array(
                        'id' => sanitize_title($meta->meta_value),
                        'text' => $meta->meta_value,
                        'tag' => 'h2',
                        'source' => 'scf:' . $meta->meta_key
                    );
                }
            }
        } else {
            // No SCF fields found in direct database query
        }
        
        // Check for Elementor data
        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        if ($elementor_data) {
            $elementor_headings = array();
            $this->extract_elementor_headings($elementor_data, $elementor_headings);
            $headings = array_merge($headings, $elementor_headings);
        } else {
            // No Elementor data found for post ID: $post_id
        }
        
        // Check post content for headings
        if ($post->post_content) {
            $content_headings = $this->extract_headings_from_content($post->post_content);
            $headings = array_merge($headings, $content_headings);
        }
        
        // Filter out duplicate headings by ID
        $unique_headings = array();
        $seen_ids = array();
        
        foreach ($headings as $heading) {
            if (!in_array($heading['id'], $seen_ids)) {
                $seen_ids[] = $heading['id'];
                $unique_headings[] = $heading;
            }
        }
        
        return $unique_headings;
    }
    
    /**
     * Check if a string looks like it might be serialized data
     * 
     * @param string $data String to check
     * @return bool True if it looks serialized
     */
    private function looks_like_serialized($data) {
        // If it's not a string, it's not serialized
        if (!is_string($data)) {
            return false;
        }
        
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        
        if (strlen($data) < 4) {
            return false;
        }
        
        // Serialized format: a:size:{i:index;s:size:"string";}
        if (':' !== $data[1]) {
            return false;
        }
        
        // Common serialized formats
        $first_char = $data[0];
        if ('a' === $first_char || 'O' === $first_char || 's' === $first_char || 'i' === $first_char || 'd' === $first_char || 'b' === $first_char) {
            // Make sure the string ends with a semicolon
            if (';' !== substr($data, -1)) {
                return false;
            }
            
            // Simple check for common serialized formats
            return (bool) preg_match('/^[aOsid]:[0-9]+:|^b:[01];/', $data);
        }
        
        return false;
    }
    
    /**
     * Extract headings from an array recursively
     * 
     * @param array $array Array to extract headings from
     * @param array &$headings Headings array to populate
     * @param string $key_prefix Key prefix for source tracking
     */
    private function extract_headings_from_array($array, &$headings, $key_prefix = '') {
        if (!is_array($array)) {
            return;
        }
        
        foreach ($array as $key => $value) {
            $current_key = $key_prefix ? $key_prefix . '.' . $key : $key;
            
            // If this looks like a heading (key contains 'title', 'heading', etc.)
            $heading_key_words = array('title', 'heading', 'header', 'section', 'subtitle', 'subheading');
            $is_heading_key = false;
            
            foreach ($heading_key_words as $word) {
                if (stripos($key, $word) !== false) {
                    $is_heading_key = true;
                    break;
                }
            }
            
            // Process string values that look like headings
            if (is_string($value) && !empty($value)) {
                $text = wp_strip_all_tags($value);
                $text = trim($text);
                
                // Skip if too short or too long to be a heading
                if (strlen($text) < 3 || strlen($text) > 100) {
                    continue;
                }
                
                // Skip ACF field keys and other non-heading content
                if ((is_string($text) && strpos($text, 'field_') === 0) || (is_string($text) && strpos($text, 'a:') === 0)) {
                    continue;
                }
                
                // If the key suggests this is a heading, or the text is short enough to be a heading
                if ($is_heading_key || strlen($text) < 60) {
                    $id = sanitize_title($text);
                    
                    $headings[] = array(
                        'id' => $id,
                        'text' => $text,
                        'tag' => 'h2', // Default to h2 for meta headings
                        'source' => 'serialized:' . $current_key
                    );
                }
            }
            
            // Recursively process nested arrays
            if (is_array($value)) {
                $this->extract_headings_from_array($value, $headings, $current_key);
            }
            // Try to unserialize string values that might be serialized arrays
            else if (is_string($value) && $this->looks_like_serialized($value)) {
                $unserialized = @unserialize($value);
                if (is_array($unserialized)) {
                    $this->extract_headings_from_array($unserialized, $headings, $current_key);
                }
            }
        }
    }
    
    /**
     * Extract headings from Elementor data
     * 
     * @param string $elementor_data Elementor data JSON string
     * @param array &$headings Headings array to populate
     */
    private function extract_elementor_headings($elementor_data, &$headings) {
        // Skip if data is empty
        if (empty($elementor_data)) {
            return;
        }
        
        // Try to decode JSON
        $data = json_decode($elementor_data, true);
        if (!is_array($data)) {
            return;
        }
        
        // Process each element
        foreach ($data as $element) {
            $this->process_elementor_elements($element, $headings);
        }
    }
    
    /**
     * Process Elementor elements recursively to find headings
     * 
     * @param array $element Elementor element
     * @param array &$headings Headings array to populate
     */
    private function process_elementor_elements($element, &$headings) {
        if (!is_array($element)) {
            return;
        }
        
        // Check for heading widgets
        if (isset($element['widgetType']) && $element['widgetType'] === 'heading') {
            if (isset($element['settings']['title'])) {
                $text = wp_strip_all_tags($element['settings']['title']);
                $text = trim($text);
                
                if (!empty($text)) {
                    // Determine heading level (h1-h6)
                    $tag = 'h2'; // Default
                    if (isset($element['settings']['header_size'])) {
                        $tag = $element['settings']['header_size'];
                    }
                    
                    $id = sanitize_title($text);
                    
                    $headings[] = array(
                        'id' => $id,
                        'text' => $text,
                        'tag' => $tag,
                        'source' => 'elementor:heading'
                    );
                }
            }
        }
        
        // Check for section titles
        if (isset($element['settings']['section_title']) && !empty($element['settings']['section_title'])) {
            $text = wp_strip_all_tags($element['settings']['section_title']);
            $text = trim($text);
            
            if (!empty($text)) {
                $id = sanitize_title($text);
                
                $headings[] = array(
                    'id' => $id,
                    'text' => $text,
                    'tag' => 'h2', // Default for section titles
                    'source' => 'elementor:section_title'
                );
            }
        }
        
        // Check for dynamic content for Elementor widgets
        if (isset($element['widgetType']) && 
            (is_string($element['widgetType']) && strpos($element['widgetType'], 'dce') !== false || 
             is_string($element['widgetType']) && strpos($element['widgetType'], 'dynamic') !== false)) {
            
            // Look for settings that might contain field mappings
            if (isset($element['settings'])) {
                foreach ($element['settings'] as $key => $value) {
                    if ((is_string($key) && strpos($key, 'title') !== false || 
                         is_string($key) && strpos($key, 'heading') !== false || 
                         is_string($key) && strpos($key, 'header') !== false) && 
                        is_string($value) && !empty($value)) {
                        
                        $text = wp_strip_all_tags($value);
                        $text = trim($text);
                        
                        if (!empty($text)) {
                            $id = sanitize_title($text);
                            $headings[] = array(
                                'id' => $id,
                                'text' => $text,
                                'tag' => 'h2', // Default
                                'source' => 'elementor_dynamic'
                            );
                        }
                    }
                }
            }
        }
        
        // Recursively process any elements that have children
        if (isset($element['elements']) && is_array($element['elements'])) {
            foreach ($element['elements'] as $child) {
                $this->process_elementor_elements($child, $headings);
            }
        }
    }

    public function add_post_title_to_menu($post_id, $post, $update) {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check if post type is supported
        if (!in_array($post->post_type, $this->settings['post_types'])) {
            return;
        }
        
        // Check if post is published
        if ('publish' !== $post->post_status) {
            return;
        }
        
        // Check if a menu already exists for this post
        $menu_name = $post->post_title;
        $existing_menu = get_term_by('name', $menu_name, 'nav_menu');
        
        if (!$existing_menu) {
            // Create a new menu with the post title as the menu name
            $menu_id = wp_create_nav_menu($menu_name);
            if (is_wp_error($menu_id)) {
                return; // Could not create menu, just return
            }
            
            // Save the menu ID as the selected menu for this post
            update_post_meta($post_id, '_wiki_dynamic_heading_anchors_headings_menu_id', $menu_id);
        } else {
            // Menu already exists, save its ID as the selected menu for this post
            update_post_meta($post_id, '_wiki_dynamic_heading_anchors_headings_menu_id', $existing_menu->term_id);
        }
    }

    public function ajax_list_post_headings() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wiki_dynamic_heading_anchors_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }
        
        // Check post ID
        if (!isset($_POST['post_id']) || !absint($_POST['post_id'])) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
        }
        
        $post_id = absint($_POST['post_id']);
        $headings = $this->list_post_headings_for_menu($post_id);
        
        wp_send_json_success(array(
            'headings' => $headings,
            'permalink' => get_permalink($post_id)
        ));
    }
    
    /**
     * AJAX handler for saving title menu
     */
    public function ajax_save_title_menu() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wiki_dynamic_heading_anchors_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }
        
        // Check post ID
        if (!isset($_POST['post_id']) || !absint($_POST['post_id'])) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
        }
        
        $post_id = absint($_POST['post_id']);
        
        // Get the post
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array('message' => 'Post not found.'));
            return;
        }
        
        // Check if a menu already exists for this post
        $menu_name = $post->post_title;
        $existing_menu = get_term_by('name', $menu_name, 'nav_menu');
        
        if ($existing_menu) {
            // Menu already exists, use its ID
            $menu_id = $existing_menu->term_id;
            
            // Still save this menu ID as the selected one for this post
            update_post_meta($post_id, '_wiki_dynamic_heading_anchors_headings_menu_id', $menu_id);
            
            // Return success with the menu ID so it can be selected in the dropdown
            wp_send_json_success(array(
                'message' => sprintf('Menu "%s" already exists and is now selected.', $menu_name),
                'menu_id' => $menu_id,
                'menu_name' => $menu_name
            ));
            return;
        } else {
            // Create a new menu with the post title as the menu name
            $menu_id = wp_create_nav_menu($menu_name);
            if (is_wp_error($menu_id)) {
                wp_send_json_error(array('message' => 'Could not create a new menu: ' . $menu_id->get_error_message()));
                return;
            }
            
            // Save the menu ID as the selected menu for this post
            update_post_meta($post_id, '_wiki_dynamic_heading_anchors_headings_menu_id', $menu_id);
            
            // Get the menu object to return its name
            $menu_obj = wp_get_nav_menu_object($menu_id);
            
            wp_send_json_success(array(
                'message' => sprintf('Created new top-level menu "%s".', $menu_name),
                'menu_id' => $menu_id,
                'menu_name' => $menu_name
            ));
        }
    }
    
    /**
     * AJAX handler for adding headings to menu
     */
    public function ajax_add_headings_to_menu() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wiki_dynamic_heading_anchors_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }
        
        // Check post ID
        if (!isset($_POST['post_id']) || !absint($_POST['post_id'])) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
        }
        
        // Check menu ID
        if (!isset($_POST['menu_id']) || !absint($_POST['menu_id'])) {
            wp_send_json_error(array('message' => 'Invalid menu ID'));
        }
        
        // Check headings
        if (!isset($_POST['headings']) || !is_array($_POST['headings'])) {
            wp_send_json_error(array('message' => 'Invalid headings'));
        }
        
        $post_id = absint($_POST['post_id']);
        $menu_id = absint($_POST['menu_id']);
        $headings = $_POST['headings'];
        
        // Save the selected menu ID as post meta
        update_post_meta($post_id, '_wiki_dynamic_heading_anchors_headings_menu_id', $menu_id);
        
        // Get existing menu items to check for duplicates
        $existing_menu_items = wp_get_nav_menu_items($menu_id);
        $existing_urls = array();
        
        if ($existing_menu_items) {
            foreach ($existing_menu_items as $item) {
                $existing_urls[] = $item->url;
            }
        }
        
        // Add headings to menu
        $added_count = 0;
        $skipped_count = 0;
        
        foreach ($headings as $heading) {
            $heading_url = get_permalink($post_id) . '#' . $heading['id'];
            
            // Check if this URL already exists in the menu
            if (in_array($heading_url, $existing_urls)) {
                $skipped_count++;
                continue; // Skip this heading as it already exists in the menu
            }
            
            $item_data = array(
                'menu-item-title' => $heading['text'],
                'menu-item-url' => $heading_url,
                'menu-item-type' => 'custom',
                'menu-item-status' => 'publish',
                'menu-item-parent-id' => 0,
                'menu-item-classes' => $this->settings['menu_class']
            );
            
            $item_id = wp_update_nav_menu_item($menu_id, 0, $item_data);
            
            if (!is_wp_error($item_id)) {
                $added_count++;
            }
        }
        
        $message = sprintf('%d heading(s) added to menu.', $added_count);
        if ($skipped_count > 0) {
            $message .= sprintf(' %d heading(s) skipped because they already exist in the menu.', $skipped_count);
        }
        
        wp_send_json_success(array(
            'message' => $message
        ));
    }
    
    /**
     * Generate ID from text
     * 
     * @param string $text Text to generate ID from
     * @return string ID
     */
    public function generate_id_from_text($text) {
        return sanitize_title($text);
    }
    
    /**
     * Register shortcode for heading permalinks
     */
    public function register_shortcodes() {
        add_shortcode('heading_permalink', array($this, 'heading_permalink_shortcode'));
    }
    
    /**
     * Shortcode to create a permalink to a heading
     * 
     * Usage: [heading_permalink text="Heading Text" link_text="Click here"]  
     * 
     * @param array $atts Shortcode attributes
     * @return string The permalink HTML
     */
    public function heading_permalink_shortcode($atts) {
        $atts = shortcode_atts(array(
            'text' => '',            // The heading text to link to
            'link_text' => 'Link',   // The text for the link
            'class' => '',           // Additional CSS classes
            'id' => '',              // Direct ID to link to (alternative to text)
        ), $atts, 'heading_permalink');
        
        $url = '';
        
        // If ID is provided directly, use it
        if (!empty($atts['id'])) {
            $url = '#' . $atts['id'];
        }
        // Otherwise generate ID from text
        else if (!empty($atts['text'])) {
            $id = sanitize_title($atts['text']);
            $url = '#' . $id;
        }
        
        if (empty($url)) {
            return '';
        }
        
        $classes = 'heading-permalink';
        if (!empty($atts['class'])) {
            $classes .= ' ' . $atts['class'];
        }
        
        return sprintf(
            '<a href="%s" class="%s">%s</a>',
            esc_url($url),
            esc_attr($classes),
            esc_html($atts['link_text'])
        );
    }
    
    /**
     * Add permalink button to TinyMCE editor
     */
    public function add_permalink_button() {
        // Only add if user can edit posts
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        
        // Add only in rich editor mode
        if (get_user_option('rich_editing') !== 'true') {
            return;
        }
        
        // Add the button to the editor
        add_filter('mce_external_plugins', array($this, 'add_permalink_tinymce_plugin'));
        add_filter('mce_buttons', array($this, 'register_permalink_button'));
    }
    
    /**
     * Register the TinyMCE plugin
     */
    public function add_permalink_tinymce_plugin($plugin_array) {
        $plugin_array['wiki_heading_permalink'] = plugins_url('js/tinymce-permalink-plugin.js', __FILE__);
        return $plugin_array;
    }
    
    /**
     * Register the button in the editor
     */
    public function register_permalink_button($buttons) {
        array_push($buttons, 'wiki_heading_permalink');
        return $buttons;
    }
    
    /**
     * Add AJAX action to get all headings in a post
     */
    public function register_ajax_actions() {
        add_action('wp_ajax_get_post_headings', array($this, 'ajax_get_post_headings'));
        add_action('wp_ajax_nopriv_get_post_headings', array($this, 'ajax_get_post_headings'));
    }
    
    /**
     * AJAX handler to get all headings in a post
     */
    public function ajax_get_post_headings() {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid post ID'));
        }
        
        $headings = $this->list_post_headings_for_menu($post_id);
        
        wp_send_json_success(array(
            'headings' => $headings,
            'post_id' => $post_id
        ));
    }
    
    /**
     * Add script to expose heading data to frontend
     */
    public function enqueue_frontend_scripts() {
        global $post;
        
        if (!is_singular() || !$post) {
            return;
        }
        
        // Get the headings for this post
        $headings = $this->list_post_headings_for_menu($post->ID);
        
        // Get post content for debugging
        $content_preview = substr($post->post_content, 0, 200);
        $content_length = strlen($post->post_content);
        
        // Get all meta keys for debugging
        $meta_keys = array_keys(get_post_meta($post->ID));
        
        // Add the headings data to the page
        wp_localize_script('wiki-dynamic-heading-anchors-js', 'wikiDynamicHeadingAnchorsData', array(
            'headings' => $headings,
            'post_id' => $post->ID,
            'ajax_url' => admin_url('admin-ajax.php'),
            'permalink' => get_permalink($post->ID),
            'debug' => array(
                'post_type' => $post->post_type,
                'content_preview' => $content_preview,
                'content_length' => $content_length,
                'meta_keys' => $meta_keys,
                'heading_tags' => $this->settings['heading_tags'],
                'post_types' => $this->settings['post_types']
            )
        ));
    }

    /**
     * Add post headings to menu
     * 
     * This function adds post headings as menu items
     * 
     * @param array $items Menu items
     * @param object $menu Menu object
     * @param array $args Menu arguments
     * @return array Modified menu items
     */
    public function add_post_headings_to_menu($items, $menu, $args) {
        // Cache the menu items to avoid duplicate processing
        static $processed_menus = array();
        $menu_id = $menu->term_id;
        
        if (isset($processed_menus[$menu_id])) {
            return $processed_menus[$menu_id];
        }
        
        // Get all post IDs that are in this menu
        $post_ids = array();
        foreach ($items as $item) {
            if ($item->type === 'post_type' && in_array($item->object, $this->settings['post_types'])) {
                $post_ids[$item->object_id] = $item->ID; // Store post ID => menu item ID mapping
            }
        }
        
        if (empty($post_ids)) {
            $processed_menus[$menu_id] = $items;
            return $items;
        }
        
        // Process each post to add its headings as menu items
        $new_items = array();
        
        foreach ($post_ids as $post_id => $parent_item_id) {
            // Get headings for this post
            $headings = $this->list_post_headings_for_menu($post_id);
            
            if (!empty($headings)) {
                foreach ($headings as $index => $heading) {
                    // Create a new menu item for this heading
                    $new_item = new stdClass();
                    
                    // Copy properties from parent menu item
                    foreach (get_object_vars($items[$parent_item_id - 1]) as $key => $value) {
                        $new_item->$key = $value;
                    }
                    
                    // Update properties for this heading
                    $new_item->ID = $parent_item_id . 'h' . ($index + 1); // Create a unique ID
                    $new_item->db_id = 0; // Not in the database
                    $new_item->menu_item_parent = $parent_item_id;
                    $new_item->title = $heading['text'];
                    $new_item->url = $items[$parent_item_id - 1]->url . '#' . $heading['id'];
                    $new_item->classes[] = $this->settings['menu_class'];
                    $new_item->classes[] = 'heading-tag-' . $heading['tag'];
                    
                    $new_items[] = $new_item;
                }
            }
        }
        
        // Merge original items with new heading items
        if (!empty($new_items)) {
            $items = array_merge($items, $new_items);
        }
        
        // Cache the result
        $processed_menus[$menu_id] = $items;
        
        return $items;
    }

    /**
     * Extract headings from post content
     * 
     * @param string $content Post content
     * @return array Headings
     */
    private function extract_headings_from_content($content) {
        $headings = array();
        
        // Skip if content is empty
        if (empty($content)) {
            return $headings;
        }
        
        // Use regex to find heading tags
        $pattern = '/<h([1-6])[^>]*>(.*?)<\/h\1>/i';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tag = 'h' . $match[1];
                $text = wp_strip_all_tags($match[2]);
                $text = trim($text);
                
                if (!empty($text)) {
                    $id = sanitize_title($text);
                    
                    $headings[] = array(
                        'id' => $id,
                        'text' => $text,
                        'tag' => $tag,
                        'source' => 'content'
                    );
                }
            }
        } else {
            // Try with rendered content
            $rendered_content = apply_filters('the_content', $content);
            if (!empty($rendered_content) && $rendered_content !== $content) {
                if (preg_match_all($pattern, $rendered_content, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $tag = 'h' . $match[1];
                        $text = wp_strip_all_tags($match[2]);
                        $text = trim($text);
                        
                        if (!empty($text)) {
                            $id = sanitize_title($text);
                            
                            $headings[] = array(
                                'id' => $id,
                                'text' => $text,
                                'tag' => $tag,
                                'source' => 'rendered_content'
                            );
                        }
                    }
                }
            }
        }
        
        return $headings;
    }

    /**
     * Add settings link to plugins page
     * 
     * @since 1.0.0
     * @param array $links Array of plugin action links.
     * @return array Modified array of plugin action links.
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=wiki-dynamic-heading-anchors' ) . '">' . esc_html__( 'Settings', 'wiki-dynamic-heading-anchors' ) . '</a>';
        $details_link = '<a href="https://github.com/wikiwyrhead/wordpress-dynamic-menu-anchor" target="_blank">' . esc_html__( 'View Details', 'wiki-dynamic-heading-anchors' ) . '</a>';
        
        array_unshift( $links, $settings_link );
        array_push( $links, $details_link );
        
        return $links;
    }

    /**
     * Modify plugin row meta links
     *
     * @since 1.1.0
     * @param array  $plugin_meta An array of the plugin's metadata.
     * @param string $plugin_file Path to the plugin file relative to the plugins directory.
     * @return array Modified plugin meta links.
     */
    public function modify_plugin_row_meta( $plugin_meta, $plugin_file ) {
        if ( plugin_basename( __FILE__ ) === $plugin_file ) {
            // Replace the "View details" link with GitHub repository
            foreach ( $plugin_meta as $key => $meta ) {
                if ( strpos( $meta, 'plugin-install.php?tab=plugin-information' ) !== false ) {
                    $plugin_meta[$key] = '<a href="https://github.com/wikiwyrhead/wordpress-dynamic-menu-anchor" target="_blank">' . __( 'View Details', 'wiki-dynamic-heading-anchors' ) . '</a>';
                }
            }
        }
        return $plugin_meta;
    }
}

/**
 * Activation hook for the plugin.
 *
 * @since 1.0.0
 */
function wiki_dynamic_heading_anchors_activate() {
    $instance = Wiki_Dynamic_Heading_Anchors::get_instance();
    $instance->activate_plugin();
}

/**
 * Deactivation hook for the plugin.
 *
 * @since 1.0.0
 */
function wiki_dynamic_heading_anchors_deactivate() {
    $instance = Wiki_Dynamic_Heading_Anchors::get_instance();
    $instance->deactivate_plugin();
}

// Initialize plugin
add_action( 'plugins_loaded', array( 'Wiki_Dynamic_Heading_Anchors', 'get_instance' ) );

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'wiki_dynamic_heading_anchors_activate' );
register_deactivation_hook( __FILE__, 'wiki_dynamic_heading_anchors_deactivate' );
