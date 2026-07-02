<?php

namespace PressGang\Snippets\WooCommerce;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes the default add-to-cart button from WooCommerce product loops.
 */
class RemoveShopLoopAddToCart implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
	}
}
