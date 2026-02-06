<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Google;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Google\Analytics;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestableAnalytics extends Analytics {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}
}

/**
 * @covers \PressGang\Snippets\Google\Analytics
 */
class AnalyticsTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new Analytics( [] );
	}

	/**
	 * @return void
	 */
	public function test_script_renders_when_tracking_id_set_and_user_not_logged_in(): void {
		$snippet = new TestableAnalytics( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'google-analytics-track-logged-in' )
			->andReturn( false );

		Functions\expect( 'is_user_logged_in' )
			->once()
			->andReturn( false );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'google-analytics-id' )
			->andReturn( 'G-XXXXXXXXXX' );

		$snippet->script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/google/analytics.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( 'G-XXXXXXXXXX', $snippet->rendered[0]['context']['google_analytics_id'] );
	}

	/**
	 * @return void
	 */
	public function test_script_skips_rendering_when_tracking_id_empty(): void {
		$snippet = new TestableAnalytics( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-analytics-track-logged-in' )
			->andReturn( false );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( false );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-analytics-id' )
			->andReturn( '' );

		$snippet->script();

		$this->assertCount( 0, $snippet->rendered );
	}

	/**
	 * @return void
	 */
	public function test_script_skips_rendering_for_logged_in_user_when_toggle_off(): void {
		$snippet = new TestableAnalytics( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-analytics-track-logged-in' )
			->andReturn( false );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( true );

		$snippet->script();

		$this->assertCount( 0, $snippet->rendered );
	}

	/**
	 * @return void
	 */
	public function test_script_renders_for_logged_in_user_when_toggle_on(): void {
		$snippet = new TestableAnalytics( [] );

		Functions\expect( 'get_theme_mod' )
			->andReturnUsing( function ( string $key ) {
				return match ( $key ) {
					'google-analytics-track-logged-in' => true,
					'google-analytics-id'              => 'G-YYYYYYYYYY',
					default                            => null,
				};
			} );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( true );

		$snippet->script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'G-YYYYYYYYYY', $snippet->rendered[0]['context']['google_analytics_id'] );
	}
}
