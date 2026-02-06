<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Facebook;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Facebook\Verify;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Facebook\Verify
 */
class VerifyTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new Verify( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_meta_tag_outputs_when_code_set(): void {
		$snippet = new Verify( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'facebook_domain_verification' )
			->andReturn( 'xyz789' );

		Functions\expect( 'esc_attr' )
			->once()
			->with( 'xyz789' )
			->andReturn( 'xyz789' );

		$this->expectOutputString( '<meta name="facebook-domain-verification" content="xyz789" />' );
		$snippet->add_meta_tag();
	}

	/**
	 * @return void
	 */
	public function test_add_meta_tag_outputs_nothing_when_empty(): void {
		$snippet = new Verify( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'facebook_domain_verification' )
			->andReturn( '' );

		$this->expectOutputString( '' );
		$snippet->add_meta_tag();
	}
}
