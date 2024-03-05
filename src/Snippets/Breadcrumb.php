<?php

namespace PressGang\Snippets;

use \Twig\Environment;
use \Twig\TwigFunction;
use \Timber\Timber;

/**
 * Class Breadcrumb
 *
 * Handles the generation and rendering of breadcrumb navigation in a WordPress theme.
 * This class dynamically creates breadcrumb links based on the current page context
 * and supports various WordPress templates and conditions like posts, pages, categories,
 * archives, and custom post types.
 *
 * @package PressGang\Snippets
 */
class Breadcrumb implements SnippetInterface {

	public array $breadcrumbs = [];

	/**
	 * Breadcrumb constructor.
	 *
	 * Initializes the breadcrumb class by setting default values and adding the
	 * breadcrumb function to Twig.
	 */
	public function __construct( array $args ) {
		\add_filter( 'timber/twig', [ $this, 'add_to_twig' ] );
	}

	/**
	 * Adds the breadcrumb function to Twig.
	 *
	 * Registers a new 'breadcrumb' function within the Twig environment that can be used
	 * to render breadcrumbs in templates.
	 *
	 * @param Environment $twig The Twig environment to extend.
	 *
	 * @return Environment Modified Twig environment with new function.
	 */
	public function add_to_twig( Environment $twig ): Environment {
		$twig->addFunction( new TwigFunction( 'breadcrumb', [ $this, 'render' ] ) );

		return $twig;
	}

	/**
	 * Renders the breadcrumb trail.
	 *
	 * Calls the links method to build the breadcrumb trail and then renders it using Timber.
	 * The breadcrumb trail is available in Twig templates via the {{ breadcrumb() }} function.
	 */
	public function render(): void {
		$this->generate_links();
		Timber::render( 'snippets/breadcrumb.twig', [ 'breadcrumbs' => $this->breadcrumbs ] );
	}

	/**
	 * Generates the breadcrumb links.
	 *
	 * Constructs the breadcrumb trail based on the current page context, such as
	 * posts, pages, categories, archives, and custom post types. Populates the
	 * breadcrumbs array with the appropriate links and labels.
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
	 * @return void
	 */
	protected function append_home_link(): void {
		$this->append_link( \_x( "Home", 'Breadcrumb', THEMENAME ), 'breadcrumb--home', \get_site_url() );
	}

	/**
	 * TODO handle post type archives
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
	 * TODO handle custom taxonomy links?
	 *
	 * @param $custom_taxonomy
	 *
	 * @return void
	 */
	protected function handle_single( $custom_taxonomy = null ): void {

		global $post;
		$post_type = \get_post_type();

		$this->add_archive_link( $post_type );

		$this->add_parent_links( $post );

		// get post category info
		$category = \get_the_category();

		if ( ! empty( $category ) ) {
			// get the last post category
			$last_category = array_values( $category );
			$last_category = end( $last_category );

			// get the parent categories
			$get_cat_parents = rtrim( \get_category_parents( $last_category->term_id, true, ',' ), ',' );
			$cat_parents     = explode( ',', $get_cat_parents );

			// create breadcrumbs for parents
			foreach ( $cat_parents as $parent ) {
				$this->append_link( $parent, 'breadcrumb--parent-category breadcrumb--current' );
			}
		}

		// if a custom post type within a custom taxonomy
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
	 * @return void
	 */
	protected function handle_category(): void {
		$post_type = \get_post_type();
		$this->add_archive_link( $post_type );
		$this->append_link( \single_cat_title( '', false ), 'breadcrumb-category' );
	}

	/**
	 * @return void
	 */
	protected function handle_page(): void {
		global $post;
		// standard page
		$this->add_parent_links( $post );
		// display current page
		$this->append_link( \get_the_title(), 'breadcrumb-page breadcrumb-current' );
	}

	/**
	 * @return void
	 */
	protected function handle_tag(): void {

		// get tag information
		$term_id  = \get_query_var( 'tag_id' );
		$taxonomy = 'post_tag';
		$args     = "include={$term_id}";
		$terms    = \get_terms( $taxonomy, $args );

		// display the tag name
		$this->append_link( $terms[0]->name, "breadcrumb--tag breadcrumb--{$terms[0]->slug} breadcrumb--current" );
	}

	/**
	 * @return void
	 */
	protected function handle_day(): void {
		// year link
		$this->append_link( \get_the_time( 'Y' ), "breadcrumb--year", \get_year_link( \get_the_time( 'Y' ) ) );

		// month link
		$this->append_link( \get_the_time( 'M' ), "breadcrumb--month", \get_month_link( \get_the_time( 'Y' ), \get_the_time( 'm' ) ) );

		// day link
		$this->append_link( sprintf( "%s %s", \get_the_time( 'jS' ), \get_the_time( 'M' ) ), "breadcrumb--day" );
	}

	/**
	 * @return void
	 */
	protected function handle_month(): void {
		// year link
		$this->append_link( \get_the_time( 'Y' ), "breadcrumb--year", \get_year_link( \get_the_time( 'Y' ) ) );

		// month link
		$this->append_link( \get_the_time( 'M' ), "breadcrumb--month breadcrumb--current" );
	}

	/**
	 * @return void
	 */
	protected function handle_year(): void {
		$this->append_link( \get_the_time( 'Y' ), "breadcrumb--year breadcrumb--current" );
	}

	/**
	 * @return void
	 */
	protected function handle_author(): void {
		global $author;
		$userdata = \get_userdata( $author );
		$this->append_link( $userdata->display_name, "breadcrumb--author breadcrumb--{$userdata->user_nicename}" );

	}

	/**
	 * @return void
	 */
	protected function handle_paged(): void {
		$this->append_link( \get_query_var( 'paged' ), "breadcrumb--paged breadcrumb--current" );
	}

	/**
	 * @return void
	 */
	protected function handle_search(): void {
		$this->append_link( _x( "Search results", 'Breadcrumb', THEMENAME ), "breadcrumb--search breadcrumb--current" );
	}

	/**
	 * @return void
	 */
	protected function handle_404(): void {
		$this->append_link( _x( "Error 404", 'Breadcrumb', THEMENAME ), "breadcrumb--404 breadcrumb--current" );
	}

	/**
	 * Adds parent page links to the breadcrumb trail.
	 *
	 * If the current post is a child page, this function adds links to all parent
	 * pages in the breadcrumb trail.
	 *
	 * @param \WP_Post $post The current post object.
	 */
	protected function add_parent_links( \WP_Post $post ): void {
		if ( $post->post_parent ) {
			// if child page, get parents
			$ancestors = \get_post_ancestors( $post->ID );

			// get parents in the right order
			$ancestors = array_reverse( $ancestors );

			// parent page loop
			foreach ( $ancestors as $ancestor ) {
				$this->append_link( \get_the_title( $ancestor ), "breadcrumb-page breadcrumb-{$ancestor}", \get_permalink( $ancestor ) );
			}
		}
	}

	/**
	 * Adds a link to the post type archive in the breadcrumb trail.
	 *
	 * @param string $post_type The post type to add the archive link for.
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
	 * Appends a link to the breadcrumb trail.
	 *
	 * @param string $title The title of the breadcrumb link.
	 * @param string $class CSS class for the breadcrumb item.
	 * @param string|null $url URL of the breadcrumb link.
	 */
	private function append_link( string $title, string $class = '', string $url = null ): void {
		$this->breadcrumbs[] = [
			'title' => $title,
			'class' => $class,
			'url'   => $url,
		];
	}
}