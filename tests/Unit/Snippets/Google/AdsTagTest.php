<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Google;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Google\AdsTag;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestableAdsTag extends AdsTag {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}
}

/**
 * @covers \PressGang\Snippets\Google\AdsTag
 */
class AdsTagTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new AdsTag( [] );
	}

	/**
	 * @return void
	 */
	public function test_script_renders_when_conversion_id_set(): void {
		$snippet = new TestableAdsTag( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-ads-conversion-id' )
			->andReturn( 'AW-123456' );

		$snippet->script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/google/ads-tag.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( 'AW-123456', $snippet->rendered[0]['context']['conversion_id'] );
	}

	/**
	 * @return void
	 */
	public function test_script_skips_when_conversion_id_empty(): void {
		$snippet = new TestableAdsTag( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-ads-conversion-id' )
			->andReturn( '' );

		$snippet->script();

		$this->assertCount( 0, $snippet->rendered );
	}
}
