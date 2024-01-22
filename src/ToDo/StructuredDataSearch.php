<?php

namespace PressGang\ToDo;

use Timber;
use function PressGang\Snippets\add_action;
use function PressGang\Snippets\get_bloginfo;
use function PressGang\Snippets\is_front_page;

class StructuredDataSearch {

	/**
	 * __construct
	 *
	 * StructuredDataSearch constructor.
	 */
	public function __construct() {
		add_action( 'wp_head', [ $this, 'render' ] );
	}

	/**
	 * render
	 *
	 * @return mixed
	 */
	public function render() {
		if ( is_front_page() ) {
			$data = [
				'name' => get_bloginfo( 'name' ),
				'url'  => get_bloginfo( 'url' ),
			];

			Timber::render( 'structured-data-search.twig', $data );
		}
	}

}

new StructuredDataSearch();
