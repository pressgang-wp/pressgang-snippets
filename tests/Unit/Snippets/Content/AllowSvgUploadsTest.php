<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Filters;
use PressGang\Snippets\Content\AllowSvgUploads;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\AllowSvgUploads
 */
class AllowSvgUploadsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_mime_filter(): void {
		Filters\expectAdded( 'upload_mimes' )->once();

		new AllowSvgUploads( [] );
	}

	/**
	 * @return void
	 */
	public function test_svg_mime_is_added(): void {
		$mimes = ( new AllowSvgUploads( [] ) )->allow_svg( [ 'jpg|jpeg' => 'image/jpeg' ] );

		self::assertSame( 'image/svg+xml', $mimes['svg'] );
		self::assertSame( 'image/jpeg', $mimes['jpg|jpeg'] );
	}
}
