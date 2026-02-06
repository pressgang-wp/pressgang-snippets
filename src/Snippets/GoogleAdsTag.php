<?php


namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Integrates Google Ads Global Site Tag (gtag.js) into the theme by adding a
 * Customizer field for the Google Ads Conversion ID and injecting the gtag.js
 * script into <head>.
 *
 * Enable this snippet to install Google Ads conversion tracking. The Conversion
 * ID is entered in the Customizer under the "Google" section.
 */
class GoogleAdsTag implements SnippetInterface {

	/**
	 * Registers a Customizer control for the Google Ads Conversion ID and
	 * hooks the tracking script into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ], 500 );
	}

	/**
	 * Adds a text field for the Google Ads Conversion ID to the Customizer
	 * under the shared "Google" section.
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

		$wp_customize->add_setting( 'google-ads-conversion-id', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-ads-conversion-id', [
				'label'   => \__( "Google Ads Conversion ID", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Outputs the Google Ads gtag.js script via the
	 * snippets/google-ads-tag.twig template when a Conversion ID is configured.
	 *
	 * @return void
	 */
	public function script(): void {
		if ( $conversion_id = \get_theme_mod( 'google-ads-conversion-id' ) ) {
			Timber::render( 'snippets/google-ads-tag.twig', [
				'conversion_id' => $conversion_id,
			] );
		}
	}
}
