<?php

namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Enables Google Analytics eCommerce tracking for WooCommerce orders.
 *
 * Injects Google Analytics eCommerce tracking code on the WooCommerce order received page,
 * capturing details about the transaction and the items purchased.
 */
class GoogleAnalyticsWooCommerce implements SnippetInterface {

	/**
	 * Initializes the class by hooking into the WordPress 'wp_head' action to inject tracking code.
	 */
	public function __construct( array $args ) {
		\add_action( 'wp_head', [ $this, 'inject_tracking_script' ], - 50 );
	}

	/**
	 * Conditionally injects the Google Analytics eCommerce tracking script.
	 *
	 * Only outputs the tracking script if on the WooCommerce order received page and the order details are accessible.
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
	 * Determines if the current page is the WooCommerce order received page and WooCommerce is active.
	 *
	 * @return bool True if conditions are met, false otherwise.
	 */
	protected function should_track_order(): bool {
		return class_exists( 'woocommerce' ) && \is_order_received_page();
	}

	/**
	 * Retrieves the order ID from the current query variables.
	 *
	 * @return int The order ID.
	 */
	protected function get_order_id_from_query(): int {
		global $wp;

		return absint( $wp->query_vars['order-received'] );
	}

	/**
	 * Gathers detailed information about the order for tracking purposes.
	 *
	 * @param \WC_Order $order The WooCommerce order object.
	 *
	 * @return array An associative array containing the order details.
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
	 * Extracts product details from an order item for tracking.
	 *
	 * @param \WC_Order_Item_Product $item The WooCommerce order item.
	 *
	 * @return array An associative array containing the product's tracking details.
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
	 * Retrieves the primary category of a product.
	 *
	 * @param \WC_Product $product The WooCommerce product.
	 *
	 * @return \WP_Term|null The primary product category, or null if no primary category is found.
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
