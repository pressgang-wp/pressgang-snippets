<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes all registered intermediate image sizes on init.
 *
 * Enable this snippet to prevent WordPress from generating intermediate
 * image sizes (useful if your theme handles responsive images manually).
 * Use with caution, as this removes all sizes, including plugin-registered ones.
 */
class RemoveImageSizes implements SnippetInterface {

	/**
	 * Hooks into init to remove image sizes.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'init', [ $this, 'remove_sizes' ], 1000 );
	}

	/**
	 * Removes all intermediate image sizes.
	 *
	 * @return void
	 */
	public function remove_sizes(): void {
		foreach ( \get_intermediate_image_sizes() as $size ) {
			\remove_image_size( $size );
		}
	}
}
