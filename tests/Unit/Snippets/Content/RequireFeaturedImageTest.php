<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\RequireFeaturedImage;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\RequireFeaturedImage
 */
class RequireFeaturedImageTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'save_post' )->once();
		Actions\expectAdded( 'admin_notices' )->once();

		new RequireFeaturedImage( [] );
	}

	/**
	 * @return void
	 */
	public function test_check_featured_image_skips_for_other_post_types(): void {
		$snippet = new RequireFeaturedImage( [ 'post_types' => [ 'page' ] ] );

		Functions\expect( 'wp_is_post_autosave' )
			->once()
			->with( 10 )
			->andReturn( false );
		Functions\expect( 'wp_is_post_revision' )
			->once()
			->with( 10 )
			->andReturn( false );
		Functions\expect( 'get_post_type' )
			->once()
			->with( 10 )
			->andReturn( 'post' );

		$snippet->check_featured_image( 10 );
	}

	/**
	 * @return void
	 */
	public function test_check_featured_image_sets_draft_when_missing(): void {
		$snippet = new RequireFeaturedImage( [] );

		Functions\expect( 'wp_is_post_autosave' )
			->once()
			->with( 12 )
			->andReturn( false );
		Functions\expect( 'wp_is_post_revision' )
			->once()
			->with( 12 )
			->andReturn( false );
		Functions\expect( 'get_post_type' )
			->once()
			->with( 12 )
			->andReturn( 'post' );
		Functions\expect( 'has_post_thumbnail' )
			->once()
			->with( 12 )
			->andReturn( false );
		Functions\expect( 'set_transient' )
			->once();
		Functions\expect( 'remove_action' )
			->once();
		Functions\expect( 'wp_update_post' )
			->once()
			->with( [ 'ID' => 12, 'post_status' => 'draft' ] );
		Functions\expect( 'add_action' )
			->once();

		$snippet->check_featured_image( 12 );
	}
}
