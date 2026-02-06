<?php

declare(strict_types=1);

namespace PressGang\Snippets;

/**
 * Allows site administrators to customise archive page titles (category,
 * tag, author, date, post-type archive, and search) via the WordPress
 * Customizer instead of accepting WordPress's hard-coded defaults.
 *
 * Enable this snippet when you want editorial control over how archive
 * headings read — for example changing "Category: News" to just "News", or
 * localising archive titles.
 */
class ArchiveTitles implements SnippetInterface {

	/**
	 * Map of Customizer setting keys to their default title patterns.
	 * Use %s as a placeholder for the dynamic value (category name, etc.).
	 *
	 * @var array<string, string>
	 */
	private array $available_titles = [
		'archives_title'          => 'Archives',
		'single_cat_title'        => 'Category: %s',
		'single_tag_title'        => 'Tag: %s',
		'single_author_title'     => 'Author: %s',
		'single_year_title'       => 'Year: %s',
		'single_month_title'      => 'Month: %s',
		'single_day_title'        => 'Day: %s',
		'post_type_archive_title' => 'Archives: %s',
		'search_results_title'    => 'Search Results for &#8220;%s&#8221;',
	];

	/**
	 * Registers Customizer controls for archive titles and hooks the title
	 * filter into get_the_archive_title.
	 *
	 * @param array<string, string> $args Optional. Keys matching
	 *     available_titles to include (with optional custom defaults). When
	 *     empty, all title types are registered.
	 */
	public function __construct( array $args = [] ) {
		$this->available_titles = ! empty( $args ) ? array_intersect_key( $this->available_titles, $args ) + $args : $this->available_titles;

		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_filter( 'get_the_archive_title', [ $this, 'custom_archive_title' ] );
	}

	/**
	 * Registers an "Archive Titles" Customizer section with a text control for
	 * each configured title type.
	 *
	 * @param \WP_Customize_Manager $wp_customize The Customizer manager instance.
	 *
	 * @return void
	 */
	public function customizer( \WP_Customize_Manager $wp_customize ): void {
		if ( ! $wp_customize->get_section( 'archive-titles' ) ) {
			$wp_customize->add_section( 'archive-titles', [
				'title' => \__( 'Archive Titles', THEMENAME ),
			] );
		}

		foreach ( $this->available_titles as $setting => $default ) {
			$wp_customize->add_setting( $setting, [
				'default'           => $default,
				'sanitize_callback' => 'sanitize_text_field',
			] );

			$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, $setting, [
				'label'   => \__( ucwords( str_replace( '_', ' ', $setting ) ), THEMENAME ),
				'section' => 'archive-titles',
				'type'    => 'text',
			] ) );
		}
	}

	/**
	 * Replaces the default archive title with the Customizer value for the
	 * current archive type, using sprintf to insert the dynamic portion
	 * (category name, date, search query, etc.).
	 *
	 * @param string $title The original archive title from WordPress.
	 *
	 * @return string The customised archive title.
	 */
	public function custom_archive_title( string $title ): string {
		$modifications = [
			'is_category'          => [ 'single_cat_title', \single_cat_title( '', false ) ],
			'is_tag'               => [ 'single_tag_title', \single_tag_title( '', false ) ],
			'is_tax'               => [ 'single_cat_title', \single_term_title( '', false ) ],
			'is_author'            => [ 'single_author_title', '<span class="vcard">' . \get_the_author() . '</span>' ],
			'is_year'              => [
				'single_year_title',
				\get_the_date( \_x( 'Y', 'yearly archives date format' ) )
			],
			'is_month'             => [
				'single_month_title',
				\get_the_date( \_x( 'F Y', 'monthly archives date format' ) )
			],
			'is_day'               => [
				'single_day_title',
				\get_the_date( \_x( 'F j, Y', 'daily archives date format' ) )
			],
			'is_post_type_archive' => [ 'post_type_archive_title', \post_type_archive_title( '', false ) ],
			'is_search'            => [ 'search_results_title', \get_search_query() ],
		];

		foreach ( $modifications as $condition => $mod ) {
			if ( \call_user_func( $condition ) ) {
				$title = sprintf( \get_theme_mod( $mod[0], $this->available_titles[ $mod[0] ] ?? '' ), $mod[1] );
				break;
			}
		}

		if ( $title === 'Archives' ) {
			$title = \get_theme_mod( 'archives_title', $this->available_titles['archives_title'] );
		}

		return $title;
	}
}
