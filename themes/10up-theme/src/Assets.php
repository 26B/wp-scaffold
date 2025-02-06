<?php
/**
 * Assets module.
 *
 * @package TenUpTheme
 */

namespace TenUpTheme;

use TenupFramework\Module;
use TenupFramework\ModuleInterface;
use TenUpTheme\Traits\GetAssetInfo;

/**
 * Assets module.
 *
 * @package TenUpTheme
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
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'styles' ] );
		add_action( 'wp_head', [ $this, 'js_detection' ], 0 );
		add_action( 'wp_head', [ $this, 'embed_ct_css' ], 0 );

		add_filter( 'script_loader_tag', [ $this, 'script_loader_tag' ], 10, 2 );
	}


	/**
	 * Enqueue scripts for front-end.
	 *
	 * @return void
	 */
	public function scripts() {

		/**
		 * Enqueuing frontend.js is required to get css hot reloading working in the frontend
		 * If you're not shipping any front-end js wrap this enqueue in a SCRIPT_DEBUG check.
		 */
		wp_enqueue_script(
			'frontend',
			TENUP_THEME_TEMPLATE_URL . '/dist/js/frontend.js',
			$this->get_asset_info( 'frontend', 'dependencies' ),
			$this->get_asset_info( 'frontend', 'version' ),
			true
		);

		/**
		 * Enqueuing shared.js is required to get css hot reloading working in the frontend
		 * If you're not shipping any shared js wrap this enqueue in a SCRIPT_DEBUG check.
		 */

		/*
		 * Uncoment this to use the shared.js file.
			wp_enqueue_script(
				'shared',
				TENUP_THEME_TEMPLATE_URL . '/dist/js/shared.js',
				$this->get_asset_info( 'shared', 'dependencies' ),
				$this->get_asset_info( 'shared', 'version' ),
				true
			);
		*/
	}

	/**
	 * Enqueue scripts for admin
	 *
	 * @return void
	 */
	public function admin_scripts() {
		wp_enqueue_script(
			'admin',
			TENUP_THEME_TEMPLATE_URL . '/dist/js/admin.js',
			$this->get_asset_info( 'admin', 'dependencies' ),
			$this->get_asset_info( 'admin', 'version' ),
			true
		);

		/*
		 * Uncoment this to use the shared.js file.
			wp_enqueue_script(
				'shared',
				TENUP_THEME_TEMPLATE_URL . '/dist/js/shared.js',
				$this->get_asset_info( 'shared', 'dependencies' ),
				$this->get_asset_info( 'shared', 'version' ),
				true
			);
		*/
	}

	/**
	 * Enqueue core block filters, styles and variations.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_scripts() {
		wp_enqueue_script(
			'block-editor-script',
			TENUP_THEME_DIST_URL . 'js/block-editor-script.js',
			$this->get_asset_info( 'block-editor-script', 'dependencies' ),
			$this->get_asset_info( 'block-editor-script', 'version' ),
			true
		);
	}

	/**
	 * Enqueue styles for admin
	 *
	 * @return void
	 */
	public function admin_styles() {

		wp_enqueue_style(
			'admin-style',
			TENUP_THEME_TEMPLATE_URL . '/dist/css/admin.css',
			[],
			$this->get_asset_info( 'admin-style', 'version' )
		);

		/*
		 * Uncoment this to use the shared.css file.
			wp_enqueue_style(
				'shared-style',
				TENUP_THEME_TEMPLATE_URL . '/dist/css/shared.css',
				[],
				$this->get_asset_info( 'shared', 'version' )
			);
		*/
	}

	/**
	 * Enqueue styles for front-end.
	 *
	 * @return void
	 */
	public function styles() {
		wp_enqueue_style(
			'styles',
			TENUP_THEME_TEMPLATE_URL . '/dist/css/frontend.css',
			[],
			$this->get_asset_info( 'frontend', 'version' )
		);
	}

	/**
	 * Handles JavaScript detection.
	 *
	 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
	 *
	 * @return void
	 */
	public function js_detection() {

		echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
	}

	/**
	 * Add async/defer attributes to enqueued scripts that have the specified script_execution flag.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12009
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
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

	/**
	 * Inlines ct.css in the head
	 *
	 * Embeds a diagnostic CSS file written by Harry Roberts
	 * that helps diagnose render blocking resources and other
	 * performance bottle necks.
	 *
	 * The CSS is inlined in the head of the document, only when requesting
	 * a page with the query param ?debug_perf=1
	 *
	 * @link https://csswizardry.com/ct/
	 * @return void
	 */
	public function embed_ct_css() {

		$debug_performance = rest_sanitize_boolean( boolval( filter_input( INPUT_GET, 'debug_perf', FILTER_SANITIZE_NUMBER_INT ) ) );

		if ( ! $debug_performance ) {
			return;
		}

		wp_register_style( 'ct', false ); // phpcs:ignore
		wp_enqueue_style( 'ct' );
		wp_add_inline_style( 'ct', 'head{--ct-is-problematic:solid;--ct-is-affected:dashed;--ct-notify:#0bce6b;--ct-warn:#ffa400;--ct-error:#ff4e42}head,head [rel=stylesheet],head script,head script:not([src])[async],head script:not([src])[defer],head script~meta[http-equiv=content-security-policy],head style,head>meta[charset]:not(:nth-child(-n+5)){display:block}head [rel=stylesheet],head script,head script~meta[http-equiv=content-security-policy],head style,head title,head>meta[charset]:not(:nth-child(-n+5)){margin:5px;padding:5px;border-width:5px;background-color:#fff;color:#333}head ::before,head script,head style{font:16px/1.5 monospace,monospace;display:block}head ::before{font-weight:700}head link[rel=stylesheet],head script[src]{border-style:var(--ct-is-problematic);border-color:var(--ct-warn)}head script[src]::before{content:"[Blocking Script – " attr(src) "]"}head link[rel=stylesheet]::before{content:"[Blocking Stylesheet – " attr(href) "]"}head script:not(:empty),head style:not(:empty){max-height:5em;overflow:auto;background-color:#ffd;white-space:pre;border-color:var(--ct-notify);border-style:var(--ct-is-problematic)}head script:not(:empty)::before{content:"[Inline Script] "}head style:not(:empty)::before{content:"[Inline Style] "}head script:not(:empty)~title,head script[src]:not([async]):not([defer]):not([type=module])~title{display:block;border-style:var(--ct-is-affected);border-color:var(--ct-error)}head script:not(:empty)~title::before,head script[src]:not([async]):not([defer]):not([type=module])~title::before{content:"[<title> blocked by JS] "}head [rel=stylesheet]:not([media=print]):not(.ct)~script,head style:not(:empty)~script{border-style:var(--ct-is-affected);border-color:var(--ct-warn)}head [rel=stylesheet]:not([media=print]):not(.ct)~script::before,head style:not(:empty)~script::before{content:"[JS blocked by CSS – " attr(src) "]"}head script[src][src][async][defer]{display:block;border-style:var(--ct-is-problematic);border-color:var(--ct-warn)}head script[src][src][async][defer]::before{content:"[async and defer is redundant: prefer defer – " attr(src) "]"}head script:not([src])[async],head script:not([src])[defer]{border-style:var(--ct-is-problematic);border-color:var(--ct-warn)}head script:not([src])[async]::before{content:"The async attribute is redundant on inline scripts"}head script:not([src])[defer]::before{content:"The defer attribute is redundant on inline scripts"}head [rel=stylesheet][href^="//"],head [rel=stylesheet][href^=http],head script[src][src][src^="//"],head script[src][src][src^=http]{border-style:var(--ct-is-problematic);border-color:var(--ct-error)}head script[src][src][src^="//"]::before,head script[src][src][src^=http]::before{content:"[Third Party Blocking Script – " attr(src) "]"}head [rel=stylesheet][href^="//"]::before,head [rel=stylesheet][href^=http]::before{content:"[Third Party Blocking Stylesheet – " attr(href) "]"}head script~meta[http-equiv=content-security-policy]{border-style:var(--ct-is-problematic);border-color:var(--ct-error)}head script~meta[http-equiv=content-security-policy]::before{content:"[Meta CSP defined after JS]"}head>meta[charset]:not(:nth-child(-n+5)){border-style:var(--ct-is-problematic);border-color:var(--ct-warn)}head>meta[charset]:not(:nth-child(-n+5))::before{content:"[Charset should appear as early as possible]"}link[rel=stylesheet].ct,link[rel=stylesheet][media=print],script[async],script[defer],script[type=module],style.ct{display:none}' );
	}
}
