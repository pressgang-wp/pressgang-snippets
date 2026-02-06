<?php

declare(strict_types=1);

namespace PressGang\Snippets;

/**
 * Provides a Customizer field for the Google Maps API key and passes it to ACF
 * so that ACF Google Map fields work in the admin without hardcoding the key.
 *
 * Enable this snippet on any site that uses ACF's Google Map field type. The
 * API key is entered once in the Customizer under the "Google" section and is
 * automatically applied to both ACF settings and the field-level API filter.
 */
class AcfGoogleMaps implements SnippetInterface {

	/**
	 * Registers Customizer controls for the Google Maps API key, stores the key
	 * in ACF settings on init, and filters the ACF Google Map field API array.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'acf/init', [ $this, 'set_google_maps_key' ] );
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_filter( 'acf/fields/google_map/api', [ $this, 'get_google_maps_key' ] );
	}

	/**
	 * Stores the Google Maps API key in ACF's internal settings so that all
	 * ACF map fields can access it without additional configuration.
	 *
	 * @return void
	 */
	public function set_google_maps_key(): void {
		if ( $google_maps_key = filter_var( \get_theme_mod( 'acf_google_maps_key' ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
			\acf_update_setting( 'acf_google_maps_key', $google_maps_key );
		}
	}

	/**
	 * Filters the ACF Google Map field API parameters to inject the API key.
	 *
	 * Required since ACF 5.6.10+ to ensure the map renders in the admin.
	 *
	 * @param array<string, string> $api The ACF Google Maps field API parameters.
	 *
	 * @return array<string, string> Modified API parameters with the key set.
	 */
	public function get_google_maps_key( array $api ): array {
		if ( $google_maps_key = filter_var( \get_theme_mod( 'acf_google_maps_key' ), FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
			$api['key'] = $google_maps_key;
		}

		return $api;
	}

	/**
	 * Adds the Google Maps API key setting and control to the Customizer under
	 * the shared "Google" section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! $wp_customize->get_section( 'google' ) ) {
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
				'description' => sprintf( \__( "See %s", THEMENAME ), 'https://goo.gl/Dn36CD' ),
				'section'     => 'google',
			] )
		);
	}
}
