<?php


namespace PressGang\Snippets;

/**
 * Adds a Google Search Console (Webmaster Tools) verification meta tag to the
 * site's <head> via a Customizer field, allowing site ownership verification
 * without editing theme files.
 *
 * Enable this snippet to verify your site with Google Search Console. The
 * verification code is entered in the Customizer under the "Google" section.
 */
class GoogleWebmaster implements SnippetInterface {

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
	 * Adds a text field for the Google Webmaster verification code to the
	 * Customizer under the shared "Google" section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
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

	/**
	 * Outputs the Google Webmaster verification meta tag in the <head> section.
	 * Only outputs the tag when a verification code has been entered in the
	 * Customizer.
	 *
	 * @return void
	 */
	public function add_meta_tag(): void {
		if ( $verification_code = \get_theme_mod( 'google-verification-code' ) ) {
			printf(
				'<meta name="google-site-verification" content="%s" />',
				\esc_attr( $verification_code )
			);
		}
	}
}
