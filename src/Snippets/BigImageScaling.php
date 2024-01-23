<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class BigImageScaling
 *
 * Handles the scaling of large images in WordPress. This class integrates with the 'big_image_size_threshold' hook,
 * allowing the modification of the maximum image width threshold. Images exceeding this threshold will be scaled down.
 * This is particularly useful for optimizing image file sizes for web performance while maintaining high-quality visuals.
 *
 * @link https://developer.wordpress.org/reference/hooks/big_image_size_threshold/
 */
class BigImageScaling implements SnippetInterface {

	/**
	 * Default maximum width for images.
	 */
	const DEFAULT_IMAGE_MAX_WIDTH = 2880;

	/**
	 * Maximum width for images.
	 *
	 * @var int
	 */
	protected int $image_max_width;

	/**
	 * Constructor.
	 *
	 * Initializes the class and sets the maximum image width.
	 * If no specific width is provided, the default value is used.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/big_image_size_threshold/
	 *
	 * @param array $args Arguments for the constructor.
	 */
	public function __construct( array $args ) {
		$this->image_max_width = $args['image_max_width'] ?? self::DEFAULT_IMAGE_MAX_WIDTH;
		\add_filter( 'big_image_size_threshold', [ $this, 'big_image_size_threshold' ], 999, 1 );
	}

	/**
	 * Get the maximum image size threshold.
	 *
	 * This method returns the maximum width value for scaling large images.
	 *
	 * @hooked big_image_size_threshold
	 * @return int The maximum width for images.
	 */
	public function big_image_size_threshold(): int {
		return $this->image_max_width;
	}
}
