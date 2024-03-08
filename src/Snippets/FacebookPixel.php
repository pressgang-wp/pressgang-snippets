<?php

namespace PressGang\Snippets;

use Timber\Timber;

class FacebookPixel {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['facebook'] ) ) {
			$wp_customize->add_section( 'facebook', [
				'title' => __( "Facebook", THEMENAME ),
			] );
		}

		$wp_customize->add_setting(
			'facebook-pixel-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'facebook-pixel-id', [
				'label'   => __( "Facebook Pixel ID", THEMENAME ),
				'section' => 'facebook',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting(
			'facebook-track-logged-in', [
				'default' => 0,
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'facebook-track-logged-in', [
				'label'   => __( "Track Logged In Users?", THEMENAME ),
				'section' => 'facebook',
				'type'    => 'checkbox',
			] ) );
	}

	/**
	 * script
	 *
	 * @return void
	 */
	public function script(): void {
		$track_logged_in = \get_theme_mod( 'facebook-track-logged-in' );

		if ( $track_logged_in || ! \is_user_logged_in() ) {
			if ( $facebook_pixel_id = urlencode( \get_theme_mod( 'facebook-pixel-id' ) ) ) {
				Timber::render( 'facebook-pixel.twig', [
					'facebook_pixel_id' => $facebook_pixel_id,
				] );
			}
		}
	}
}