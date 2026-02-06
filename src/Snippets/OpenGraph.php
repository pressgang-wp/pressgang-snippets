<?php


namespace PressGang\Snippets;

use Timber\Timber;

/**
 * Outputs Open Graph meta tags in <head> for improved social media sharing
 * previews (Facebook, LinkedIn, etc.), including og:title, og:description,
 * og:type, og:url, og:image, and og:site_name.
 *
 * Enable this snippet to control how your pages appear when shared on social
 * platforms. No configuration required — values are derived automatically from
 * the current page context.
 *
 * Note: depends on PressGang\SEO\MetaDescriptionService from the parent theme.
 *
 * @see https://developers.facebook.com/docs/sharing/webmasters/#markup
 */
class OpenGraph implements SnippetInterface {

	/**
	 * Hooks the Open Graph meta tag output into wp_head at a high priority
	 * so tags appear early in <head>.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'wp_head', [ $this, 'add_meta_tags' ], 5 );
	}

	/**
	 * Renders the Open Graph meta tags via the snippets/open-graph.twig
	 * template. All values are filterable via pressgang_og_* filters.
	 *
	 * @return void
	 */
	public function add_meta_tags(): void {
		Timber::render( 'snippets/open-graph.twig', [
			'site_name'   => \apply_filters( 'pressgang_og_site_name', \get_bloginfo() ),
			'title'       => \apply_filters( 'pressgang_og_title', $this->get_title() ),
			'description' => \apply_filters( 'pressgang_og_description', $this->get_description() ),
			'type'        => \apply_filters( 'pressgang_og_type', $this->get_type() ),
			'url'         => \apply_filters( 'pressgang_og_url', $this->get_url() ),
			'image'       => \apply_filters( 'pressgang_og_image', $this->get_image_url() ),
		] );
	}

	/**
	 * Returns the featured image URL for the current post, falling back to the
	 * site logo theme mod.
	 *
	 * @return string The Open Graph image URL.
	 */
	protected function get_image_url(): string {
		$post    = Timber::get_post();
		$img_url = $post && \has_post_thumbnail( $post->ID )
			? \wp_get_attachment_image_src( \get_post_thumbnail_id( $post->ID ), 'large' )[0]
			: ( \get_theme_mod( 'logo' ) );

		return \apply_filters( 'og_image', $img_url );
	}

	/**
	 * Determines the Open Graph type for the current page.
	 *
	 * @return string 'profile' for author pages, 'article' for single posts,
	 *     'website' otherwise.
	 */
	protected function get_type(): string {
		return \is_author() ? 'profile' : ( \is_single() ? 'article' : 'website' );
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
	protected function get_url(): string {
		if ( \is_tax() ) {
			return \get_term_link( \get_query_var( 'term' ), \get_query_var( 'taxonomy' ) );
		} elseif ( \is_post_type_archive() ) {
			return \get_post_type_archive_link( \get_query_var( 'post_type' ) );
		}

		return \get_permalink();
	}
}
