<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce\Api;

use Brain\Monkey\Filters;
use PressGang\Snippets\WooCommerce\Api\BatchItemsLimit;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\Api\BatchItemsLimit
 */
class BatchItemsLimitTest extends TestCase {

	public function test_constructor_registers_filter(): void {
		Filters\expectAdded( 'woocommerce_rest_batch_items_limit' )->once();

		new BatchItemsLimit( [] );
	}

	public function test_filter_batch_items_limit_returns_default_size(): void {
		$snippet = new BatchItemsLimit( [] );

		$this->assertSame( 1000, $snippet->filter_batch_items_limit() );
	}

	public function test_filter_batch_items_limit_returns_configured_size(): void {
		$snippet = new BatchItemsLimit( [ 'batch_size' => 2000 ] );

		$this->assertSame( 2000, $snippet->filter_batch_items_limit() );
	}
}
