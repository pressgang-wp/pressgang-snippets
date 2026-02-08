<?php

namespace PressGang\Snippets\Integration;

use PressGang\Snippets\SnippetInterface;

/**
 * Integrates Disqus comments by swapping the WordPress comments template
 * for a Disqus embed when a shortname is configured.
 *
 * Enable this snippet to use Disqus for comments. The shortname is entered
 * in the Customizer under the "Disqus" section.
 */
class Disqus implements SnippetInterface {

	/**
	 * Registers Customizer controls and hooks into comments_template to render
	 * the Disqus embed.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_filter( 'comments_template', [ $this, 'render' ] );
	}

	/**
	 * Adds a text field for the Disqus shortname to the Customizer.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_disqus_section_exists( $wp_customize );

		$wp_customize->add_setting(
			'disqus-shortname',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'disqus-shortname', [
				'label'   => \__( "Disqus Shortname", THEMENAME ),
				'section' => 'disqus',
			] ) );
	}

	/**
	 * Filters the comments template to use the Disqus embed when configured.
	 *
	 * @param string $template Existing comments template path.
	 *
	 * @return string Template path to use.
	 */
	public function render( string $template ): string {
		if ( ! $this->get_shortname() ) {
			return $template;
		}

		$disqus_template = $this->get_template_path();

		return $disqus_template ?? $template;
	}

	/**
	 * Ensure the "Disqus" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_disqus_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( $wp_customize->get_section( 'disqus' ) ) {
			return;
		}

		$wp_customize->add_section( 'disqus', [
			'title' => \__( "Disqus", THEMENAME ),
		] );
	}

	/**
	 * Get the configured Disqus shortname.
	 *
	 * @return string
	 */
	private function get_shortname(): string {
		return (string) \get_theme_mod( 'disqus-shortname' );
	}

	/**
	 * Resolve the Disqus template path within this package.
	 *
	 * @return string|null
	 */
	private function get_template_path(): ?string {
		$path = dirname( __DIR__, 4 ) . '/views/snippets/integration/disqus.php';

		return \file_exists( $path ) ? $path : null;
	}
}
