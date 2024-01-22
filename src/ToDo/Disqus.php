<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\__;
use function PressGang\Snippets\get_theme_mod;

class Disqus {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_filter( 'comments_template', [ $this, 'render' ] );
	}

	/**
	 * Add to customizer
	 *
	 * @param $wp_customize
	 */
	public function customizer( $wp_customize ) {
		$wp_customize->add_section( 'disqus', [
			'title' => __( "Disqus", THEMENAME ),
		] );

		$wp_customize->add_setting(
			'disqus-shortname',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'disqus-shortname', [
				'label'   => __( "Disqus Shortname", THEMENAME ),
				'section' => 'disqus',
			] ) );
	}

	/**
	 * render
	 *
	 * Render disqus.twig
	 *
	 */
	public function render() {
		\Timber\Timber::render( 'disqus.twig',
			[ 'disqus_shortname' => get_theme_mod( 'disqus-shortname' ), ] );
	}

}

new Disqus();
