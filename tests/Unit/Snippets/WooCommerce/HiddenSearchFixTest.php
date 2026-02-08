<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\HiddenSearchFix;
use PressGang\Tests\Snippets\Unit\TestCase;

if ( ! class_exists( 'WooCommerce' ) ) {
	class WooCommerce {}
}

if ( ! class_exists( 'WP_Query' ) ) {
	class WP_Query {
		public bool $is_search = true;
		public array $set_data = [];

		public function is_search(): bool {
			return $this->is_search;
		}

		public function set( string $key, $value ): void {
			$this->set_data[ $key ] = $value;
		}
	}
}

/**
 * @covers \PressGang\Snippets\WooCommerce\HiddenSearchFix
 */
class HiddenSearchFixTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hook(): void {
		Actions\expectAdded( 'pre_get_posts' )->once();

		new HiddenSearchFix( [] );
	}

	/**
	 * @return void
	 */
	public function test_hidden_search_query_applies_tax_query(): void {
		$snippet = new HiddenSearchFix( [] );

		Functions\expect( 'is_admin' )
			->once()
			->andReturn( false );

		$query = new \WP_Query();
		$GLOBALS['wp_the_query'] = $query;

		$snippet->hidden_product_search_query_fix( $query );

		$this->assertArrayHasKey( 'tax_query', $query->set_data );
		$this->assertSame( 'product_visibility', $query->set_data['tax_query'][0]['taxonomy'] );
	}
}
