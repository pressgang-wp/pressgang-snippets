<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\__;
use function PressGang\Snippets\add_action;
use function PressGang\Snippets\get_theme_mod;
use function PressGang\Snippets\is_user_logged_in;

if ( ! defined( 'EXPLICIT_CONSENT' ) ) {
	define( "EXPLICIT_CONSENT", false );
}

class GoogleAnalytics {

	protected $consented = false;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'customize_register', [ $this, 'customizer' ] );
		add_action( 'wp_head', [ $this, 'script' ] );

		$this->consented = isset( $_COOKIE['cookie-consent'] ) && ! ! $_COOKIE['cookie-consent'];
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => __( "Google", THEMENAME ),
			] );
		}

		// analytics id

		$wp_customize->add_setting(
			'google-analytics-id',
			[
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

		// track logged in users?

		$wp_customize->add_setting(
			'track-logged-in',
			[
				'default' => 0,
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'track-logged-in', [
				'label'   => __( "Track Logged In Users?", THEMENAME ),
				'section' => 'google',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * script
	 *
	 * @return void
	 */
	public function script() {
		$track_logged_in = get_theme_mod( 'track-logged-in' );

		if ( ( $track_logged_in || ( ! $track_logged_in && ! is_user_logged_in() ) ) && ( ! EXPLICIT_CONSENT || $this->consented ) ) {
			if ( $google_analytics_id = get_theme_mod( 'google-analytics-id' ) ) {
				\Timber\Timber::render( 'snippets/google-analytics.twig', [
					'google_analytics_id' => $google_analytics_id,
				] );
			}
		}
	}

}

new GoogleAnalytics();
