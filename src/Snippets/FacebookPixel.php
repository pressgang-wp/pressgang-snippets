<?php


namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Integrates the Facebook (Meta) Pixel tracking script into the site by adding
 * a Customizer field for the Pixel ID and injecting the tracking snippet into
 * wp_head.
 *
 * Enable this snippet to track page views and conversions via Facebook Ads.
 * The Pixel ID and a "track logged-in users" toggle are configured in the
 * Customizer under the "Facebook" section.
 */
class FacebookPixel implements SnippetInterface {

	/**
	 * Registers Customizer controls for the Facebook Pixel ID and a
	 * logged-in-user tracking toggle, and hooks the tracking script into
	 * wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );
	}

	/**
	 * Adds the Facebook Pixel ID setting and a "track logged-in users"
	 * checkbox to the Customizer under the shared "Facebook" section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['facebook'] ) ) {
			$wp_customize->add_section( 'facebook', [
				'title' => \__( "Facebook", THEMENAME ),
			] );
		}

		$wp_customize->add_setting(
			'facebook-pixel-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'facebook-pixel-id', [
				'label'   => \__( "Facebook Pixel ID", THEMENAME ),
				'section' => 'facebook',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting(
			'facebook-track-logged-in', [
				'default' => 0,
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'facebook-track-logged-in', [
				'label'   => \__( "Track Logged In Users?", THEMENAME ),
				'section' => 'facebook',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * Outputs the Facebook Pixel tracking script via the
	 * snippets/facebook-pixel.twig template. Skips output for logged-in users
	 * unless the "track logged-in users" Customizer option is enabled.
	 *
	 * @return void
	 */
	public function script(): void {
		$track_logged_in = \get_theme_mod( 'facebook-track-logged-in' );

		if ( $track_logged_in || ! \is_user_logged_in() ) {
			if ( $facebook_pixel_id = urlencode( \get_theme_mod( 'facebook-pixel-id' ) ) ) {
				Timber::render( 'snippets/facebook-pixel.twig', [
					'facebook_pixel_id' => $facebook_pixel_id,
				] );
			}
		}
	}
}
