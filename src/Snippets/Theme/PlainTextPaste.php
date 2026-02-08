<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Forces TinyMCE and TeenyMCE editors to paste as plain text by default,
 * preventing inline styles and formatting from being pasted into the editor.
 *
 * Enable this snippet to keep content clean when editors paste from Word or
 * other rich text sources. No configuration required.
 */
class PlainTextPaste implements SnippetInterface {

	/**
	 * Registers TinyMCE filters to enable plain-text paste defaults.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'tiny_mce_before_init', [ $this, 'plain_text_paste' ] );
		\add_filter( 'teeny_mce_before_init', [ $this, 'plain_text_paste' ] );
	}

	/**
	 * Sets TinyMCE paste settings to stick to plain text.
	 *
	 * @param array<string, mixed> $mce_init Existing TinyMCE settings.
	 *
	 * @return array<string, mixed> Updated TinyMCE settings.
	 */
	public function plain_text_paste( array $mce_init ): array {
		$mce_init['paste_text_sticky']         = true;
		$mce_init['paste_text_sticky_default'] = true;

		return $mce_init;
	}
}
