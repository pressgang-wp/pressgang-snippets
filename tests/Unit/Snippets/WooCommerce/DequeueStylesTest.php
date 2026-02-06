<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce;

use Brain\Monkey\Filters;
use PressGang\Snippets\WooCommerce\DequeueStyles;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\DequeueStyles
 */
class DequeueStylesTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filter(): void {
		Filters\expectAdded( 'woocommerce_enqueue_styles' )->once();

		new DequeueStyles( [] );
	}

	/**
	 * @return void
	 */
	public function test_dequeue_styles_removes_wc_style_keys(): void {
		$snippet = new DequeueStyles( [] );

		$styles = [
			'woocommerce-general'     => [ 'src' => '/path/general.css' ],
			'woocommerce-layout'      => [ 'src' => '/path/layout.css' ],
			'woocommerce-smallscreen' => [ 'src' => '/path/smallscreen.css' ],
			'custom-theme-style'      => [ 'src' => '/path/custom.css' ],
		];

		$result = $snippet->dequeue_styles( $styles );

		$this->assertArrayNotHasKey( 'woocommerce-general', $result );
		$this->assertArrayNotHasKey( 'woocommerce-layout', $result );
		$this->assertArrayNotHasKey( 'woocommerce-smallscreen', $result );
		$this->assertArrayHasKey( 'custom-theme-style', $result );
	}

	/**
	 * @return void
	 */
	public function test_dequeue_styles_preserves_other_keys(): void {
		$snippet = new DequeueStyles( [] );

		$styles = [
			'my-shop-styles' => [ 'src' => '/path/shop.css' ],
			'my-cart-styles' => [ 'src' => '/path/cart.css' ],
		];

		$result = $snippet->dequeue_styles( $styles );

		$this->assertSame( $styles, $result );
	}

	/**
	 * @return void
	 */
	public function test_dequeue_styles_handles_empty_array(): void {
		$snippet = new DequeueStyles( [] );

		$result = $snippet->dequeue_styles( [] );

		$this->assertSame( [], $result );
	}
}
