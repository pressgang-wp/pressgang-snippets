<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Filters;
use PressGang\Snippets\Theme\RemoveGalleryStyle;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\RemoveGalleryStyle
 */
class RemoveGalleryStyleTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_disables_default_gallery_style(): void {
		Filters\expectAdded( 'use_default_gallery_style' )
			->once()
			->with( '__return_false' );

		new RemoveGalleryStyle( [] );
	}
}
