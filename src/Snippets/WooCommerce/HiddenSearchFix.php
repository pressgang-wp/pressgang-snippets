<?php

namespace PressGang\Snippets\WooCommerce;

use PressGang\Snippets\SnippetInterface;

/**
 * Ensures WooCommerce products hidden from search are excluded in front-end
 * searches by applying the product_visibility tax query.
 *
 * Enable this snippet when WooCommerce search results include hidden products.
 * Requires WooCommerce to be active.
 */
class HiddenSearchFix implements SnippetInterface {

	/**
	 * Hooks into pre_get_posts to adjust the main search query.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'pre_get_posts', [ $this, 'hidden_product_search_query_fix' ] );
	}

	/**
	 * Applies the product_visibility NOT IN tax query on the front-end search.
	 *
	 * @param \WP_Query $query
	 *
	 * @return void
	 */
	public function hidden_product_search_query_fix( \WP_Query $query ): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		global $wp_the_query;

		if ( $query !== $wp_the_query || ! $query->is_search() || \is_admin() ) {
			return;
		}

		$query->set( 'tax_query', [
			'relation' => 'OR',
			[
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'exclude-from-search',
				'operator' => 'NOT IN',
			],
		] );
	}
}
