<?php

namespace PressGang\Snippets\WooCommerce\Cart;

use PressGang\Snippets\SnippetInterface;

/**
 * Keeps product-loop add-to-cart controls from publishing crawlable mutation URLs.
 *
 * WooCommerce product loops render simple-product add-to-cart controls as
 * anchors whose `href` contains `?add-to-cart=ID`. Browsers with JavaScript
 * use the button data attributes and send a POST AJAX add-to-cart request, so
 * the `href` can safely be a product-page fallback. This avoids advertising
 * cart-mutation URLs to crawlers without blocking legitimate custom
 * WooCommerce add-to-cart URLs elsewhere.
 */
class DecrawlAddToCartLinks implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_product_add_to_cart_url', [ $this, 'filter_product_add_to_cart_url' ], 20, 2 );
		\add_action( 'wp_loaded', [ $this, 'redirect_get_add_to_cart_requests' ], 1 );
	}

	/**
	 * Replaces crawlable loop add-to-cart URLs with product-page fallbacks.
	 *
	 * The WooCommerce AJAX add-to-cart script reads `data-product_id`, not the
	 * `href`, so normal JavaScript customers still add to basket from the loop.
	 * Without JavaScript, the fallback becomes the product page instead of a cart
	 * mutation URL.
	 *
	 * @param string      $url WooCommerce-generated add-to-cart URL.
	 * @param \WC_Product $product Current product.
	 *
	 * @return string
	 */
	public function filter_product_add_to_cart_url( string $url, \WC_Product $product ): string {
		if ( $this->is_admin_or_ajax() || $this->is_cart_surface() || ! $product->supports( 'ajax_add_to_cart' ) ) {
			return $url;
		}

		$permalink = $product->get_permalink();

		return \is_string( $permalink ) && $permalink !== '' ? $permalink : $url;
	}

	/**
	 * Redirects direct GET/HEAD add-to-cart hits before WooCommerce processes them.
	 *
	 * WooCommerce attaches its form handler on `wp_loaded` priority 20. This
	 * snippet registers at priority 1, so crawler/stale URL hits avoid
	 * cart/session/totals work while normal POST/AJAX add-to-cart remains intact.
	 *
	 * @return void
	 */
	public function redirect_get_add_to_cart_requests(): void {
		$redirect_url = $this->get_get_add_to_cart_redirect_url();

		if ( null === $redirect_url ) {
			return;
		}

		\wp_safe_redirect( $redirect_url, 302 );
		exit;
	}

	/**
	 * Returns the product URL for a non-AJAX GET/HEAD add-to-cart request.
	 *
	 * @return string|null
	 */
	public function get_get_add_to_cart_redirect_url(): ?string {
		if ( ! \in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', [ 'GET', 'HEAD' ], true ) ) {
			return null;
		}

		if ( ! isset( $_GET['add-to-cart'] ) || ! \function_exists( 'wp_unslash' ) || ! \is_numeric( \wp_unslash( $_GET['add-to-cart'] ) ) ) {
			return null;
		}

		if ( $this->is_admin_or_ajax() || ! \function_exists( 'wc_get_product' ) ) {
			return null;
		}

		$product_id = \abs( (int) \wp_unslash( $_GET['add-to-cart'] ) );
		if ( $product_id <= 0 ) {
			return null;
		}

		$product = \wc_get_product( $product_id );
		if ( ! $product instanceof \WC_Product ) {
			return null;
		}

		$permalink = $product->get_permalink();

		return \is_string( $permalink ) && $permalink !== '' ? $permalink : null;
	}

	/**
	 * Whether the request is a context whose output is never crawled.
	 */
	private function is_admin_or_ajax(): bool {
		if ( \function_exists( 'is_admin' ) && \is_admin() ) {
			return true;
		}

		return \function_exists( 'wp_doing_ajax' ) && \wp_doing_ajax();
	}

	/**
	 * Whether the current request is the cart or checkout screen.
	 */
	private function is_cart_surface(): bool {
		if ( \function_exists( 'is_cart' ) && \is_cart() ) {
			return true;
		}

		return \function_exists( 'is_checkout' ) && \is_checkout();
	}
}
