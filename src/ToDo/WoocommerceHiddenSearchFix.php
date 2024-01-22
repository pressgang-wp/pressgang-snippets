<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\add_action;
use function PressGang\Snippets\is_admin;

class WooCommerceHiddenSearchFix {

	/**
	 * WooCommerceHiddenSearchFix constructor
	 *
	 */
	public function __construct() {
		add_action( 'pre_get_posts',
			[ $this, 'hidden_product_search_query_fix' ] );
	}

	/**
	 * hidden_product_search_query_fix
	 *
	 * @param  bool  $query
	 */
	public function hidden_product_search_query_fix( $query = false ) {
		global $wp_the_query;

		if ( $query === $wp_the_query && $query->is_search() && ! is_admin() ) {
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

}

new WooCommerceHiddenSearchFix();
