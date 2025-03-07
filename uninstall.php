<?php
/**
 * Uninstall Wiki Dynamic Heading Anchors
 *
 * @package WikiDynamicHeadingAnchors
 * @since   1.0.1
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options
delete_option( 'wiki_heading_anchors_settings' );
delete_option( 'wiki_dynamic_heading_anchors_settings' );
