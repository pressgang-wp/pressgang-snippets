<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class WooCommerce
 *
 * A snippet for removing the category "Uncategorized" from the shop page
 *
 * @package Primetools
 */
class WooCommerceRemoveUncategorized implements SnippetInterface {

	/**
	 * WooCommerceRemoveUncategorized constructor.
	 *
	 * @param array $args
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_product_subcategories_args', [ $this, 'remove_uncategorized_category' ] );
	}

	/**
	 * Remove "Uncategorized" from shop page.
	 *
	 * @hooked woocommerce_product_subcategories_args
	 *
	 * @param array $args Current arguments.
	 *
	 * @return array
	 **/
	public function remove_uncategorized_category( array $args ): array {
		$uncategorized   = \get_option( 'default_product_cat' );
		$args['exclude'] = $uncategorized;

		return $args;
	}

}
