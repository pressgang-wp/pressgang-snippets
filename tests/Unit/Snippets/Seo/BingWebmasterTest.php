<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Seo;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Seo\BingWebmaster;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Seo\BingWebmaster
 */
class BingWebmasterTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new BingWebmaster( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_meta_tag_outputs_when_code_set(): void {
		$snippet = new BingWebmaster( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'bing_verification_code' )
			->andReturn( 'bingcode123' );

		Functions\expect( 'esc_attr' )
			->once()
			->with( 'bingcode123' )
			->andReturn( 'bingcode123' );

		$this->expectOutputString( '<meta name="msvalidate.01" content="bingcode123" />' );
		$snippet->add_meta_tag();
	}

	/**
	 * @return void
	 */
	public function test_add_meta_tag_outputs_nothing_when_empty(): void {
		$snippet = new BingWebmaster( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'bing_verification_code' )
			->andReturn( '' );

		$this->expectOutputString( '' );
		$snippet->add_meta_tag();
	}
}
