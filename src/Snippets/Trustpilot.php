<?php

namespace PressGang\Snippets;

use Twig\TwigFunction;

/**
 * Class Trustpilot
 *
 * Handles the integration of the Trustpilot widget and customizer settings in a
 * WordPress theme. This includes adding custom settings in the WordPress
 * Customizer for Trustpilot configuration and embedding the Trustpilot script
 * in the website's head section for the widgets to function properly.
 */
class Trustpilot {

	/**
	 * Trustpilot constructor.
	 *
	 * Sets up WordPress customizer options, includes Trustpilot script,
	 * and adds Trustpilot widgets to Twig.
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
		\add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );
	}

	/**
	 * Add Trustpilot settings to the WordPress Customizer.
	 *
	 * Registers settings and controls for managing Trustpilot integration,
	 * including Business ID, Template ID, and Reviews URL, and creates a new
	 * section in the Customizer dedicated to Trustpilot settings.
	 *
	 * @hooked customize_register
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
	 * Registers and enqueues the Trustpilot script.
	 *
	 * Adds the necessary Trustpilot script to the website's head section.
	 * This script is required for the proper functioning of Trustpilot widgets.
	 *
	 * @hooked wp_enqueue_scripts
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

	/**
	 * Adds custom functions to Twig.
	 *
	 * Integrates custom Trustpilot-related Twig functions for rendering
	 * Trustpilot widgets within templates.
	 *
	 * @hooked timber/twig
	 * @param \Twig\Environment $twig The Twig environment instance.
	 */
	public function add_to_twig( $twig ): void {
		$twig->addFunction( new \TwigFunction( 'trustpilot_mini', [ $this, 'render_mini_widget' ] ) );
	}

	/**
	 * Renders the Trustpilot mini widget.
	 *
	 * @usage {{ trustpilot_mini() }}
	 *
	 * Uses Timber to render the Trustpilot mini widget based on the template
	 * and theme customization settings like Business ID, Template ID, and Reviews URL.
	 */
	public function render_mini_widget(): void {
		\Timber::render( 'snippets/trustpilot-mini.twig', [
			'trustpilot_template_id' => \get_theme_mod( 'trustpilot_template_id' ),
			'trustpilot_business_id' => \get_theme_mod( 'trustpilot_business_id' ),
			'trustpilot_reviews_url' => \get_theme_mod( 'trustpilot_reviews_url' ),
		] );
	}
}