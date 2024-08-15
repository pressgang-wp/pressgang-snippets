<?php

namespace PressGang\Snippets;

class Pinterest implements SnippetInterface {

	/**
	 * Constructor to initialize hooks.
	 *
	 * @param array $args Arguments passed from the parent class or other sources.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );
	}

	/**
	 * Add Pinterest verification to the WordPress customizer.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WordPress Customizer object.
	 *
	 * @return void
	 */
	public function customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['pinterest'] ) ) {
			$wp_customize->add_section( 'pinterest', [
				'title' => __( "Pinterest", THEMENAME ),
			] );
		}

		$wp_customize->add_setting(
			'pinterest_verification_id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'pinterest_verification_id', [
				'label'   => __( "Verification", THEMENAME ),
				'section' => 'pinterest',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Output the Pinterest verification meta tag in the head section.
	 *
	 * @return void
	 */
	public function script(): void {
		$pinterest_verification_id = sanitize_text_field( \get_theme_mod( 'pinterest_verification_id' ) );

		if ( $pinterest_verification_id ) {
			echo sprintf(
				'<meta name="p:domain_verify" content="%s" />',
				esc_attr( $pinterest_verification_id )
			);
		}
	}
}