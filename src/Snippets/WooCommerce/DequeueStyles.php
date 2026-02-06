<?php


namespace PressGang\Snippets\WooCommerce;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes the three default WooCommerce stylesheets (general, layout, and
 * smallscreen) so the theme can provide its own shop styles from scratch.
 *
 * Enable this snippet when your theme fully handles WooCommerce styling.
 */
class DequeueStyles implements SnippetInterface {

	/**
	 * Hooks into the woocommerce_enqueue_styles filter.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_enqueue_styles', [ $this, 'dequeue_styles' ] );
	}

	/**
	 * Removes the woocommerce-general, woocommerce-layout, and
	 * woocommerce-smallscreen stylesheets from the enqueue array.
	 *
	 * @param array<string, array<string, mixed>> $enqueue_styles WooCommerce styles array.
	 *
	 * @return array<string, array<string, mixed>> The filtered styles array.
	 */
	public function dequeue_styles( array $enqueue_styles ): array {
		unset(
			$enqueue_styles['woocommerce-general'],
			$enqueue_styles['woocommerce-layout'],
			$enqueue_styles['woocommerce-smallscreen']
		);

		return $enqueue_styles;
	}
}
