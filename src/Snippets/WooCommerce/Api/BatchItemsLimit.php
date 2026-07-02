<?php

namespace PressGang\Snippets\WooCommerce\Api;

use PressGang\Snippets\SnippetInterface;

/**
 * Raises the WooCommerce REST batch items limit for bulk integrations.
 */
class BatchItemsLimit implements SnippetInterface {

	private int $batch_size;

	/**
	 * @param array<string, mixed> $args Supports `batch_size`.
	 */
	public function __construct( array $args ) {
		$this->batch_size = (int) ( $args['batch_size'] ?? 1000 );

		\add_filter( 'woocommerce_rest_batch_items_limit', [ $this, 'filter_batch_items_limit' ] );
	}

	/**
	 * @return int
	 */
	public function filter_batch_items_limit(): int {
		return $this->batch_size;
	}
}
