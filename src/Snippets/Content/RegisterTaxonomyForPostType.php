<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Associates an already-registered taxonomy with a post type using WordPress's
 * register_taxonomy_for_object_type() function.
 *
 * Useful when a taxonomy (e.g. 'category', 'post_tag', or a custom taxonomy)
 * exists but is not yet connected to a particular post type. The association
 * is deferred to the 'init' hook so that both the taxonomy and the post type
 * are guaranteed to be registered.
 *
 * Both 'taxonomy' and 'post_type' args are required.
 */
class RegisterTaxonomyForPostType implements SnippetInterface {

	/**
	 * Hooks into 'init' to associate the taxonomy with the post type.
	 *
	 * @param array $args {
	 *     @type string $taxonomy  The registered taxonomy slug (e.g. 'category', 'post_tag').
	 *     @type string $post_type The registered post type slug (e.g. 'page', 'product').
	 * }
	 */
	public function __construct( array $args ) {
		\add_action( 'init', function () use ( $args ) {
			\register_taxonomy_for_object_type( $args['taxonomy'], $args['post_type'] );
		} );
	}
}