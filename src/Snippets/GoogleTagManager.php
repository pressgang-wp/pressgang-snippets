<?php

namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Integrates Google Tag Manager into a WordPress theme.
 *
 * This class facilitates adding Google Tag Manager (GTM) to a WordPress site by providing a Customizer option
 * for entering a GTM ID and injecting the necessary GTM script and no-script fallback into the site's head and body.
 */
class GoogleTagManager implements SnippetInterface {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ], 500 );
		\add_action( 'wp_body_open', [ $this, 'no_script' ] );
	}

	/**
	 * Adds a section and settings to the WordPress Customizer for configuring Google Tag Manager.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance, used for adding sections and settings.
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => __( "Google", THEMENAME ),
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
				'label'   => __( "Google Tag Manager ID", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting(
			'google-tag-manager-track-logged-in', [
			'default' => 0,
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-tag-manager-track-logged-in', [
				'label'   => __( "Tag Manager Track Logged In Users?", THEMENAME ),
				'section' => 'google',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * Renders the Google Tag Manager script in the site's head.
	 *
	 * This method checks for a GTM ID from the theme's Customizer setting and renders the GTM script if present.
	 */
	public function script(): void {
		if ( $google_tag_manager_id = \get_theme_mod( 'google-tag-manager-id' ) ) {
			Timber::render( 'snippets/google-tag-manager.twig', [
				'google_tag_manager_id' => $google_tag_manager_id,
			] );
		}
	}

	/**
	 * Renders the Google Tag Manager no-script fallback in the site's body.
	 *
	 * This method provides a no-script fallback for GTM, ensuring tracking functionality in environments where
	 * JavaScript is disabled. It renders the fallback if a GTM ID is present in the theme's Customizer setting.
	 */
	public function no_script(): void {
		$track_logged_in = \get_theme_mod( 'google-tag-manager-track-logged-in' );

		if ( $track_logged_in || \is_user_logged_in() ) {
			if ( $google_tag_manager_id = \get_theme_mod( 'google-tag-manager-id' ) ) {
				Timber::render( 'snippets/google-tag-manager-no-script.twig', [
					'google_tag_manager_id' => $google_tag_manager_id,
				] );
			}
		}
	}

}