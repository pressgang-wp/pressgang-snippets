<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Integration\Pinterest;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Integration\Pinterest
 */
class PinterestTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new Pinterest( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_meta_tag_outputs_when_id_set(): void {
		$snippet = new Pinterest( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'pinterest_verification_id' )
			->andReturn( 'abc123' );

		// sanitize_text_field is pre-stubbed by YoastTestCase (returns first arg).

		Functions\expect( 'esc_attr' )
			->once()
			->with( 'abc123' )
			->andReturn( 'abc123' );

		$this->expectOutputString( '<meta name="p:domain_verify" content="abc123" />' );

		$snippet->add_meta_tag();
	}

	/**
	 * @return void
	 */
	public function test_add_meta_tag_outputs_nothing_when_empty(): void {
		$snippet = new Pinterest( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'pinterest_verification_id' )
			->andReturn( '' );

		// sanitize_text_field is pre-stubbed by YoastTestCase (returns first arg).

		$this->expectOutputString( '' );

		$snippet->add_meta_tag();
	}
}
