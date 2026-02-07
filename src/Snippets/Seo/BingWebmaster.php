<?php

namespace PressGang\Snippets\Seo;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds a Microsoft Bing Webmaster Tools verification meta tag to the site's
 * <head> via a Customizer field, allowing site ownership verification without
 * editing theme files.
 *
 * Enable this snippet to verify your site with Bing Webmaster Tools. The
 * verification code is entered in the Customizer under the "Microsoft" section.
 */
class BingWebmaster implements SnippetInterface {

	/**
	 * Registers the Customizer control for the verification code and hooks
	 * the meta tag output into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'add_meta_tag' ] );
	}

	/**
	 * Adds a text field for the Bing Webmaster Tools verification code to the
	 * Customizer under the shared "Microsoft" section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_microsoft_section_exists( $wp_customize );
		$this->add_verification_code_setting( $wp_customize );
	}

	/**
	 * Outputs the Bing Webmaster Tools verification meta tag in the <head>
	 * section. Only outputs the tag when a verification code has been entered
	 * in the Customizer.
	 *
	 * @return void
	 */
	public function add_meta_tag(): void {
		$verification_code = $this->get_verification_code();
		if ( ! $verification_code ) {
			return;
		}

		printf(
			'<meta name="msvalidate.01" content="%s" />',
			\esc_attr( $verification_code )
		);
	}

	/**
	 * Ensure the "Microsoft" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_microsoft_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( isset( $wp_customize->sections['microsoft'] ) ) {
			return;
		}

		$wp_customize->add_section( 'microsoft', [
			'title' => \__( "Microsoft", THEMENAME ),
		] );
	}

	/**
	 * Register the verification code setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_verification_code_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting(
			'bing_verification_code',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'bing_verification_code', [
				'label'       => \__( "Bing Verification Code", THEMENAME ),
				'description' => sprintf( \__( "See %s", THEMENAME ), 'https://www.bing.com/webmasters/' ),
				'section'     => 'microsoft',
			] ) );
	}

	/**
	 * Get the configured verification code.
	 *
	 * @return string
	 */
	private function get_verification_code(): string {
		return (string) \get_theme_mod( 'bing_verification_code' );
	}
}
