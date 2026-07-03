<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes the WordPress gallery inline CSS so the theme can style galleries
 * itself.
 */
class RemoveGalleryStyle implements SnippetInterface {

	/**
	 * @param array $args
	 */
	public function __construct( array $args ) {
		\add_filter( 'gallery_style', [ $this, 'remove_style' ] );
	}

	/**
	 * Replaces the gallery style block with an empty string.
	 *
	 * @param string $existing_code Gallery style block.
	 *
	 * @return string
	 */
	public function remove_style( $existing_code ): string {
		return '';
	}
}
