<?php

namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;

/**
 * Disables Contact Form 7 JavaScript and CSS on the frontend by returning
 * false from the wpcf7_load_js and wpcf7_load_css filters.
 *
 * Useful when CF7 assets are not needed globally, e.g. when forms are loaded
 * via AJAX or only appear on specific pages with their own styles. No
 * configuration required.
 */
class DisableCf7Assets implements SnippetInterface {

	/**
	 * Registers filters to prevent Contact Form 7 from enqueuing its
	 * JavaScript and CSS assets.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'wpcf7_load_js', '__return_false' );
		\add_filter( 'wpcf7_load_css', '__return_false' );
	}
}
