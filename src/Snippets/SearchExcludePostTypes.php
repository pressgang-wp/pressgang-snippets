<?php

namespace PressGang\Snippets;

/**
 * Class SearchExclude
 *
 * Exclude specific post types from WordPress search results.
 *
 * @package PressGang
 */
class SearchExcludePostTypes implements SnippetInterface {

	/**
	 * The post types to exclude from search results.
	 *
	 * @var array
	 */
	protected array $exclude = [];

	/**
	 * SearchExclude constructor.
	 *
	 * Initializes the class by setting up the post types to exclude from search and hooking into the query filtering mechanism.
	 *
	 * @param array $args Arguments to specify options, including 'exclude' which lists the post types to exclude.
	 */
	public function __construct( array $args ) {
		$this->exclude = $args['exclude'] ?? [];

		if ( ! \is_admin() ) {
			\add_filter( 'pre_get_posts', [ $this, 'filter_search_post_types' ] );
		}
	}

	/**
	 * Modifies the search query to exclude specified post types.
	 *
	 * @param \WP_Query $query The current query object.
	 *
	 * @return void
	 */
	public function filter_search_post_types( \WP_Query $query ): void {
		if ( $query->is_search && ! is_admin() ) { // Ensure this only affects front-end search queries
			// Get current post types queried, default to 'any' if not set
			$current_post_types = (array) ( $query->get( 'post_type' ) ?: [ 'any' ] );

			// If 'any' is specified, we need to convert it to an explicit list of all public post types
			if ( in_array( 'any', $current_post_types, true ) ) {
				$current_post_types = \get_post_types( [ 'public' => true ], 'names' );
			}

			// Exclude the specified post types
			$post_types_to_include = array_diff( $current_post_types, $this->excluded_post_types );

			// Apply the modified list of post types to the query
			$query->set( 'post_type', $post_types_to_include );
		}
	}
}