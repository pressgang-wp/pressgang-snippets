<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\SearchExcludePostTypes;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\SearchExcludePostTypes
 */
class SearchExcludePostTypesTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filter_on_frontend(): void {
		Functions\expect( 'is_admin' )
			->once()
			->andReturn( false );

		Filters\expectAdded( 'pre_get_posts' )->once();

		new SearchExcludePostTypes( [ 'exclude' => [ 'page' ] ] );
	}

	/**
	 * @return void
	 */
	public function test_constructor_skips_filter_on_admin(): void {
		Functions\expect( 'is_admin' )
			->once()
			->andReturn( true );

		Filters\expectAdded( 'pre_get_posts' )->never();

		new SearchExcludePostTypes( [ 'exclude' => [ 'page' ] ] );
	}

	/**
	 * @return void
	 */
	public function test_filter_excludes_specified_post_types(): void {
		Functions\expect( 'is_admin' )
			->andReturn( false );

		$snippet = new SearchExcludePostTypes( [ 'exclude' => [ 'page', 'attachment' ] ] );

		$query            = \Mockery::mock( 'WP_Query' );
		$query->is_search = true;

		$query->shouldReceive( 'get' )
			->with( 'post_type' )
			->andReturn( '' );

		Functions\expect( 'get_post_types' )
			->once()
			->with( [ 'public' => true ], 'names' )
			->andReturn( [
				'post'       => 'post',
				'page'       => 'page',
				'attachment' => 'attachment',
				'product'    => 'product',
			] );

		$query->shouldReceive( 'set' )
			->once()
			->with( 'post_type', \Mockery::on( function ( $val ) {
				$types = array_values( $val );

				return in_array( 'post', $types )
					&& in_array( 'product', $types )
					&& ! in_array( 'page', $types )
					&& ! in_array( 'attachment', $types );
			} ) );

		$snippet->filter_search_post_types( $query );
	}

	/**
	 * @return void
	 */
	public function test_filter_ignores_non_search_queries(): void {
		Functions\expect( 'is_admin' )
			->andReturn( false );

		$snippet = new SearchExcludePostTypes( [ 'exclude' => [ 'page' ] ] );

		$query            = \Mockery::mock( 'WP_Query' );
		$query->is_search = false;

		$query->shouldReceive( 'set' )->never();

		$snippet->filter_search_post_types( $query );
	}
}
