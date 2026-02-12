<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Filters;
use PressGang\Snippets\Integration\DisableCf7Assets;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Integration\DisableCf7Assets
 */
class DisableCf7AssetsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filters(): void {
		Filters\expectAdded( 'wpcf7_load_js' )
			->once()
			->with( '__return_false' );
		Filters\expectAdded( 'wpcf7_load_css' )
			->once()
			->with( '__return_false' );

		new DisableCf7Assets( [] );
	}
}
