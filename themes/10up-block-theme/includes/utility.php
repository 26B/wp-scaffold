<?php
/**
 * Utility functions for the theme.
 *
 * This file is for custom helper functions.
 * These should not be confused with WordPress template
 * tags. Template tags typically use prefixing, as opposed
 * to Namespaces.
 *
 * @link https://developer.wordpress.org/themes/basics/template-tags/
 * @package TenupBlockTheme
 */

namespace TenupBlockTheme\Utility;

/**
 * Get asset info from extracted asset files
 *
 * @param string $slug Asset slug as defined in build/webpack configuration
 * @param string $attribute Optional attribute to get. Can be version or dependencies
 * @return string|array
 */
function get_asset_info( $slug, $attribute = null ) {
	if ( file_exists( TENUP_BLOCK_THEME_PATH . 'dist/js/' . $slug . '.asset.php' ) ) {
		$asset = require TENUP_BLOCK_THEME_PATH . 'dist/js/' . $slug . '.asset.php';
	} elseif ( file_exists( TENUP_BLOCK_THEME_PATH . 'dist/css/' . $slug . '.asset.php' ) ) {
		$asset = require TENUP_BLOCK_THEME_PATH . 'dist/css/' . $slug . '.asset.php';
	} else {
		return null;
	}

	if ( ! empty( $attribute ) && isset( $asset[ $attribute ] ) ) {
		return $asset[ $attribute ];
	}

	return $asset;
}



/**
 * Enqueue a view script for a block
 *
 * @param string $block_name Block name.
 * @param array  $args {
 *    Optional. Array of arguments for enqueuing a view script.
 *    @type string $handle Script handle.
 *    @type string $src Script URL.
 *    @type array $dep Script dependencies.
 *    @type string|bool $ver Script version.
 *    @type bool $in_footer Whether to enqueue the script before </body> instead of in the <head>.
 * }
 * @return void
 */
function wp_enqueue_block_view_script( $block_name, $args = array() ) {
	$default_args = array(
		'dep'       => array(),
		'ver'       => false,
		'in_footer' => true,
	);

	$args = wp_parse_args( $args, $default_args );

	$block = \WP_Block_Type_Registry::get_instance()->get_registered( $block_name );

	if ( ! empty( $args['src'] ) ) {
		wp_register_script(
			$args['handle'],
			$args['src'],
			$args['dep'],
			$args['ver'],
			$args['in_footer']
		);
	}

	if ( ! empty( $block ) ) {
		$block->view_script_handles[] = $args['handle'];
	}
}
