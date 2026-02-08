<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Filters;
use PressGang\Snippets\Theme\WysiwygStyles;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\WysiwygStyles
 */
class WysiwygStylesTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filters(): void {
		Filters\expectAdded( 'mce_buttons_2' )->once();
		Filters\expectAdded( 'tiny_mce_before_init' )->once();

		new WysiwygStyles( [ 'styles' => [] ] );
	}

	/**
	 * @return void
	 */
	public function test_show_custom_styles_dropdown_adds_formats(): void {
		$styles = [
			[
				'title'    => 'Lead',
				'selector' => 'p',
				'classes'  => 'lead',
			],
		];

		$snippet = new WysiwygStyles( [ 'styles' => $styles ] );
		$result = $snippet->show_custom_styles_dropdown( [] );

		$this->assertTrue( $result['style_formats_merge'] );
		$this->assertStringContainsString( 'Custom Styles', $result['style_formats'] );
		$this->assertStringContainsString( 'Lead', $result['style_formats'] );
	}
}
