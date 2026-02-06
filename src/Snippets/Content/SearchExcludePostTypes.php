<?php


namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Excludes specified post types from front-end WordPress search results by
 * filtering pre_get_posts.
 *
 * Configure via the 'exclude' key in $args, passing an array of post type
 * slugs to hide from search.
 */
class SearchExcludePostTypes implements SnippetInterface {

	/**
	 * Post type slugs to exclude from search.
	 *
	 * @var array<int, string>
	 */
	protected array $exclude = [];

	/**
	 * Stores the exclusion list and hooks into pre_get_posts on the front end.
	 *
	 * @param array{exclude?: array<int, string>} $args Supported keys:
	 *     'exclude' — array of post type slugs to remove from search results.
	 */
	public function __construct( array $args ) {
		$this->exclude = $args['exclude'] ?? [];

		if ( ! \is_admin() ) {
			\add_filter( 'pre_get_posts', [ $this, 'filter_search_post_types' ] );
		}
	}

	/**
	 * Removes the configured post types from the search query. Converts
	 * 'any' to an explicit list of public post types before applying the
	 * exclusion.
	 *
	 * @param \WP_Query $query The current query object.
	 *
	 * @return void
	 */
	public function filter_search_post_types( \WP_Query $query ): void {
		if ( $query->is_search && ! \is_admin() ) {
			$current_post_types = (array) ( $query->get( 'post_type' ) ?: [ 'any' ] );

			if ( in_array( 'any', $current_post_types, true ) ) {
				$current_post_types = \get_post_types( [ 'public' => true ], 'names' );
			}

			$query->set( 'post_type', array_diff( $current_post_types, $this->exclude ) );
		}
	}
}