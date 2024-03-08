<?php

namespace PressGang\Snippets;

use Timber\Timber;

class GoogleAnalytics implements SnippetInterface {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );
	}

	/**
	 * Add to customizer
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => __( "Google", THEMENAME ),
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
				'label'   => __( "Google Analytics ID", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting(
			'google-analytics-track-logged-in', [
			'default' => 0,
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-analytics-track-logged-in', [
				'label'   => __( "Analytics Track Logged In Users?", THEMENAME ),
				'section' => 'google',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * script
	 *
	 * @return void
	 */
	public function script(): void {
		$track_logged_in = \get_theme_mod( 'google-analytics-track-logged-in' );

		if ( $track_logged_in || \is_user_logged_in() ) {
			if ( $google_analytics_id = \get_theme_mod( 'google-analytics-id' ) ) {
				Timber::render( 'snippets/google-analytics.twig', [
					'google_analytics_id' => $google_analytics_id,
				] );
			}
		}
	}
}