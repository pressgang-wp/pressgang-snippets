<?php


namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Integrates Google Analytics (GA4 / gtag.js) into the theme by adding
 * Customizer fields for the tracking ID and a "track logged-in users" toggle,
 * then injecting the tracking script into wp_head.
 *
 * Enable this snippet to add Google Analytics page-view tracking. The tracking
 * ID is entered in the Customizer under the "Google" section.
 */
class GoogleAnalytics implements SnippetInterface {

	/**
	 * Registers Customizer controls for the Google Analytics ID and
	 * logged-in-user tracking toggle, and hooks the script into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );
	}

	/**
	 * Adds the Google Analytics ID setting and a "track logged-in users"
	 * checkbox to the Customizer under the shared "Google" section.
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
			'google-analytics-id', [
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-analytics-id', [
				'label'   => \__( "Google Analytics ID", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting(
			'google-analytics-track-logged-in', [
			'default' => 0,
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-analytics-track-logged-in', [
				'label'   => \__( "Analytics Track Logged In Users?", THEMENAME ),
				'section' => 'google',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * Outputs the Google Analytics tracking script via the
	 * snippets/google-analytics.twig template. Skips output for logged-in
	 * users unless the "track logged-in users" option is enabled.
	 *
	 * @return void
	 */
	public function script(): void {
		$track_logged_in = \get_theme_mod( 'google-analytics-track-logged-in' );

		if ( ! \is_user_logged_in() || $track_logged_in ) {
			if ( $google_analytics_id = \get_theme_mod( 'google-analytics-id' ) ) {
				Timber::render( 'snippets/google-analytics.twig', [
					'google_analytics_id' => $google_analytics_id,
				] );
			}
		}
	}
}
