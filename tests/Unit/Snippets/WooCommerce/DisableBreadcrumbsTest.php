<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\DisableBreadcrumbs;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\DisableBreadcrumbs
 */
class DisableBreadcrumbsTest extends TestCase {

	public function test_constructor_registers_template_redirect_hook(): void {
		Actions\expectAdded( 'template_redirect' )->once();

		new DisableBreadcrumbs( [] );
	}

	public function test_remove_breadcrumbs_removes_woocommerce_breadcrumb_action(): void {
		$snippet = new DisableBreadcrumbs( [] );

		Functions\expect( 'remove_action' )
			->once()
			->with( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );

		$snippet->remove_breadcrumbs();
	}
}
