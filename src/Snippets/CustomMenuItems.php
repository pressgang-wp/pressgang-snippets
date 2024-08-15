<?php

namespace PressGang\Snippets;

/**
 * Class CustomMenuItems
 *
 * Adds items to a given WordPress menu according to config settings in custom-menu-items.php
 *
 * @package PressGang\Snippets
 */
class CustomMenuItems implements SnippetInterface {

	/**
	 * @var array
	 */
	private array $menus = [];

	/**
	 * @param array $args
	 */
	public function __construct( array $args ) {
		// Don't do anything in the admin area
		if ( \is_admin() ) {
			return;
		}

		// Index the menus by slug
		foreach ( $args as $slug => $menu_args ) {
			$this->menus[ $slug ] = [
				'parent_object_id' => $menu_args['parent_object_id'] ?? 0,
				'subitems'         => $menu_args['subitems'] ?? [],
			];
		}

		\add_filter( 'wp_get_nav_menu_items', [ $this, 'filter_nav_menu_items' ], 10, 2 );
	}

	/**
	 * add_subitems_to_menu
	 *
	 * Adds custom items to a navigation menu
	 *
	 * @see http://teleogistic.net/2013/02/dynamically-add-items-to-a-wp_nav_menu-list/
	 * @link https://github.com/timber/timber/issues/200
	 *
	 * @param array $items
	 * @param \WP_Term $menu
	 *
	 * @return array
	 */
	public function filter_nav_menu_items( array $items, \WP_Term $menu ): array {
		// Check if the current menu has a configuration
		if ( ! isset( $this->menus[ $menu->slug ] ) ) {
			return $items;
		}

		$menu_config         = $this->menus[ $menu->slug ];
		$parent_menu_item_id = 0;

		foreach ( $items as $item ) {
			if ( $menu_config['parent_object_id'] == $item->object_id ) {
				$parent_menu_item_id = $item->ID;
				break;
			}
		}

		$menu_order = count( $items ) + 1;

		foreach ( $menu_config['subitems'] as $subitem ) {
			$items[] = (object) [
				'ID'               => uniqid( '', true ),
				'title'            => $subitem['text'],
				'url'              => $subitem['url'],
				'menu_item_parent' => $parent_menu_item_id,
				'menu_order'       => $menu_order,
				'type'             => '',
				'object'           => '',
				'object_id'        => '',
				'db_id'            => '',
				'classes'          => $subitem['classes'] ?? '',
				'target'           => $subitem['target'] ?? '_blank',
				'attr_title'       => $subitem['text'],
				'description'      => $subitem['description'] ?? '',
				'xfn'              => '',
				'status'           => '',
			];
			$menu_order ++;
		}

		return $items;
	}
}