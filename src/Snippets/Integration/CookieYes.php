<?php


namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;

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
		$this->ensure_cookieyes_section_exists( $wp_customize );
		$this->add_cookieyes_id_setting( $wp_customize );
	}

	/**
	 * Enqueues the CookieYes client script if a site ID has been configured
	 * in the Customizer.
	 *
	 * @return void
	 */
	public function cookieyes_header_script(): void {
		$cookieyes_id = $this->get_cookieyes_id();
		if ( ! $cookieyes_id ) {
			return;
		}

		$script_url = $this->build_script_url( $cookieyes_id );

		\wp_register_script( 'cookieyes', $script_url, [], null );
		\wp_enqueue_script( 'cookieyes' );
	}

	/**
	 * Ensure the "CookieYes" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_cookieyes_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( $wp_customize->get_section( 'cookieyes' ) ) {
			return;
		}

		$wp_customize->add_section( 'cookieyes', [
			'title' => \_x( "CookieYes", 'Customizer', THEMENAME ),
		] );
	}

	/**
	 * Register the CookieYes ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_cookieyes_id_setting( \WP_Customize_Manager $wp_customize ): void {
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
	 * Get the configured CookieYes site ID.
	 *
	 * @return string
	 */
	private function get_cookieyes_id(): string {
		return \sanitize_text_field( \get_theme_mod( 'cookieyes-id' ) );
	}

	/**
	 * Build the CookieYes script URL for a site ID.
	 *
	 * @param string $cookieyes_id
	 *
	 * @return string
	 */
	private function build_script_url( string $cookieyes_id ): string {
		return \esc_url( "https://cdn-cookieyes.com/client_data/{$cookieyes_id}/script.js" );
	}
}
