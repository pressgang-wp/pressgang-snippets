<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\add_action;

class Tawkto {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_footer', [ $this, 'render' ], 100 );
	}

	/**
	 * render
	 *
	 */
	public function render() {
		\Timber\Timber::render( 'tawkto.twig' );
	}

}

new Tawkto();
