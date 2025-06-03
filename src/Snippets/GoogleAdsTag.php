<?php

namespace PressGang\Snippets;

use Timber\Timber;
use PressGang\Snippets\SnippetInterface;
use function PrimeTools\Snippets\__;

/**
 * Integrates Google Ads Global Site Tag (gtag.js) into a WordPress theme.
 *
 * This class adds a Customizer setting for a Google Ads Conversion ID and
 * injects the required gtag.js script into the <head>.
 */
class GoogleAdsTag implements SnippetInterface {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ], 500 );
	}

	/**
	 * Adds Customizer settings for Google Ads Conversion ID.
	 *
	 * @param  \WP_Customize_Manager  $wp_customize  The Customizer manager
	 *     instance.
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize
	): void {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => __( "Google", THEMENAME ),
			] );
		}

		$wp_customize->add_setting( 'google-ads-conversion-id', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-ads-conversion-id', [
				'label'   => __( "Google Ads Conversion ID", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Outputs the Google Ads gtag.js script in the <head> section.
	 */
	public function script(): void {
		if ( $conversion_id = \get_theme_mod( 'google-ads-conversion-id' ) ) {
			Timber::render( 'snippets/google-ads-tag.twig', [
				'conversion_id' => $conversion_id,
			] );
		}
	}

}
