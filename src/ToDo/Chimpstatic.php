<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\__;

class Chimpstatic {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_action( 'wp_head', [ $this, 'script' ] );
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {
		if ( ! isset( $wp_customize->sections['chimpstatic'] ) ) {
			$wp_customize->add_section( 'chimpstatic', [
				'title' => __( "Chimpstatic", THEMENAME ),
			] );
		}

		// tracking id

		$wp_customize->add_setting(
			'chimpstatic-id',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'chimpstatic-id', [
				'label'   => __( "Chimpstatic ID", THEMENAME ),
				'section' => 'chimpstatic',
				'type'    => 'text',
			] ) );
	}

	/**
	 * script
	 *
	 * @return void
	 */
	public function script() {
		if ( $chimpstatic_id = urlencode( get_theme_mod( 'chimpstatic-id' ) ) ) {
			\Timber\Timber::render( 'chimpstatic.twig', [
				'chimpstatic_id' => $chimpstatic_id,
			] );
		}
	}

}

new Chimpstatic();
