<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Filters;
use PressGang\Snippets\Theme\HideSinglePageTemplate;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\HideSinglePageTemplate
 */
class HideSinglePageTemplateTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filter(): void {
		Filters\expectAdded( 'theme_page_templates' )->once();

		new HideSinglePageTemplate( [] );
	}

	/**
	 * @return void
	 */
	public function test_hide_single_page_template_removes_entry(): void {
		$snippet = new HideSinglePageTemplate( [] );
		$result  = $snippet->hide_single_page_template( [
			'page-templates/single-page.php' => 'Single Page',
			'page-templates/default.php'     => 'Default',
		] );

		$this->assertArrayNotHasKey( 'page-templates/single-page.php', $result );
		$this->assertArrayHasKey( 'page-templates/default.php', $result );
	}
}
