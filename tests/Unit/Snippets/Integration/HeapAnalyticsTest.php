<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Integration\HeapAnalytics;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestableHeapAnalytics extends HeapAnalytics {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}
}

/**
 * @covers \PressGang\Snippets\Integration\HeapAnalytics
 */
class HeapAnalyticsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new HeapAnalytics( [] );
	}

	/**
	 * @return void
	 */
	public function test_script_renders_when_id_set_and_user_allowed(): void {
		$snippet = new TestableHeapAnalytics( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'heap-analytics-track-logged-in' )
			->andReturn( 0 );
		Functions\expect( 'is_user_logged_in' )
			->once()
			->andReturn( false );
		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'heap-analytics-id' )
			->andReturn( '123456789' );

		$snippet->script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/integration/heap-analytics.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( '123456789', $snippet->rendered[0]['context']['heap_analytics_id'] );
	}

	/**
	 * @return void
	 */
	public function test_script_skips_when_id_empty(): void {
		$snippet = new TestableHeapAnalytics( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'heap-analytics-track-logged-in' )
			->andReturn( 1 );
		Functions\expect( 'is_user_logged_in' )
			->once()
			->andReturn( true );
		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'heap-analytics-id' )
			->andReturn( '' );

		$snippet->script();

		$this->assertCount( 0, $snippet->rendered );
	}
}
