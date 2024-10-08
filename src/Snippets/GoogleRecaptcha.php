<?php

namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Class GoogleRecaptcha
 *
 * Integrates Google reCAPTCHA settings into the WordPress Customizer and provides a method to render the reCAPTCHA script.
 *
 * @package PressGang\Snippets
 */
class GoogleRecaptcha implements SnippetInterface {

	/**
	 * Constructor.
	 *
	 * Registers the customization settings and controls for Google reCAPTCHA in the WordPress Customizer.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
	}

	/**
	 * Adds Google reCAPTCHA settings and controls to the WordPress Customizer.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer object.
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_google_section_exists( $wp_customize );
		$this->add_site_key_setting( $wp_customize );
		$this->add_secret_setting( $wp_customize );
	}

	/**
	 * Ensures that a section for Google settings exists in the Customizer.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer object.
	 */
	protected function ensure_google_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => __( "Google", THEMENAME ),
			] );
		}
	}

	/**
	 * Adds a setting for the Google reCAPTCHA site key to the Customizer.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer object.
	 */
	protected function add_site_key_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting( 'google-recaptcha-site-key', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control(
			$wp_customize, 'google-recaptcha-site-key', [
				'label'   => __( "Google Recaptcha Site Key", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			]
		) );
	}

	/**
	 * Adds a setting for the Google reCAPTCHA secret key to the Customizer.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer object.
	 */
	protected function add_secret_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting( 'google-recaptcha-secret', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control(
			$wp_customize, 'google-recaptcha-secret', [
				'label'   => __( "Google Recaptcha Secret", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			]
		) );
	}

	/**
	 * Renders the Google reCAPTCHA script.
	 *
	 * This method should be called in the appropriate place to ensure the reCAPTCHA script is included on the page.
	 *
	 * @param string $form_id
	 */
	public static function render_script( string $form_id ): void {
		Timber::render( 'snippets/google-recaptcha.twig', [
			'form_id'            => $form_id,
			'recaptcha_site_key' => \get_theme_mod( 'google-recaptcha-site-key' ),
			'recaptcha_secret'   => \get_theme_mod( 'google-recaptcha-secret' )
		] );
	}
}