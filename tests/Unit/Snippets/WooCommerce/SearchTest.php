<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\Search;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\Search
 */
class WooCommerceSearchTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Filters\expectAdded( 'search_post_types' )->once();
		Actions\expectAdded( 'woocommerce_after_shop_loop' )->once();

		new Search( [] );
	}

	/**
	 * @return void
	 */
	public function test_search_post_types_returns_products(): void {
		$snippet = new Search( [] );

		$this->assertSame( [ 'product' ], $snippet->search_post_types() );
	}

	/**
	 * @return void
	 */
	public function test_add_search_pagination_sets_globals(): void {
		$snippet = new Search( [] );

		Functions\expect( 'is_search' )
			->once()
			->andReturn( true );

		$GLOBALS['wp_query'] = (object) [
			'found_posts'   => 12,
			'max_num_pages' => 3,
		];

		$snippet->add_search_pagination();

		$this->assertSame( 12, $GLOBALS['woocommerce_loop']['total'] );
		$this->assertSame( 3, $GLOBALS['woocommerce_loop']['total_pages'] );
	}
}
