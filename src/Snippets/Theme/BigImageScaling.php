<?php


namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Overrides the WordPress "big image" scaling threshold — the maximum pixel
 * dimension at which uploaded images are automatically down-scaled.
 *
 * WordPress defaults to 2560 px. Enable this snippet to raise (or lower) that
 * limit — for example, to 2880 px to support Retina hero images without
 * WordPress silently resizing them on upload.
 *
 * @link https://developer.wordpress.org/reference/hooks/big_image_size_threshold/
 */
class BigImageScaling implements SnippetInterface {

	/**
	 * Default maximum pixel dimension for images.
	 */
	const DEFAULT_IMAGE_MAX_WIDTH = 2880;

	/**
	 * Maximum pixel dimension for uploaded images.
	 *
	 * @var int
	 */
	protected int $image_max_width;

	/**
	 * Stores the configured maximum dimension and hooks into the
	 * big_image_size_threshold filter.
	 *
	 * @param array<string, mixed> $args Optional. Supports:
	 *     - 'image_max_width': int — maximum pixel dimension (default 2880).
	 */
	public function __construct( array $args ) {
		$this->image_max_width = $args['image_max_width'] ?? self::DEFAULT_IMAGE_MAX_WIDTH;
		\add_filter( 'big_image_size_threshold', [ $this, 'big_image_size_threshold' ], 999, 1 );
	}

	/**
	 * Returns the configured maximum image dimension, replacing the WordPress
	 * default of 2560 px.
	 *
	 * @return int The maximum pixel dimension for image scaling.
	 */
	public function big_image_size_threshold(): int {
		return $this->image_max_width;
	}
}
