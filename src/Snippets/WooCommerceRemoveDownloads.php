<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class WooCommerceRemoveDownloads
 *
 * A snippet file for removing the WooCommerce Downloads features.
 *
 * @package PressGang\Snippets
 */
class WooCommerceRemoveDownloads implements SnippetInterface {

	/**
	 * WooCommerceRemoveDownloads constructor.
	 *
	 * @param array $args
	 */
	public function __construct( array $args ) {
		\add_filter( 'woocommerce_account_menu_items', [ $this, 'filter_account_menu_items' ] );
	}

	/**
	 * Removes the "Downloads" tab from the WooCommerce account menu
	 *
	 * @hooked woocommerce_account_menu_items
	 * @param $items
	 *
	 * @return mixed
	 */
	public function filter_account_menu_items( $items ): mixed {
		unset( $items['downloads'] );
		return $items;
	}

}
