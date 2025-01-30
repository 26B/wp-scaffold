<?php
/**
 * Theme specific helpers.
 *
 * @package TenUpTheme
 */

namespace TenUpTheme;

/**
 * Get an initialized class by its full class name, including namespace.
 *
 * @param string $class_name The class name including the namespace.
 *
 * @return false|\TenupFramework\ModuleInterface
 */
function get_module( $class_name ) {
	return \TenupFramework\ModuleInitialization::get_module( $class_name );
}
