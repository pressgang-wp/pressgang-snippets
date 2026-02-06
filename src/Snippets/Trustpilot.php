<?php

declare(strict_types=1);

namespace PressGang\Snippets;

use Timber\Timber;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Integrates Trustpilot review widgets by adding Customizer fields for the
 * Business ID, Template ID, and Reviews URL, enqueuing the Trustpilot
 * bootstrap script, and registering a {{ trustpilot_mini() }} Twig function
 * for rendering the mini widget in templates.
 *
 * Enable this snippet to display Trustpilot widgets on your site.
 */
class Trustpilot implements SnippetInterface {

	private const SCRIPT_ENQUEUE_KEY = 'trustpilot-pressgang-snippet';

	/**
	 * Registers Customizer controls, enqueues the Trustpilot script, and
	 * adds the trustpilot_mini Twig function.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
		\add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );
	}

	/**
	 * Adds a "Trustpilot" Customizer section with fields for Business ID,
	 * Template ID, and Reviews URL.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! $wp_customize->get_section( 'trustpilot' ) ) {
			$wp_customize->add_section( 'trustpilot', [
				'title' => \_x( "Trustpilot", 'Trustpilot', THEMENAME ),
			] );
		}

		$wp_customize->add_setting( 'trustpilot_business_id', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'trustpilot_business_id', [
				'label'   => \_x( "Business ID", 'Trustpilot', THEMENAME ),
				'section' => 'trustpilot',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting( 'trustpilot_template_id', [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'trustpilot_template_id', [
				'label'   => \__( "Template ID", THEMENAME ),
				'section' => 'trustpilot',
				'type'    => 'text',
			] ) );

		$wp_customize->add_setting( 'trustpilot_reviews_link', [
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		] );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'trustpilot_reviews_link', [
				'label'   => \__( "Reviews URL", THEMENAME ),
				'section' => 'trustpilot',
				'type'    => 'text',
			] ) );
	}

	/**
	 * Registers and enqueues the Trustpilot widget bootstrap script.
	 *
	 * @return void
	 */
	public function register_scripts(): void {
		\wp_register_script(
			self::SCRIPT_ENQUEUE_KEY,
			'//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js',
			[],
			null,
			true
		);

		\wp_enqueue_script( self::SCRIPT_ENQUEUE_KEY );
	}

	/**
	 * Registers the trustpilot_mini() Twig function.
	 *
	 * @param Environment $twig The Twig environment instance.
	 *
	 * @return Environment The modified Twig environment.
	 */
	public function add_to_twig( Environment $twig ): Environment {
		$twig->addFunction( new TwigFunction( 'trustpilot_mini', [ $this, 'render_mini_widget' ] ) );

		return $twig;
	}

	/**
	 * Renders the Trustpilot mini widget via the snippets/trustpilot-mini.twig
	 * template using the Customizer theme mod values.
	 *
	 * @return void
	 */
	public function render_mini_widget(): void {
		Timber::render( 'snippets/trustpilot-mini.twig', [
			'trustpilot_template_id' => \get_theme_mod( 'trustpilot_template_id' ),
			'trustpilot_business_id' => \get_theme_mod( 'trustpilot_business_id' ),
			'trustpilot_reviews_url' => \get_theme_mod( 'trustpilot_reviews_url' ),
		] );
	}
}
