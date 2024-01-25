<?php

namespace PressGang\Snippets;

/**
 * Class WooCommerceAjaxCartCount
 *
 * Handles the dynamic update of the cart item count in a WooCommerce store. This class ensures
 * that the cart count is updated via Ajax after items are added to the cart, providing a
 * smoother user experience without needing to reload the page.
 */
class WooCommerceAjaxCartCount implements SnippetInterface {

	/**
	 * WooCommerceAjaxCartCount constructor.
	 *
	 * Adds a filter hook for 'woocommerce_add_to_cart_fragments' to update the cart count
	 * dynamically using Ajax.
	 *
	 * @param array $args Arguments passed to the constructor, currently not used.
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart_count_fragment' ] );
	}

	/**
	 * Updates the cart count fragment for Ajax requests.
	 *
	 * This method is called whenever an Ajax request is made to add items to the cart.
	 * It updates the cart count fragment so that the cart item count is dynamically
	 * refreshed in the frontend without a page reload.
	 *
	 * @param array $fragments Fragments to be updated via Ajax.
	 *
	 * @return array Updated fragments including the cart count.
	 */
	public function cart_count_fragment( array $fragments ): array {
		global $woocommerce;

		$fragments['a#cart-link'] = \Timber\Timber::compile( 'woocommerce/cart-link.twig',
			[
				'cart_link'           => \esc_url( \wc_get_cart_url() ),
				'cart_contents_count' => $woocommerce->cart->cart_contents_count,
			] );

		return $fragments;
	}

}