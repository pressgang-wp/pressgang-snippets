<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes a taxonomy's admin UI (submenu page and edit-screen meta box)
 * without unregistering the taxonomy.
 *
 * Args:
 * - 'taxonomy'  (optional): taxonomy slug, default 'post_tag'.
 * - 'post_type' (optional): post type whose menu/edit screen is cleaned, default 'post'.
 */
class RemoveTaxonomyUi implements SnippetInterface {

	/**
	 * @var string
	 */
	protected string $taxonomy;

	/**
	 * @var string
	 */
	protected string $post_type;

	/**
	 * @param array $args {
	 *     @type string $taxonomy  Taxonomy slug (optional, default 'post_tag').
	 *     @type string $post_type Post type slug (optional, default 'post').
	 * }
	 */
	public function __construct( array $args ) {
		$this->taxonomy  = $args['taxonomy'] ?? 'post_tag';
		$this->post_type = $args['post_type'] ?? 'post';

		\add_action( 'admin_menu', [ $this, 'remove_taxonomy_ui' ] );
	}

	/**
	 * Removes the taxonomy submenu page and meta box.
	 *
	 * Both hierarchical ("{taxonomy}div") and non-hierarchical
	 * ("tagsdiv-{taxonomy}") meta box IDs are removed from both contexts;
	 * the absent combinations are no-ops.
	 *
	 * @return void
	 */
	public function remove_taxonomy_ui(): void {

		$parent  = $this->post_type === 'post' ? 'edit.php' : "edit.php?post_type={$this->post_type}";
		$submenu = "edit-tags.php?taxonomy={$this->taxonomy}";

		if ( $this->post_type !== 'post' ) {
			$submenu .= "&post_type={$this->post_type}";
		}

		\remove_submenu_page( $parent, $submenu );

		foreach ( [ 'normal', 'side' ] as $context ) {
			\remove_meta_box( "tagsdiv-{$this->taxonomy}", $this->post_type, $context );
			\remove_meta_box( "{$this->taxonomy}div", $this->post_type, $context );
		}
	}
}
