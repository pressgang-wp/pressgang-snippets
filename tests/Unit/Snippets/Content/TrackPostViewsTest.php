<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\TrackPostViews;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures set_post_views calls.
 */
class TestableTrackPostViews extends TrackPostViews {

	/** @var int|null */
	public ?int $last_tracked = null;

	public function set_post_views( int $post_id ): void {
		$this->last_tracked = $post_id;
	}
}

/**
 * @covers \PressGang\Snippets\Content\TrackPostViews
 */
class TrackPostViewsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'wp_head' )->twice();
		Actions\expectAdded( 'the_post' )->once();

		Functions\expect( 'remove_action' )
			->once()
			->with( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

		new TrackPostViews( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_views_to_post_sets_views_property(): void {
		Functions\when( 'remove_action' )->justReturn( null );

		$snippet = new TrackPostViews( [] );

		Functions\expect( 'get_post_meta' )
			->once()
			->with( 12, TrackPostViews::COUNT_KEY, true )
			->andReturn( 5 );

		$post = (object) [ 'ID' => 12 ];
		$result = $snippet->add_views_to_post( $post );

		$this->assertSame( 5, $result->views );
	}

	/**
	 * @return void
	 */
	public function test_track_post_views_calls_set_when_single(): void {
		Functions\when( 'remove_action' )->justReturn( null );

		$snippet = new TestableTrackPostViews( [] );

		Functions\expect( 'is_single' )
			->once()
			->andReturn( true );

		$snippet->track_post_views( 42 );

		$this->assertSame( 42, $snippet->last_tracked );
	}

	/**
	 * @return void
	 */
	public function test_cache_breaker_outputs_when_single(): void {
		Functions\when( 'remove_action' )->justReturn( null );

		$snippet = new TrackPostViews( [] );

		Functions\expect( 'is_single' )
			->once()
			->andReturn( true );

		$this->expectOutputRegex( '/mfunc/' );
		$snippet->cache_breaker();
	}
}
