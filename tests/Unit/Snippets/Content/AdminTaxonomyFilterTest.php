<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\AdminTaxonomyFilter;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\AdminTaxonomyFilter
 */
class AdminTaxonomyFilterTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hook(): void {
		Actions\expectAdded( 'restrict_manage_posts' )->once();

		new AdminTaxonomyFilter( [] );
	}

	/**
	 * @return void
	 */
	public function test_admin_taxonomy_filter_outputs_selects(): void {
		$this->stubEscapeFunctions();

		$snippet = new AdminTaxonomyFilter( [] );

		$taxonomy = (object) [
			'name'   => 'genre',
			'labels' => (object) [ 'name' => 'Genres' ],
		];
		$term = (object) [
			'slug' => 'fiction',
			'name' => 'Fiction',
		];

		Functions\expect( 'get_taxonomies' )
			->once()
			->andReturn( [ 'genre' => $taxonomy ] );
		Functions\expect( 'apply_filters' )
			->once()
			->with( 'admin_taxonomy_filters', [ 'genre' => $taxonomy ] )
			->andReturn( [ 'genre' => $taxonomy ] );
		Functions\expect( 'get_terms' )
			->once()
			->with( 'genre' )
			->andReturn( [ $term ] );

		$this->expectOutputRegex( '/<select[^>]*name="genre"/' );

		$snippet->admin_taxonomy_filter();
	}
}
