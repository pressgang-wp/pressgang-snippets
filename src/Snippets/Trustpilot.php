<?php

namespace PressGang\Snippets;

/**
 * Class Trustpilot
 *
 * Handles the integration of Trustpilot widget and customizer settings in a
 * WordPress theme. This includes adding custom settings in the WordPress
 * Customizer for Trustpilot configuration and embedding the Trustpilot script
 * in the website's head section for the widgets to function properly.
 */
class Trustpilot {

	/**
	 * Trustpilot constructor.
	 *
	 * Sets up WordPress customizer options and includes Trustpilot script.
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
	}

	/**
	 * Add Trustpilot settings to the WordPress Customizer.
	 *
	 * This function registers settings and controls for managing Trustpilot
	 * integration such as Business ID, Template ID, and Reviews URL. It creates
	 * a new section in the WordPress Customizer dedicated to Trustpilot settings,
	 * allowing for easy customization and integration of Trustpilot features.
	 *
	 * @param \WP_Customize_Manager $wp_customize WordPress Customizer object.
	 */
	public function customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['trustpilot'] ) ) {
			$wp_customize->add_section( 'trustpilot', [
				'title' => _x( "Trustpilot", 'Trustpilot', THEMENAME ),
			] );
		}

		$wp_customize->add_setting( 'trustpilot_business_id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'trustpilot_business_id', [
				'label'   => _x( "Business ID", 'Trustpilot', THEMENAME ),
				'section' => 'trustpilot',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting( 'trustpilot_template_id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'trustpilot_template_id', [
				'label'   => __( "Template ID", THEMENAME ),
				'section' => 'trustpilot',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting( 'trustpilot_reviews_link',
			[
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'trustpilot_reviews_link', [
				'label'   => __( "Reviews URL", THEMENAME ),
				'section' => 'trustpilot',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Outputs the Trustpilot script tag in the website's head section.
	 *
	 * This script is necessary for Trustpilot widgets to function properly.
	 * It should be included in the head of each page where Trustpilot widgets
	 * are intended to be used.
	 */
	public function register_scripts(): void {
		\wp_register_script(
			'trustpilot-snippet',
			'//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js',
			[],
			null,
			true
		);

		\wp_enqueue_script( 'trustpilot-widget-script' );
	}
}