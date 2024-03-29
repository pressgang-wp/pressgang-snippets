<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\add_action;
use function PressGang\Snippets\remove_menu_page;

class RemovePosts {

	/**
	 * RemovePosts constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'post_remove' ] );
	}

	/**
	 * Removes posts from the admin menu
	 */
	public function post_remove() {
		remove_menu_page( 'edit.php' );
	}

}

new RemovePosts();
