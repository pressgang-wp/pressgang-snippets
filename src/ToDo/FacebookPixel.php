<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\__;
use function PressGang\Snippets\add_action;
use function PressGang\Snippets\get_theme_mod;
use function PressGang\Snippets\is_user_logged_in;

class FacebookPixel {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'customize_register', [ $this, 'customizer' ] );
		add_action( 'wp_head', [ $this, 'script' ] );
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {
		if ( ! isset( $wp_customize->sections['facebook'] ) ) {
			$wp_customize->add_section( 'facebook', [
				'title' => __( "Facebook", THEMENAME ),
			] );
		}

		// tracking id

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

		// track logged in users?

		$wp_customize->add_setting(
			'facebook-track-logged-in',
			[
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
	public function script() {
		$track_logged_in = get_theme_mod( 'facebook-track-logged-in' );

		if ( $track_logged_in || ( ! $track_logged_in && ! is_user_logged_in() ) ) {
			if ( $facebook_pixel_id = urlencode( get_theme_mod( 'facebook-pixel-id' ) ) ) {
				\Timber\Timber::render( 'facebook-pixel.twig', [
					'facebook_pixel_id' => $facebook_pixel_id,
				] );
			}
		}
	}

}

new FacebookPixel();
