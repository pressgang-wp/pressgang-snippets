<?php


namespace PressGang\Snippets\WooCommerce;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds a "Backorder date" custom field to the WooCommerce product stock
 * options panel and modifies out-of-stock / on-backorder availability
 * messages to include the expected delivery date.
 *
 * Enable this snippet on stores that accept backorders and want to
 * communicate expected delivery dates to customers.
 */
class Backorders implements SnippetInterface {

	/**
	 * Registers product editor fields and availability-text filters.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
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
			$backorder_date = \get_post_meta( $product->get_id(), 'backorder_date', true );
			if ( $backorder_date ) {
				$text = sprintf(
					\__( 'Out of stock. Expected delivery date %s.', THEMENAME ),
					\wp_date( \get_option( 'date_format' ), strtotime( $backorder_date ) )
				);
			}
		}

		return $text;
	}

	/**
	 * Modifies availability text for on-backorder and out-of-stock products
	 * to include the expected delivery date. Supports both standard
	 * WC_Product and WC Composite Products (WC_CP_Product).
	 *
	 * @param string $availability The original availability text.
	 * @param mixed  $instance     The product or composite product instance.
	 *
	 * @return string The modified availability text.
	 */
	public function availability_backorder_text( string $availability, mixed $instance ): string {
		if ( is_a( $instance, 'WC_CP_Product' ) ) {
			$product = $instance->get_product();
		} else {
			global $product;
		}
		if ( $product ) {
			switch ( $product->get_stock_status() ) {
				case 'onbackorder':
					$backorder_date = \get_post_meta( $product->get_id(), 'backorder_date', true );
					if ( $backorder_date ) {
						$availability = sprintf(
							\__( 'Available on backorder. Expected delivery date %s.', THEMENAME ),
							\wp_date( \get_option( 'date_format' ), strtotime( $backorder_date ) )
						);
					}
					break;
				case 'outofstock':
					$availability = $this->out_of_stock_message( $availability );
					break;
			}
		}

		return $availability;
	}

	/**
	 * Renders the backorder date input field in the product stock options
	 * panel.
	 *
	 * @return void
	 */
	public function woocommerce_product_custom_fields(): void {
		echo '<div class="product_custom_field form-field backorder-date hide_if_variable hide_if_external hide_if_grouped">';
		\woocommerce_wp_text_input( [
			'id'       => 'backorder_date',
			'label'    => \__( 'Backorder date', 'woocommerce' ),
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
		if ( array_key_exists( 'backorder_date', $_POST ) ) {
			$raw = \wp_unslash( (string) $_POST['backorder_date'] );
			$val = trim( $raw );

			// If cleared, remove the meta so messages don't persist.
			if ( $val === '' ) {
				\delete_post_meta( $post_id, 'backorder_date' );

				return;
			}

			// Validate and normalise to Y-m-d for consistency.
			$dt = \DateTime::createFromFormat( 'Y-m-d', $val );
			if ( $dt ) {
				\update_post_meta( $post_id, 'backorder_date', $dt->format( 'Y-m-d' ) );
			} else {
				// Fallback: store the sanitised string.
				\update_post_meta( $post_id, 'backorder_date', \sanitize_text_field( $val ) );
			}
		}
	}
}