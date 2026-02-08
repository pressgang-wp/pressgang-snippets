<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds taxonomy filters to admin post list tables for custom taxonomies,
 * allowing editors to filter by terms in the list view.
 *
 * Enable this snippet when you want quick taxonomy filtering in the admin.
 * Filters are added for public, non-built-in taxonomies by default, and can
 * be adjusted via the admin_taxonomy_filters filter.
 */
class AdminTaxonomyFilter implements SnippetInterface {

	/**
	 * Hooks the taxonomy filter output into the admin list table controls.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'restrict_manage_posts', [ $this, 'admin_taxonomy_filter' ] );
	}

	/**
	 * Outputs taxonomy dropdown filters in the admin list table.
	 *
	 * @return void
	 */
	public function admin_taxonomy_filter(): void {
		$taxonomies = \apply_filters( 'admin_taxonomy_filters', \get_taxonomies( [
			'public'   => true,
			'_builtin' => false,
		], 'objects' ) );

		foreach ( $taxonomies as $taxonomy ) {
			$terms = \get_terms( $taxonomy->name );
			if ( empty( $terms ) ) {
				continue;
			}

			$taxonomy_name = $taxonomy->name;
			$selected      = $this->get_selected_term( $taxonomy_name );

			echo '<select name="' . \esc_attr( $taxonomy_name ) . '" id="' . \esc_attr( $taxonomy_name ) . '" class="postform">';
			echo '<option value="">' . \esc_html( sprintf( "Show All %s", $taxonomy->labels->name ) ) . '</option>';

			foreach ( $terms as $term ) {
				$is_selected = $selected === $term->slug ? ' selected="selected"' : '';
				echo '<option value="' . \esc_attr( $term->slug ) . '"' . $is_selected . '>' . \esc_html( $term->name ) . '</option>';
			}

			echo '</select>';
		}
	}

	/**
	 * Determine the currently selected term for a taxonomy in the admin UI.
	 *
	 * @param string $taxonomy_name
	 *
	 * @return string
	 */
	private function get_selected_term( string $taxonomy_name ): string {
		if ( ! isset( $_GET[ $taxonomy_name ] ) ) {
			return '';
		}

		return \sanitize_text_field( (string) $_GET[ $taxonomy_name ] );
	}
}
