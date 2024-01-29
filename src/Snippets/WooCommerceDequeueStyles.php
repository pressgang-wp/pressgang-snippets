<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class WooCommerceDequeueStyles
 *
 * A snippet file for removing the WooCommerce default styles so that the shop can be custom styled.
 *
 * @package PressGang\Snippets
 */
class WooCommerceDequeueStyles implements SnippetInterface {

	/**
	 * WooCommerceDequeueStyles constructor.
	 *
	 * @param array $args
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_enqueue_styles', [ $this, 'dequeue_styles' ] );
	}

	/**
	 * Dequeues the styles
	 *
	 * @hooked woocommerce_enqueue_styles
	 *
	 * @param $enqueue_styles
	 *
	 * @return mixed
	 */
	public function dequeue_styles( $enqueue_styles ): mixed {
		unset( $enqueue_styles['woocommerce-general'] ); // remove the gloss
		unset( $enqueue_styles['woocommerce-layout'] ); // remove the layout
		unset( $enqueue_styles['woocommerce-smallscreen'] ); // remove the smallscreen optimisation

		return $enqueue_styles;
	}
}
