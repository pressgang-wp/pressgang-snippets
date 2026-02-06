<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\DuplicatePost;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\DuplicatePost
 */
class DuplicatePostTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Filters\expectAdded( 'post_row_actions' )->once();
		Filters\expectAdded( 'page_row_actions' )->once();
		Actions\expectAdded( 'admin_action_duplicate_post_as_draft' )->once();
		Actions\expectAdded( 'admin_notices' )->once();

		new DuplicatePost( [] );
	}

	/**
	 * @return void
	 */
	public function test_duplicate_post_link_returns_unmodified_when_user_lacks_capability(): void {
		$snippet = new DuplicatePost( [] );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'edit_posts' )
			->andReturn( false );

		$post     = \Mockery::mock( '\WP_Post' );
		$post->ID = 42;

		$actions = [ 'edit' => '<a href="#">Edit</a>' ];
		$result  = $snippet->duplicate_post_link( $actions, $post );

		$this->assertSame( $actions, $result );
		$this->assertArrayNotHasKey( 'duplicate', $result );
	}

	/**
	 * @return void
	 */
	public function test_duplicate_post_link_adds_duplicate_action_when_user_has_capability(): void {
		$snippet = new DuplicatePost( [] );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'edit_posts' )
			->andReturn( true );

		Functions\expect( 'add_query_arg' )
			->once()
			->andReturn( 'admin.php?action=duplicate_post_as_draft&post=42' );

		Functions\expect( 'wp_nonce_url' )
			->once()
			->andReturn( 'admin.php?action=duplicate_post_as_draft&post=42&duplicate_nonce=abc123' );

		Functions\expect( 'esc_url' )
			->once()
			->andReturnFirstArg();

		Functions\expect( 'esc_attr__' )
			->once()
			->andReturn( 'Duplicate this item' );

		Functions\expect( 'esc_html__' )
			->once()
			->andReturn( 'Duplicate' );

		$post     = \Mockery::mock( '\WP_Post' );
		$post->ID = 42;

		$actions = [ 'edit' => '<a href="#">Edit</a>' ];
		$result  = $snippet->duplicate_post_link( $actions, $post );

		$this->assertArrayHasKey( 'duplicate', $result );
		$this->assertStringContainsString( 'Duplicate', $result['duplicate'] );
		$this->assertArrayHasKey( 'edit', $result );
	}
}
