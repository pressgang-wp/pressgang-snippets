<?php


namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

/**
 * Integrates the tawk.to live-chat widget by providing a Customizer field for
 * the tawk.to Property ID and rendering the embed script in wp_footer.
 *
 * Enable this snippet to add tawk.to live chat to your site. Enter the
 * Property ID in the Customizer under the "tawk.to" section.
 *
 * @see https://developer.tawk.to/
 */
class Tawkto implements SnippetInterface {

	/**
	 * Registers the Customizer control and hooks the widget render into
	 * wp_footer at a late priority.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_footer', [ $this, 'render' ], 100 );
	}

	/**
	 * Adds a "tawk.to" Customizer section with a text field for the
	 * Property ID.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_tawkto_section_exists( $wp_customize );
		$this->add_tawkto_id_setting( $wp_customize );
	}

	/**
	 * Renders the tawk.to embed script via Twig when a Property ID is
	 * configured.
	 *
	 * @return void
	 */
	public function render(): void {
		$tawkto_id = $this->get_tawkto_id();
		if ( ! $tawkto_id ) {
			return;
		}

		$this->render_template( 'snippets/integration/tawkto.twig', [ 'tawkto_id' => $tawkto_id ] );
	}

	/**
	 * Ensure the "tawk.to" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_tawkto_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( $wp_customize->get_section( 'tawkto' ) ) {
			return;
		}

		$wp_customize->add_section( 'tawkto', [
			'title'    => \_x( "tawk.to", "Customizer", THEMENAME ),
			'priority' => 30,
		] );
	}

	/**
	 * Register the tawk.to ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_tawkto_id_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting( 'tawkto-id', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'tawkto-id', [
				'label'   => \__( "tawk.to ID", THEMENAME ),
				'section' => 'tawkto',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Get the configured tawk.to property ID.
	 *
	 * @return string
	 */
	private function get_tawkto_id(): string {
		return (string) \get_theme_mod( 'tawkto-id' );
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
