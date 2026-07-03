<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;
use Timber\URLHelper;

/**
 * Adds active/current classes to custom-link menu items whose URL matches
 * the current request, so Timber's MenuItem::current highlighting works for
 * them (WordPress only sets current classes on object-linked items).
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
