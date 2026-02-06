<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\RemovePosts;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\RemovePosts
 */
class RemovePostsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_admin_menu_hook(): void {
		Actions\expectAdded( 'admin_menu' )->once();

		new RemovePosts( [] );
	}

	/**
	 * @return void
	 */
	public function test_post_remove_calls_remove_menu_page(): void {
		$snippet = new RemovePosts( [] );

		Functions\expect( 'remove_menu_page' )
			->once()
			->with( 'edit.php' );

		$snippet->post_remove();
	}
}
