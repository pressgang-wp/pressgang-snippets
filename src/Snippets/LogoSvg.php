<?php


namespace PressGang\Snippets;

/**
 * Adds an SVG logo upload control to the WordPress Customizer and stores the
 * raw SVG markup as a theme mod for inline rendering in templates.
 *
 * When the Customizer is saved, this snippet reads the SVG file from the
 * filesystem and stores its contents in the 'logo_svg_raw' theme mod, so
 * templates can output the SVG inline without an additional HTTP request.
 *
 * Enable this snippet when you want an SVG logo that can be inlined in
 * templates. Assumes SVG upload support is enabled (e.g. via a sanitisation
 * plugin). For raster logos, use the Logo snippet instead.
 */
class LogoSvg implements SnippetInterface {

	/**
	 * Registers the Customizer SVG upload control and hooks into
	 * customize_save_after to store the raw SVG content.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'customize_save_after', [ $this, 'save_logo_svg_raw_content' ] );
	}

	/**
	 * Adds an SVG image upload control to the Customizer under the "Logo"
	 * section, restricted to .svg file extensions.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
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
	 * Reads the SVG file referenced by the 'logo_svg_url' theme mod and stores
	 * its raw content in the 'logo_svg_raw' theme mod for inline rendering.
	 *
	 * @return void
	 */
	public function save_logo_svg_raw_content(): void {
		$logo_svg_content = $this->fetch_logo_svg_raw_content();

		if ( $logo_svg_content ) {
			\set_theme_mod( 'logo_svg_raw', $logo_svg_content );
		}
	}

	/**
	 * Converts the SVG URL to a filesystem path and returns the file contents.
	 *
	 * @return string|null The SVG markup, or null if the file cannot be read.
	 */
	private function fetch_logo_svg_raw_content(): ?string {
		$logo_svg_url = \get_theme_mod( 'logo_svg_url', '' );

		if ( $logo_svg_url ) {
			$attachment_id = \attachment_url_to_postid( $logo_svg_url );
			$logo_svg_path = \get_attached_file( $attachment_id );

			if ( $logo_svg_path && \file_exists( $logo_svg_path ) && \is_readable( $logo_svg_path ) ) {
				return \file_get_contents( $logo_svg_path );
			}
		}

		return null;
	}

	/**
	 * Retrieves the stored inline SVG content from the 'logo_svg_raw' theme
	 * mod.
	 *
	 * @return string|null The SVG markup, or null if not available.
	 */
	public function get_logo_svg_content(): ?string {
		return \get_theme_mod( 'logo_svg_raw', null );
	}
}
