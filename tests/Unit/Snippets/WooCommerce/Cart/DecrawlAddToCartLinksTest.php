<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce\Cart;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use PressGang\Snippets\WooCommerce\Cart\DecrawlAddToCartLinks;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\WooCommerce\Cart\DecrawlAddToCartLinks
 */
class DecrawlAddToCartLinksTest extends TestCase {

	public function test_constructor_registers_hooks(): void {
		Filters\expectAdded( 'woocommerce_product_add_to_cart_url' )
			->once()
			->with( Mockery::type( 'array' ), 20, 2 );
		Actions\expectAdded( 'wp_loaded' )
			->once()
			->with( Mockery::type( 'array' ), 1 );

		new DecrawlAddToCartLinks( [] );
	}

	public function test_filter_replaces_ajax_add_to_cart_url_with_product_permalink(): void {
		$this->mock_frontend_context();

		$product = Mockery::mock( \WC_Product::class );
		$product->shouldReceive( 'supports' )->with( 'ajax_add_to_cart' )->once()->andReturn( true );
		$product->shouldReceive( 'get_permalink' )->once()->andReturn( 'https://example.com/product/tool/' );

		$snippet = new DecrawlAddToCartLinks( [] );

		$this->assertSame(
			'https://example.com/product/tool/',
			$snippet->filter_product_add_to_cart_url( 'https://example.com/shop/?add-to-cart=123', $product )
		);
	}

	public function test_filter_preserves_non_ajax_add_to_cart_url(): void {
		$this->mock_frontend_context();

		$product = Mockery::mock( \WC_Product::class );
		$product->shouldReceive( 'supports' )->with( 'ajax_add_to_cart' )->once()->andReturn( false );
		$product->shouldReceive( 'get_permalink' )->never();

		$snippet = new DecrawlAddToCartLinks( [] );

		$this->assertSame(
			'https://example.com/product/options/',
			$snippet->filter_product_add_to_cart_url( 'https://example.com/product/options/', $product )
		);
	}

	public function test_filter_preserves_url_on_cart_surface(): void {
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'wp_doing_ajax' )->justReturn( false );
		Functions\when( 'is_cart' )->justReturn( true );
		Functions\when( 'is_checkout' )->justReturn( false );

		$product = Mockery::mock( \WC_Product::class );
		$product->shouldReceive( 'supports' )->never();

		$snippet = new DecrawlAddToCartLinks( [] );

		$this->assertSame(
			'https://example.com/cart/?add-to-cart=123',
			$snippet->filter_product_add_to_cart_url( 'https://example.com/cart/?add-to-cart=123', $product )
		);
	}

	public function test_get_redirect_url_returns_product_permalink_for_get_add_to_cart_request(): void {
		$this->mock_frontend_context();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_GET['add-to-cart']       = '40330';

		$product = Mockery::mock( \WC_Product::class );
		$product->shouldReceive( 'get_permalink' )->once()->andReturn( 'https://example.com/product/tool/' );

		Functions\when( 'wp_unslash' )->alias( static fn( mixed $value ): mixed => $value );
		Functions\expect( 'wc_get_product' )->once()->with( 40330 )->andReturn( $product );

		$snippet = new DecrawlAddToCartLinks( [] );

		$this->assertSame( 'https://example.com/product/tool/', $snippet->get_get_add_to_cart_redirect_url() );
	}

	public function test_get_redirect_url_returns_product_permalink_for_head_add_to_cart_request(): void {
		$this->mock_frontend_context();
		$_SERVER['REQUEST_METHOD'] = 'HEAD';
		$_GET['add-to-cart']       = '40330';

		$product = Mockery::mock( \WC_Product::class );
		$product->shouldReceive( 'get_permalink' )->once()->andReturn( 'https://example.com/product/tool/' );

		Functions\when( 'wp_unslash' )->alias( static fn( mixed $value ): mixed => $value );
		Functions\expect( 'wc_get_product' )->once()->with( 40330 )->andReturn( $product );

		$snippet = new DecrawlAddToCartLinks( [] );

		$this->assertSame( 'https://example.com/product/tool/', $snippet->get_get_add_to_cart_redirect_url() );
	}

	public function test_get_redirect_url_ignores_post_add_to_cart_request(): void {
		$this->mock_frontend_context();
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_GET['add-to-cart']       = '40330';

		$snippet = new DecrawlAddToCartLinks( [] );

		$this->assertNull( $snippet->get_get_add_to_cart_redirect_url() );
	}

	public function test_get_redirect_url_ignores_ajax_request(): void {
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'wp_doing_ajax' )->justReturn( true );
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_GET['add-to-cart']       = '40330';

		$snippet = new DecrawlAddToCartLinks( [] );

		$this->assertNull( $snippet->get_get_add_to_cart_redirect_url() );
	}

	protected function tearDown(): void {
		unset( $_GET['add-to-cart'], $_SERVER['REQUEST_METHOD'] );

		parent::tearDown();
	}

	private function mock_frontend_context(): void {
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'wp_doing_ajax' )->justReturn( false );
		Functions\when( 'is_cart' )->justReturn( false );
		Functions\when( 'is_checkout' )->justReturn( false );
	}
}
