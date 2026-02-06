<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Integration\Trustpilot;
use PressGang\Tests\Snippets\Unit\TestCase;

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
				'//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js',
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
}
