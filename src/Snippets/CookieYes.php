<?php

namespace PressGang\Snippets;

/**
 * CookieYes Class
 *
 * This class is designed to integrate the CookieYes service into a WordPress theme. It allows theme users to manage CookieYes settings through the WordPress Customizer and automatically includes the necessary CookieYes script in the website header.
 */
class CookieYes implements SnippetInterface {

	/**
	 * Constructor
	 *
	 * Registers two WordPress actions to integrate CookieYes settings into the WordPress Customizer and enqueue the CookieYes script into the site header.
	 *
	 * @param array $args An associative array of initialization parameters (currently not used but can be expanded for future use).
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'cookieyes_header_script' ] );
	}

	/**
	 * Add to customizer
	 *
	 * Adds a new section to the WordPress Customizer specifically for configuring CookieYes. This includes a setting for the CookieYes ID, allowing it to be dynamically updated.
	 *
	 * @param \WP_Customize_Manager $wp_customize WordPress Customizer object, providing APIs to add sections, settings, and controls.
	 */
	public function add_to_customizer( $wp_customize ): void {
		if ( ! $wp_customize->get_section( 'cookieyes' ) ) {
			$wp_customize->add_section( 'cookieyes', [
				'title' => \_x( "CookieYes", 'Customizer', THEMENAME ),
			] );
		}

		$wp_customize->add_setting(
			'cookieyes-id', [
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'cookieyes-id', [
				'label'       => \_x( "CookieYes ID", 'Customizer', THEMENAME ),
				'description' => sprintf( \_x( "See %s", 'Customizer', THEMENAME ), 'https://www.cookieyes.com/' ),
				'section'     => 'cookieyes',
			] ) );
	}

	/**
	 * CookieYes Header Script
	 *
	 * Enqueues the CookieYes script into the WordPress site header using the specified CookieYes ID from the theme customization settings.
	 *
	 * @return void
	 */
	public function cookieyes_header_script() {
		if ( $cookieyes_id = \get_theme_mod( 'cookieyes-id' ) ) {
			\wp_enqueue_script( 'cookie-yes', "https://cdn-cookieyes.com/client_data/{$cookieyes_id}/script.js", [] );
		}
	}

}
