<?php

declare(strict_types=1);

namespace PressGang\Snippets;

/**
 * Manages WordPress image sizes: updates default sizes (thumbnail, medium,
 * large), registers custom sizes, and can disable sizes by setting them to
 * false.
 *
 * Configured via the $args array passed from config/snippets.php. Each key is
 * a size name and each value is either an array of {width, height, crop} or
 * false to disable the size.
 */
class ImageSizes implements SnippetInterface {

	/**
	 * Image size configuration map.
	 *
	 * @var array<string, array{width: int, height: int, crop?: bool}|false>
	 */
	private array $args;

	/**
	 * Stores the image size configuration and hooks into 'init' to apply it.
	 *
	 * @param array<string, array{width: int, height: int, crop?: bool}|false> $args
	 *     Associative array of image size definitions. Each key is a size name
	 *     and each value is one of:
	 *     - array{width: int, height: int, crop?: bool} to set/add the size
	 *     - false to disable/remove the size
	 */
	public function __construct( array $args ) {
		$this->args = $args;
		\add_action( 'init', [ $this, 'setup_image_sizes' ] );
	}

	/**
	 * Iterates through each configured size and applies the appropriate
	 * action (update, add, or disable).
	 *
	 * @return void
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
	 * Routes a size definition to either update_default_size or
	 * add_custom_size based on whether it is a built-in WordPress size.
	 *
	 * @param string                                     $size     Size name.
	 * @param array{width?: int, height?: int, crop?: bool} $settings Size configuration.
	 *
	 * @return void
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
	 * Updates the WordPress options for a built-in image size.
	 *
	 * @param string $size   Size name ('thumbnail', 'medium', or 'large').
	 * @param int    $width  Width in pixels.
	 * @param int    $height Height in pixels.
	 * @param bool   $crop   Whether to hard-crop the image.
	 *
	 * @return void
	 */
	private function update_default_size( string $size, int $width, int $height, bool $crop ): void {
		\update_option( "{$size}_size_w", $width );
		\update_option( "{$size}_size_h", $height );
		\update_option( "{$size}_crop", $crop );
	}

	/**
	 * Registers a new custom image size with WordPress.
	 *
	 * @param string $size   Size name.
	 * @param int    $width  Width in pixels.
	 * @param int    $height Height in pixels.
	 * @param bool   $crop   Whether to hard-crop the image.
	 *
	 * @return void
	 */
	private function add_custom_size( string $size, int $width, int $height, bool $crop ): void {
		if ( $width > 0 && $height > 0 ) {
			\add_image_size( $size, $width, $height, $crop );
		}
	}

	/**
	 * Disables an image size by zeroing built-in sizes or removing custom ones.
	 *
	 * @param string $size Size name to disable.
	 *
	 * @return void
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
