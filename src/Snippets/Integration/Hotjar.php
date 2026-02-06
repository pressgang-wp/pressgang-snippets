<?php


namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

/**
 * Integrates Hotjar behaviour analytics into the theme by adding a Customizer
 * field for the Hotjar Site ID and injecting the Hotjar tracking script into
 * wp_head.
 *
 * Enable this snippet to install Hotjar heatmaps, recordings, and surveys on
 * your site. The Site ID is entered in the Customizer under its own "Hotjar"
 * section.
 */
class Hotjar implements SnippetInterface {

	/**
	 * Registers a Customizer control for the Hotjar Site ID and hooks the
	 * tracking script into wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ], 500 );
	}

	/**
	 * Adds a "Hotjar" Customizer section with a text field for the Site ID.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_hotjar_section_exists( $wp_customize );
		$this->add_hotjar_id_setting( $wp_customize );
	}

	/**
	 * Outputs the Hotjar tracking script via the snippets/integration/hotjar.twig template
	 * when a Site ID is configured.
	 *
	 * @return void
	 */
	public function script(): void {
		$hotjar_id = $this->get_hotjar_id();
		if ( ! $hotjar_id ) {
			return;
		}

		$this->render_template( 'snippets/integration/hotjar.twig', [
			'hotjar_id' => $hotjar_id,
		] );
	}

	/**
	 * Ensure the "Hotjar" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_hotjar_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( isset( $wp_customize->sections['hotjar'] ) ) {
			return;
		}

		$wp_customize->add_section( 'hotjar', [
			'title' => \__( "Hotjar", THEMENAME ),
		] );
	}

	/**
	 * Register the Hotjar Site ID setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_hotjar_id_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting( 'hotjar-id', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'hotjar-id', [
				'label'   => \__( "Hotjar Site ID", THEMENAME ),
				'section' => 'hotjar',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Get the configured Hotjar site ID.
	 *
	 * @return string
	 */
	private function get_hotjar_id(): string {
		return (string) \get_theme_mod( 'hotjar-id' );
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
