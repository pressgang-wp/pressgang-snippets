<?php


namespace PressGang\Snippets\Google;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

/**
 * Adds Customizer fields for the Google reCAPTCHA site key and secret, and
 * provides a static render_script() method that themes can call to output
 * the reCAPTCHA widget on specific forms.
 *
 * Enable this snippet when your theme includes forms that need reCAPTCHA
 * protection. The keys are entered in the Customizer under the "Google"
 * section; the script is rendered by calling
 * Recaptcha::render_script($form_id) in your template or PHP.
 */
class Recaptcha implements SnippetInterface {

	/**
	 * Registers Customizer controls for the reCAPTCHA site key and secret
	 * under the shared "Google" section.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
	}

	/**
	 * Adds the Google reCAPTCHA site key and secret settings to the Customizer.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_google_section_exists( $wp_customize );
		$this->add_site_key_setting( $wp_customize );
		$this->add_secret_setting( $wp_customize );
	}

	/**
	 * Creates the shared "Google" Customizer section if it does not exist.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	protected function ensure_google_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => \__( "Google", THEMENAME ),
			] );
		}
	}

	/**
	 * Registers the reCAPTCHA site key Customizer setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	protected function add_site_key_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting( 'google-recaptcha-site-key', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control(
			$wp_customize, 'google-recaptcha-site-key', [
				'label'   => \__( "Google Recaptcha Site Key", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			]
		) );
	}

	/**
	 * Registers the reCAPTCHA secret key Customizer setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	protected function add_secret_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting( 'google-recaptcha-secret', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control(
			$wp_customize, 'google-recaptcha-secret', [
				'label'   => \__( "Google Recaptcha Secret", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			]
		) );
	}

	/**
	 * Renders the Google reCAPTCHA script and hidden input for a specific form.
	 * Call this method from your template or controller where the reCAPTCHA
	 * widget should appear.
	 *
	 * @param string $form_id The HTML ID of the form to protect with reCAPTCHA.
	 *
	 * @return void
	 */
	public static function render_script( string $form_id ): void {
		Timber::render( 'snippets/google/recaptcha.twig', [
			'form_id'            => $form_id,
			'recaptcha_site_key' => \get_theme_mod( 'google-recaptcha-site-key' ),
			'recaptcha_secret'   => \get_theme_mod( 'google-recaptcha-secret' )
		] );
	}
}
