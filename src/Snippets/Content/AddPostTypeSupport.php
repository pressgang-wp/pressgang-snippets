<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds feature support to an existing post type using WordPress's
 * add_post_type_support() function.
 *
 * Useful when a post type registered elsewhere (e.g. by a plugin) needs an
 * additional feature such as 'excerpt', 'thumbnail', or 'custom-fields'
 * without modifying the original registration call.
 *
 * Both 'post_type' and 'feature' args are required.
 */
class AddPostTypeSupport implements SnippetInterface {

	/**
	 * Calls add_post_type_support() immediately to register the given feature
	 * for the specified post type.
	 *
	 * @param array $args {
	 *     @type string $post_type The registered post type slug (e.g. 'page', 'product').
	 *     @type string $feature   The feature to add (e.g. 'excerpt', 'thumbnail', 'custom-fields').
	 * }
	 */
	public function __construct( array $args ) {
		\add_post_type_support( $args['post_type'], $args['feature'] );
	}
}
