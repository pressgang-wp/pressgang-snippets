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
		$product = $this->get_global_product();
		if ( ! $product ) {
			return $text;
		}

		$backorder_date = $this->get_backorder_date( $product->get_id() );
		if ( ! $backorder_date ) {
			return $text;
		}

		return $this->format_out_of_stock_message( $backorder_date );
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
		$product = $this->resolve_product_instance( $instance );
		if ( ! $product ) {
			return $availability;
		}

		if ( $product->get_stock_status() === 'outofstock' ) {
			return $this->out_of_stock_message( $availability );
		}

		if ( $product->get_stock_status() !== 'onbackorder' ) {
			return $availability;
		}

		$backorder_date = $this->get_backorder_date( $product->get_id() );
		if ( ! $backorder_date ) {
			return $availability;
		}

		return $this->format_backorder_message( $backorder_date );
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
		if ( ! array_key_exists( 'backorder_date', $_POST ) ) {
			return;
		}

		$this->save_backorder_date( $post_id, $_POST['backorder_date'] );
	}

	/**
	 * Resolve the product from either a composite product instance or globals.
	 *
	 * @param mixed $instance Product instance passed by WooCommerce.
	 *
	 * @return \WC_Product|null
	 */
	private function resolve_product_instance( mixed $instance ): ?\WC_Product {
		if ( is_a( $instance, 'WC_CP_Product' ) ) {
			return $instance->get_product();
		}

		return $this->get_global_product();
	}

	/**
	 * Get the global WooCommerce product for the current context.
	 *
	 * @return \WC_Product|null
	 */
	private function get_global_product(): ?\WC_Product {
		global $product;

		return $product instanceof \WC_Product ? $product : null;
	}

	/**
	 * Fetch the stored backorder date for a product.
	 *
	 * @param int $product_id
	 *
	 * @return string|null
	 */
	private function get_backorder_date( int $product_id ): ?string {
		$date = \get_post_meta( $product_id, 'backorder_date', true );

		return $date ? (string) $date : null;
	}

	/**
	 * Format a backorder date for display.
	 *
	 * @param string $backorder_date
	 *
	 * @return string
	 */
	private function format_backorder_date( string $backorder_date ): string {
		return \wp_date( \get_option( 'date_format' ), strtotime( $backorder_date ) );
	}

	/**
	 * Build an out-of-stock message including a backorder date.
	 *
	 * @param string $backorder_date
	 *
	 * @return string
	 */
	private function format_out_of_stock_message( string $backorder_date ): string {
		return sprintf(
			\__( 'Out of stock. Expected delivery date %s.', THEMENAME ),
			$this->format_backorder_date( $backorder_date )
		);
	}

	/**
	 * Build an on-backorder message including a backorder date.
	 *
	 * @param string $backorder_date
	 *
	 * @return string
	 */
	private function format_backorder_message( string $backorder_date ): string {
		return sprintf(
			\__( 'Available on backorder. Expected delivery date %s.', THEMENAME ),
			$this->format_backorder_date( $backorder_date )
		);
	}

	/**
	 * Persist the backorder date input from the product editor.
	 *
	 * @param int $post_id
	 * @param mixed $raw_value
	 *
	 * @return void
	 */
	private function save_backorder_date( int $post_id, mixed $raw_value ): void {
		$raw = \wp_unslash( (string) $raw_value );
		$val = trim( $raw );

		if ( $val === '' ) {
			\delete_post_meta( $post_id, 'backorder_date' );
			return;
		}

		$dt = \DateTime::createFromFormat( 'Y-m-d', $val );
		if ( $dt ) {
			\update_post_meta( $post_id, 'backorder_date', $dt->format( 'Y-m-d' ) );
			return;
		}

		\update_post_meta( $post_id, 'backorder_date', \sanitize_text_field( $val ) );
	}
}
