<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class WooCommerceDequeueSelectWoo
 *
 * A snippet file for removing the WooCommerce Select Woo styles so that the dropdowns can be custom styled.
 *
 * @package PressGang\Snippets
 */
class WooCommerceDequeueSelectWoo implements SnippetInterface {

	/**
	 * WooCommerceDequeueStyles constructor.
	 *
	 * @param array $args
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_enqueue_styles', [ $this, 'dequeue_select_woo' ] );
	}

	/**
	 * Dequeues Select2/SelectWoo styles to allow custom dropdown styling.
	 *
	 * Although hooked to the 'woocommerce_enqueue_styles' filter, this method
	 * dequeues styles via wp_dequeue_style/wp_deregister_style rather than
	 * filtering the array.
	 *
	 * @param array<string, array<string, mixed>> $enqueue_styles WooCommerce styles array (passed through).
	 *
	 * @return array<string, array<string, mixed>> The unmodified styles array.
	 */
	public function dequeue_select_woo( array $enqueue_styles = [] ): array {
		\wp_dequeue_style( 'select2' );
		\wp_deregister_style( 'select2' );
		\wp_dequeue_style( 'selectWoo' );
		\wp_deregister_style( 'selectWoo' );

		return $enqueue_styles;
	}
}
