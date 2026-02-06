<?php


namespace PressGang\Snippets\WooCommerce;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes the "Downloads" tab from the WooCommerce My Account navigation
 * menu.
 *
 * Enable this snippet on stores that do not sell downloadable products to
 * simplify the customer account area.
 */
class RemoveDownloads implements SnippetInterface {

	/**
	 * Hooks into the woocommerce_account_menu_items filter.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_account_menu_items', [ $this, 'filter_account_menu_items' ] );
	}

	/**
	 * Removes the 'downloads' key from the account menu items array.
	 *
	 * @param array<string, string> $items Account menu items.
	 *
	 * @return array<string, string> The filtered menu items.
	 */
	public function filter_account_menu_items( array $items ): array {
		unset( $items['downloads'] );

		return $items;
	}
}
