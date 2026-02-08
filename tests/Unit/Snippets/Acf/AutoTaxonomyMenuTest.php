<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Acf;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Acf\AutoTaxonomyMenu;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Acf\AutoTaxonomyMenu
 */
class AutoTaxonomyMenuTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filters(): void {
		Filters\expectAdded( 'acf/load_field/name=taxonomy_sub_menu' )->once();
		Filters\expectAdded( 'wp_get_nav_menu_items' )->once();

		new AutoTaxonomyMenu( [] );
	}

	/**
	 * @return void
	 */
	public function test_taxonomy_select_populates_choices(): void {
		$snippet = new AutoTaxonomyMenu( [] );

		Functions\expect( 'get_taxonomies' )
			->once()
			->andReturn( [ 'genre' ] );

		Functions\expect( 'get_taxonomy' )
			->once()
			->with( 'genre' )
			->andReturn( (object) [ 'name' => 'genre', 'label' => 'Genre' ] );

		$field  = [ 'choices' => [] ];
		$result = $snippet->taxonomy_select( $field );

		$this->assertSame( [ 'genre' => 'Genre' ], $result['choices'] );
	}

	/**
	 * @return void
	 */
	public function test_add_sub_menu_items_appends_terms(): void {
		$snippet = new AutoTaxonomyMenu( [] );

		$item = (object) [ 'ID' => 10 ];
		$term = (object) [ 'term_id' => 7, 'name' => 'Fiction', 'slug' => 'fiction' ];

		Functions\when( 'get_field' )->justReturn( 'genre' );

		Functions\expect( 'is_admin' )
			->once()
			->andReturn( false );
		Functions\expect( 'get_field' )
			->once()
			->with( 'taxonomy_sub_menu', $item )
			->andReturn( 'genre' );
		Functions\expect( 'get_terms' )
			->once()
			->andReturn( [ $term ] );
		Functions\expect( 'is_wp_error' )
			->once()
			->with( [ $term ] )
			->andReturn( false );
		Functions\expect( 'get_term_link' )
			->once()
			->with( $term )
			->andReturn( 'https://example.test/genre/fiction' );

		$items = $snippet->add_sub_menu_items( [ $item ], (object) [], [] );

		$this->assertCount( 2, $items );
		$this->assertSame( 'Fiction', $items[1]->title );
		$this->assertSame( 10, $items[1]->menu_item_parent );
	}
}
