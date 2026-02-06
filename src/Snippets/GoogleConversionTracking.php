<?php


namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Integrates Google AdWords (Google Ads) conversion tracking into WooCommerce
 * order confirmation pages. Provides Customizer fields for the AdWords ID and
 * conversion label, and injects the gtag.js conversion snippet on the
 * order-received page.
 *
 * Enable this snippet on WooCommerce sites that track purchase conversions via
 * Google Ads. The AdWords ID and conversion label are entered in the Customizer
 * under the "Google" section.
 */
class GoogleConversionTracking implements SnippetInterface {

	/**
	 * Registers Customizer controls for the Google AdWords ID and conversion
	 * label, and hooks the tracking script into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'add_tracking' ] );
	}

	/**
	 * Adds settings for the Google AdWords ID and conversion label to the
	 * Customizer under the shared "Google" section.
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

		$wp_customize->add_setting(
			'google-adwords-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'google-adwords-id', [
				'label'   => \__( "Google Ad Words ID", THEMENAME ),
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
				'label'   => \__( "Google Conversion Label", THEMENAME ),
				'section' => 'google',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Outputs the Google Ads conversion tracking script via the
	 * snippets/google-conversion-tracking.twig template. On WooCommerce
	 * order-received pages, includes the order total, currency, and ID.
	 *
	 * @return void
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
				$order_id = \absint( $wp->query_vars['order-received'] );
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
