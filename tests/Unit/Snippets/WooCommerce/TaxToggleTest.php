<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\WooCommerce;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\WooCommerce\TaxToggle;
use PressGang\Tests\Snippets\Unit\TestCase;
use Twig\Environment;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render().
 */
class TestableTaxToggle extends TaxToggle {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	public function render_tax_toggle(): void {
		$this->rendered[] = [
			'template' => 'snippets/woocommerce/tax-toggle.twig',
			'context'  => [
				'display_tax' => ( $this->get_tax_display() === 'incl' ),
				'cookie_name' => self::COOKIE_NAME,
			],
		];
	}
}

/**
 * @covers \PressGang\Snippets\WooCommerce\TaxToggle
 */
class TaxToggleTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filters(): void {
		Filters\expectAdded( 'timber/twig' )->once();
		Filters\expectAdded( 'option_woocommerce_tax_display_shop' )->once();
		Filters\expectAdded( 'option_woocommerce_tax_display_cart' )->once();

		new TaxToggle( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_to_twig_registers_function(): void {
		$snippet = new TaxToggle( [] );
		$twig = $this->createMock( Environment::class );

		$twig->expects( $this->once() )->method( 'addFunction' );

		$snippet->add_to_twig( $twig );
	}

	/**
	 * @return void
	 */
	public function test_render_tax_toggle_adds_context(): void {
		$snippet = new TestableTaxToggle( [] );

		$snippet->render_tax_toggle();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/woocommerce/tax-toggle.twig', $snippet->rendered[0]['template'] );
		$this->assertArrayHasKey( 'display_tax', $snippet->rendered[0]['context'] );
	}
}
