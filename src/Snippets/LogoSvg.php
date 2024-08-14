<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class LogoSvg
 *
 * Handles the addition of an SVG logo setting to the WordPress Customizer
 * and manages the saving and retrieval of the raw SVG content in the theme
 * mods.
 *
 * Assumes that SVG support is enabled in WordPress via an SVG sanitization
 * plugin.
 *
 * @package PressGang\Snippets
 */
class LogoSvg implements SnippetInterface {

	/**
	 * Constructor.
	 *
	 * Registers the methods to the appropriate WordPress hooks.
	 *
	 * @param array $args Optional arguments that might be passed for future
	 *     extensibility.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'customize_save_after', [ $this, 'save_logo_svg_raw_content' ] );
	}

	/**
	 * Add Logo SVG to Customizer.
	 *
	 * Adds a new control for an SVG logo in the WordPress Customizer under the
	 * 'logo' section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP_Customize_Manager
	 *     instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! $wp_customize->get_section( 'logo' ) ) {
			$wp_customize->add_section( 'logo', [
				'title'    => \_x( "Logo", 'Customizer', THEMENAME ),
				'priority' => 30,
			] );
		}

		$wp_customize->add_setting( 'logo_svg_url', [ 'default' => '' ] );

		$wp_customize->add_control( new \WP_Customize_Image_Control( $wp_customize,
			'logo_svg_url', [
				'label'      => \_x( "Logo SVG", 'Customizer', THEMENAME ),
				'section'    => 'logo',
				'extensions' => [ 'svg' ],
			] ) );
	}

	/**
	 * Saves the raw SVG content to the theme mods when the Customizer settings
	 * are saved.
	 *
	 * This method is triggered after the Customizer settings are saved and
	 * ensures that the raw SVG content is stored as a theme mod for easy
	 * retrieval and inline rendering.
	 *
	 * @return void
	 */
	public function save_logo_svg_raw_content(): void {
		$logo_svg_content = $this->fetch_logo_svg_raw_content();

		if ( $logo_svg_content ) {
			// Save the raw SVG content as a theme mod
			\set_theme_mod( 'logo_svg_raw', $logo_svg_content );
		}
	}

	/**
	 * Fetches the raw SVG content from the file system based on the URL stored
	 * in the Customizer.
	 *
	 * This method converts the SVG URL to a file path, checks if the file
	 * exists and is readable, and then returns the file content.
	 *
	 * @return string|null The SVG content as a string, or null if the file
	 *     could not be read.
	 */
	private function fetch_logo_svg_raw_content(): ?string {
		// Get the URL of the SVG logo from the Customizer
		$logo_svg_url = \get_theme_mod( 'logo_svg_url', '' );

		if ( $logo_svg_url ) {
			// Convert the URL to a file path
			$attachment_id = \attachment_url_to_postid( $logo_svg_url );
			$logo_svg_path = \get_attached_file( $attachment_id );

			// Check if the file exists and is readable
			if ( $logo_svg_path && \file_exists( $logo_svg_path ) && \is_readable( $logo_svg_path ) ) {
				// Get the SVG file content
				return \file_get_contents( $logo_svg_path );
			}
		}

		return null;
	}

	/**
	 * Retrieves the inline SVG content from the theme mod.
	 *
	 * This method fetches the raw SVG content stored in the theme mods, which
	 * can then be used for inline rendering in templates.
	 *
	 * @return string|null The SVG content as a string, or null if not
	 *     available.
	 */
	public function get_logo_svg_content(): ?string {
		return \get_theme_mod( 'logo_svg_raw', null );
	}

}
