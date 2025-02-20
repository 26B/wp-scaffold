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
		// Hook to allow async or defer on asset loading.
		add_filter( 'script_loader_tag', [ $this, 'script_loader_tag' ], 10, 2 );
	}

	/**
	 * The list of knows contexts for enqueuing scripts/styles.
	 *
	 * @return array<string>
	 */
	protected function get_enqueue_contexts() {
		return [ 'admin' ];
	}

	/**
	 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
	 *
	 * @param string $script  Script file name (no .js extension)
	 * @param string $context Context for the script ('admin')
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
	 * @param string $context    Context for the script ('admin')
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
	 * Enqueue scripts for admin.
	 *
	 * @return void
	 */
	public function admin_scripts() {
		wp_enqueue_script(
			'tenup_plugin_admin',
			$this->script_url( 'admin', 'admin' ),
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

	/**
	 * Add async/defer attributes to enqueued scripts that have the specified script_execution flag.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12009
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 *
	 * @return string|null
	 */
	public function script_loader_tag( $tag, $handle ) {
		$script_execution = wp_scripts()->get_data( $handle, 'script_execution' );

		if ( ! $script_execution ) {
			return $tag;
		}

		if ( 'async' !== $script_execution && 'defer' !== $script_execution ) {
			return $tag;
		}

		// Abort adding async/defer for scripts that have this script as a dependency. _doing_it_wrong()?
		foreach ( wp_scripts()->registered as $script ) {
			if ( in_array( $handle, $script->deps, true ) ) {
				return $tag;
			}
		}

		// Add the attribute if it hasn't already been added.
		if ( ! preg_match( ":\s$script_execution(=|>|\s):", $tag ) ) {
			$tag = preg_replace( ':(?=></script>):', " $script_execution", $tag, 1 );
		}

		return $tag;
	}
}
