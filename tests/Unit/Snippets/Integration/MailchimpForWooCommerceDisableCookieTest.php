<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Integration;

use Brain\Monkey\Filters;
use PressGang\Snippets\Integration\MailchimpForWooCommerceDisableCookie;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Integration\MailchimpForWooCommerceDisableCookie
 */
class MailchimpForWooCommerceDisableCookieTest extends TestCase {

	public function test_constructor_registers_filter(): void {
		Filters\expectAdded( 'mailchimp_allowed_to_use_cookie' )->once();

		new MailchimpForWooCommerceDisableCookie( [] );
	}

	public function test_disable_cookie_returns_false(): void {
		$snippet = new MailchimpForWooCommerceDisableCookie( [] );

		$this->assertFalse( $snippet->disable_cookie() );
	}
}
