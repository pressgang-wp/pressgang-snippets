<?php


namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes the default "Posts" (edit.php) menu item from the WordPress admin
 * sidebar.
 *
 * Enable this snippet on sites that do not use the built-in 'post' post type
 * to reduce admin clutter.
 */
class RemovePosts implements SnippetInterface {

	/**
	 * Hooks into admin_menu to remove the Posts menu page.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'admin_menu', [ $this, 'post_remove' ] );
	}

	/**
	 * Removes the Posts menu page from the admin sidebar.
	 *
	 * @return void
	 */
	public function post_remove(): void {
		\remove_menu_page( 'edit.php' );
	}
}