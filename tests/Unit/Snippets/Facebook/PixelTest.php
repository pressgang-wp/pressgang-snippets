<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Facebook;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Facebook\Pixel;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestablePixel extends Pixel {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}
}

/**
 * @covers \PressGang\Snippets\Facebook\Pixel
 */
class PixelTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new Pixel( [] );
	}

	/**
	 * @return void
	 */
	public function test_script_renders_for_logged_out_user(): void {
		$snippet = new TestablePixel( [] );

		Functions\expect( 'get_theme_mod' )
			->andReturnUsing( function ( string $key ) {
				return match ( $key ) {
					'facebook-track-logged-in' => false,
					'facebook-pixel-id'        => '123456789',
					default                    => null,
				};
			} );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( false );

		$snippet->script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/facebook/pixel.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( '123456789', $snippet->rendered[0]['context']['facebook_pixel_id'] );
	}

	/**
	 * @return void
	 */
	public function test_script_skips_for_logged_in_user_when_toggle_off(): void {
		$snippet = new TestablePixel( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'facebook-track-logged-in' )
			->andReturn( false );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( true );

		$snippet->script();

		$this->assertCount( 0, $snippet->rendered );
	}

	/**
	 * @return void
	 */
	public function test_script_renders_for_logged_in_user_when_toggle_on(): void {
		$snippet = new TestablePixel( [] );

		Functions\expect( 'get_theme_mod' )
			->andReturnUsing( function ( string $key ) {
				return match ( $key ) {
					'facebook-track-logged-in' => true,
					'facebook-pixel-id'        => '123456789',
					default                    => null,
				};
			} );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( true );

		$snippet->script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( '123456789', $snippet->rendered[0]['context']['facebook_pixel_id'] );
	}

	/**
	 * @return void
	 */
	public function test_script_skips_when_pixel_id_empty(): void {
		$snippet = new TestablePixel( [] );

		Functions\expect( 'get_theme_mod' )
			->andReturnUsing( function ( string $key ) {
				return match ( $key ) {
					'facebook-track-logged-in' => false,
					'facebook-pixel-id'        => '',
					default                    => null,
				};
			} );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( false );

		$snippet->script();

		$this->assertCount( 0, $snippet->rendered );
	}
}
