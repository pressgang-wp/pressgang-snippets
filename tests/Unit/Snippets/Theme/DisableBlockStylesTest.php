<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\DisableBlockStyles;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\DisableBlockStyles
 */
class DisableBlockStylesTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_late_enqueue_hook(): void {
		Actions\expectAdded( 'wp_enqueue_scripts' )
			->once()
			->with( \Mockery::type( 'array' ), 999 );

		new DisableBlockStyles( [] );
	}

	/**
	 * @return void
	 */
	public function test_dequeues_and_deregisters_default_handles(): void {
		Functions\expect( 'wp_dequeue_style' )->times( 6 );
		Functions\expect( 'wp_deregister_style' )->times( 6 );

		( new DisableBlockStyles( [] ) )->dequeue_block_styles();
	}

	/**
	 * @return void
	 */
	public function test_custom_handles_replace_defaults(): void {
		Functions\expect( 'wp_dequeue_style' )->once()->with( 'my-style' );
		Functions\expect( 'wp_deregister_style' )->once()->with( 'my-style' );

		( new DisableBlockStyles( [ 'handles' => [ 'my-style' ] ] ) )->dequeue_block_styles();
	}
}
