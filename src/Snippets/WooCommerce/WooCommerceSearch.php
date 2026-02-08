<?php

namespace PressGang\Snippets\WooCommerce;

use PressGang\Snippets\SnippetInterface;

/**
 * Customises WooCommerce search by limiting search results to products and
 * ensuring WooCommerce loop pagination totals are set correctly on search pages.
 *
 * Enable this snippet to make WooCommerce search behave consistently in themes
 * that rely on the WooCommerce loop totals for pagination.
 */
class WooCommerceSearch implements SnippetInterface {

	/**
	 * Registers filters and actions for WooCommerce search adjustments.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'search_post_types', [ $this, 'search_post_types' ] );
		\add_action( 'woocommerce_after_shop_loop', [ $this, 'add_search_pagination' ], 5 );
	}

	/**
	 * Restricts search to WooCommerce products.
	 *
	 * @return array<int, string>
	 */
	public function search_post_types(): array {
		return [ 'product' ];
	}

	/**
	 * Ensures WooCommerce loop pagination totals are set on search pages.
	 *
	 * @return void
	 */
	public function add_search_pagination(): void {
		if ( ! class_exists( 'WooCommerce' ) || ! \is_search() ) {
			return;
		}

		global $wp_query;

		$GLOBALS['woocommerce_loop']['total']       = $wp_query->found_posts;
		$GLOBALS['woocommerce_loop']['total_pages'] = $wp_query->max_num_pages;
	}
}
