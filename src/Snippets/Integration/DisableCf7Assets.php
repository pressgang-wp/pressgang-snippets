<?php

namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;

/**
 * Disables Contact Form 7 JavaScript and CSS on the frontend.
 *
 * Useful when CF7 assets are not needed globally, e.g. when forms are loaded
 * via AJAX or only appear on specific pages with their own styles.
 *
 * Usage in config/snippets.php:
 *
 *     'Integration\DisableCf7Assets' => [],
 *
 * @package PressGang\Snippets\Integration
 */
class DisableCf7Assets implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args = [] ) {
		\add_filter( 'wpcf7_load_js', '__return_false' );
		\add_filter( 'wpcf7_load_css', '__return_false' );
	}
}
