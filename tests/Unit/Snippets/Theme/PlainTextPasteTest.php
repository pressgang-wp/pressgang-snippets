<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Filters;
use PressGang\Snippets\Theme\PlainTextPaste;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\PlainTextPaste
 */
class PlainTextPasteTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filters(): void {
		Filters\expectAdded( 'tiny_mce_before_init' )->once();
		Filters\expectAdded( 'teeny_mce_before_init' )->once();

		new PlainTextPaste( [] );
	}

	/**
	 * @return void
	 */
	public function test_plain_text_paste_sets_flags(): void {
		$snippet = new PlainTextPaste( [] );
		$result  = $snippet->plain_text_paste( [ 'toolbar1' => 'bold italic' ] );

		$this->assertTrue( $result['paste_text_sticky'] );
		$this->assertTrue( $result['paste_text_sticky_default'] );
		$this->assertSame( 'bold italic', $result['toolbar1'] );
	}
}
