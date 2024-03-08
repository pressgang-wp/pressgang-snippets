<?php

namespace PressGang\Snippets;

/**
 * Class WooCommerceBackorders
 *
 * An Snippet for adding a WooCommerce Backorder Date
 *
 * @package PressGang
 */
class WooCommerceBackorders implements SnippetInterface {

	/**
	 * Initializes the WooCommerceBackorders class by setting up actions and filters related to WooCommerce products.
	 *
	 * @param array $args Optional. Arguments for initializing the class. Not used in the current implementation.
	 */
	public function __construct( array $args ) {
		// Display and save custom field in WooCommerce
		\add_action( 'woocommerce_product_options_stock_status', [ $this, 'woocommerce_product_custom_fields', ], 10 );
		\add_action( 'woocommerce_process_product_meta', [ $this, 'woocommerce_product_custom_fields_save' ] );

		// Message display
		\add_filter( 'woocommerce_out_of_stock_message', [ $this, 'out_of_stock_message' ] );
		\add_filter( 'woocommerce_get_availability_text', [ $this, 'availability_backorder_text' ], 10, 2 );
		\add_filter( 'woocommerce_composited_product_availability', [ $this, 'availability_backorder_text' ], 10, 2 );
		\add_filter( 'woocommerce_composited_product_availability_text', [
			$this,
			'availability_backorder_text',
		], 10, 2 );
	}

	/**
	 * Modifies the out-of-stock message to include the backorder date.
	 *
	 * @param string $text The original out-of-stock text.
	 *
	 * @return string The modified out-of-stock text including the expected backorder date if available.
	 */
	public function out_of_stock_message( string $text ): string {
		global $product;

		if ( $product ) {
			if ( $backorder_date = \get_field( 'backorder_date', $product->get_id() ) ) {
				$backorder_date = \wp_date( \get_option( 'date_format' ), strtotime( $backorder_date ) );
				$text           = sprintf( __( "Out of stock. Expected delivery date %s.", THEMENAME ), $backorder_date );
			}
		}

		return $text;
	}

	/**
	 * Modifies the availability text for products, especially for those on backorder or out of stock, to include the expected backorder date.
	 *
	 * @param string $availability The original availability text.
	 * @param mixed $instance The product instance or the composite product instance.
	 *
	 * @return string The modified availability text including the expected backorder date if applicable.
	 */
	public function availability_backorder_text( string $availability, mixed $instance ): string {
		if ( is_a( $instance, 'WC_CP_Product' ) ) {
			$product = $instance->get_product();
		} else {
			global $product;
		}

		if ( $product ) {
			switch ( $product->get_stock_status() ) {
				case 'onbackorder' :
					if ( $backorder_date = \get_field( 'backorder_date',
						$product->get_id() ) ) {
						$backorder_date = \wp_date( \get_option( 'date_format' ), strtotime( $backorder_date ) );
						$availability   = sprintf( __( "Available on backorder. Expected delivery date %s.", THEMENAME ), $backorder_date );
					}
					break;
				case 'outofstock' :
					$availability = $this->out_of_stock_message( $availability );
					break;
			}
		}

		return $availability;
	}

	/**
	 * Displays a custom field in the WooCommerce product data panel for specifying the backorder date.
	 *
	 * @return void
	 */
	public function woocommerce_product_custom_fields(): void {
		echo '<div class="product_custom_field form-field backorder-date hide_if_variable hide_if_external hide_if_grouped">';
		woocommerce_wp_text_input( [
			'id'       => 'backorder_date',
			'label'    => __( 'Backorder date', 'woocommerce' ),
			'desc_tip' => 'true',
			'type'     => 'date',
		] );
		echo '</div>';
	}

	/**
	 * Saves the custom backorder date field when a product is saved.
	 *
	 * @param int $post_id The ID of the product being saved.
	 *
	 * @return void
	 */
	public function woocommerce_product_custom_fields_save( int $post_id ): void {

		if ( isset( $_POST['backorder_date'] ) ) {
			$backorder_date = $_POST['backorder_date'];

			if ( ! empty( $backorder_date ) ) {
				\update_post_meta( $post_id, 'backorder_date', \esc_attr( $backorder_date ) );
			}
		}
	}

}