<?php
/**
 * Core setup, site hooks and filters.
 *
 * @package TenupBlockTheme
 */

namespace TenupBlockTheme\Core;

use function TenupBlockTheme\Utility\get_asset_info;

/**
 * Set up theme defaults and register supported WordPress features.
 *
 * @return void
 */
function setup() {
	$n = function ( $function_name ) {
		return __NAMESPACE__ . "\\$function_name";
	};

	add_action( 'after_setup_theme', 'TenupBlockTheme\Core\i18n' );
	add_action( 'after_setup_theme', 'TenupBlockTheme\Core\theme_setup' );
	add_action( 'init', 'TenupBlockTheme\Core\scripts' );
	add_action( 'wp_enqueue_scripts', 'TenupBlockTheme\Core\styles' );
	add_action( 'enqueue_block_editor_assets', 'TenupBlockTheme\Core\editor_style_overrides' );
	add_action( 'wp_head', 'TenupBlockTheme\Core\js_detection', 0 );
	add_action( 'wp_head', 'TenupBlockTheme\Core\scrollbar_detection', 0 );
	add_action( 'wp_head', 'TenupBlockTheme\Core\embed_ct_css', 0 );
	add_action( 'init', 'TenupBlockTheme\Core\register_all_icons', 10 );
}

/**
 * Makes Theme available for translation.
 *
 * Translations can be added to the /languages directory.
 * If you're building a theme based on "tenup-theme", change the
 * filename of '/languages/TenupBlockTheme.pot' to the name of your project.
 *
 * @return void
 */
function i18n() {
	load_theme_textdomain( 'tenup-theme', TENUP_BLOCK_THEME_PATH . '/languages' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function theme_setup() {
	add_theme_support( 'editor-styles' );
	add_editor_style( '/dist/css/frontend.css' );
	remove_theme_support( 'core-block-patterns' );
}

/**
 * Enqueue scripts for front-end.
 *
 * @return void
 */
function scripts() {

	wp_enqueue_script(
		'frontend',
		TENUP_BLOCK_THEME_TEMPLATE_URL . '/dist/js/frontend.js',
		get_asset_info( 'frontend', 'dependencies' ),
		get_asset_info( 'frontend', 'version' ),
		[
			'strategy' => 'defer',
		]
	);
}


/**
 * Enqueue styles for front-end.
 *
 * @return void
 */
function styles() {
	wp_enqueue_style(
		'tenup-theme-styles',
		TENUP_BLOCK_THEME_TEMPLATE_URL . '/dist/css/frontend.css',
		[],
		get_asset_info( 'frontend', 'version' )
	);
}

/**
 * Enqueue styles for editor only.
 *
 * @return void
 */
function editor_style_overrides() {
	wp_enqueue_style(
		'tenup-theme-editor-style-overrides',
		TENUP_BLOCK_THEME_TEMPLATE_URL . '/dist/css/editor-style-overrides.css',
		[],
		TENUP_BLOCK_THEME_VERSION
	);

	wp_enqueue_script(
		'tenup-theme-block-extensions',
		TENUP_BLOCK_THEME_TEMPLATE_URL . '/dist/js/block-extensions.js',
		get_asset_info( 'block-extensions', 'dependencies' ),
		get_asset_info( 'block-extensions', 'version' ),
		true
	);
}

/**
 * register all icons located in the dist/svg folder
 */
function register_all_icons() {
	if ( ! function_exists( '\UIKitCore\Helpers\register_icons' ) ) {
		return;
	}

	$icon_paths = glob( TENUP_BLOCK_THEME_DIST_PATH . 'svg/*.svg' );
	$icons      = array_map(
		function ( $icon_path ) {
			$icon_name = preg_replace( '#\..*$#', '', basename( $icon_path ) );

			return new \UIKitCore\Icon(
				$icon_name,
				ucwords( str_replace( '-', ' ', $icon_name ) ),
				$icon_path
			);
		},
		$icon_paths
	);

	\UIKitCore\Helpers\register_icons(
		[
			'name'  => 'tenup',
			'label' => 'Theme Icons',
			'icons' => $icons,
		]
	);
}

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @return void
 */
function js_detection() {

	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}

/**
 * Handles scrollbar width detection.
 *
 * Adds a JavaScript event listener to the DOMContentLoaded event. When the DOM is fully loaded,
 * it calculates the width of the scrollbar and sets a CSS variable `--wp--custom--scrollbar-width` with the width.
 * It also adds an event listener to the window resize event to update the scrollbar width when the window is resized.
 *
 * @return void
 */
function scrollbar_detection() {
	echo '<script>window.addEventListener("DOMContentLoaded",()=>{const t=()=>window.innerWidth-document.body.clientWidth;const e=()=>{document.documentElement.style.setProperty("--wp--custom--scrollbar-width",`${t()}px`)};e();window.addEventListener("resize",e);});</script>' . "\n";
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
function embed_ct_css() {

	$debug_performance = rest_sanitize_boolean( filter_input( INPUT_GET, 'debug_perf', FILTER_SANITIZE_NUMBER_INT ) );

	if ( ! $debug_performance ) {
		return;
	}

	wp_register_style( 'ct', false ); // phpcs:ignore
	wp_enqueue_style( 'ct' );
	wp_add_inline_style( 'ct', 'head{--ct-is-problematic:solid;--ct-is-affected:dashed;--ct-notify:#0bce6b;--ct-warn:#ffa400;--ct-error:#ff4e42}head,head [rel=stylesheet],head script,head script:not([src])[async],head script:not([src])[defer],head script~meta[http-equiv=content-security-policy],head style,head>meta[charset]:not(:nth-child(-n+5)){display:block}head [rel=stylesheet],head script,head script~meta[http-equiv=content-security-policy],head style,head title,head>meta[charset]:not(:nth-child(-n+5)){margin:5px;padding:5px;border-width:5px;background-color:#fff;color:#333}head ::before,head script,head style{font:16px/1.5 monospace,monospace;display:block}head ::before{font-weight:700}head link[rel=stylesheet],head script[src]{border-style:var(--ct-is-problematic);border-color:var(--ct-warn)}head script[src]::before{content:"[Blocking Script – " attr(src) "]"}head link[rel=stylesheet]::before{content:"[Blocking Stylesheet – " attr(href) "]"}head script:not(:empty),head style:not(:empty){max-height:5em;overflow:auto;background-color:#ffd;white-space:pre;border-color:var(--ct-notify);border-style:var(--ct-is-problematic)}head script:not(:empty)::before{content:"[Inline Script] "}head style:not(:empty)::before{content:"[Inline Style] "}head script:not(:empty)~title,head script[src]:not([async]):not([defer]):not([type=module])~title{display:block;border-style:var(--ct-is-affected);border-color:var(--ct-error)}head script:not(:empty)~title::before,head script[src]:not([async]):not([defer]):not([type=module])~title::before{content:"[<title> blocked by JS] "}head [rel=stylesheet]:not([media=print]):not(.ct)~script,head style:not(:empty)~script{border-style:var(--ct-is-affected);border-color:var(--ct-warn)}head [rel=stylesheet]:not([media=print]):not(.ct)~script::before,head style:not(:empty)~script::before{content:"[JS blocked by CSS – " attr(src) "]"}head script[src][src][async][defer]{display:block;border-style:var(--ct-is-problematic);border-color:var(--ct-warn)}head script[src][src][async][defer]::before{content:"[async and defer is redundant: prefer defer – " attr(src) "]"}head script:not([src])[async],head script:not([src])[defer]{border-style:var(--ct-is-problematic);border-color:var(--ct-warn)}head script:not([src])[async]::before{content:"The async attribute is redundant on inline scripts"}head script:not([src])[defer]::before{content:"The defer attribute is redundant on inline scripts"}head [rel=stylesheet][href^="//"],head [rel=stylesheet][href^=http],head script[src][src][src^="//"],head script[src][src][src^=http]{border-style:var(--ct-is-problematic);border-color:var(--ct-error)}head script[src][src][src^="//"]::before,head script[src][src][src^=http]::before{content:"[Third Party Blocking Script – " attr(src) "]"}head [rel=stylesheet][href^="//"]::before,head [rel=stylesheet][href^=http]::before{content:"[Third Party Blocking Stylesheet – " attr(href) "]"}head script~meta[http-equiv=content-security-policy]{border-style:var(--ct-is-problematic);border-color:var(--ct-error)}head script~meta[http-equiv=content-security-policy]::before{content:"[Meta CSP defined after JS]"}head>meta[charset]:not(:nth-child(-n+5)){border-style:var(--ct-is-problematic);border-color:var(--ct-warn)}head>meta[charset]:not(:nth-child(-n+5))::before{content:"[Charset should appear as early as possible]"}link[rel=stylesheet].ct,link[rel=stylesheet][media=print],script[async],script[defer],script[type=module],style.ct{display:none}' );
}
