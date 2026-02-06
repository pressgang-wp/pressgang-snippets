<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Integration\Hotjar;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestableHotjar extends Hotjar {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}
}

/**
 * @covers \PressGang\Snippets\Integration\Hotjar
 */
class HotjarTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new Hotjar( [] );
	}

	/**
	 * @return void
	 */
	public function test_script_renders_when_id_set(): void {
		$snippet = new TestableHotjar( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'hotjar-id' )
			->andReturn( '12345' );

		$snippet->script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/integration/hotjar.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( '12345', $snippet->rendered[0]['context']['hotjar_id'] );
	}

	/**
	 * @return void
	 */
	public function test_script_skips_when_id_empty(): void {
		$snippet = new TestableHotjar( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'hotjar-id' )
			->andReturn( '' );

		$snippet->script();

		$this->assertCount( 0, $snippet->rendered );
	}
}
