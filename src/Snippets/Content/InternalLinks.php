<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Automatically links taxonomy terms within post content to their term pages.
 *
 * Enable this snippet to turn matching term names into internal links. Uses
 * a simple regex pass, so content with heavy markup should be reviewed.
 */
class InternalLinks implements SnippetInterface {

	/**
	 * Hooks content filtering to inject internal links.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'the_content', [ $this, 'add_internal_links' ] );
	}

	/**
	 * Adds internal links for taxonomy terms in the content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function add_internal_links( string $content ): string {
		$terms = \get_terms();
		if ( \is_wp_error( $terms ) || empty( $terms ) ) {
			return $content;
		}

		$terms = $this->filter_duplicate_terms( $terms );

		$pattern = array_map( function ( $term ) {
			return sprintf( "/\\b(%s)\\b(?=[^<>]*<)(?!.*?\\<\\/a\\>)/ui",
				preg_quote( $term->name, '/' ) );
		}, $terms );

		$replacement = array_map( function ( $term ) {
			return sprintf(
				'<a href="%s" class="inbound">$1</a>',
				\esc_url( \get_term_link( $term ) )
			);
		}, $terms );

		return preg_replace( $pattern, $replacement, $content );
	}

	/**
	 * Filters duplicate term names to avoid repeated replacements.
	 *
	 * @param array<int, \WP_Term> $terms
	 *
	 * @return array<int, \WP_Term>
	 */
	private function filter_duplicate_terms( array $terms ): array {
		return array_filter( $terms, function ( $term ) {
			static $names = [];
			if ( in_array( $term->name, $names, true ) ) {
				return false;
			}
			$names[] = $term->name;

			return true;
		} );
	}
}
