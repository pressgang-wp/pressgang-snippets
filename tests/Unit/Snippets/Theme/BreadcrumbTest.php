<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\Breadcrumb;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\Breadcrumb
 */
class BreadcrumbTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_twig_filter(): void {
		Filters\expectAdded( 'timber/twig' )->once();

		new Breadcrumb( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_to_twig_adds_breadcrumb_function(): void {
		$snippet = new Breadcrumb( [] );

		$twig = \Mockery::mock( \Twig\Environment::class );
		$twig->shouldReceive( 'addFunction' )
			->once()
			->with( \Mockery::on( function ( $fn ) {
				return $fn instanceof \Twig\TwigFunction && $fn->getName() === 'breadcrumb';
			} ) );

		$result = $snippet->add_to_twig( $twig );

		$this->assertSame( $twig, $result );
	}

	/**
	 * @return void
	 */
	public function test_generate_links_returns_empty_for_front_page(): void {
		$snippet = new Breadcrumb( [] );

		Functions\expect( 'is_front_page' )->once()->andReturn( true );

		$snippet->generate_links();

		$this->assertEmpty( $snippet->breadcrumbs );
	}

	/**
	 * @return void
	 */
	public function test_generate_links_builds_home_and_page_breadcrumbs(): void {
		$snippet = new Breadcrumb( [] );

		Functions\expect( 'is_front_page' )->once()->andReturn( false );

		// append_home_link
		Functions\expect( '_x' )
			->once()
			->with( 'Home', 'Breadcrumb', THEMENAME )
			->andReturn( 'Home' );

		Functions\expect( 'get_site_url' )
			->once()
			->andReturn( 'https://example.com' );

		// Conditional checks — only is_page returns true
		Functions\expect( 'is_category' )->once()->andReturn( false );
		Functions\expect( 'is_tag' )->once()->andReturn( false );
		Functions\expect( 'is_tax' )->once()->andReturn( false );
		Functions\expect( 'is_archive' )->once()->andReturn( false );
		Functions\expect( 'is_single' )->once()->andReturn( false );
		Functions\expect( 'is_page' )->once()->andReturn( true );

		// handle_page: global $post with no parent
		$post              = \Mockery::mock( '\WP_Post' );
		$post->post_parent = 0;
		$GLOBALS['post']   = $post;

		Functions\expect( 'get_the_title' )
			->once()
			->andReturn( 'About Us' );

		// Paged check
		Functions\expect( 'get_query_var' )
			->once()
			->with( 'paged' )
			->andReturn( 0 );

		$snippet->generate_links();

		$this->assertCount( 2, $snippet->breadcrumbs );

		// First: home link
		$this->assertSame( 'Home', $snippet->breadcrumbs[0]['title'] );
		$this->assertSame( 'https://example.com', $snippet->breadcrumbs[0]['url'] );

		// Second: current page
		$this->assertSame( 'About Us', $snippet->breadcrumbs[1]['title'] );
		$this->assertNull( $snippet->breadcrumbs[1]['url'] );
	}
}
