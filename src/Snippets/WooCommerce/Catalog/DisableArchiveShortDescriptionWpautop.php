<?php

namespace PressGang\Snippets\WooCommerce\Catalog;

use PressGang\Snippets\SnippetInterface;

/**
 * Prevents WordPress from auto-wrapping WooCommerce short descriptions.
 */
class DisableArchiveShortDescriptionWpautop implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\remove_filter( 'woocommerce_short_description', 'wpautop' );
	}
}
