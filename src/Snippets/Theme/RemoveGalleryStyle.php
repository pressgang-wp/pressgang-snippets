<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Suppresses the WordPress gallery shortcode's default inline CSS so the
 * theme can style galleries itself.
 *
 * Not needed when the theme declares `add_theme_support('html5', ['gallery'])`
 * — WordPress already skips the inline styles for html5 galleries. Use this
 * for themes without html5 gallery support.
 */
class RemoveGalleryStyle implements SnippetInterface {

	/**
	 * @param array $args
	 */
	public function __construct( array $args ) {
		\add_filter( 'use_default_gallery_style', '__return_false' );
	}
}
