<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;
use Timber\URLHelper;

/**
 * Adds active/current classes to menu items whose URL matches the current
 * request, using Timber's normalised URL comparison. More robust than
 * WordPress core's exact-match handling for custom links (scheme/host/
 * trailing-slash differences), and adds an `active` class for CSS hooks.
 *
 * Ported from the PressGang v1 parent theme's Filters class.
 */
class MenuActiveClasses implements SnippetInterface {

	/**
	 * @param array $args
	 */
	public function __construct( array $args ) {
		\add_filter( 'nav_menu_css_class', [ $this, 'add_active_classes' ], 10, 2 );
	}

	/**
	 * Adds active classes when the menu item URL matches the current URL.
	 *
	 * @param array  $classes Menu item CSS classes.
	 * @param object $item    Menu item.
	 *
	 * @return array
	 */
	public function add_active_classes( $classes, $item ): array {

		if ( $item->url === URLHelper::get_current_url() ) {
			$classes[] = 'active';
			$classes[] = 'current-menu-item';
		}

		return $classes;
	}
}
