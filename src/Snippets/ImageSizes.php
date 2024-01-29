<?php

namespace PressGang\Snippets;

/**
 * Class ImageSizes
 *
 * Dynamically sets WordPress image sizes based on a provided configuration array.
 * This class allows for flexible customization of image dimensions and cropping settings.
 *
 * @package PressGang\Snippets
 */
class ImageSizes implements SnippetInterface {

	/**
	 * ImageSizes constructor.
	 *
	 * Initializes the image size settings based on the provided arguments. Each argument
	 * should specify the image size (e.g., 'thumbnail', 'medium', 'large'), along with
	 * an array of settings for 'width', 'height', and 'crop'. The 'crop' setting is optional
	 * and defaults to false if not specified.
	 *
	 * Example usage:
	 * $args = [
	 *     'thumbnail' => ['width' => 266, 'height' => 200, 'crop' => true],
	 *     'medium' => ['width' => 720, 'height' => 540],
	 *     'large' => ['width' => 1140, 'height' => 641, 'crop' => true],
	 * ];
	 *
	 * @see https://timber.github.io/docs/v2/guides/cookbook-images/#use-a-wordpress-image-size
	 * @param array $args An associative array where keys are size names and values are
	 *                    settings arrays with keys 'width', 'height', and optional 'crop'.
	 */
	public function __construct( $args ) {
		foreach ( $args as $size => $settings ) {
			if ( isset( $settings['width'], $settings['height'], $settings['crop'] ) ) {
				\update_option( "{$size}_size_w", $settings['width'] );
				\update_option( "{$size}_size_h", $settings['height'] );
				\update_option( "{$size}_crop", $settings['crop'] );
			}
		}
	}

}