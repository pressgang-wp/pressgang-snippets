<?php

namespace PressGang\Snippets\WooCommerce;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes WooCommerce breadcrumbs from the main content area.
 */
class DisableBreadcrumbs implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'template_redirect', [ $this, 'remove_breadcrumbs' ] );
	}

	/**
	 * @return void
	 */
	public function remove_breadcrumbs(): void {
		\remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
	}
}
