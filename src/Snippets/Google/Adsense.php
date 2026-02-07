<?php

namespace PressGang\Snippets\Google;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

/**
 * Integrates Google AdSense by adding a Customizer field for the publisher ID
 * and injecting the AdSense script into wp_head.
 *
 * Enable this snippet to load AdSense auto ads. The publisher ID (e.g.
 * "ca-pub-XXXXXXXXXXXX") is entered in the Customizer under the "Google"
 * section.
 */
class Adsense implements SnippetInterface {

	/**
	 * Registers the AdSense Customizer control and hooks the script into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ], 500 );
	}

	/**
	 * Adds a text field for the AdSense publisher ID to the Customizer under
	 * the shared "Google" section.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_google_section_exists( $wp_customize );
		$this->add_adsense_id_setting( $wp_customize );
		$this->add_page_level_ads_setting( $wp_customize );
	}

	/**
	 * Outputs the AdSense script via the snippets/google/adsense.twig template.
	 *
	 * @return void
	 */
	public function script(): void {
		$publisher_id = $this->get_adsense_id();
		if ( ! $publisher_id ) {
			return;
		}

		$this->render_template( 'snippets/google/adsense.twig', [
			'google_adsense_id'   => $publisher_id,
			'show_page_level_ads' => $this->should_show_page_level_ads(),
		] );
	}

	/**
	 * Ensure the shared "Google" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_google_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( isset( $wp_customize->sections['google'] ) ) {
			return;
		}

		$wp_customize->add_section( 'google', [
			'title' => \__( "Google", THEMENAME ),
		] );
	}

	/**
	 * Register the AdSense publisher ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_adsense_id_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting(
			'google-adsense-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-adsense-id', [
				'label'   => \__( "Google AdSense ID", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Register the page-level ads toggle (legacy support).
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_page_level_ads_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting( 'show-page-level-ads', [
			'default' => '0',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'show-page-level-ads', [
				'label'    => \__( 'Show Page-Level Ads', THEMENAME ),
				'section'  => 'google',
				'settings' => 'show-page-level-ads',
				'type'     => 'checkbox',
			] ) );
	}

	/**
	 * Get the configured AdSense publisher ID.
	 *
	 * @return string
	 */
	private function get_adsense_id(): string {
		return (string) \get_theme_mod( 'google-adsense-id' );
	}

	/**
	 * Determine whether page-level ads should be shown (legacy toggle).
	 *
	 * @return bool
	 */
	private function should_show_page_level_ads(): bool {
		return (bool) \get_theme_mod( 'show-page-level-ads' );
	}

	/**
	 * @param string               $template Template path.
	 * @param array<string, mixed> $context  Template context.
	 *
	 * @return void
	 */
	protected function render_template( string $template, array $context ): void {
		Timber::render( $template, $context );
	}
}
