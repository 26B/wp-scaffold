<?php
/**
 * Assets module.
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin;

use TenupFramework\Module;
use TenupFramework\ModuleInterface;
use TenUpPlugin\Traits\GetAssetInfo;

/**
 * Assets module.
 *
 * @package TenUpPlugin
 */
class Assets implements ModuleInterface {

	use Module;
	use GetAssetInfo;

	/**
	 * Can this module be registered?
	 *
	 * @return bool
	 */
	public function can_register() {
		return true;
	}

	/**
	 * Register any hooks and filters.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );

		// Editor styles. add_editor_style() doesn't work outside of a theme.
		add_filter( 'mce_css', [ $this, 'mce_css' ] );
	}

	/**
	 * Enqueue scripts for admin.
	 *
	 * @return void
	 */
	public function admin_scripts() {
		wp_enqueue_script(
			'tenup_plugin_admin',
			TENUP_PLUGIN_URL . 'dist/js/admin.js',
			$this->get_asset_info( 'admin', 'dependencies' ),
			$this->get_asset_info( 'admin', 'version' ),
			true
		);
	}

	/**
	 * Enqueue styles for admin.
	 *
	 * @return void
	 */
	public function admin_styles() {
		wp_enqueue_style(
			'tenup_plugin_admin',
			TENUP_PLUGIN_URL . 'dist/css/admin-style.css',
			[],
			$this->get_asset_info( 'admin', 'version' ),
		);
	}

	/**
	 * Enqueue editor styles. Filters the comma-delimited list of stylesheets to load in TinyMCE.
	 *
	 * @param string $stylesheets Comma-delimited list of stylesheets.
	 *
	 * @return string
	 */
	public function mce_css( $stylesheets ) {
		if ( ! empty( $stylesheets ) ) {
			$stylesheets .= ',';
		}

		return $stylesheets . TENUP_PLUGIN_URL . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ?
				'assets/css/frontend/editor-style.css' :
				'dist/css/editor-style.min.css' );
	}
}
