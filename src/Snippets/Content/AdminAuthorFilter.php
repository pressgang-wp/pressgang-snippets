<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds an "Author" dropdown filter to admin post list tables so editors can
 * quickly filter posts by author.
 *
 * Enable this snippet to make author filtering available in the admin post
 * and page list screens. No configuration required.
 */
class AdminAuthorFilter implements SnippetInterface {

	/**
	 * Hooks the author filter into the admin list table controls.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'restrict_manage_posts', [ $this, 'filter_by_the_author' ] );
	}

	/**
	 * Outputs a dropdown of authors in the admin list table filters.
	 *
	 * @return void
	 */
	public function filter_by_the_author(): void {
		$params = [
			'name'            => 'author',
			'show_option_all' => \__( 'All Authors', THEMENAME ),
		];

		if ( isset( $_GET['user'] ) ) {
			$params['selected'] = \absint( $_GET['user'] );
		}

		\wp_dropdown_users( $params );
	}
}
