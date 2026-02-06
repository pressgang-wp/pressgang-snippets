<?php

declare(strict_types=1);

namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Provides an AJAX cart-count fragment so the cart item count in the header
 * updates dynamically when products are added without a page reload.
 *
 * Requires a woocommerce/cart-link.twig template and an <a id="cart-link">
 * element in the theme.
 */
class WooCommerceAjaxCartCount implements SnippetInterface {

	/**
	 * Hooks into woocommerce_add_to_cart_fragments.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart_count_fragment' ] );
	}

	/**
	 * Compiles the cart-link Twig template with the current cart URL and
	 * item count, replacing the 'a#cart-link' fragment in the AJAX response.
	 *
	 * @param array<string, string> $fragments AJAX cart fragments.
	 *
	 * @return array<string, string> The fragments with the updated cart link.
	 */
	public function cart_count_fragment( array $fragments ): array {
		$fragments['a#cart-link'] = Timber::compile( 'woocommerce/cart-link.twig', [
			'cart_link'           => \esc_url( \wc_get_cart_url() ),
			'cart_contents_count' => \WC()->cart->get_cart_contents_count(),
		] );

		return $fragments;
	}
}