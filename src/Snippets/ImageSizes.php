<?php

namespace PressGang\Snippets;

/**
 * Class ImageSizes
 *
 * Manages WordPress image sizes, allowing customization of existing sizes and addition of new custom sizes.
 *
 * @package PressGang\Snippets
 */
class ImageSizes implements SnippetInterface {

	private $args;

	/**
	 * ImageSizes constructor.
	 *
	 * Sets up the image sizes according to the provided configuration.
	 *
	 * @param array $args Associative array for image size configuration.
	 */
	public function __construct( $args ) {
		$this->args = $args;
		\add_action( 'init', [ $this, 'setup_image_sizes' ] );
	}

	/**
	 * Initializes the setup of image sizes.
	 *
	 * Iterates through each image size configuration and applies the necessary changes.
	 */
	public function setup_image_sizes(): void {
		foreach ( $this->args as $size => $settings ) {
			if ( $settings === false ) {
				$this->disable_image_size( $size );
			} elseif ( is_array( $settings ) ) {
				$this->handle_image_size( $size, $settings );
			}
		}
	}

	/**
	 * Handles the setting or addition of an image size.
	 *
	 * Determines whether to update a default size or add a custom size.
	 *
	 * @param string $size Name of the image size.
	 * @param array $settings Configuration settings for the image size.
	 */
	private function handle_image_size( string $size, array $settings ): void {
		$width  = $settings['width'] ?? 0;
		$height = $settings['height'] ?? 0;
		$crop   = $settings['crop'] ?? false;

		if ( in_array( $size, [ 'thumbnail', 'medium', 'large' ] ) ) {
			$this->update_default_size( $size, $width, $height, $crop );
		} else {
			$this->add_custom_size( $size, $width, $height, $crop );
		}
	}

	/**
	 * Updates the settings for a default WordPress image size.
	 *
	 * @param string $size Name of the image size.
	 * @param int $width Width of the image size.
	 * @param int $height Height of the image size.
	 * @param bool $crop Whether to crop the image.
	 */
	private function update_default_size( string $size, int $width, int $height, bool $crop ): void {
		\update_option( "{$size}_size_w", $width );
		\update_option( "{$size}_size_h", $height );
		\update_option( "{$size}_crop", $crop );
	}

	/**
	 * Adds a new custom image size.
	 *
	 * @param string $size Name of the image size.
	 * @param int $width Width of the image size.
	 * @param int $height Height of the image size.
	 * @param bool $crop Whether to crop the image.
	 */
	private function add_custom_size( string $size, int $width, int $height, bool $crop ): void {
		if ( $width > 0 && $height > 0 ) {
			\add_image_size( $size, $width, $height, $crop );
		}
	}

	/**
	 * Disables an image size.
	 *
	 * Sets default sizes to zero or removes custom sizes.
	 *
	 * @param string $size Name of the image size to be disabled.
	 */
	protected function disable_image_size( string $size ): void {
		if ( in_array( $size, [ 'thumbnail', 'medium', 'large' ] ) ) {
			\update_option( "{$size}_size_w", 0 );
			\update_option( "{$size}_size_h", 0 );
			\update_option( "{$size}_crop", 0 );
		} else {
			\remove_image_size( $size );
		}
	}

}