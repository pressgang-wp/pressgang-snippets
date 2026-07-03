<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Registers custom Timber MenuItem classes per menu location via the
 * `timber/menuitem/classmap` filter.
 *
 * Args: a map of menu location => fully-qualified MenuItem class name, e.g.
 * `[ 'primary' => \MyTheme\Models\MainMenuItem::class ]`.
 */
class MenuItemClassMap implements SnippetInterface {

	/**
	 * Menu location => MenuItem class map.
	 *
	 * @var array<string, class-string>
	 */
	protected array $classmap;

	/**
	 * @param array<string, class-string> $args Menu location => MenuItem class name.
	 */
	public function __construct( array $args ) {
		$this->classmap = $args;

		\add_filter( 'timber/menuitem/classmap', [ $this, 'add_menu_item_classes' ] );
	}

	/**
	 * Merges the configured classes into Timber's menuitem classmap.
	 *
	 * @param array $classmap Location => class map.
	 *
	 * @return array
	 */
	public function add_menu_item_classes( array $classmap ): array {
		return array_merge( $classmap, $this->classmap );
	}
}
