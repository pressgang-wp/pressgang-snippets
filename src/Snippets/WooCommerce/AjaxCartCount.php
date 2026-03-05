<?php

namespace PressGang\Snippets\WooCommerce;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

/**
 * Provides an AJAX cart-count fragment so the cart item count in the header
 * updates dynamically when products are added without a page reload.
 *
 * The base Timber context and cart URL are cached permanently via wp_cache_set
 * (until manual purge). Only the cart count is fetched fresh on each call.
 *
 * Requires a woocommerce/cart-link.twig template and an <a id="cart-link">
 * element in the theme.
 */
class AjaxCartCount implements SnippetInterface {

	private const CACHE_KEY = 'ajax_cart_count_context';
	private const CACHE_GROUP = 'pressgang_snippets';

	/**
	 * Hooks into woocommerce_add_to_cart_fragments to update the cart link
	 * fragment on AJAX add-to-cart.
	 *
	 * @param array $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart_count_fragment' ] );
	}

	/**
	 * Compiles the cart-link Twig template with the current cart count,
	 * replacing the 'a#cart-link' fragment in the AJAX response.
	 *
	 * @param array<string, string> $fragments AJAX cart fragments.
	 *
	 * @return array<string, string> The fragments with the updated cart link.
	 */
	public function cart_count_fragment( array $fragments ): array {
		$context = \wp_cache_get( self::CACHE_KEY, self::CACHE_GROUP );

		if ( ! $context ) {
			$context = Timber::context();
			$context['cart_link'] = \esc_url( \wc_get_cart_url() );
			\wp_cache_set( self::CACHE_KEY, $context, self::CACHE_GROUP );
		}

		$context['cart_contents_count'] = (int) ( \WC()->cart?->get_cart_contents_count() ?? 0 );

		$fragments['a#cart-link'] = Timber::compile( 'woocommerce/cart-link.twig', $context );

		return $fragments;
	}
}
