<?php


namespace PressGang\Snippets\Seo;

use PressGang\Snippets\SnippetInterface;

/**
 * Filters the wp_title() output for basic SEO improvements: appends the site
 * name, adds the site description on the front/home page, includes page
 * numbers for paginated views, and builds a custom title for search results.
 *
 * Enable this snippet for lightweight title-tag SEO when a full SEO plugin
 * is not in use.
 */
class Title implements SnippetInterface {

	/**
	 * Hooks into the wp_title filter.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'wp_title', [ $this, 'filter_wp_title' ], 10, 3 );
	}

	/**
	 * Filters the wp_title output, delegating to context-specific helpers
	 * for search pages and standard pages. Feed titles are returned as-is.
	 *
	 * @param string $title     The original title.
	 * @param string $separator The title separator character.
	 * @param string $location  Where the site name appears ('left' or 'right').
	 *
	 * @return string The modified title.
	 */
	public function filter_wp_title( string $title, string $separator, string $location ): string {
		$separator = trim( $separator );
		$separator = $separator ? " {$separator} " : ' ';

		if ( \is_feed() ) {
			return $title;
		}

		if ( \is_search() ) {
			return $this->get_search_title( $separator );
		}

		return $this->get_standard_title( $title, $separator, $location );
	}

	/**
	 * Builds a title for search result pages in the format:
	 * Search '{query}' [| page] | Site Name.
	 *
	 * @param string $separator The title separator.
	 *
	 * @return string The search page title.
	 */
	protected function get_search_title( string $separator ): string {
		global $paged;

		$title = sprintf( "%s '%s'", \_x( "Search", 'Title', THEMENAME ), \get_search_query() );

		if ( $paged >= 2 ) {
			$title .= $separator . $paged;
		}

		$title .= $separator . \get_bloginfo( 'name', 'display' );

		return $title;
	}

	/**
	 * Builds a standard page title with the site name positioned according
	 * to $location, appending the site description on the front page and
	 * page numbers on paginated views.
	 *
	 * @param string $title     The original title.
	 * @param string $separator The title separator.
	 * @param string $location  Site name position ('left', 'right', or default).
	 *
	 * @return string The assembled title.
	 */
	protected function get_standard_title( string $title, string $separator, string $location ): string {
		global $paged, $page;

		switch ( strtolower( $location ) ) {
			case 'left':
				$title = \get_bloginfo( 'name', 'display' ) . $title;
				break;
			case 'right':
				$title .= \get_bloginfo( 'name', 'display' );
				break;
			default:
				$title .= $separator . \get_bloginfo( 'name', 'display' );
		}

		if ( \is_home() || \is_front_page() ) {
			$title .= $separator . \get_bloginfo( 'description', 'display' );
		}

		if ( ( is_numeric( $paged ) && $paged >= 2 ) || ( is_numeric( $page ) && $page >= 2 ) ) {
			$title .= $separator . max( $paged, $page );
		}

		return trim( $title );
	}
}