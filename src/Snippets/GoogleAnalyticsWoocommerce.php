<?php

declare(strict_types=1);

namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Injects Google Analytics eCommerce tracking code on the WooCommerce "order
 * received" (thank-you) page, capturing transaction details and line items for
 * each completed order.
 *
 * Enable this snippet alongside GoogleAnalytics to track revenue data in
 * Google Analytics. Requires WooCommerce to be active. No configuration needed.
 */
class GoogleAnalyticsWooCommerce implements SnippetInterface {

	/**
	 * Hooks the tracking script injection into wp_head at an early priority
	 * so it appears before other head scripts.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'wp_head', [ $this, 'inject_tracking_script' ], - 50 );
	}

	/**
	 * Outputs the eCommerce tracking script on the WooCommerce order-received
	 * page via the snippets/google-analytics-ecommerce.twig template.
	 *
	 * @return void
	 */
	public function inject_tracking_script(): void {
		if ( $this->should_track_order() ) {
			$order_id = $this->get_order_id_from_query();
			$order    = \wc_get_order( $order_id );

			if ( $order ) {
				$order_details = $this->get_order_details( $order );
				Timber::render( 'snippets/google-analytics-ecommerce.twig', $order_details );
			}
		}
	}

	/**
	 * Checks whether the current page is the WooCommerce order-received page
	 * and WooCommerce is active.
	 *
	 * @return bool True if conditions are met for tracking.
	 */
	protected function should_track_order(): bool {
		return class_exists( 'woocommerce' ) && \is_order_received_page();
	}

	/**
	 * Extracts the order ID from the current WordPress query variables.
	 *
	 * @return int The order ID from the 'order-received' query var.
	 */
	protected function get_order_id_from_query(): int {
		global $wp;

		return \absint( $wp->query_vars['order-received'] );
	}

	/**
	 * Builds an associative array of order details for the tracking template.
	 *
	 * @param \WC_Order $order The WooCommerce order.
	 *
	 * @return array<string, mixed> Transaction and product details for rendering.
	 */
	protected function get_order_details( \WC_Order $order ): array {
		$items = array_map( [ $this, 'get_product_details' ], $order->get_items() );

		return [
			'transaction_id'          => $order->get_order_key(),
			'transaction_affiliation' => 'WooCommerce',
			'transaction_total'       => $order->get_total(),
			'transaction_tax'         => $order->get_total_tax(),
			'transaction_shipping'    => $order->get_shipping_total(),
			'transaction_products'    => $items,
		];
	}

	/**
	 * Extracts tracking-relevant details from a single order line item.
	 *
	 * @param \WC_Order_Item_Product $item The order line item.
	 *
	 * @return array<string, mixed> Product SKU, name, category, price, and quantity.
	 */
	protected function get_product_details( $item ): array {
		$product  = $item->get_product();
		$category = $this->get_primary_product_category( $product );

		return [
			'sku'      => $product->get_sku(),
			'name'     => $item->get_name(),
			'category' => $category ? $category->name : '',
			'price'    => $item->get_subtotal(),
			'quantity' => $item->get_quantity(),
		];
	}

	/**
	 * Returns the top-level (parent = 0) product category for a product, or
	 * null if none exists.
	 *
	 * @param \WC_Product $product The WooCommerce product.
	 *
	 * @return \WP_Term|null The primary product category term.
	 */
	protected function get_primary_product_category( \WC_Product $product ): ?\WP_Term {
		$categories = \get_the_terms( $product->get_id(), 'product_cat' );

		if ( ! \is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				if ( $category->parent === 0 ) {
					return $category;
				}
			}
		}

		return null;
	}
}
