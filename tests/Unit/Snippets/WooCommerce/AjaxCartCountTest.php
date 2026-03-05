<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\AjaxCartCount;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\AjaxCartCount
 */
class AjaxCartCountTest extends TestCase {

	public function test_constructor_registers_fragment_filter(): void {
		Filters\expectAdded( 'woocommerce_add_to_cart_fragments' )->once();

		new AjaxCartCount( [] );
	}
}
