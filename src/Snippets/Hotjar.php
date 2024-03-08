<?php

namespace PressGang\Snippets;

use \Timber\Timber;

/**
 * Integrates Hotjar analytics tracking.
 *
 * This class provides functionality to add a customizer option for Hotjar tracking, allowing users to easily configure
 * Hotjar tracking by entering their Hotjar ID and optionally deciding whether to track logged-in users.
 */
class Hotjar implements SnippetInterface {

	/**
	 * Constructor
	 *
	 * Adds necessary actions to integrate Hotjar settings into the WordPress Customizer and to inject the Hotjar
	 * tracking script into the site's head section based on those settings.
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );
	}

	/**
	 * Adds Hotjar configuration options to the WordPress Customizer.
	 *
	 * This method introduces a new section in the Customizer for Hotjar where users can enter their Hotjar ID and
	 * specify whether to track logged-in users.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance, used for adding sections and settings.
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['hotjar'] ) ) {
			$wp_customize->add_section( 'hotjar', [
				'title' => __( "Hotjar", THEMENAME ),
			] );
		}

		$wp_customize->add_setting(
			'hotjar_id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'hotjar-id', [
				'label'   => __( "Hotjar ID", THEMENAME ),
				'section' => 'hotjar',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting(
			'hotjar-track-logged-in', [
				'default' => 0,
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'hotjar-track-logged-in', [
				'label'   => __( "Track Logged In Users?", THEMENAME ),
				'section' => 'hotjar',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * Outputs the Hotjar tracking script if conditions are met.
	 *
	 * This method checks if tracking for logged-in users is enabled or if the current visitor is not logged in, and if
	 * a Hotjar ID is set. If these conditions are satisfied, it renders the Hotjar tracking script using Timber.
	 */
	public function script(): void {
		$track_logged_in = get_theme_mod( 'hotjar-track-logged-in' );

		if ( $track_logged_in || ! is_user_logged_in() ) {
			if ( $hotjar_id = urlencode( \get_theme_mod( 'hotjar-id' ) ) ) {
				Timber::render( 'snippets/hotjar.twig', [
					'hotjar_id' => $hotjar_id,
				] );
			}
		}
	}

}
