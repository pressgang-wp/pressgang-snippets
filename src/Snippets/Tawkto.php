<?php

namespace PressGang\Snippets;

use \Timber\Timber;

/**
 * Integrates Tawk.to live chat widget into a WordPress theme.
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
		if ( ! $wp_customize->get_section( 'tawkto' ) ) {
			// Add a new section for Tawk.to settings
			$wp_customize->add_section( 'tawkto', [
				'title'    => \_x( "Tawk.to", "Customizer", THEMENAME ),
				'priority' => 30,
			] );
		}

		$wp_customize->add_setting( 'tawkto_id', [ 'default' => '' ] );

		$wp_customize->add_control( new \WP_Customize_Image_Control( $wp_customize,
			'tawkto_id', [
				'label'   => \__( "ID", THEMENAME ),
				'section' => 'tawkto',
			] ) );
	}

	/**
	 * Renders the Tawk.to widget.
	 *
	 */
	public function render(): void {
		if ( $tawkto_id = \get_theme_mod( 'tawkto_id' ) ) {
			Timber::render( 'snippets/tawkto.twig', [ 'id' => $tawkto_id ] );
		}
	}

}
