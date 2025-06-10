<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class RemovePosts
 *
 * Removes the default "Posts" menu item from the WordPress admin dashboard.
 *
 * @package PressGang\Snippets
 */
class RemovePosts implements SnippetInterface {

	/**
	 * Constructor.
	 *
	 * Registers the action to remove the Posts menu on the 'admin_menu' hook.
	 *
	 * @param array $args Optional arguments for future extensibility.
	 */
	public function __construct( array $args ) {
		\add_action( 'admin_menu', [ $this, 'post_remove' ] );
	}

	/**
	 * Removes the 'Posts' menu page from the WordPress admin menu.
	 *
	 * This is useful for sites that do not use the default 'post' post type.
	 *
	 * @return void
	 */
	public function post_remove(): void {
		\remove_menu_page( 'edit.php' );
	}

}