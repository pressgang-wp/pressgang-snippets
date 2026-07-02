<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce\Cart;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use PressGang\Snippets\WooCommerce\Cart\DisableEmptyCartFragments;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\Cart\DisableEmptyCartFragments
 */
class DisableEmptyCartFragmentsTest extends TestCase {

	public function test_constructor_registers_filter(): void {
		Filters\expectAdded( 'woocommerce_get_script_data' )->once()->with( Mockery::type( 'array' ), 10, 2 );

		new DisableEmptyCartFragments( [] );
	}

	public function test_filter_preserves_unrelated_script_data(): void {
		$snippet = new DisableEmptyCartFragments( [] );
		$params  = [ 'ajax_url' => '/admin-ajax.php' ];

		$this->assertSame( $params, $snippet->filter_script_data( $params, 'woocommerce' ) );
	}

	public function test_filter_preserves_false_for_unlocalized_woocommerce_handles(): void {
		$snippet = new DisableEmptyCartFragments( [] );

		$this->assertFalse( $snippet->filter_script_data( false, 'wc-country-select' ) );
	}

	public function test_filter_accepts_false_cart_fragment_data_without_fatal(): void {
		$this->mock_cart_context( is_empty: true );

		$snippet = new DisableEmptyCartFragments( [] );

		$this->assertNull( $snippet->filter_script_data( false, 'wc-cart-fragments' ) );
	}

	public function test_filter_disables_cart_fragments_for_empty_cart_on_browsing_page(): void {
		$this->mock_cart_context( is_empty: true );

		$snippet = new DisableEmptyCartFragments( [] );

		$this->assertNull( $snippet->filter_script_data( [ 'wc_ajax_url' => '/?wc-ajax=%%endpoint%%' ], 'wc-cart-fragments' ) );
	}

	public function test_filter_preserves_cart_fragments_when_cart_has_items(): void {
		$this->mock_cart_context( is_empty: false );

		$snippet = new DisableEmptyCartFragments( [] );
		$params  = [ 'wc_ajax_url' => '/?wc-ajax=%%endpoint%%' ];

		$this->assertSame( $params, $snippet->filter_script_data( $params, 'wc-cart-fragments' ) );
	}

	public function test_filter_preserves_cart_fragments_on_cart_page(): void {
		$this->mock_cart_context( is_empty: true, is_cart: true );

		$snippet = new DisableEmptyCartFragments( [] );
		$params  = [ 'wc_ajax_url' => '/?wc-ajax=%%endpoint%%' ];

		$this->assertSame( $params, $snippet->filter_script_data( $params, 'wc-cart-fragments' ) );
	}

	public function test_filter_preserves_cart_fragments_on_checkout_page(): void {
		$this->mock_cart_context( is_empty: true, is_checkout: true );

		$snippet = new DisableEmptyCartFragments( [] );
		$params  = [ 'wc_ajax_url' => '/?wc-ajax=%%endpoint%%' ];

		$this->assertSame( $params, $snippet->filter_script_data( $params, 'wc-cart-fragments' ) );
	}

	public function test_filter_preserves_cart_fragments_during_ajax(): void {
		$this->mock_cart_context( is_empty: true, doing_ajax: true );

		$snippet = new DisableEmptyCartFragments( [] );
		$params  = [ 'wc_ajax_url' => '/?wc-ajax=%%endpoint%%' ];

		$this->assertSame( $params, $snippet->filter_script_data( $params, 'wc-cart-fragments' ) );
	}

	private function mock_cart_context( bool $is_empty, bool $is_cart = false, bool $is_checkout = false, bool $doing_ajax = false ): void {
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'wp_doing_ajax' )->justReturn( $doing_ajax );
		Functions\when( 'is_cart' )->justReturn( $is_cart );
		Functions\when( 'is_checkout' )->justReturn( $is_checkout );

		$cart = new class( $is_empty ) {
			public function __construct( private bool $is_empty ) {
			}

			public function is_empty(): bool {
				return $this->is_empty;
			}
		};

		Functions\when( 'WC' )->justReturn( (object) [ 'cart' => $cart ] );
	}
}
