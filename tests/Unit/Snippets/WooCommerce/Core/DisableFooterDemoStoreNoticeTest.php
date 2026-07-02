<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce\Core;

use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\Core\DisableFooterDemoStoreNotice;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\Core\DisableFooterDemoStoreNotice
 */
class DisableFooterDemoStoreNoticeTest extends TestCase {

	public function test_constructor_removes_footer_demo_store_notice(): void {
		Functions\expect( 'remove_action' )
			->once()
			->with( 'wp_footer', 'woocommerce_demo_store' );

		new DisableFooterDemoStoreNotice( [] );
	}
}
