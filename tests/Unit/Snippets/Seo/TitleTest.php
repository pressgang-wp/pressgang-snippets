<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Seo;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Seo\Title;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Seo\Title
 */
class TitleTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filter(): void {
		Filters\expectAdded( 'wp_title' )->once();

		new Title( [] );
	}

	/**
	 * @return void
	 */
	public function test_filter_returns_title_unchanged_for_feeds(): void {
		$snippet = new Title( [] );

		Functions\expect( 'is_feed' )
			->once()
			->andReturn( true );

		$result = $snippet->filter_wp_title( 'My Post', '|', 'right' );

		$this->assertSame( 'My Post', $result );
	}

	/**
	 * YoastTestCase stubs get_bloginfo to return $show ('name' -> 'name').
	 *
	 * @return void
	 */
	public function test_filter_builds_search_title(): void {
		$snippet = new Title( [] );

		$GLOBALS['paged'] = 0;

		Functions\expect( 'is_feed' )
			->once()
			->andReturn( false );

		Functions\expect( 'is_search' )
			->once()
			->andReturn( true );

		Functions\expect( '_x' )
			->once()
			->with( 'Search', 'Title', THEMENAME )
			->andReturn( 'Search' );

		Functions\expect( 'get_search_query' )
			->once()
			->andReturn( 'test query' );

		// get_bloginfo is pre-stubbed by YoastTestCase: returns $show for
		// unknown keys, so get_bloginfo('name','display') returns 'name'.

		$result = $snippet->filter_wp_title( '', '|', 'right' );

		$this->assertStringContainsString( "Search 'test query'", $result );
		$this->assertStringContainsString( 'name', $result );
	}

	/**
	 * @return void
	 */
	public function test_filter_builds_standard_title_with_right_location(): void {
		$snippet = new Title( [] );

		$GLOBALS['paged'] = 0;
		$GLOBALS['page']  = 0;

		Functions\expect( 'is_feed' )
			->once()
			->andReturn( false );

		Functions\expect( 'is_search' )
			->once()
			->andReturn( false );

		// get_bloginfo('name','display') returns 'name' via YoastTestCase stub.

		Functions\expect( 'is_home' )
			->once()
			->andReturn( false );

		Functions\expect( 'is_front_page' )
			->once()
			->andReturn( false );

		$result = $snippet->filter_wp_title( 'My Post ', '|', 'right' );

		// 'right' case appends site name directly: 'My Post ' . 'name'
		$this->assertStringContainsString( 'My Post', $result );
		$this->assertStringContainsString( 'name', $result );
	}

	/**
	 * @return void
	 */
	public function test_filter_appends_description_on_front_page(): void {
		$snippet = new Title( [] );

		$GLOBALS['paged'] = 0;
		$GLOBALS['page']  = 0;

		Functions\expect( 'is_feed' )
			->once()
			->andReturn( false );

		Functions\expect( 'is_search' )
			->once()
			->andReturn( false );

		// get_bloginfo('name','display') returns 'name'
		// get_bloginfo('description','display') returns 'description'

		Functions\expect( 'is_home' )
			->once()
			->andReturn( true );

		Functions\expect( 'is_front_page' )
			->andReturn( true );

		$result = $snippet->filter_wp_title( '', '-', 'right' );

		// Site description is appended on front page
		$this->assertStringContainsString( 'description', $result );
	}

	/**
	 * @return void
	 */
	public function test_filter_appends_page_number_when_paged(): void {
		$snippet = new Title( [] );

		$GLOBALS['paged'] = 3;
		$GLOBALS['page']  = 0;

		Functions\expect( 'is_feed' )
			->andReturn( false );

		Functions\expect( 'is_search' )
			->andReturn( false );

		// get_bloginfo('name','display') returns 'name' via stub.

		Functions\expect( 'is_home' )
			->andReturn( false );

		Functions\expect( 'is_front_page' )
			->andReturn( false );

		$result = $snippet->filter_wp_title( 'Archive ', '|', 'right' );

		$this->assertStringContainsString( '3', $result );
	}

	/**
	 * @return void
	 */
	public function test_search_title_includes_page_number_when_paged(): void {
		$snippet = new Title( [] );

		$GLOBALS['paged'] = 2;

		Functions\expect( 'is_feed' )
			->andReturn( false );

		Functions\expect( 'is_search' )
			->andReturn( true );

		Functions\expect( '_x' )
			->with( 'Search', 'Title', THEMENAME )
			->andReturn( 'Search' );

		Functions\expect( 'get_search_query' )
			->andReturn( 'foo' );

		$result = $snippet->filter_wp_title( '', '|', 'right' );

		$this->assertStringContainsString( '2', $result );
		$this->assertStringContainsString( "Search 'foo'", $result );
	}

	/**
	 * @return void
	 */
	public function test_standard_title_with_left_location(): void {
		$snippet = new Title( [] );

		$GLOBALS['paged'] = 0;
		$GLOBALS['page']  = 0;

		Functions\expect( 'is_feed' )
			->andReturn( false );

		Functions\expect( 'is_search' )
			->andReturn( false );

		Functions\expect( 'is_home' )
			->andReturn( false );

		Functions\expect( 'is_front_page' )
			->andReturn( false );

		// 'left' case prepends site name: 'name' . $title
		$result = $snippet->filter_wp_title( ' My Post', '|', 'left' );

		$this->assertStringStartsWith( 'name', $result );
	}
}
