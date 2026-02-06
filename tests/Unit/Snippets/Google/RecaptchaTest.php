<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Google;

use Brain\Monkey\Actions;
use PressGang\Snippets\Google\Recaptcha;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Google\Recaptcha
 */
class RecaptchaTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_customizer_hook(): void {
		Actions\expectAdded( 'customize_register' )->once();

		new Recaptcha( [] );
	}
}
