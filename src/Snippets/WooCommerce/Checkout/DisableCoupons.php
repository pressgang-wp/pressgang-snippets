<?php

namespace PressGang\Snippets\WooCommerce\Checkout;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes the WooCommerce coupon form from checkout.
 */
class DisableCoupons implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	}
}
