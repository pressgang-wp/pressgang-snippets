<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\add_action;
use function PressGang\Snippets\get_intermediate_image_sizes;
use function PressGang\Snippets\remove_image_size;

class RemoveImageSizes {

	/**
	 * __construct
	 *
	 * We mostly don't need these as building responsive images in Timber
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'remove_sizes' ], 1000 );
	}

	/**
	 * remove_sizes
	 */
	public function remove_sizes() {
		foreach ( get_intermediate_image_sizes() as &$size ) {
			remove_image_size( $size );
		}
	}

}

new RemoveImageSizes();
