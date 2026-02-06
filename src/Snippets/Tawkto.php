<?php

declare(strict_types=1);

namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Integrates the tawk.to live-chat widget by providing a Customizer field for
 * the tawk.to Property ID and rendering the embed script in wp_footer.
 *
 * Enable this snippet to add tawk.to live chat to your site. Enter the
 * Property ID in the Customizer under the "tawk.to" section.
 *
 * @see https://developer.tawk.to/
 */
class Tawkto implements SnippetInterface {

	/**
	 * Registers the Customizer control and hooks the widget render into
	 * wp_footer at a late priority.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_footer', [ $this, 'render' ], 100 );
	}

	/**
	 * Adds a "tawk.to" Customizer section with a text field for the
	 * Property ID.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! $wp_customize->get_section( 'tawkto' ) ) {
			$wp_customize->add_section( 'tawkto', [
				'title'    => \_x( "tawk.to", "Customizer", THEMENAME ),
				'priority' => 30,
			] );
		}

		$wp_customize->add_setting( 'tawkto-id', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'tawkto-id', [
				'label'   => \__( "tawk.to ID", THEMENAME ),
				'section' => 'tawkto',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Renders the tawk.to embed script via Twig when a Property ID is
	 * configured.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( $tawkto_id = \get_theme_mod( 'tawkto-id' ) ) {
			Timber::render( 'snippets/tawkto.twig', [ 'tawkto_id' => $tawkto_id ] );
		}
	}
}
