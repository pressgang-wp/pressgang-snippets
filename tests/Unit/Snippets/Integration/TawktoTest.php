<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Integration\Tawkto;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestableTawkto extends Tawkto {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}
}

/**
 * @covers \PressGang\Snippets\Integration\Tawkto
 */
class TawktoTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_footer' )->once();

		new Tawkto( [] );
	}

	/**
	 * @return void
	 */
	public function test_render_outputs_when_id_set(): void {
		$snippet = new TestableTawkto( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'tawkto-id' )
			->andReturn( 'abc123/def456' );

		$snippet->render();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/integration/tawkto.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( 'abc123/def456', $snippet->rendered[0]['context']['tawkto_id'] );
	}

	/**
	 * @return void
	 */
	public function test_render_skips_when_id_empty(): void {
		$snippet = new TestableTawkto( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'tawkto-id' )
			->andReturn( '' );

		$snippet->render();

		$this->assertCount( 0, $snippet->rendered );
	}
}
