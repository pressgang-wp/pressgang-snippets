<?php

namespace PressGang\Snippets;

use \Timber\Timber;

/**
 * Integrates tawk.to live chat widget into a WordPress theme.
 *
 * @see https://developer.tawk.to/
 */
class Tawkto implements SnippetInterface {

	/**
	 * Constructor.
	 *
	 * Hooks the Tawk.to widget rendering method to the WordPress footer.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_footer', [ $this, 'render' ], 100 );
	}

	/**
	 * Adds a new section for the TakwTo widget to the WordPress Customizer
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP_Customize_Manager instance.
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->get_section['tawkto'] ) ) {
			// Add a new section for Tawk.to settings
			$wp_customize->add_section( 'tawkto', [
				'title'    => \_x( "tawk.to", "Customizer", THEMENAME ),
				'priority' => 30,
			] );
		}

		$wp_customize->add_setting(
			'tawkto-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'tawkto-id', [
				'label'   => __( "tawk.to ID", THEMENAME ),
				'section' => 'tawkto',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Renders the tawk.to widget.
	 *
	 */
	public function render(): void {
		if ( $tawkto_id = \get_theme_mod( 'tawkto-id' ) ) {
			Timber::render( 'snippets/tawkto.twig', [ 'tawkto_id' => $tawkto_id ] );
		}
	}

}
