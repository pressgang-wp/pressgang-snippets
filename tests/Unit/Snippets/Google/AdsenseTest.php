<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Google;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Google\Adsense;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestableAdsense extends Adsense {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}
}

/**
 * @covers \PressGang\Snippets\Google\Adsense
 */
class AdsenseTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new Adsense( [] );
	}

	/**
	 * @return void
	 */
	public function test_script_renders_when_id_set(): void {
		$snippet = new TestableAdsense( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'google-adsense-id' )
			->andReturn( 'ca-pub-1234567890' );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'show-page-level-ads' )
			->andReturn( 0 );

		$snippet->script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/google/adsense.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( 'ca-pub-1234567890', $snippet->rendered[0]['context']['google_adsense_id'] );
		$this->assertFalse( $snippet->rendered[0]['context']['show_page_level_ads'] );
	}

	/**
	 * @return void
	 */
	public function test_script_skips_when_id_empty(): void {
		$snippet = new TestableAdsense( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'google-adsense-id' )
			->andReturn( '' );

		Functions\expect( 'get_theme_mod' )
			->with( 'show-page-level-ads' )
			->never();

		$snippet->script();

		$this->assertCount( 0, $snippet->rendered );
	}
}
