<?php

namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

if ( ! defined( 'EXPLICIT_CONSENT' ) ) {
	define( 'EXPLICIT_CONSENT', false );
}

/**
 * Integrates the X (Twitter) Pixel by adding Customizer fields for the pixel
 * ID and a "track logged-in users" toggle, then injecting the base pixel
 * script into wp_head.
 *
 * Enable this snippet to track conversions and page views for X Ads. The
 * pixel ID is entered in the Customizer under the "Twitter" section.
 * If EXPLICIT_CONSENT is true, the script only renders when a "cookie-consent"
 * cookie is present and truthy.
 */
class TwitterPixel implements SnippetInterface {

	private bool $consented;

	/**
	 * Registers Customizer controls for the pixel ID and hooks the tracking
	 * script into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );

		$this->consented = $this->resolve_cookie_consent();
	}

	/**
	 * Adds settings for the pixel ID and logged-in tracking toggle to the
	 * Customizer under the shared "Twitter" section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_twitter_section_exists( $wp_customize );
		$this->add_pixel_id_setting( $wp_customize );
		$this->add_track_logged_in_setting( $wp_customize );
	}

	/**
	 * Outputs the X (Twitter) Pixel script via the
	 * snippets/integration/twitter-pixel.twig template. Skips output for
	 * logged-in users unless the "track logged-in users" option is enabled.
	 *
	 * @return void
	 */
	public function script(): void {
		if ( ! $this->should_render_for_current_user() || ! $this->has_consent() ) {
			return;
		}

		$pixel_id = $this->get_pixel_id();
		if ( ! $pixel_id ) {
			return;
		}

		$this->render_template( 'snippets/integration/twitter-pixel.twig', [
			'twitter_pixel_id' => $pixel_id,
		] );
	}

	/**
	 * Ensure the "Twitter" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_twitter_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( isset( $wp_customize->sections['twitter'] ) ) {
			return;
		}

		$wp_customize->add_section( 'twitter', [
			'title' => \__( "Twitter", THEMENAME ),
		] );
	}

	/**
	 * Register the pixel ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_pixel_id_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting(
			'twitter-pixel-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'twitter-pixel-id', [
				'label'   => \__( "Twitter Pixel ID", THEMENAME ),
				'section' => 'twitter',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Register the "track logged-in users" setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_track_logged_in_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting(
			'twitter-track-logged-in',
			[
				'default' => 0,
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'twitter-track-logged-in', [
				'label'   => \__( "Track Logged In Users?", THEMENAME ),
				'section' => 'twitter',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * Get the configured pixel ID.
	 *
	 * @return string
	 */
	private function get_pixel_id(): string {
		return (string) \get_theme_mod( 'twitter-pixel-id' );
	}

	/**
	 * Determine whether tracking should render for the current user.
	 *
	 * @return bool
	 */
	private function should_render_for_current_user(): bool {
		$track_logged_in = \get_theme_mod( 'twitter-track-logged-in' );

		return ! \is_user_logged_in() || $track_logged_in;
	}

	/**
	 * Determine whether explicit consent is required and has been granted.
	 *
	 * @return bool
	 */
	private function has_consent(): bool {
		return ! EXPLICIT_CONSENT || $this->consented;
	}

	/**
	 * Resolve the cookie-consent signal when explicit consent is required.
	 *
	 * @return bool
	 */
	private function resolve_cookie_consent(): bool {
		if ( ! isset( $_COOKIE['cookie-consent'] ) ) {
			return false;
		}

		return (bool) $_COOKIE['cookie-consent'];
	}

	/**
	 * @param string               $template Template path.
	 * @param array<string, mixed> $context  Template context.
	 *
	 * @return void
	 */
	protected function render_template( string $template, array $context ): void {
		Timber::render( $template, $context );
	}
}
