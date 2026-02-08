<?php

namespace PressGang\Snippets\Seo;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

/**
 * Outputs Twitter/X Card meta tags in <head> for improved social sharing
 * previews, including title, description, URL, and image.
 *
 * Enable this snippet to add Twitter Card metadata. Optional site and creator
 * handles can be configured in the Customizer under the "Twitter" section.
 *
 * Note: depends on PressGang\SEO\MetaDescriptionService from the parent theme.
 */
class TwitterSummary implements SnippetInterface {

	/**
	 * Hooks the Twitter Card meta tag output into wp_head at a high priority.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'customize_register', [ $this, 'add_to_customizer' ] );
		\add_action( 'wp_head', [ $this, 'add_meta_tags' ], 5 );
	}

	/**
	 * Adds Customizer fields for the Twitter site and creator handles.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function add_to_customizer( \WP_Customize_Manager $wp_customize ): void {
		$this->ensure_twitter_section_exists( $wp_customize );
		$this->add_site_handle_setting( $wp_customize );
		$this->add_creator_handle_setting( $wp_customize );
	}

	/**
	 * Renders the Twitter Card meta tags via the
	 * snippets/seo/twitter-summary.twig template.
	 *
	 * @return void
	 */
	public function add_meta_tags(): void {
		$this->render_template( 'snippets/seo/twitter-summary.twig', $this->build_context() );
	}

	/**
	 * Build the template context for the current request.
	 *
	 * @return array<string, string|null>
	 */
	private function build_context(): array {
		$image = $this->get_image_url();

		return [
			'card_type'      => $image ? 'summary_large_image' : 'summary',
			'site_handle'    => $this->get_site_handle(),
			'creator_handle' => $this->get_creator_handle(),
			'title'          => \apply_filters( 'pressgang_twitter_title', $this->get_title() ),
			'description'    => \apply_filters( 'pressgang_twitter_description', $this->get_description() ),
			'image'          => \apply_filters( 'pressgang_twitter_image', $image ),
		];
	}

	/**
	 * Ensure the "Twitter" Customizer section exists.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function ensure_twitter_section_exists( \WP_Customize_Manager $wp_customize ): void {
		if ( isset( $wp_customize->sections['twitter'] ) ) {
			return;
		}

		$wp_customize->add_section( 'twitter', [
			'title' => \__( "Twitter", THEMENAME ),
		] );
	}

	/**
	 * Register the site handle setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_site_handle_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting(
			'twitter-site-handle',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'twitter-site-handle', [
				'label'       => \__( "Twitter Site Handle", THEMENAME ),
				'description' => \__( "e.g. @pressgang", THEMENAME ),
				'section'     => 'twitter',
				'type'        => 'text',
			] ) );
	}

	/**
	 * Register the creator handle setting and control.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function add_creator_handle_setting( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_setting(
			'twitter-creator-handle',
			[
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize,
			'twitter-creator-handle', [
				'label'       => \__( "Twitter Creator Handle", THEMENAME ),
				'description' => \__( "Optional. Used for content author.", THEMENAME ),
				'section'     => 'twitter',
				'type'        => 'text',
			] ) );
	}

	/**
	 * Get the configured site handle.
	 *
	 * @return string|null
	 */
	private function get_site_handle(): ?string {
		$handle = trim( (string) \get_theme_mod( 'twitter-site-handle' ) );

		return $handle !== '' ? $handle : null;
	}

	/**
	 * Get the configured creator handle.
	 *
	 * @return string|null
	 */
	private function get_creator_handle(): ?string {
		$handle = trim( (string) \get_theme_mod( 'twitter-creator-handle' ) );

		return $handle !== '' ? $handle : null;
	}

	/**
	 * Returns the featured image URL for the current post, falling back to the
	 * site logo theme mod.
	 *
	 * @return string The Twitter card image URL.
	 */
	protected function get_image_url(): string {
		$post    = Timber::get_post();
		$img_url = $post && \has_post_thumbnail( $post->ID )
			? \wp_get_attachment_image_src( \get_post_thumbnail_id( $post->ID ), 'large' )[0]
			: ( \get_theme_mod( 'logo' ) );

		return (string) $img_url;
	}

	/**
	 * Returns the meta description for the current page via the PressGang
	 * SEO service.
	 *
	 * @return string The page description.
	 */
	protected function get_description(): string {
		return \PressGang\SEO\MetaDescriptionService::get_meta_description();
	}

	/**
	 * Returns the page title, handling taxonomy and post-type archive contexts.
	 *
	 * @return string The page title with HTML tags stripped.
	 */
	protected function get_title(): string {
		$title = \get_the_title();

		if ( \is_tax() ) {
			$title = \single_term_title( '', false );
		} elseif ( \is_post_type_archive() ) {
			$title = \get_the_archive_title();
		}

		return \wp_strip_all_tags( $title );
	}

	/**
	 * Returns the canonical URL for the current page, handling taxonomy and
	 * post-type archive contexts.
	 *
	 * @return string The canonical URL.
	 */
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
