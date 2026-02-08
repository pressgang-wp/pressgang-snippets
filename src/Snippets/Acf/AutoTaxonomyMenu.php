<?php

namespace PressGang\Snippets\Acf;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds an ACF selector to menu items so a taxonomy can auto-populate submenu
 * items on the front end.
 *
 * Enable this snippet when you want a menu item to automatically expand into
 * a list of taxonomy terms. Requires ACF to be active.
 */
class AutoTaxonomyMenu implements SnippetInterface {

	/**
	 * Registers ACF fields and hooks for populating menu items.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		$this->add_acf_fields();

		\add_filter( 'acf/load_field/name=taxonomy_sub_menu', [ $this, 'taxonomy_select' ] );
		\add_filter( 'wp_get_nav_menu_items', [ $this, 'add_sub_menu_items' ], 10, 3 );
	}

	/**
	 * Populate the ACF select field choices with available taxonomies.
	 *
	 * @param array<string, mixed> $field ACF field configuration.
	 *
	 * @return array<string, mixed> Updated field configuration.
	 */
	public function taxonomy_select( array $field ): array {
		$field['choices'] = [];

		$taxonomies = \get_taxonomies();
		foreach ( $taxonomies as $taxonomy_name ) {
			$taxonomy = \get_taxonomy( $taxonomy_name );
			if ( $taxonomy ) {
				$field['choices'][ $taxonomy->name ] = $taxonomy->label;
			}
		}

		return $field;
	}

	/**
	 * Adds taxonomy term submenu items for menu items configured via ACF.
	 *
	 * @param array<int, \WP_Post> $items Menu items.
	 * @param \WP_Term             $menu  Menu object.
	 * @param array<string, mixed> $args  Menu args.
	 *
	 * @return array<int, \WP_Post> Updated menu items.
	 */
	public function add_sub_menu_items( array $items, \WP_Term $menu, array $args ): array {
		if ( \is_admin() || ! function_exists( 'get_field' ) ) {
			return $items;
		}

		foreach ( $items as $item ) {
			$taxonomy = \get_field( 'taxonomy_sub_menu', $item );
			if ( ! $taxonomy ) {
				continue;
			}

			$terms = \get_terms( [
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
			] );

			if ( \is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			foreach ( $terms as $index => $term ) {
				$items[] = $this->get_nav_menu_item( $term, $item->ID, $index * 10 );
			}
		}

		return $items;
	}

	/**
	 * Build a nav menu item object for a term.
	 *
	 * @param \WP_Term $term
	 * @param int $parent_id
	 * @param int $order
	 *
	 * @return \stdClass
	 */
	protected function get_nav_menu_item( \WP_Term $term, int $parent_id = 0, int $order = 0 ): \stdClass {
		$item                   = new \stdClass();
		$item->ID               = $term->term_id;
		$item->db_id            = 1000000 + $order + $parent_id;
		$item->title            = $term->name;
		$item->url              = \get_term_link( $term );
		$item->menu_order       = $order;
		$item->menu_item_parent = $parent_id;
		$item->type             = '';
		$item->type_label       = '';
		$item->object           = '';
		$item->object_id        = '';
		$item->classes          = [];
		$item->target           = '';
		$item->attr_title       = '';
		$item->description      = '';
		$item->xfn              = '';
		$item->status           = '';

		return $item;
	}

	/**
	 * Register the ACF field group for taxonomy submenu selection.
	 *
	 * @return void
	 */
	private function add_acf_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		\acf_add_local_field_group( [
			'key'                   => 'group_5a4647ba28148',
			'title'                 => 'Auto Populate Tax Menu',
			'fields'                => [
				[
					'key'               => 'field_5a4647c64131b',
					'label'             => 'Taxonomy Sub Menu',
					'name'              => 'taxonomy_sub_menu',
					'type'              => 'select',
					'instructions'      => 'Select if this item should auto populate a submenu using the given taxonomy.',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'choices'           => [
						'post_tag' => 'Tag',
						'category' => 'Category',
					],
					'default_value'     => [],
					'allow_null'        => 1,
					'multiple'          => 0,
					'ui'                => 0,
					'ajax'              => 0,
					'return_format'     => 'value',
					'placeholder'       => '',
				],
			],
			'location'              => [
				[
					[
						'param'    => 'nav_menu_item',
						'operator' => '==',
						'value'    => 'all',
					],
				],
			],
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'seamless',
			'label_placement'       => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => 1,
			'description'           => 'Used by PressGang include "auto-taxonomy-menu.php"',
		] );
	}
}
