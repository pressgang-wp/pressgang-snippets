<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce;

use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\RemoveShopLoopAddToCart;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\RemoveShopLoopAddToCart
 */
class RemoveShopLoopAddToCartTest extends TestCase {

	public function test_constructor_removes_loop_add_to_cart_action(): void {
		Functions\expect( 'remove_action' )
			->once()
			->with( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );

		new RemoveShopLoopAddToCart( [] );
	}
}
