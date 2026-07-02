<?php

namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;

/**
 * Disables Mailchimp for WooCommerce's tracking cookie.
 */
class MailchimpForWooCommerceDisableCookie implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'mailchimp_allowed_to_use_cookie', [ $this, 'disable_cookie' ] );
	}

	/**
	 * @return bool
	 */
	public function disable_cookie(): bool {
		return false;
	}
}
