<?php

namespace PressGang\Snippets\WooCommerce\Core;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes WooCommerce's default content wrappers.
 */
class DisableContentWrappers implements SnippetInterface {

	/**
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		\remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	}
}
