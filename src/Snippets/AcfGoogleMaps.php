<?php

namespace PressGang\Snippets;

/**
 * Class AcfGoogleMaps
 *
 * This class integrates the Advanced Custom Fields (ACF) Google Maps functionality with the WordPress theme customizer,
 * allowing for easy configuration of the Google Maps API key.
 */
class AcfGoogleMaps implements SnippetInterface {

	/**
	 * AcfGoogleMaps constructor.
	 * Adds hooks for setting and getting the Google Maps API key and integrates with the WP customizer.
	 *
	 * @param array $args Additional arguments for the constructor.
	 */
	public function __construct( array $args ) {
		\add_action( 'acf/init', [ $this, 'set_google_maps_key' ] );
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_filter( 'acf/fields/google_map/api', [ $this, 'get_google_maps_key' ] );
	}

	/**
	 * Sets the Google Maps API key in ACF settings.
	 *
	 * @hooked 'acf/init'.
	 */
	public function set_google_maps_key() {
		if ( $google_maps_key = filter_var( \get_theme_mod( 'acf_google_maps_key' ),
			FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
			\acf_update_setting( 'acf_google_maps_key', $google_maps_key );
		}
	}


	/**
	 * Filters the ACF Google Maps field API key.
	 *
	 * Added after ACF update
	 * see -
	 * https://support.advancedcustomfields.com/forums/topic/google-map-not-displaying-on-wp-backend/
	 *
	 * @hooked 'acf/fields/google_map/api'
	 *
	 * @param array $api The ACF Google Maps field API parameters.
	 *
	 * @return array Modified API parameters with the correct API key.
	 */
	public function get_google_maps_key( $api ) {
		if ( $google_maps_key = filter_var( get_theme_mod( 'acf_google_maps_key' ),
			FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
			$api['key'] = $google_maps_key;
		}

		return $api;
	}

	/**
	 * Adds the Google Maps API key setting to the WP customizer.
	 * This function is hooked into 'customize_register'.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WP_Customize_Manager instance.
	 */
	public function customizer( $wp_customize ) {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => \__( "Google", THEMENAME ),
			] );
		}

		$wp_customize->add_setting(
			'acf_google_maps_key',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'acf_google_maps_key', [
				'label'       => \__( "ACF Google Maps Key", THEMENAME ),
				'description' => sprintf( __( "See %s" ),
					'https://goo.gl/Dn36CD' ),
				'section'     => 'google',
			] )
		);
	}

}