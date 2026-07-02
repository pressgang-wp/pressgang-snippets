<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce\Checkout;

use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\Checkout\DisableCoupons;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\Checkout\DisableCoupons
 */
class DisableCouponsTest extends TestCase {

	public function test_constructor_removes_checkout_coupon_form(): void {
		Functions\expect( 'remove_action' )
			->once()
			->with( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

		new DisableCoupons( [] );
	}
}
