<?php

namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Integrates Google AdWords Conversion Tracking into a WordPress site using WooCommerce.
 *
 * This class allows for the easy addition of Google AdWords conversion tracking code to WooCommerce order
 * confirmation pages through the WordPress Customizer. It provides settings for the Google AdWords ID and
 * conversion label, which can be set by site administrators.
 */
class GoogleConversionTracking implements SnippetInterface {

	/**
	 * Initializes the class by hooking into WordPress actions for Customizer integration and script injection.
	 *
	 * @param array $args
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'add_tracking' ] );
	}

	/**
	 * Adds settings to the WordPress Customizer for configuring Google AdWords Conversion Tracking.
	 *
	 * Creates a new section in the Customizer for Google settings if it does not exist, and adds settings for the
	 * Google AdWords ID and conversion label.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WordPress Customizer object, providing access to the Customizer's API.
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => __( "Google", THEMENAME ),
			] );
		}

		$wp_customize->add_setting(
			'google-adwords-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-adwords-id', [
				'label'   => __( "Google Ad Words ID", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting(
			'google-conversion-label',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-conversion-label', [
				'label'   => __( "Google Conversion Label", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Injects the Google AdWords Conversion Tracking script into the site's head.
	 *
	 * Outputs the conversion tracking script on WooCommerce order received pages if the required settings are
	 * configured. It optionally checks if Google Analytics tracking is set up and adjusts the script output accordingly.
	 */
	public function add_tracking(): void {
		if ( $google_adwords_id = \get_theme_mod( 'google-adwords-id' ) ) {
			$data = [
				'google_adwords_id'       => $google_adwords_id,
				'add_gtag_script'         => ! \get_theme_mod( 'google-analytics-id' ) || \get_theme_mod( 'track-logged-in' ) || ! \is_user_logged_in(),
				'order_total'             => 0,
				'currency'                => '',
				'tracking_id'             => 0,
				'google_conversion_label' => \get_theme_mod( 'google-conversion-label' )
			];

			if ( class_exists( 'woocommerce' ) && \is_order_received_page() ) {
				global $wp;
				$order_id = absint( $wp->query_vars['order-received'] );
				if ( $order_id && ( $order = \wc_get_order( $order_id ) ) ) {
					$data['order_total'] = $order->get_total();
					$data['currency']    = $order->get_currency();
					$data['tracking_id'] = $order->get_id();
				}
			}

			Timber::render( 'snippets/google-conversion-tracking.twig', $data );
		}
	}
}