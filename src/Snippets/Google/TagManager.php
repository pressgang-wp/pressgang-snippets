<?php


namespace PressGang\Snippets\Google;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

/**
 * Integrates Google Tag Manager (GTM) into the theme by adding a Customizer
 * field for the GTM container ID and injecting the GTM script (in <head>) and
 * noscript fallback (after <body>).
 *
 * Enable this snippet to install GTM on your site. The container ID and a
 * "track logged-in users" toggle are configured in the Customizer under the
 * "Google" section.
 */
class TagManager implements SnippetInterface {

	/**
	 * Registers Customizer controls for the GTM container ID and
	 * logged-in-user toggle, and hooks the script and noscript output.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ], 500 );
		\add_action( 'wp_body_open', [ $this, 'no_script' ] );
	}

	/**
	 * Adds the GTM container ID setting and a "track logged-in users"
	 * checkbox to the Customizer under the shared "Google" section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_google_section_exists( $wp_customize );
		$this->add_container_id_setting( $wp_customize );
		$this->add_track_logged_in_setting( $wp_customize );
	}

	/**
	 * Outputs the GTM <script> tag via the snippets/google/tag-manager.twig
	 * template when a container ID is configured.
	 *
	 * @return void
	 */
	public function script(): void {
		$container_id = $this->get_container_id();
		if ( ! $container_id ) {
			return;
		}

		Timber::render( 'snippets/google/tag-manager.twig', [
			'google_tag_manager_id' => $container_id,
		] );
	}

	/**
	 * Outputs the GTM <noscript> iframe fallback via the
	 * snippets/google/tag-manager-no-script.twig template. Respects the
	 * "track logged-in users" toggle — skips output for logged-in users
	 * unless the option is enabled.
	 *
	 * @return void
	 */
	public function no_script(): void {
		if ( ! $this->should_render_for_current_user() ) {
			return;
		}

		$container_id = $this->get_container_id();
		if ( ! $container_id ) {
			return;
		}

		Timber::render( 'snippets/google/tag-manager-no-script.twig', [
			'google_tag_manager_id' => $container_id,
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
	 * Register the GTM container ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_container_id_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting(
			'google-tag-manager-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-tag-manager-id', [
				'label'   => \__( "Google Tag Manager ID", THEMENAME ),
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
			'google-tag-manager-track-logged-in', [
			'default' => 0,
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-tag-manager-track-logged-in', [
				'label'   => \__( "Tag Manager Track Logged In Users?", THEMENAME ),
				'section' => 'google',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * Get the configured GTM container ID.
	 *
	 * @return string
	 */
	private function get_container_id(): string {
		return (string) \get_theme_mod( 'google-tag-manager-id' );
	}

	/**
	 * Determine whether GTM should render for the current user.
	 *
	 * @return bool
	 */
	private function should_render_for_current_user(): bool {
		$track_logged_in = \get_theme_mod( 'google-tag-manager-track-logged-in' );

		return $track_logged_in || ! \is_user_logged_in();
	}
}
