<?php


namespace PressGang\Snippets\Google;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

/**
 * Integrates Google Analytics (GA4 / gtag.js) into the theme by adding
 * Customizer fields for the tracking ID and a "track logged-in users" toggle,
 * then injecting the tracking script into wp_head.
 *
 * Enable this snippet to add Google Analytics page-view tracking. The tracking
 * ID is entered in the Customizer under the "Google" section.
 */
class Analytics implements SnippetInterface {

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
		$this->ensure_google_section_exists( $wp_customize );
		$this->add_tracking_id_setting( $wp_customize );
		$this->add_track_logged_in_setting( $wp_customize );
	}

	/**
	 * Outputs the Google Analytics tracking script via the
	 * snippets/google/analytics.twig template. Skips output for logged-in
	 * users unless the "track logged-in users" option is enabled.
	 *
	 * @return void
	 */
	public function script(): void {
		if ( ! $this->should_render_for_current_user() ) {
			return;
		}

		$tracking_id = $this->get_tracking_id();
		if ( ! $tracking_id ) {
			return;
		}

		$this->render_template( 'snippets/google/analytics.twig', [
			'google_analytics_id' => $tracking_id,
		] );
	}

	/**
	 * Ensure the shared "Google" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_google_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( isset( $wp_customize->sections['google'] ) ) {
			return;
		}

		$wp_customize->add_section( 'google', [
			'title' => \__( "Google", THEMENAME ),
		] );
	}

	/**
	 * Register the Google Analytics ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_tracking_id_setting( \WP_Customize_Manager $wp_customize ): void {
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
	 * Get the configured tracking ID.
	 *
	 * @return string
	 */
	private function get_tracking_id(): string {
		return (string) \get_theme_mod( 'google-analytics-id' );
	}

	/**
	 * Determine whether analytics should render for the current user.
	 *
	 * @return bool
	 */
	private function should_render_for_current_user(): bool {
		$track_logged_in = \get_theme_mod( 'google-analytics-track-logged-in' );

		return ! \is_user_logged_in() || $track_logged_in;
	}

	/**
	 * Renders a Twig template via Timber.
	 *
	 * Extracted as a testability seam so tests can override this method
	 * via anonymous subclass instead of mocking the Timber static call.
	 *
	 * @param string               $template Template path.
	 * @param array<string, mixed> $context  Template context.
	 *
	 * @return void
	 */
	protected function render_template( string $template, array $context ): void {
		Timber::render( $template, $context );
	}
}
