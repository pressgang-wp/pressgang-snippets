<?php

namespace PressGang\Snippets;

/**
 * Class ArchiveTitles
 *
 * Customizes archive titles in WordPress using the Customizer.
 *
 * @package PressGang\Snippets
 */
class ArchiveTitles implements SnippetInterface {

	/**
	 * @var array
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
	 * Constructor to initialize hooks and configure archive titles.
	 *
	 * @param array $args Specify which archive titles to include and their default labels.
	 */
	public function __construct( array $args = [] ) {
		// If $args is empty, use all available titles
		$this->available_titles = ! empty( $args ) ? array_intersect_key( $this->available_titles, $args ) + $args : $this->available_titles;

		\add_action( 'customize_register', [ $this, 'customizer' ] );
		\add_filter( 'get_the_archive_title', [ $this, 'custom_archive_title' ] );
	}

	/**
	 * Add archive title settings to the WordPress customizer.
	 *
	 * @param \WP_Customize_Manager $wp_customize The WordPress Customizer object.
	 *
	 * @return void
	 */
	public function customizer( \WP_Customize_Manager $wp_customize ): void {
		// Check if the section already exists
		if ( ! $wp_customize->get_section( 'archive-titles' ) ) {
			$wp_customize->add_section( 'archive-titles', [
				'title' => __( 'Archive Titles', THEMENAME ),
			] );
		}

		foreach ( $this->available_titles as $setting => $default ) {
			$wp_customize->add_setting( $setting, [
				'default'           => $default,
				'sanitize_callback' => 'sanitize_text_field',
			] );

			$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, $setting, [
				'label'   => __( ucwords( str_replace( '_', ' ', $setting ) ), THEMENAME ),
				'section' => 'archive-titles',
				'type'    => 'text',
			] ) );
		}
	}

	/**
	 * Customize the archive title based on the current archive type.
	 *
	 * @param string $title The original archive title.
	 *
	 * @return string The customized archive title.
	 */
	public function custom_archive_title( string $title ): string {
		$modifications = [
			'is_category'          => [ 'single_cat_title', \single_cat_title( '', false ) ],
			'is_tag'               => [ 'single_tag_title', \single_tag_title( '', false ) ],
			'is_tax'               => [ 'single_cat_title', \single_term_title( '', false ) ],
			'is_author'            => [ 'single_author_title', '<span class="vcard">' . \get_the_author() . '</span>' ],
			'is_year'              => [
				'single_year_title',
				\get_the_date( _x( 'Y', 'yearly archives date format' ) )
			],
			'is_month'             => [
				'single_month_title',
				\get_the_date( _x( 'F Y', 'monthly archives date format' ) )
			],
			'is_day'               => [
				'single_day_title',
				\get_the_date( _x( 'F j, Y', 'daily archives date format' ) )
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
