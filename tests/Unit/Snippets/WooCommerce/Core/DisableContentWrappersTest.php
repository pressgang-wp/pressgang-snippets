<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce\Core;

use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\Core\DisableContentWrappers;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\Core\DisableContentWrappers
 */
class DisableContentWrappersTest extends TestCase {

	public function test_constructor_removes_content_wrappers(): void {
		Functions\expect( 'remove_action' )
			->once()
			->with( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );

		Functions\expect( 'remove_action' )
			->once()
			->with( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

		new DisableContentWrappers( [] );
	}
}
