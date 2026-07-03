<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\RemoveTaxonomyUi;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\RemoveTaxonomyUi
 */
class RemoveTaxonomyUiTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_admin_menu_hook(): void {
		Actions\expectAdded( 'admin_menu' )->once();

		new RemoveTaxonomyUi( [] );
	}

	/**
	 * @return void
	 */
	public function test_defaults_remove_post_tag_ui_from_posts(): void {
		Functions\expect( 'remove_submenu_page' )
			->once()
			->with( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
		Functions\expect( 'remove_meta_box' )
			->times( 4 ); // tagsdiv/{tax}div x normal/side contexts

		( new RemoveTaxonomyUi( [] ) )->remove_taxonomy_ui();
	}

	/**
	 * @return void
	 */
	public function test_custom_post_type_uses_scoped_menu_slugs(): void {
		Functions\expect( 'remove_submenu_page' )
			->once()
			->with( 'edit.php?post_type=event', 'edit-tags.php?taxonomy=event_type&post_type=event' );
		Functions\expect( 'remove_meta_box' )
			->times( 4 )
			->with( \Mockery::type( 'string' ), 'event', \Mockery::type( 'string' ) );

		( new RemoveTaxonomyUi( [ 'taxonomy' => 'event_type', 'post_type' => 'event' ] ) )->remove_taxonomy_ui();
	}
}
