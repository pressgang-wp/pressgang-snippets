<?php


namespace PressGang\Snippets;

/**
 * Hides the default "Uncategorized" product category from the shop page's
 * subcategory listing by excluding it via the
 * woocommerce_product_subcategories_args filter.
 *
 * Enable this snippet to keep the shop page tidy when the default product
 * category is not meaningful.
 */
class WooCommerceRemoveUncategorized implements SnippetInterface {

	/**
	 * Hooks into the woocommerce_product_subcategories_args filter.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_product_subcategories_args', [ $this, 'remove_uncategorized_category' ] );
	}

	/**
	 * Adds the default product category ID to the 'exclude' key so
	 * WooCommerce omits "Uncategorized" from subcategory listings.
	 *
	 * @param array<string, mixed> $args Subcategory query arguments.
	 *
	 * @return array<string, mixed> The modified arguments.
	 */
	public function remove_uncategorized_category( array $args ): array {
		$args['exclude'] = \get_option( 'default_product_cat' );

		return $args;
	}
}
