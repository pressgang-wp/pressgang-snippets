<?php

namespace PressGang\Snippets\WooCommerce\Core;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes WooCommerce's footer demo store notice output.
 */
class DisableFooterDemoStoreNotice implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\remove_action( 'wp_footer', 'woocommerce_demo_store' );
	}
}
