<?php
/**
 * Get asset info from extracted asset files
 *
 * @package TenUpTheme\Traits
 */

namespace TenUpTheme\Traits;

/**
 * Trait GetAssetInfo
 *
 * @package TenUpTheme\Traits
 */
trait GetAssetInfo {

	/**
	 * Get asset info from extracted asset files
	 *
	 * @param string $slug      Asset slug as defined in build/webpack configuration
	 * @param string $attribute Optional attribute to get. Can be version or dependencies
	 *
	 * @return ($attribute is null ? array{version: string, dependencies: array<string>} : $attribute is 'dependencies' ? array<string> : string)
	 */
	protected function get_asset_info( $slug, $attribute = null ) {
		if ( file_exists( TENUP_THEME_PATH . 'dist/js/' . $slug . '.asset.php' ) ) {
			$asset = require TENUP_THEME_PATH . 'dist/js/' . $slug . '.asset.php';
		} elseif ( file_exists( TENUP_THEME_PATH . 'dist/css/' . $slug . '.asset.php' ) ) {
			$asset = require TENUP_THEME_PATH . 'dist/css/' . $slug . '.asset.php';
		} else {
			$asset = [
				'version'      => TENUP_THEME_VERSION,
				'dependencies' => [],
			];
		}

		if ( ! empty( $attribute ) && isset( $asset[ $attribute ] ) ) {
			return $asset[ $attribute ];
		}

		return $asset;
	}
}
