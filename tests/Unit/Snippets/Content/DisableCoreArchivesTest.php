<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use PressGang\Snippets\Content\DisableCoreArchives;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\DisableCoreArchives
 */
class DisableCoreArchivesTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_disables_author_and_date_archives_by_default(): void {
		Filters\expectAdded( 'author_rewrite_rules' )->once();
		Filters\expectAdded( 'date_rewrite_rules' )->once();
		Filters\expectAdded( 'post_rewrite_rules' )->never();
		Filters\expectAdded( 'category_rewrite_rules' )->never();
		Filters\expectAdded( 'post_tag_rewrite_rules' )->never();
		Filters\expectAdded( 'pre_handle_404' )->once()->with( Mockery::type( 'array' ), 10, 2 );

		new DisableCoreArchives( [] );
	}

	/**
	 * @return void
	 */
	public function test_constructor_registers_only_configured_route_filters(): void {
		Filters\expectAdded( 'post_rewrite_rules' )->once();
		Filters\expectAdded( 'category_rewrite_rules' )->once();
		Filters\expectAdded( 'post_tag_rewrite_rules' )->once();
		Filters\expectAdded( 'author_rewrite_rules' )->never();
		Filters\expectAdded( 'date_rewrite_rules' )->never();

		new DisableCoreArchives( [ 'routes' => [ 'post', 'category', 'tag', 'unsupported' ] ] );
	}

	/**
	 * @return void
	 */
	public function test_remove_rewrite_rules_returns_an_empty_array(): void {
		$snippet = new DisableCoreArchives( [] );

		$this->assertSame( [], $snippet->remove_rewrite_rules( [ '^author/(.+)' => 'index.php?author_name=$matches[1]' ] ) );
	}

	/**
	 * @return void
	 */
	public function test_disabled_author_query_becomes_a_404(): void {
		$snippet = new DisableCoreArchives( [ 'routes' => [ 'author' ] ] );
		$query   = Mockery::mock( 'WP_Query' );

		$query->shouldReceive( 'is_author' )->once()->andReturn( true );
		$query->shouldReceive( 'set_404' )->once();
		Functions\expect( 'status_header' )->once()->with( 404 );
		Functions\expect( 'nocache_headers' )->once();

		$this->assertTrue( $snippet->maybe_set_404( false, $query ) );
	}

	/**
	 * @return void
	 */
	public function test_unrelated_query_preserves_existing_status_handling(): void {
		$snippet = new DisableCoreArchives( [ 'routes' => [ 'author', 'date' ] ] );
		$query   = Mockery::mock( 'WP_Query' );

		$query->shouldReceive( 'is_author' )->once()->andReturn( false );
		$query->shouldReceive( 'is_date' )->once()->andReturn( false );
		$query->shouldReceive( 'set_404' )->never();
		Functions\expect( 'status_header' )->never();
		Functions\expect( 'nocache_headers' )->never();

		$this->assertFalse( $snippet->maybe_set_404( false, $query ) );
	}

	/**
	 * @return void
	 */
	public function test_existing_preemption_is_preserved_without_inspecting_query(): void {
		$snippet = new DisableCoreArchives( [] );
		$query   = Mockery::mock( 'WP_Query' );

		$query->shouldNotReceive( 'is_author' );
		$query->shouldNotReceive( 'is_date' );

		$this->assertTrue( $snippet->maybe_set_404( true, $query ) );
	}
}
