<?php

namespace Snippets;

class InternalLinks {

	/**
	 * __construct
	 *
	 */
	public function __construct() {
		add_filter( 'the_content', [ $this, 'add_internal_links' ] );
	}

	/**
	 * add_internal_links
	 *
	 */
	public function add_internal_links( $content ) {
		$terms = get_terms();

		// filter duplicate term names (e.g. categories and tags with save name)

		$terms = array_filter( $terms, function ( $term ) {
			static $names = [];
			if ( in_array( $term->name, $names ) ) {
				return false;
			}
			$names[] = $term->name;

			return true;
		} );

		$pattern = array_map( function ( $term ) {
			return sprintf( "/\\b(%s)\\b(?=[^<>]*<)(?!.*?\\<\\/a\\>)/ui",
				preg_quote( $term->name, '/' ) );
		}, $terms );

		$replacement = array_map( function ( $term ) {
			return sprintf( "<a href=\"%s\" class=\"inbound\">$1</a>",
				esc_url( \get_term_link( $term ) ), esc_html( $term->name ) );
		}, $terms );

		$content = preg_replace( $pattern, $replacement, $content );

		return $content;
	}

}

new InternalLinks();
