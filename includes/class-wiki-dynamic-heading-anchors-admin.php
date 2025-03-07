<?php
/**
 * Admin functionality
 *
 * @package    WikiDynamicHeadingAnchors
 * @subpackage WikiDynamicHeadingAnchors/includes
 * @since      1.0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class
 *
 * Handles all admin functionality including settings page.
 *
 * @since      1.0.1
 * @package    WikiDynamicHeadingAnchors
 * @subpackage WikiDynamicHeadingAnchors/includes
 */
class Wiki_Dynamic_Heading_Anchors_Admin {

	/**
	 * Plugin settings
	 *
	 * @since  1.0.1
	 * @access private
	 * @var    array
	 */
	private $settings;

	/**
	 * Constructor
	 *
	 * @since 1.0.1
	 * @param array $settings Plugin settings.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 *
	 * @since  1.0.1
	 * @return void
	 */
	private function init_hooks() {
		// Admin hooks
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . WDHA_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add settings link to plugins page
	 *
	 * @since  1.0.1
	 * @param  array $links Plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=wiki-dynamic-heading-anchors' ),
			esc_html__( 'Settings', 'wiki-dynamic-heading-anchors' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function add_menu_page() {
		add_options_page(
			esc_html__( 'Wiki Dynamic Heading Anchors Settings', 'wiki-dynamic-heading-anchors' ),
			esc_html__( 'Wiki Dynamic Heading Anchors', 'wiki-dynamic-heading-anchors' ),
			'manage_options',
			'wiki-dynamic-heading-anchors',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'wiki_heading_anchors_settings_group',
			'wiki_heading_anchors_settings',
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'wiki_heading_anchors_general_section',
			esc_html__( 'General Settings', 'wiki-dynamic-heading-anchors' ),
			array( $this, 'render_general_section' ),
			'wiki-dynamic-heading-anchors'
		);

		// Add settings fields
		$this->add_settings_fields();
	}

	/**
	 * Add settings fields
	 *
	 * @since  1.0.1
	 * @return void
	 */
	private function add_settings_fields() {
		$fields = array(
			'heading_tags' => array(
				'title'    => esc_html__( 'Heading Tags', 'wiki-dynamic-heading-anchors' ),
				'callback' => 'render_heading_tag_field',
			),
			'post_types' => array(
				'title'    => esc_html__( 'Post Types', 'wiki-dynamic-heading-anchors' ),
				'callback' => 'render_post_types_field',
			),
			'menu_class' => array(
				'title'    => esc_html__( 'Menu Item CSS Class', 'wiki-dynamic-heading-anchors' ),
				'callback' => 'render_menu_class_field',
			),
			'scroll_offset' => array(
				'title'    => esc_html__( 'Scroll Offset (px)', 'wiki-dynamic-heading-anchors' ),
				'callback' => 'render_scroll_offset_field',
			),
		);

		foreach ( $fields as $id => $field ) {
			add_settings_field(
				$id,
				$field['title'],
				array( $this, $field['callback'] ),
				'wiki-dynamic-heading-anchors',
				'wiki_heading_anchors_general_section'
			);
		}
	}

	/**
	 * Sanitize settings
	 *
	 * @since  1.0.1
	 * @param  array $input The value being saved.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();
		
		// Sanitize heading tags (now multiple)
		$valid_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
		$sanitized['heading_tags'] = isset( $input['heading_tags'] ) && is_array( $input['heading_tags'] )
			? array_intersect( $input['heading_tags'], $valid_tags )
			: array( 'h2' );
		
		// Sanitize post types
		$valid_post_types = array_keys( get_post_types( array( 'public' => true ) ) );
		$sanitized['post_types'] = isset( $input['post_types'] ) && is_array( $input['post_types'] )
			? array_intersect( $input['post_types'], $valid_post_types )
			: array( 'post', 'page' );
		
		// Sanitize CSS class
		$sanitized['menu_class'] = sanitize_html_class( $input['menu_class'] );
		
		// Sanitize scroll offset
		$sanitized['scroll_offset'] = absint( $input['scroll_offset'] );
		
		return $sanitized;
	}

	/**
	 * Render settings page
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wiki_heading_anchors_settings_group' );
				do_settings_sections( 'wiki-dynamic-heading-anchors' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render general section description
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure which heading tags and post types should have dynamic menu anchors.', 'wiki-dynamic-heading-anchors' ) . '</p>';
	}

	/**
	 * Render heading tag field
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function render_heading_tag_field() {
		$heading_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
		$selected = isset( $this->settings['heading_tags'] ) ? $this->settings['heading_tags'] : 'h2';
		
		echo '<select name="wiki_heading_anchors_settings[heading_tags][]" multiple="multiple">';
		foreach ( $heading_tags as $tag ) {
			$is_selected = in_array( $tag, (array) $selected, true );
			echo '<option value="' . esc_attr( $tag ) . '"' . selected( $is_selected, true, false ) . '>';
			echo esc_html( strtoupper( $tag ) );
			echo '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Select which heading tags should have dynamic anchors.', 'wiki-dynamic-heading-anchors' ) . '</p>';
	}

	/**
	 * Render post types field
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function render_post_types_field() {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$selected = isset( $this->settings['post_types'] ) ? $this->settings['post_types'] : array( 'post', 'page' );
		
		foreach ( $post_types as $post_type ) {
			echo '<label>';
			echo '<input type="checkbox" name="wiki_heading_anchors_settings[post_types][]" value="' . esc_attr( $post_type->name ) . '" ';
			checked( in_array( $post_type->name, $selected, true ) );
			echo '> ' . esc_html( $post_type->label );
			echo '</label><br>';
		}
		echo '<p class="description">' . esc_html__( 'Select which post types should have dynamic anchors.', 'wiki-dynamic-heading-anchors' ) . '</p>';
	}

	/**
	 * Render menu class field
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function render_menu_class_field() {
		$menu_class = isset( $this->settings['menu_class'] ) ? $this->settings['menu_class'] : 'heading-anchor';
		echo '<input type="text" name="wiki_heading_anchors_settings[menu_class]" value="' . esc_attr( $menu_class ) . '" class="regular-text">';
		echo '<p class="description">' . esc_html__( 'CSS class for menu items. This class will be added to the menu items and can be used for styling.', 'wiki-dynamic-heading-anchors' ) . '</p>';
	}

	/**
	 * Render scroll offset field
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function render_scroll_offset_field() {
		$scroll_offset = isset( $this->settings['scroll_offset'] ) ? absint( $this->settings['scroll_offset'] ) : 100;
		echo '<input type="number" name="wiki_heading_anchors_settings[scroll_offset]" value="' . esc_attr( $scroll_offset ) . '" class="small-text">';
		echo '<p class="description">' . esc_html__( 'Offset in pixels from the top of the window when scrolling to anchors.', 'wiki-dynamic-heading-anchors' ) . '</p>';
	}
}
