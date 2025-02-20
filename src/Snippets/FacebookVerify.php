<?php

namespace PressGang\Snippets;

use WP_Customize_Manager;
use WP_Customize_Control;

/**
 * Class FacebookVerify
 *
 * Adds a Facebook Domain Verification customizer field & meta tag.
 */
class FacebookVerify implements SnippetInterface {

	private const OPTION_NAME = 'facebook_domain_verification';
	private const SECTION_ID = 'facebook';
	private const LABEL = 'Facebook Domain Verification';
	private const DESCRIPTION = 'See <a href="https://bit.ly/3eUd2fI" target="_blank">Facebook Domain Verification Guide</a>';

	/**
	 * Constructor.
	 *
	 * @param array $args Unused, but required by the interface.
	 */
	public function __construct( array $args = [] ) {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_action( 'wp_head', [ $this, 'add_meta_tag' ] );
	}

	/**
	 * Registers the Customizer setting for Facebook domain verification.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WordPress Customizer object.
	 */
	public function customizer( \WP_Customize_Manager $wp_customize ): void {
		// Ensure the Facebook section exists.
		if ( ! $wp_customize->get_section( self::SECTION_ID ) ) {
			$wp_customize->add_section( self::SECTION_ID, [
				'title' => \esc_html__( 'Facebook', 'pressgang' ),
			] );
		}

		$wp_customize->add_setting(
			self::OPTION_NAME,
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control(
			$wp_customize,
			self::OPTION_NAME,
			[
				'label'       => \esc_html__( self::LABEL, 'pressgang' ),
				'description' => \wp_kses_post( self::DESCRIPTION ),
				'section'     => self::SECTION_ID,
			]
		) );
	}

	/**
	 * Adds the Facebook Domain Verification meta tag to the page head.
	 */
	public function add_meta_tag(): void {
		$verification_code = \get_theme_mod( self::OPTION_NAME );

		if ( ! $verification_code ) {
			return;
		}

		printf(
			'<meta name="facebook-domain-verification" content="%s" />',
			\esc_attr( $verification_code )
		);
	}
}