<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class PostTypeMenuHighlighter
 *
 * This class provides a workaround for a known issue in WordPress where the menu
 * item highlighting for custom post types doesn't work correctly out of the box.
 *
 * The problem:
 * 1. For single custom post type pages, WordPress doesn't correctly highlight
 *    the corresponding archive page in the menu.
 * 2. For custom post type archive pages, the menu item for the archive page
 *    itself is not highlighted.
 *
 * This class fixes these issues by:
 * 1. Adding the 'current_page_parent' class to the menu item of the archive
 *    page when viewing a single post of that custom post type.
 * 2. Adding the 'current_page_item' class to the menu item of the archive page
 *    when viewing the archive page itself.
 *
 * It works by hooking into the 'nav_menu_css_class' filter and modifying the
 * CSS classes applied to menu items based on the current context (single post
 * or archive page of a custom post type).
 *
 * @package PressGang\Snippets
 */
class PostTypeMenuHighlighter implements SnippetInterface {

	/**
	 * CSS classes to be managed by this highlighter.
	 *
	 * @var array
	 */
	private const MANAGED_CLASSES = [ 'current_page_parent', 'current_page_item' ];

	/**
	 * Constructor for the PostTypeMenuHighlighter class.
	 *
	 * @param array $args Constructor arguments (unused in this implementation)
	 */
	public function __construct( array $args = [] ) {
		\add_filter( 'nav_menu_css_class', [ $this, 'custom_menu_classes' ], 10, 2 );
	}

	/**
	 * Customizes menu item classes for custom post types.
	 *
	 * @param array $classes The array of existing CSS classes.
	 * @param \WP_Post $item The current menu item.
	 *
	 * @return array The modified array of CSS classes.
	 */
	public function custom_menu_classes( array $classes, \WP_Post $item ): array {
		$current_post_type = \get_post_type();

		$classes = $this->remove_highlight_classes( $classes );

		if ( \is_singular( $current_post_type ) ) {
			$classes = $this->handle_singular_view( $classes, $item );
		} elseif ( \is_post_type_archive( $current_post_type ) ) {
			$classes = $this->handle_archive_view( $classes, $item, $current_post_type );
		}

		return $classes;
	}

	/**
	 * Removes highlight classes from the given classes array.
	 *
	 * @param array $classes The current classes.
	 *
	 * @return array
	 */
	private function remove_highlight_classes( array $classes ): array {
		return array_diff( $classes, self::MANAGED_CLASSES );
	}

	/**
	 * Handles class modification for singular views.
	 *
	 * @param array $classes The current classes.
	 * @param \WP_Post $item The menu item.
	 *
	 * @return array Modified classes.
	 */
	private function handle_singular_view( array $classes, \WP_Post $item ): array {
		$current_url = $this->get_current_url();
		$item_url    = rtrim( $item->url, '/' );

		if ( $this->is_url_ancestor( $current_url, $item_url ) ) {
			$classes[] = 'current_page_parent';
		}

		return $classes;
	}

	/**
	 * Handles class modification for archive views.
	 *
	 * @param array $classes The current classes.
	 * @param \WP_Post $item The menu item.
	 * @param string $current_post_type The current post type.
	 *
	 * @return array Modified classes.
	 */
	private function handle_archive_view( array $classes, \WP_Post $item, string $current_post_type ): array {
		$archive_link = \get_post_type_archive_link( $current_post_type );
		if ( rtrim( $item->url, '/' ) === rtrim( $archive_link, '/' ) ) {
			$classes[] = 'current_page_item';
		}

		return $classes;
	}

	/**
	 * Get the current URL.
	 *
	 * @return string The current URL.
	 */
	private function get_current_url(): string {
		global $wp;

		return \home_url( $wp->request );
	}

	/**
	 * Check if a given URL is an ancestor of the current URL.
	 *
	 * @param string $current_url The current URL.
	 * @param string $potential_ancestor The potential ancestor URL.
	 *
	 * @return bool True if the potential ancestor is indeed an ancestor, false otherwise.
	 */
	private function is_url_ancestor( string $current_url, string $potential_ancestor ): bool {
		return str_starts_with( $current_url, $potential_ancestor );
	}
}
