<?php


namespace PressGang\Snippets;

/**
 * Integrates the CookieYes cookie consent banner by adding a Customizer field
 * for the CookieYes site ID and enqueuing the CookieYes client script.
 *
 * Enable this snippet to add GDPR/cookie-consent functionality via CookieYes
 * without editing theme templates. The site ID is entered in the Customizer.
 */
class CookieYes implements SnippetInterface {

	/**
	 * Registers Customizer controls for the CookieYes ID and hooks the script
	 * enqueue into wp_enqueue_scripts.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'cookieyes_header_script' ] );
	}

	/**
	 * Adds a "CookieYes" Customizer section with a text field for the site ID.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
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
	 * Enqueues the CookieYes client script if a site ID has been configured
	 * in the Customizer.
	 *
	 * @return void
	 */
	public function cookieyes_header_script(): void {
		if ( $cookieyes_id = \sanitize_text_field( \get_theme_mod( 'cookieyes-id' ) ) ) {
			$script_url = \esc_url( "https://cdn-cookieyes.com/client_data/{$cookieyes_id}/script.js" );

			\wp_register_script( 'cookieyes', $script_url, [], null );
			\wp_enqueue_script( 'cookieyes' );
		}
	}
}
