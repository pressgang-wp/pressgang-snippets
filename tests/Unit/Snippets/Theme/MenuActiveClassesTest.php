<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\MenuActiveClasses;
use PressGang\Tests\Snippets\Unit\TestCase;
use Timber\URLHelper;

/**
 * @covers \PressGang\Snippets\Theme\MenuActiveClasses
 */
class MenuActiveClassesTest extends TestCase {

	/**
	 * Stubs the WP/server state URLHelper::get_current_url() reads.
	 *
	 * @return void
	 */
	protected function stub_current_url(): void {
		Functions\when( 'is_ssl' )->justReturn( false );

		$_SERVER['HTTP_HOST']   = 'example.test';
		$_SERVER['SERVER_NAME'] = 'example.test';
		$_SERVER['REQUEST_URI'] = '/current/';
	}

	/**
	 * @return void
	 */
	public function test_constructor_registers_menu_class_filter(): void {
		Filters\expectAdded( 'nav_menu_css_class' )
			->once()
			->with( \Mockery::type( 'array' ), 10, 2 );

		new MenuActiveClasses( [] );
	}

	/**
	 * @return void
	 */
	public function test_non_matching_url_leaves_classes_unchanged(): void {
		$this->stub_current_url();

		$item = (object) [ 'url' => 'http://example.test/elsewhere/' ];

		$classes = ( new MenuActiveClasses( [] ) )->add_active_classes( [ 'menu-item' ], $item );

		self::assertSame( [ 'menu-item' ], $classes );
	}

	/**
	 * @return void
	 */
	public function test_matching_url_adds_active_classes(): void {
		$this->stub_current_url();

		$item = (object) [ 'url' => URLHelper::get_current_url() ];

		$classes = ( new MenuActiveClasses( [] ) )->add_active_classes( [ 'menu-item' ], $item );

		self::assertSame( [ 'menu-item', 'active', 'current-menu-item' ], $classes );
	}
}
