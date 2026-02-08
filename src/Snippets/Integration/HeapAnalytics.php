<?php

namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

if ( ! defined( 'EXPLICIT_CONSENT' ) ) {
	define( 'EXPLICIT_CONSENT', false );
}

/**
 * Integrates Heap Analytics by adding Customizer fields for the app ID and a
 * "track logged-in users" toggle, then injecting the Heap tracking script
 * into wp_head.
 *
 * Enable this snippet to collect Heap analytics. The app ID is entered in the
 * Customizer under the "Heap Analytics" section. If EXPLICIT_CONSENT is true,
 * the script only renders when a "cookie-consent" cookie is present and truthy.
 */
class HeapAnalytics implements SnippetInterface {

	private bool $consented;

	/**
	 * Registers Customizer controls and hooks the tracking script into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );

		$this->consented = $this->resolve_cookie_consent();
	}

	/**
	 * Adds settings for the Heap app ID and logged-in tracking toggle.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_heap_section_exists( $wp_customize );
		$this->add_heap_id_setting( $wp_customize );
		$this->add_track_logged_in_setting( $wp_customize );
	}

	/**
	 * Outputs the Heap tracking script via the
	 * snippets/integration/heap-analytics.twig template.
	 *
	 * @return void
	 */
	public function script(): void {
		if ( ! $this->should_render_for_current_user() || ! $this->has_consent() ) {
			return;
		}

		$heap_id = $this->get_heap_id();
		if ( ! $heap_id ) {
			return;
		}

		$this->render_template( 'snippets/integration/heap-analytics.twig', [
			'heap_analytics_id' => $heap_id,
		] );
	}

	/**
	 * Ensure the "Heap Analytics" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_heap_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( $wp_customize->get_section( 'heap-analytics' ) ) {
			return;
		}

		$wp_customize->add_section( 'heap-analytics', [
			'title' => \__( "Heap Analytics", THEMENAME ),
		] );
	}

	/**
	 * Register the Heap app ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_heap_id_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting(
			'heap-analytics-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'heap-analytics-id', [
				'label'   => \__( "Heap App ID", THEMENAME ),
				'section' => 'heap-analytics',
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
			'heap-analytics-track-logged-in',
			[
				'default' => 0,
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'heap-analytics-track-logged-in', [
				'label'   => \__( "Track Logged In Users?", THEMENAME ),
				'section' => 'heap-analytics',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * Get the configured Heap app ID.
	 *
	 * @return string
	 */
	private function get_heap_id(): string {
		return (string) \get_theme_mod( 'heap-analytics-id' );
	}

	/**
	 * Determine whether tracking should render for the current user.
	 *
	 * @return bool
	 */
	private function should_render_for_current_user(): bool {
		$track_logged_in = \get_theme_mod( 'heap-analytics-track-logged-in' );

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
