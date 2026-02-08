<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\AdminAuthorFilter;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\AdminAuthorFilter
 */
class AdminAuthorFilterTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hook(): void {
		Actions\expectAdded( 'restrict_manage_posts' )->once();

		new AdminAuthorFilter( [] );
	}

	/**
	 * @return void
	 */
	public function test_filter_by_the_author_outputs_dropdown(): void {
		$snippet = new AdminAuthorFilter( [] );

		Functions\expect( 'wp_dropdown_users' )
			->once()
			->with( [
				'name'            => 'author',
				'show_option_all' => 'All Authors',
			] );

		Functions\expect( '__' )
			->once()
			->with( 'All Authors', THEMENAME )
			->andReturn( 'All Authors' );

		$snippet->filter_by_the_author();
	}
}
