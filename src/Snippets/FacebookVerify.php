<?php


namespace PressGang\Snippets;

/**
 * Adds a Facebook Domain Verification meta tag to the site's <head> so that
 * Facebook can verify domain ownership for Business Manager features.
 *
 * Enable this snippet when you need to verify your domain with Facebook
 * Business Manager. The verification code is entered in the Customizer under
 * the "Facebook" section.
 */
class FacebookVerify implements SnippetInterface {

	private const OPTION_NAME = 'facebook_domain_verification';
	private const SECTION_ID = 'facebook';

	/**
	 * Registers the Customizer setting for the verification code and hooks
	 * the meta tag output into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args = [] ) {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_action( 'wp_head', [ $this, 'add_meta_tag' ] );
	}

	/**
	 * Adds a text field for the Facebook domain verification code to the
	 * Customizer under the shared "Facebook" section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! $wp_customize->get_section( self::SECTION_ID ) ) {
			$wp_customize->add_section( self::SECTION_ID, [
				'title' => \esc_html__( 'Facebook', 'pressgang' ),
			] );
		}

		$wp_customize->add_setting(
			self::OPTION_NAME,
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control(
			$wp_customize,
			self::OPTION_NAME,
			[
				'label'       => \esc_html__( 'Facebook Domain Verification', 'pressgang' ),
				'description' => \wp_kses_post( 'See <a href="https://bit.ly/3eUd2fI" target="_blank">Facebook Domain Verification Guide</a>' ),
				'section'     => self::SECTION_ID,
			]
		) );
	}

	/**
	 * Outputs a <meta name="facebook-domain-verification"> tag in <head>
	 * when a verification code has been configured.
	 *
	 * @return void
	 */
	public function add_meta_tag(): void {
		$verification_code = \get_theme_mod( self::OPTION_NAME );

		if ( ! $verification_code ) {
			return;
		}

		printf(
			'<meta name="facebook-domain-verification" content="%s" />',
			\esc_attr( $verification_code )
		);
	}
}
