<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\__;
use function PressGang\Snippets\absint;
use function PressGang\Snippets\add_action;
use function PressGang\Snippets\get_theme_mod;
use function PressGang\Snippets\is_order_received_page;
use function PressGang\Snippets\is_user_logged_in;
use function PressGang\Snippets\wc_get_order;

class GoogleConversionTracking {

	/**
	 * __construct
	 *
	 */
	public function __construct() {
		add_action( 'customize_register', [ $this, 'customizer' ] );
		add_action( 'wp_head', [ $this, 'add_tracking' ] );
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {
		if ( ! isset( $wp_customize->sections['google'] ) ) {
			$wp_customize->add_section( 'google', [
				'title' => __( "Google", THEMENAME ),
			] );
		}

		// adwords id

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

		// conversion label

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
	 * add_order_tracking
	 *
	 */
	public function add_tracking() {
		if ( $google_adword_id = get_theme_mod( 'google-adwords-id' ) ) {
			$data = [
				'google_adwords_id' => $google_adword_id,
			];

			// we only want one call to the Global site tag, so check if Google Analytics is already running
			$data['add_gtag_script'] = true;
			if ( $google_analytics_id = get_theme_mod( 'google-analytics-id' ) ) {
				$track_logged_in = get_theme_mod( 'track-logged-in' );

				if ( $track_logged_in || ( ! $track_logged_in && ! is_user_logged_in() ) ) {
					$data['add_gtag_script'] = false;
				}
			}

			if ( class_exists( 'woocommerce' ) && is_order_received_page() ) {
				global $wp;

				$order_id = absint( $wp->query_vars['order-received'] );

				if ( $order_id ) {
					if ( $order = wc_get_order( $order_id ) ) {
						$data['order_total']             = $order->get_total();
						$data['currency']                = $order->get_currency();
						$data['tracking_id']             = $order->get_id();
						$data['google_conversion_label'] = get_theme_mod( 'google-conversion-label' );
					}
				}
			}

			\Timber\Timber::render( 'snippets/google-conversion-tracking.twig',
				$data );
		}
	}

}

new GoogleConversionTracking();