<?php

namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;

/**
 * Prevents Relevanssi from hijacking WP_Query on a specified post type archive.
 *
 * Relevanssi hooks into posts_request and posts_pre_query to intercept all WP_Query instances,
 * not just the main query. This can break custom queries on archive pages that rely on their
 * own ordering, filtering, or pagination logic.
 *
 * Usage in config/snippets.php:
 *
 *     'Integration\DisableRelevanssiOnArchive' => ['post_type' => 'research_project'],
 *
 * @package PressGang\Snippets\Integration
 */
class DisableRelevanssiOnArchive implements SnippetInterface {

	private string $post_type;

	/**
	 * @param array{post_type: string} $args Required: 'post_type' — the post type archive to disable Relevanssi on.
	 */
	public function __construct( array $args ) {
		$this->post_type = $args['post_type'];
		\add_action( 'template_redirect', [ $this, 'remove_relevanssi' ] );
	}

	/**
	 * Remove Relevanssi query filters on the specified post type archive.
	 *
	 * @return void
	 */
	public function remove_relevanssi(): void {
		if ( \is_post_type_archive( $this->post_type ) ) {
			\remove_filter( 'posts_request', 'relevanssi_prevent_default_request' );
			\remove_filter( 'posts_pre_query', 'relevanssi_query', 99 );
		}
	}
}
