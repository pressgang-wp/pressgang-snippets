<?php

declare(strict_types=1);

namespace PressGang\Snippets;

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
class GoogleTagManager implements SnippetInterface {

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
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => \__( "Google", THEMENAME ),
			] );
		}

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
	 * Outputs the GTM <script> tag via the snippets/google-tag-manager.twig
	 * template when a container ID is configured.
	 *
	 * @return void
	 */
	public function script(): void {
		if ( $google_tag_manager_id = \get_theme_mod( 'google-tag-manager-id' ) ) {
			Timber::render( 'snippets/google-tag-manager.twig', [
				'google_tag_manager_id' => $google_tag_manager_id,
			] );
		}
	}

	/**
	 * Outputs the GTM <noscript> iframe fallback via the
	 * snippets/google-tag-manager-no-script.twig template. Respects the
	 * "track logged-in users" toggle — skips output for logged-in users
	 * unless the option is enabled.
	 *
	 * @return void
	 */
	public function no_script(): void {
		$track_logged_in = \get_theme_mod( 'google-tag-manager-track-logged-in' );

		if ( $track_logged_in || ! \is_user_logged_in() ) {
			if ( $google_tag_manager_id = \get_theme_mod( 'google-tag-manager-id' ) ) {
				Timber::render( 'snippets/google-tag-manager-no-script.twig', [
					'google_tag_manager_id' => $google_tag_manager_id,
				] );
			}
		}
	}
}
