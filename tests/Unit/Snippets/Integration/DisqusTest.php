<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Integration\Disqus;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Integration\Disqus
 */
class DisqusTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Filters\expectAdded( 'comments_template' )->once();

		new Disqus( [] );
	}

	/**
	 * @return void
	 */
	public function test_render_returns_original_template_when_no_shortname(): void {
		$snippet = new Disqus( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'disqus-shortname' )
			->andReturn( '' );

		$this->assertSame( 'original.php', $snippet->render( 'original.php' ) );
	}

	/**
	 * @return void
	 */
	public function test_render_returns_disqus_template_when_shortname_set(): void {
		$snippet = new Disqus( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'disqus-shortname' )
			->andReturn( 'example-shortname' );

		$result = $snippet->render( 'original.php' );

		$this->assertStringContainsString( 'views/snippets/integration/disqus.php', $result );
	}
}
