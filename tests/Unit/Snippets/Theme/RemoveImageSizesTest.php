<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\RemoveImageSizes;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\RemoveImageSizes
 */
class RemoveImageSizesTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hook(): void {
		Actions\expectAdded( 'init' )->once();

		new RemoveImageSizes( [] );
	}

	/**
	 * @return void
	 */
	public function test_remove_sizes_removes_all(): void {
		$snippet = new RemoveImageSizes( [] );

		Functions\expect( 'get_intermediate_image_sizes' )
			->once()
			->andReturn( [ 'thumbnail', 'medium' ] );

		Functions\expect( 'remove_image_size' )
			->once()
			->with( 'thumbnail' );
		Functions\expect( 'remove_image_size' )
			->once()
			->with( 'medium' );

		$snippet->remove_sizes();
	}
}
