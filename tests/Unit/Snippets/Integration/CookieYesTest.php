<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Integration\CookieYes;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Integration\CookieYes
 */
class CookieYesTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_enqueue_scripts' )->once();

		new CookieYes( [] );
	}

	/**
	 * @return void
	 */
	public function test_enqueue_script_when_id_set(): void {
		$snippet = new CookieYes( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'cookieyes-id' )
			->andReturn( 'abc-123' );

		// sanitize_text_field is pre-stubbed by YoastTestCase (returns first arg).

		Functions\expect( 'esc_url' )
			->once()
			->andReturnFirstArg();

		Functions\expect( 'wp_register_script' )
			->once()
			->with( 'cookieyes', 'https://cdn-cookieyes.com/client_data/abc-123/script.js', [], null );

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with( 'cookieyes' );

		$snippet->cookieyes_header_script();
	}

	/**
	 * @return void
	 */
	public function test_skips_enqueue_when_id_empty(): void {
		$snippet = new CookieYes( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'cookieyes-id' )
			->andReturn( '' );

		// sanitize_text_field is pre-stubbed by YoastTestCase (returns first arg).

		Functions\expect( 'wp_register_script' )->never();
		Functions\expect( 'wp_enqueue_script' )->never();

		$snippet->cookieyes_header_script();
	}
}
