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
	 * Dequeues select woo styles
	 *
	 * @hooked woocommerce_enqueue_styles
	 * @return void
	 */
	public function dequeue_select_woo(): void {
		\wp_dequeue_style( 'select2' );
		\wp_deregister_style( 'select2' );
		\wp_dequeue_style( 'selectWoo' );
		\wp_deregister_style( 'selectWoo' );
	}
}
