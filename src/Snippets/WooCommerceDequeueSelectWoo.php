<?php

declare(strict_types=1);

namespace PressGang\Snippets;

/**
 * Dequeues and deregisters the Select2/SelectWoo CSS bundled with WooCommerce,
 * allowing themes to provide their own dropdown styling.
 *
 * Enable this snippet when you need full control over select/dropdown
 * appearance in WooCommerce.
 */
class WooCommerceDequeueSelectWoo implements SnippetInterface {

	/**
	 * Hooks into the woocommerce_enqueue_styles filter.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_enqueue_styles', [ $this, 'dequeue_select_woo' ] );
	}

	/**
	 * Dequeues and deregisters both 'select2' and 'selectWoo' stylesheets.
	 * The styles array is passed through unmodified since the removal is
	 * done via wp_dequeue_style / wp_deregister_style.
	 *
	 * @param array<string, array<string, mixed>> $enqueue_styles WooCommerce styles array.
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
