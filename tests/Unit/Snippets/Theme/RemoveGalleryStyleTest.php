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
	public function test_constructor_registers_gallery_style_filter(): void {
		Filters\expectAdded( 'gallery_style' )->once();

		new RemoveGalleryStyle( [] );
	}

	/**
	 * @return void
	 */
	public function test_gallery_style_is_emptied(): void {
		$style = ( new RemoveGalleryStyle( [] ) )->remove_style( '<style>.gallery {}</style>' );

		self::assertSame( '', $style );
	}
}
