<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Seo;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Seo\OpenGraph;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls and stubs the page
 * context. Named class avoids BrainMonkey's anonymous-class name parsing issue.
 */
class TestableOpenGraph extends OpenGraph {

	public bool $yoast = false;

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function yoast_outputs_open_graph(): bool {
		return $this->yoast;
	}

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}

	protected function get_title(): string {
		return 'Example Title';
	}

	protected function get_description(): string {
		return 'Example description';
	}

	protected function get_type(): string {
		return 'website';
	}

	protected function get_url(): string {
		return 'https://example.test/';
	}

	protected function get_image_url(): string {
		return 'https://example.test/logo.png';
	}
}

/**
 * @covers \PressGang\Snippets\Seo\OpenGraph
 */
class OpenGraphTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hook(): void {
		Actions\expectAdded( 'wp_head' )->once();

		new OpenGraph( [] );
	}

	/**
	 * @return void
	 */
	public function test_renders_tags_without_yoast(): void {
		$snippet = new TestableOpenGraph( [] );

		Functions\when( 'get_bloginfo' )->justReturn( 'Example Site' );

		$snippet->add_meta_tags();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/seo/open-graph.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( 'Example Title', $snippet->rendered[0]['context']['title'] );
	}

	/**
	 * @return void
	 */
	public function test_skips_tags_when_yoast_outputs_open_graph(): void {
		$snippet        = new TestableOpenGraph( [] );
		$snippet->yoast = true;

		$snippet->add_meta_tags();

		$this->assertSame( [], $snippet->rendered );
	}

	/**
	 * @return void
	 */
	public function test_filter_can_force_tags_alongside_yoast(): void {
		$snippet        = new TestableOpenGraph( [] );
		$snippet->yoast = true;

		Filters\expectApplied( 'pressgang_og_enabled' )->once()->with( false )->andReturn( true );
		Functions\when( 'get_bloginfo' )->justReturn( 'Example Site' );

		$snippet->add_meta_tags();

		$this->assertCount( 1, $snippet->rendered );
	}
}
