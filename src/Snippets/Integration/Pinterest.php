<?php


namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds a Pinterest domain verification meta tag to wp_head and provides a
 * Customizer field for the verification ID.
 *
 * Enable this snippet to verify site ownership with Pinterest. Enter the
 * verification ID in the Customizer under the "Pinterest" section.
 */
class Pinterest implements SnippetInterface {

	/**
	 * Registers the Customizer control and hooks the meta tag output into
	 * wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_action( 'wp_head', [ $this, 'add_meta_tag' ] );
	}

	/**
	 * Adds a "Pinterest" Customizer section with a text field for the
	 * verification ID.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! $wp_customize->get_section( 'pinterest' ) ) {
			$wp_customize->add_section( 'pinterest', [
				'title' => \__( "Pinterest", THEMENAME ),
			] );
		}

		$wp_customize->add_setting( 'pinterest_verification_id', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'pinterest_verification_id', [
				'label'   => \__( "Verification", THEMENAME ),
				'section' => 'pinterest',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Outputs the Pinterest domain verification meta tag when a verification
	 * ID is configured.
	 *
	 * @return void
	 */
	public function add_meta_tag(): void {
		$pinterest_verification_id = \sanitize_text_field( \get_theme_mod( 'pinterest_verification_id' ) );

		if ( $pinterest_verification_id ) {
			printf(
				'<meta name="p:domain_verify" content="%s" />',
				\esc_attr( $pinterest_verification_id )
			);
		}
	}
}