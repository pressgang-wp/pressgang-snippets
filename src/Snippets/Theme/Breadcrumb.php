<?php


namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;
use \Twig\Environment;
use \Twig\TwigFunction;
use \Timber\Timber;

/**
 * Registers a {{ breadcrumb() }} Twig function that renders context-aware
 * breadcrumb navigation based on the current WordPress page type (single,
 * page, archive, category, tag, taxonomy, author, date, search, 404).
 *
 * Enable this snippet to provide automatic breadcrumb trails in templates
 * without a third-party plugin. The output is rendered via the
 * snippets/theme/breadcrumb.twig template.
 */
class Breadcrumb implements SnippetInterface {

	/**
	 * Accumulated breadcrumb trail for the current request.
	 *
	 * @var array<int, array{title: string, class: string, url: string|null}>
	 */
	public array $breadcrumbs = [];

	/**
	 * Registers the breadcrumb() function with the Timber/Twig environment.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );
	}

	/**
	 * Adds the breadcrumb() function to the Twig environment so it can be
	 * called directly in templates.
	 *
	 * @param Environment $twig The Twig environment to extend.
	 *
	 * @return Environment The modified Twig environment.
	 */
	public function add_to_twig( Environment $twig ): Environment {
		$twig->addFunction( new TwigFunction( 'breadcrumb', [ $this, 'render' ] ) );

		return $twig;
	}

	/**
	 * Builds and renders the breadcrumb trail for the current request using
	 * the snippets/theme/breadcrumb.twig template.
	 *
	 * @return void
	 */
	public function render(): void {
		$this->generate_links();
		Timber::render( 'snippets/theme/breadcrumb.twig', [ 'breadcrumbs' => $this->breadcrumbs ] );
	}

	/**
	 * Populates the breadcrumbs array based on the current WordPress
	 * conditional context (category, tag, taxonomy, archive, single, page,
	 * author, date, search, or 404). Does nothing on the front page.
	 *
	 * @return void
	 */
	public function generate_links(): void {

		if ( \is_front_page() ) {
			return;
		}

		$this->append_home_link();

		if ( \is_category() ) {
			$this->handle_category();
		} elseif ( \is_tag() ) {
			$this->handle_tag();
		} elseif ( \is_tax() ) {
			$this->handle_tax();
		} elseif ( \is_archive() ) {
			$this->handle_archive();
		} elseif ( \is_single() ) {
			$this->handle_single();
		} elseif ( \is_page() ) {
			$this->handle_page();
		} elseif ( \is_author() ) {
			$this->handle_author();
		} elseif ( \is_day() ) {
			$this->handle_day();
		} elseif ( \is_month() ) {
			$this->handle_month();
		} elseif ( \is_year() ) {
			$this->handle_year();
		} elseif ( \is_search() ) {
			$this->handle_search();
		} elseif ( \is_404() ) {
			$this->handle_404();
		}

		if ( \get_query_var( 'paged' ) ) {
			$this->handle_paged();
		}
	}

	/**
	 * Appends the "Home" breadcrumb linking to the site root.
	 *
	 * @return void
	 */
	protected function append_home_link(): void {
		$this->append_link( \_x( "Home", 'Breadcrumb', THEMENAME ), 'breadcrumb--home', \get_site_url() );
	}

	/**
	 * Builds breadcrumb entries for archive pages, including the post-type
	 * archive link and the current archive title.
	 *
	 * @return void
	 */
	protected function handle_archive(): void {

		if ( $post_type = \get_post_type() ) {
			if ( $post_type !== 'post' ) {
				$this->add_archive_link( $post_type );
			}
		}

		$archive_title = \apply_filters( 'get_the_archive_title', \get_queried_object()->name );

		$this->append_link( $archive_title, 'breadcrumb--archive breadcrumb-current' );
	}

	/**
	 * Builds breadcrumb entries for single posts, including archive link,
	 * parent page links, category hierarchy, and the post title.
	 *
	 * @param string|null $custom_taxonomy Optional taxonomy to use for
	 *     custom post types that don't have standard categories.
	 *
	 * @return void
	 */
	protected function handle_single( ?string $custom_taxonomy = null ): void {

		global $post;
		$post_type = \get_post_type();

		$this->add_archive_link( $post_type );

		$this->add_parent_links( $post );

		$category = \get_the_category();

		if ( ! empty( $category ) ) {
			$last_category = array_values( $category );
			$last_category = end( $last_category );

			$get_cat_parents = rtrim( \get_category_parents( $last_category->term_id, true, ',' ), ',' );
			$cat_parents     = explode( ',', $get_cat_parents );

			foreach ( $cat_parents as $parent ) {
				$this->append_link( $parent, 'breadcrumb--parent-category breadcrumb--current' );
			}
		}

		$taxonomy_exists = \taxonomy_exists( $custom_taxonomy );

		if ( empty( $category ) && ! empty( $custom_taxonomy ) && $taxonomy_exists ) {
			$taxonomy_terms = \get_the_terms( $post->ID, $custom_taxonomy );
			$cat_id         = $taxonomy_terms[0]->term_id;
			$cat_nicename   = $taxonomy_terms[0]->slug;
			$cat_link       = \get_term_link( $cat_id, $custom_taxonomy );
			$cat_name       = $taxonomy_terms[0]->name;

			$this->append_link( $cat_nicename, "breadcrumb--{$post_type}-{$cat_name}", $cat_link );
		}

		$this->append_link( \get_the_title(), 'breadcrumb--current' );
	}

	/**
	 * Builds breadcrumb entries for category archive pages.
	 *
	 * @return void
	 */
	protected function handle_category(): void {
		$post_type = \get_post_type();
		$this->add_archive_link( $post_type );
		$this->append_link( \single_cat_title( '', false ), 'breadcrumb-category' );
	}

	/**
	 * Builds breadcrumb entries for static pages, including parent page
	 * hierarchy.
	 *
	 * @return void
	 */
	protected function handle_page(): void {
		global $post;
		$this->add_parent_links( $post );
		$this->append_link( \get_the_title(), 'breadcrumb-page breadcrumb-current' );
	}

	/**
	 * Builds breadcrumb entries for tag archive pages.
	 *
	 * @return void
	 */
	protected function handle_tag(): void {
		$term_id  = \get_query_var( 'tag_id' );
		$taxonomy = 'post_tag';
		$args     = "include={$term_id}";
		$terms    = \get_terms( $taxonomy, $args );

		$this->append_link( $terms[0]->name, "breadcrumb--tag breadcrumb--{$terms[0]->slug} breadcrumb--current" );
	}

	/**
	 * Builds breadcrumb entries for custom taxonomy archive pages.
	 *
	 * @return void
	 */
	protected function handle_tax(): void {
		$this->handle_archive();
	}

	/**
	 * Builds breadcrumb entries for daily archive pages (year > month > day).
	 *
	 * @return void
	 */
	protected function handle_day(): void {
		$this->append_link( \get_the_time( 'Y' ), "breadcrumb--year", \get_year_link( \get_the_time( 'Y' ) ) );
		$this->append_link( \get_the_time( 'M' ), "breadcrumb--month", \get_month_link( \get_the_time( 'Y' ), \get_the_time( 'm' ) ) );
		$this->append_link( sprintf( "%s %s", \get_the_time( 'jS' ), \get_the_time( 'M' ) ), "breadcrumb--day" );
	}

	/**
	 * Builds breadcrumb entries for monthly archive pages (year > month).
	 *
	 * @return void
	 */
	protected function handle_month(): void {
		$this->append_link( \get_the_time( 'Y' ), "breadcrumb--year", \get_year_link( \get_the_time( 'Y' ) ) );
		$this->append_link( \get_the_time( 'M' ), "breadcrumb--month breadcrumb--current" );
	}

	/**
	 * Builds a breadcrumb entry for yearly archive pages.
	 *
	 * @return void
	 */
	protected function handle_year(): void {
		$this->append_link( \get_the_time( 'Y' ), "breadcrumb--year breadcrumb--current" );
	}

	/**
	 * Builds a breadcrumb entry for author archive pages.
	 *
	 * @return void
	 */
	protected function handle_author(): void {
		global $author;
		$userdata = \get_userdata( $author );
		$this->append_link( $userdata->display_name, "breadcrumb--author breadcrumb--{$userdata->user_nicename}" );
	}

	/**
	 * Appends a page-number breadcrumb entry for paginated results.
	 *
	 * @return void
	 */
	protected function handle_paged(): void {
		$this->append_link( \get_query_var( 'paged' ), "breadcrumb--paged breadcrumb--current" );
	}

	/**
	 * Builds a breadcrumb entry for search results pages.
	 *
	 * @return void
	 */
	protected function handle_search(): void {
		$this->append_link( \_x( "Search results", 'Breadcrumb', THEMENAME ), "breadcrumb--search breadcrumb--current" );
	}

	/**
	 * Builds a breadcrumb entry for 404 pages.
	 *
	 * @return void
	 */
	protected function handle_404(): void {
		$this->append_link( \_x( "Error 404", 'Breadcrumb', THEMENAME ), "breadcrumb--404 breadcrumb--current" );
	}

	/**
	 * Traverses the parent page hierarchy and adds a breadcrumb link for each
	 * ancestor, ordered from the top-level parent down.
	 *
	 * @param \WP_Post $post The current post whose ancestors to traverse.
	 *
	 * @return void
	 */
	protected function add_parent_links( \WP_Post $post ): void {
		if ( $post->post_parent ) {
			$ancestors = \get_post_ancestors( $post->ID );
			$ancestors = array_reverse( $ancestors );

			foreach ( $ancestors as $ancestor ) {
				$this->append_link( \get_the_title( $ancestor ), "breadcrumb-page breadcrumb-{$ancestor}", \get_permalink( $ancestor ) );
			}
		}
	}

	/**
	 * Adds the post-type archive link as a breadcrumb entry, using the
	 * archive page title as the label.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return void
	 */
	protected function add_archive_link( string $post_type ): void {
		$post_type_object  = \get_post_type_object( $post_type );
		$post_type_archive = \apply_filters( 'breadcrumb_archive_link', \get_post_type_archive_link( $post_type ), $post_type_object );

		$archive_title = $post_type === 'post'
			? \get_the_title( \get_option( 'page_for_posts', true ) )
			: $post_type_object->labels->name;

		$archive_title = \apply_filters( 'get_the_archive_title', $archive_title );

		$this->append_link( $archive_title, "breadcrumb-{$post_type}", $post_type_archive );
	}

	/**
	 * Pushes a single breadcrumb entry onto the trail.
	 *
	 * @param string      $title The display text for the breadcrumb.
	 * @param string      $class CSS class(es) for the breadcrumb item.
	 * @param string|null $url   Optional link URL; null for the current item.
	 *
	 * @return void
	 */
	private function append_link( string $title, string $class = '', ?string $url = null ): void {
		$this->breadcrumbs[] = [
			'title' => $title,
			'class' => $class,
			'url'   => $url,
		];
	}
}
