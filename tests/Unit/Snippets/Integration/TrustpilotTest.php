<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Integration\Trustpilot;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestableTrustpilot extends Trustpilot {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}
}

/**
 * @covers \PressGang\Snippets\Integration\Trustpilot
 */
class TrustpilotTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_enqueue_scripts' )->once();
		Filters\expectAdded( 'timber/twig' )->once();

		new Trustpilot( [] );
	}

	/**
	 * @return void
	 */
	public function test_register_scripts_enqueues_trustpilot(): void {
		$snippet = new Trustpilot( [] );

		Functions\expect( 'wp_register_script' )
			->once()
			->with(
				'trustpilot-pressgang-snippet',
				'https://widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js',
				[],
				null,
				true
			);

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with( 'trustpilot-pressgang-snippet' );

		$snippet->register_scripts();
	}

	/**
	 * @return void
	 */
	public function test_add_to_twig_adds_function(): void {
		$snippet = new Trustpilot( [] );

		$twig = \Mockery::mock( \Twig\Environment::class );
		$twig->shouldReceive( 'addFunction' )
			->once()
			->with( \Mockery::on( function ( $fn ) {
				return $fn instanceof \Twig\TwigFunction
					&& $fn->getName() === 'trustpilot_mini';
			} ) );

		$result = $snippet->add_to_twig( $twig );

		$this->assertSame( $twig, $result );
	}

	/**
	 * @return void
	 */
	public function test_render_mini_widget_prefers_reviews_link_setting(): void {
		$snippet = new TestableTrustpilot( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'trustpilot_template_id' )
			->andReturn( 'tpl-id' );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'trustpilot_business_id' )
			->andReturn( 'biz-id' );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'trustpilot_reviews_link' )
			->andReturn( 'https://uk.trustpilot.com/review/example.com' );

		$snippet->render_mini_widget();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/integration/trustpilot-mini.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( 'https://uk.trustpilot.com/review/example.com', $snippet->rendered[0]['context']['trustpilot_reviews_url'] );
	}

	/**
	 * @return void
	 */
	public function test_render_mini_widget_falls_back_to_legacy_setting_key(): void {
		$snippet = new TestableTrustpilot( [] );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'trustpilot_template_id' )
			->andReturn( 'tpl-id' );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'trustpilot_business_id' )
			->andReturn( 'biz-id' );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'trustpilot_reviews_link' )
			->andReturn( '' );

		Functions\expect( 'get_theme_mod' )
			->once()
			->with( 'trustpilot_reviews_url' )
			->andReturn( 'https://uk.trustpilot.com/review/legacy.com' );

		$snippet->render_mini_widget();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'https://uk.trustpilot.com/review/legacy.com', $snippet->rendered[0]['context']['trustpilot_reviews_url'] );
	}
}
