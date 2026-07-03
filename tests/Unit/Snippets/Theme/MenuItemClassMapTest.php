<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Filters;
use PressGang\Snippets\Theme\MenuItemClassMap;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\MenuItemClassMap
 */
class MenuItemClassMapTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_classmap_filter(): void {
		Filters\expectAdded( 'timber/menuitem/classmap' )->once();

		new MenuItemClassMap( [ 'primary' => 'MyTheme\Models\MainMenuItem' ] );
	}

	/**
	 * @return void
	 */
	public function test_configured_locations_merge_into_classmap(): void {
		$snippet = new MenuItemClassMap( [ 'primary' => 'MyTheme\Models\MainMenuItem' ] );

		$classmap = $snippet->add_menu_item_classes( [ 'footer' => 'Existing\FooterItem' ] );

		self::assertSame( 'MyTheme\Models\MainMenuItem', $classmap['primary'] );
		self::assertSame( 'Existing\FooterItem', $classmap['footer'] );
	}

	/**
	 * @return void
	 */
	public function test_configured_location_overrides_existing_entry(): void {
		$snippet = new MenuItemClassMap( [ 'primary' => 'MyTheme\Models\MainMenuItem' ] );

		$classmap = $snippet->add_menu_item_classes( [ 'primary' => 'Timber\MenuItem' ] );

		self::assertSame( 'MyTheme\Models\MainMenuItem', $classmap['primary'] );
	}
}
