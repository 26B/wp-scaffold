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
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );

		// Editor styles. add_editor_style() doesn't work outside of a theme.
		add_filter( 'mce_css', [ $this, 'mce_css' ] );
	}

	/**
	 * The list of knows contexts for enqueuing scripts/styles.
	 *
	 * @return array<string>
	 */
	protected function get_enqueue_contexts() {
		return [ 'admin', 'frontend', 'shared' ];
	}

	/**
	 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
	 *
	 * @param string $script  Script file name (no .js extension)
	 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
	 *
	 * @return string URL
	 * @throws \RuntimeException If an invalid $context is specified.
	 */
	public function script_url( $script, $context ) {

		if ( ! in_array( $context, $this->get_enqueue_contexts(), true ) ) {
			throw new \RuntimeException( 'Invalid $context specified in TenUpPlugin script loader.' );
		}

		return TENUP_PLUGIN_URL . "dist/js/{$script}.js";
	}

	/**
	 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
	 *
	 * @param string $stylesheet Stylesheet file name (no .css extension)
	 * @param string $context    Context for the script ('admin', 'frontend', or 'shared')
	 *
	 * @return string URL
	 * @throws \RuntimeException If an invalid $context is specified.
	 */
	public function style_url( $stylesheet, $context ) {

		if ( ! in_array( $context, $this->get_enqueue_contexts(), true ) ) {
			throw new \RuntimeException( 'Invalid $context specified in TenUpPlugin stylesheet loader.' );
		}

		return TENUP_PLUGIN_URL . "dist/css/{$stylesheet}.css";
	}

	/**
	 * Enqueue scripts for front-end.
	 *
	 * @return void
	 */
	public function scripts() {

		wp_enqueue_script(
			'tenup_plugin_shared',
			$this->script_url( 'shared', 'shared' ),
			$this->get_asset_info( 'shared', 'dependencies' ),
			$this->get_asset_info( 'shared', 'version' ),
			true
		);

		wp_enqueue_script(
			'tenup_plugin_frontend',
			$this->script_url( 'frontend', 'frontend' ),
			$this->get_asset_info( 'frontend', 'dependencies' ),
			$this->get_asset_info( 'frontend', 'version' ),
			true
		);
	}

	/**
	 * Enqueue scripts for admin.
	 *
	 * @return void
	 */
	public function admin_scripts() {

		wp_enqueue_script(
			'tenup_plugin_shared',
			$this->script_url( 'shared', 'shared' ),
			$this->get_asset_info( 'shared', 'dependencies' ),
			$this->get_asset_info( 'shared', 'version' ),
			true
		);

		wp_enqueue_script(
			'tenup_plugin_admin',
			$this->script_url( 'admin', 'admin' ),
			$this->get_asset_info( 'admin', 'dependencies' ),
			$this->get_asset_info( 'admin', 'version' ),
			true
		);
	}

	/**
	 * Enqueue styles for front-end.
	 *
	 * @return void
	 */
	public function styles() {

		wp_enqueue_style(
			'tenup_plugin_shared',
			$this->style_url( 'shared', 'shared' ),
			[],
			$this->get_asset_info( 'shared', 'version' ),
		);

		if ( is_admin() ) {
			wp_enqueue_style(
				'tenup_plugin_admin',
				$this->style_url( 'admin', 'admin' ),
				[],
				$this->get_asset_info( 'admin', 'version' ),
			);
		} else {
			wp_enqueue_style(
				'tenup_plugin_frontend',
				$this->style_url( 'frontend', 'frontend' ),
				[],
				$this->get_asset_info( 'frontend', 'version' ),
			);
		}
	}

	/**
	 * Enqueue styles for admin.
	 *
	 * @return void
	 */
	public function admin_styles() {

		wp_enqueue_style(
			'tenup_plugin_shared',
			$this->style_url( 'shared', 'shared' ),
			[],
			$this->get_asset_info( 'shared', 'version' ),
		);

		wp_enqueue_style(
			'tenup_plugin_admin',
			$this->style_url( 'admin', 'admin' ),
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
