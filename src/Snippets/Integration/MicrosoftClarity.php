<?php

namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

if ( ! defined( 'EXPLICIT_CONSENT' ) ) {
	define( 'EXPLICIT_CONSENT', false );
}

/**
 * Integrates Microsoft Clarity by adding Customizer fields for the Clarity
 * project ID and a "track logged-in users" toggle, then injecting the Clarity
 * tracking script into wp_head.
 *
 * Enable this snippet to record Clarity session replays and heatmaps. The
 * project ID is entered in the Customizer under the "Microsoft" section.
 * If EXPLICIT_CONSENT is true, the script only renders when a "cookie-consent"
 * cookie is present and truthy.
 */
class MicrosoftClarity implements SnippetInterface {

	private bool $consented;

	/**
	 * Registers Customizer controls for Clarity and hooks the tracking script
	 * into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );

		$this->consented = $this->resolve_cookie_consent();
	}

	/**
	 * Adds settings for the Clarity project ID and logged-in tracking toggle
	 * to the Customizer under the shared "Microsoft" section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_microsoft_section_exists( $wp_customize );
		$this->add_clarity_id_setting( $wp_customize );
		$this->add_track_logged_in_setting( $wp_customize );
	}

	/**
	 * Outputs the Microsoft Clarity tracking script via the
	 * snippets/integration/microsoft-clarity.twig template. Skips output
	 * for logged-in users unless the "track logged-in users" option is enabled.
	 *
	 * @return void
	 */
	public function script(): void {
		if ( ! $this->should_render_for_current_user() || ! $this->has_consent() ) {
			return;
		}

		$clarity_id = $this->get_clarity_id();
		if ( ! $clarity_id ) {
			return;
		}

		$this->render_template( 'snippets/integration/microsoft-clarity.twig', [
			'clarity_id' => $clarity_id,
		] );
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
	 * Register the Clarity ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_clarity_id_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting(
			'clarity-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'clarity-id', [
				'label'       => \__( "Clarity ID", THEMENAME ),
				'description' => sprintf( \__( "See %s", THEMENAME ), 'https://learn.microsoft.com/en-us/clarity/' ),
				'section'     => 'microsoft',
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
			'clarity-track-logged-in',
			[
				'default' => 0,
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'clarity-track-logged-in', [
				'label'   => \__( "Track Logged In Users?", THEMENAME ),
				'section' => 'microsoft',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * Get the configured Clarity project ID.
	 *
	 * @return string
	 */
	private function get_clarity_id(): string {
		return (string) \get_theme_mod( 'clarity-id' );
	}

	/**
	 * Determine whether tracking should render for the current user.
	 *
	 * @return bool
	 */
	private function should_render_for_current_user(): bool {
		$track_logged_in = \get_theme_mod( 'clarity-track-logged-in' );

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
