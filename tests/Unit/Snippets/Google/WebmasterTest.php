<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Google;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Google\Webmaster;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Google\Webmaster
 */
class WebmasterTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new Webmaster( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_meta_tag_outputs_when_code_set(): void {
		$snippet = new Webmaster( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-verification-code' )
			->andReturn( 'abc123xyz' );

		Functions\expect( 'esc_attr' )
			->once()
			->with( 'abc123xyz' )
			->andReturn( 'abc123xyz' );

		$this->expectOutputString( '<meta name="google-site-verification" content="abc123xyz" />' );
		$snippet->add_meta_tag();
	}

	/**
	 * @return void
	 */
	public function test_add_meta_tag_outputs_nothing_when_empty(): void {
		$snippet = new Webmaster( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-verification-code' )
			->andReturn( '' );

		$this->expectOutputString( '' );
		$snippet->add_meta_tag();
	}
}
