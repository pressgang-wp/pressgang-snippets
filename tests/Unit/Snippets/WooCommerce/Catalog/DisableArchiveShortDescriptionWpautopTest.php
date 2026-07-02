<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce\Catalog;

use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\Catalog\DisableArchiveShortDescriptionWpautop;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\Catalog\DisableArchiveShortDescriptionWpautop
 */
class DisableArchiveShortDescriptionWpautopTest extends TestCase {

	public function test_constructor_removes_wpautop_filter(): void {
		Functions\expect( 'remove_filter' )
			->once()
			->with( 'woocommerce_short_description', 'wpautop' );

		new DisableArchiveShortDescriptionWpautop( [] );
	}
}
