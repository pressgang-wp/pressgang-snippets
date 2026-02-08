<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes the single-page template from the page template dropdown in the
 * WordPress admin.
 *
 * Enable this snippet when the single-page template should not be selectable
 * for pages. No configuration required.
 */
class HideSinglePageTemplate implements SnippetInterface {

	/**
	 * Hooks into theme_page_templates to remove the template.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'theme_page_templates', [ $this, 'hide_single_page_template' ], 20, 1 );
	}

	/**
	 * Filters the theme page templates list to remove the single-page template.
	 *
	 * @param array<string, string> $page_templates Page templates array.
	 *
	 * @return array<string, string> Modified templates array.
	 */
	public function hide_single_page_template( array $page_templates ): array {
		unset( $page_templates['page-templates/single-page.php'] );

		return $page_templates;
	}
}
