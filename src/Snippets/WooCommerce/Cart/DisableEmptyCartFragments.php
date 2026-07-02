<?php

namespace PressGang\Snippets\WooCommerce\Cart;

use PressGang\Snippets\SnippetInterface;

/**
 * Stops WooCommerce's legacy cart-fragments script from making a cold-load
 * AJAX request for anonymous/empty-cart page views.
 *
 * Returning null from {@see filter_script_data()} suppresses localization for
 * `wc-cart-fragments`, leaving the script enqueued but inert for empty carts.
 * The mini cart still updates normally once an item is added, because a
 * populated cart no longer matches the empty-cart guard.
 */
class DisableEmptyCartFragments implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_get_script_data', [ $this, 'filter_script_data' ], 10, 2 );
	}

	/**
	 * Suppresses cart-fragments localization for empty carts off the cart flow.
	 *
	 * @param mixed  $params Localization data WooCommerce built for the handle.
	 * @param string $handle Script handle being localized.
	 *
	 * @return mixed Null to drop cart-fragments localization, otherwise $params unchanged.
	 */
	public function filter_script_data( mixed $params, string $handle ): mixed {
		if ( $handle !== 'wc-cart-fragments' ) {
			return $params;
		}

		if ( $this->is_cart_surface() || ! $this->cart_is_empty() ) {
			return $params;
		}

		return null;
	}

	/**
	 * Whether the current request needs live cart fragments regardless of cart state.
	 */
	private function is_cart_surface(): bool {
		if ( \function_exists( 'is_admin' ) && \is_admin() ) {
			return true;
		}

		if ( \function_exists( 'wp_doing_ajax' ) && \wp_doing_ajax() ) {
			return true;
		}

		if ( \function_exists( 'is_cart' ) && \is_cart() ) {
			return true;
		}

		return \function_exists( 'is_checkout' ) && \is_checkout();
	}

	/**
	 * Whether the current cart has no items.
	 */
	private function cart_is_empty(): bool {
		if ( ! \function_exists( 'WC' ) ) {
			return true;
		}

		$woocommerce = \WC();
		$cart        = \is_object( $woocommerce ) ? ( $woocommerce->cart ?? null ) : null;

		if ( ! \is_object( $cart ) || ! \is_callable( [ $cart, 'is_empty' ] ) ) {
			return true;
		}

		return (bool) $cart->is_empty();
	}
}
