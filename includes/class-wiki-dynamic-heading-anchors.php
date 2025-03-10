<?php

/**
 * Main plugin class
 *
 * @package    WikiDynamicHeadingAnchors
 * @subpackage WikiDynamicHeadingAnchors/includes
 * @since      1.0.1
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
	exit;
}

/**
 * Main plugin class
 *
 * This class defines all core functionality of the plugin.
 *
 * @since      1.0.1
 * @package    WikiDynamicHeadingAnchors
 * @subpackage WikiDynamicHeadingAnchors/includes
 */
class Wiki_Dynamic_Heading_Anchors
{

	/**
	 * Plugin instance
	 *
	 * @since  1.0.1
	 * @access private
	 * @var    Wiki_Dynamic_Heading_Anchors
	 */
	private static $instance = null;

	/**
	 * Admin class instance
	 *
	 * @since  1.0.1
	 * @access private
	 * @var    Wiki_Dynamic_Heading_Anchors_Admin
	 */
	private $admin;

	/**
	 * Public class instance
	 *
	 * @since  1.0.1
	 * @access private
	 * @var    Wiki_Dynamic_Heading_Anchors_Public
	 */
	private $public;

	/**
	 * Plugin settings
	 *
	 * @since  1.0.1
	 * @access private
	 * @var    array
	 */
	private $settings;

	/**
	 * Get plugin instance
	 *
	 * @since  1.0.1
	 * @return Wiki_Dynamic_Heading_Anchors
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.1
	 */
	private function __construct()
	{
		$this->settings = get_option('wiki_heading_anchors_settings', array(
			'heading_tag'    => 'h2',
			'post_types'     => array('post', 'page'),
			'menu_class'     => 'wiki-heading-anchor',
			'scroll_offset'  => 100
		));
	}

	/**
	 * Initialize plugin
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function init()
	{
		// Initialize admin and public classes
		$this->admin  = new Wiki_Dynamic_Heading_Anchors_Admin($this->settings);
		$this->public = new Wiki_Dynamic_Heading_Anchors_Public($this->settings);

		// Load text domain
		add_action('plugins_loaded', array($this, 'load_textdomain'));
	}

	/**
	 * Load plugin textdomain
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function load_textdomain()
	{
		load_plugin_textdomain(
			'wiki-dynamic-heading-anchors',
			false,
			dirname(WDHA_PLUGIN_BASENAME) . '/languages'
		);
	}

	/**
	 * Get plugin settings
	 *
	 * @since  1.0.1
	 * @return array
	 */
	public function get_settings()
	{
		return $this->settings;
	}
}
