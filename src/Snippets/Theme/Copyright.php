<?php


namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds a "Copyright" text field to the WordPress Customizer under a "Footer"
 * section, making the copyright notice editable by site administrators.
 *
 * Enable this snippet when you want the footer copyright text to be
 * configurable via the Customizer. The value is available in templates via
 * get_theme_mod('copyright').
 */
class Copyright implements SnippetInterface {

	/**
	 * Registers the Customizer controls for the copyright text field.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
	}

	/**
	 * Adds a "Footer" Customizer section (if it doesn't exist) and registers
	 * a "Copyright" text setting and control within it.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! $wp_customize->get_section( 'footer' ) ) {
			$wp_customize->add_section( 'footer', [
				'title'    => \_x( "Footer", "Customizer", THEMENAME ),
				'priority' => 100,
			] );
		}

		$wp_customize->add_setting(
			'copyright',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'copyright', [
				'label'   => \_x( "Copyright", 'Customizer', THEMENAME ),
				'section' => 'footer',
			] ) );
	}
}
