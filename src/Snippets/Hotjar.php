<?php

declare(strict_types=1);

namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Integrates Hotjar behaviour analytics into the theme by adding a Customizer
 * field for the Hotjar Site ID and injecting the Hotjar tracking script into
 * wp_head.
 *
 * Enable this snippet to install Hotjar heatmaps, recordings, and surveys on
 * your site. The Site ID is entered in the Customizer under its own "Hotjar"
 * section.
 */
class Hotjar implements SnippetInterface {

	/**
	 * Registers a Customizer control for the Hotjar Site ID and hooks the
	 * tracking script into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ], 500 );
	}

	/**
	 * Adds a "Hotjar" Customizer section with a text field for the Site ID.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['hotjar'] ) ) {
			$wp_customize->add_section( 'hotjar', [
				'title' => \__( "Hotjar", THEMENAME ),
			] );
		}

		$wp_customize->add_setting( 'hotjar-id', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'hotjar-id', [
				'label'   => \__( "Hotjar Site ID", THEMENAME ),
				'section' => 'hotjar',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Outputs the Hotjar tracking script via the snippets/hotjar.twig template
	 * when a Site ID is configured.
	 *
	 * @return void
	 */
	public function script(): void {
		if ( $hotjar_id = \get_theme_mod( 'hotjar-id' ) ) {
			Timber::render( 'snippets/hotjar.twig', [
				'hotjar_id' => $hotjar_id,
			] );
		}
	}
}
