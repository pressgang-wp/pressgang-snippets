<?php


namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds a raster logo (PNG/JPG/GIF) upload control to the WordPress Customizer
 * under a "Logo" section, storing the image URL as the 'logo' theme mod.
 *
 * Enable this snippet to let site administrators set a logo image via the
 * Customizer. The value is available in templates via get_theme_mod('logo').
 * For SVG logos, use the LogoSvg snippet instead (or alongside this one).
 */
class Logo implements SnippetInterface {

	/**
	 * Registers the Customizer image control for the logo.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
	}

	/**
	 * Adds a "Logo" Customizer section (if it doesn't exist) with an image
	 * upload control for the site logo.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! $wp_customize->get_section( 'logo' ) ) {
			$wp_customize->add_section( 'logo', [
				'title'    => \_x( "Logo", "Customizer", THEMENAME ),
				'priority' => 30,
			] );
		}

		$wp_customize->add_setting( 'logo', [ 'default' => '' ] );

		$wp_customize->add_control( new \WP_Customize_Image_Control( $wp_customize,
			'logo', [
				'label'   => \__( "Logo", THEMENAME ),
				'section' => 'logo',
			] ) );
	}
}
