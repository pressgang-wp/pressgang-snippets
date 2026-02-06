<?php


namespace PressGang\Snippets\Google;

use PressGang\Snippets\SnippetInterface;
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
class ConversionTracking implements SnippetInterface {

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
		$this->ensure_google_section_exists( $wp_customize );
		$this->add_adwords_id_setting( $wp_customize );
		$this->add_conversion_label_setting( $wp_customize );
	}

	/**
	 * Outputs the Google Ads conversion tracking script via the
	 * snippets/google/conversion-tracking.twig template. On WooCommerce
	 * order-received pages, includes the order total, currency, and ID.
	 *
	 * @return void
	 */
	public function add_tracking(): void {
		$google_adwords_id = $this->get_adwords_id();
		if ( ! $google_adwords_id ) {
			return;
		}

		$data = $this->build_tracking_context( $google_adwords_id );
		$data = $this->add_order_context_if_available( $data );

		Timber::render( 'snippets/google/conversion-tracking.twig', $data );
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
	 * Register the AdWords ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_adwords_id_setting( \WP_Customize_Manager $wp_customize ): void {
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
	}

	/**
	 * Register the conversion label setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_conversion_label_setting( \WP_Customize_Manager $wp_customize ): void {
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
	 * Fetch the configured AdWords ID.
	 *
	 * @return string
	 */
	private function get_adwords_id(): string {
		return (string) \get_theme_mod( 'google-adwords-id' );
	}

	/**
	 * Build the base tracking context for the Twig template.
	 *
	 * @param string $google_adwords_id
	 *
	 * @return array<string, mixed>
	 */
	private function build_tracking_context( string $google_adwords_id ): array {
		return [
			'google_adwords_id'       => $google_adwords_id,
			'add_gtag_script'         => $this->should_add_gtag_script(),
			'order_total'             => 0,
			'currency'                => '',
			'tracking_id'             => 0,
			'google_conversion_label' => \get_theme_mod( 'google-conversion-label' ),
		];
	}

	/**
	 * Determine whether to include the gtag.js loader.
	 *
	 * @return bool
	 */
	private function should_add_gtag_script(): bool {
		return ! \get_theme_mod( 'google-analytics-id' )
			|| \get_theme_mod( 'track-logged-in' )
			|| ! \is_user_logged_in();
	}

	/**
	 * Add WooCommerce order details to the tracking context when available.
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<string, mixed>
	 */
	private function add_order_context_if_available( array $data ): array {
		if ( ! class_exists( 'woocommerce' ) || ! \is_order_received_page() ) {
			return $data;
		}

		global $wp;
		$order_id = \absint( $wp->query_vars['order-received'] );
		if ( ! $order_id ) {
			return $data;
		}

		$order = \wc_get_order( $order_id );
		if ( ! $order ) {
			return $data;
		}

		$data['order_total'] = $order->get_total();
		$data['currency']    = $order->get_currency();
		$data['tracking_id'] = $order->get_id();

		return $data;
	}
}
