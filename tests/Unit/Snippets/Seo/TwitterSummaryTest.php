<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Seo;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Seo\TwitterSummary;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestableTwitterSummary extends TwitterSummary {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}

	protected function get_title(): string {
		return 'Example Title';
	}

	protected function get_description(): string {
		return 'Example description';
	}

	protected function get_image_url(): string {
		return 'https://example.test/logo.png';
	}
}

/**
 * @covers \PressGang\Snippets\Seo\TwitterSummary
 */
class TwitterSummaryTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();

		new TwitterSummary( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_meta_tags_renders_template(): void {
		$snippet = new TestableTwitterSummary( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'twitter-site-handle' )
			->andReturn( '' );
		Functions\expect( 'get_theme_mod' )
			->with( 'twitter-creator-handle' )
			->andReturn( '' );

		// Pass through the filtered value unchanged (standard WordPress behaviour).
		Functions\when( 'apply_filters' )->returnArg( 2 );

		$snippet->add_meta_tags();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/seo/twitter-summary.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( 'Example Title', $snippet->rendered[0]['context']['title'] );
		$this->assertSame( 'Example description', $snippet->rendered[0]['context']['description'] );
	}
}
