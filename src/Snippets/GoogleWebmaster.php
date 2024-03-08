<?php

namespace PressGang\Snippets;

/**
 * Integrates Google Webmaster verification code setting into the WordPress Customizer.
 *
 * This class allows site administrators to easily add their Google Webmaster verification code to their site
 * through the WordPress Customizer.
 *
 * The verification code is used to verify ownership of the site with Google Webmaster Tools.
 */
class GoogleWebmaster implements SnippetInterface {

	/**
	 * Constructor for GoogleWebmaster.
	 *
	 * Registers an action with WordPress to add a custom field to the Customizer for the Google Webmaster verification code.
	 *
	 * @param array $args Arguments for the constructor, allowing for future expansion or customization.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
	}

	/**
	 * Adds Google Webmaster verification code setting to the WordPress Customizer.
	 *
	 * Creates a new section in the Customizer if it doesn't exist and adds a setting for the Google verification code.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WordPress Customizer object, providing access to the Customizer's API.
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => \__( "Google", THEMENAME ),
			] );
		}

		$wp_customize->add_setting(
			'google-verification-code',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-verification-code', [
				'label'       => \__( "Google Webmaster Verification Code", THEMENAME ),
				'description' => sprintf( \__( "See %s", THEMENAME ), 'https://goo.gl/kXrMha' ),
				'section'     => 'google',
				'type'        => 'text',
			] ) );
	}
}