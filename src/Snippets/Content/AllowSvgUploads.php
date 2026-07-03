<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Allows SVG files in the media library.
 *
 * Note: SVGs can carry scripts — only enable on sites where uploaders are
 * trusted, or sanitise uploads with a dedicated plugin.
 */
class AllowSvgUploads implements SnippetInterface {

	/**
	 * @param array $args
	 */
	public function __construct( array $args ) {
		\add_filter( 'upload_mimes', [ $this, 'allow_svg' ] );
	}

	/**
	 * Adds the SVG mime type to the allowed upload types.
	 *
	 * @param array $mimes Allowed mime types.
	 *
	 * @return array
	 */
	public function allow_svg( $mimes ): array {
		$mimes['svg'] = 'image/svg+xml';

		return $mimes;
	}
}
