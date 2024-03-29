<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\add_action;
use function PressGang\Snippets\add_rewrite_rule;
use function PressGang\Snippets\apply_filters;
use function PressGang\Snippets\get_field;
use function PressGang\Snippets\get_permalink;
use function PressGang\Snippets\get_post_modified_time;
use function PressGang\Snippets\get_post_types;
use function PressGang\Snippets\get_taxonomies;
use function PressGang\Snippets\get_term_link;
use function PressGang\Snippets\is_multisite;
use function PressGang\Snippets\wpml_get_active_languages_filter;
use function PressGang\Snippets\wpml_object_id_filter;

class Sitemap {

	private $change_frequency = 'weekly';
	private $priority = '0.5';

	// available frequencies in order of least recent first.
	private $frequencies = [
		'never',
		'yearly',
		'monthly',
		'weekly',
		'daily',
		'hourly',
		'always',
	];

	/**
	 * __construct
	 *
	 */
	public function __construct(
		$change_frequency = 'daily',
		$priority = '0.5'
	) {
		// set the default change frequency
		if ( in_array( $change_frequency, $this->frequencies ) ) {
			$this->change_frequency = $change_frequency;
		}

		// set the default priority
		$this->priority = number_format( $priority, 1 );

		// update sitemap.xml when a post is saved
		add_action( 'save_post', [ $this, 'create_sitemap' ], 10, 3 );

		// we need to redirect requests to individual sitemaps files on multisite installs
		if ( is_multisite() ) {
			// TODO - not working! add redirect manually
			// add_action('init', array($this, 'add_rewrite'));
		}
	}

	/**
	 * Function to create sitemap.xml file in root directory
	 *
	 */
	public function create_sitemap( $post_id, $post, $update ) {
		$post_types = get_post_types( [
			'public'             => true,
			'publicly_queryable' => true,
			// TODO this seems to prevent 'page' post types returning?
		] );

		$post_types[] = 'page';

		$nodes = [];

		$posts = \get_posts( [
			'numberposts' => - 1,
			'orderby'     => [
				'type'       => 'ASC',
				'menu_order' => 'ASC',
				'modified'   => 'DESC',
			],
			'post_type'   => $post_types,
			'post_status' => 'publish',
		] );

		$langs = [];

		foreach ( $posts as &$post ) {
			$node = [
				'loc'        => \get_permalink( $post->ID ),
				'lastmod'    => get_post_modified_time( 'c', false, $post ),
				'changefreq' => $this->get_post_change_frequency( $post ),
				'priority'   => $this->get_priority( $post ),
			];

			// get translations if WPML
			if ( function_exists( 'wpml_get_active_languages_filter' ) ) {
				$langs = wpml_get_active_languages_filter( null,
					[ 'skip_missing' => true ] );

				if ( count( $langs ) > 1 ) {
					foreach ( $langs as &$lang ) {
						if ( $icl_id = wpml_object_id_filter( $post->ID,
							$post->post_type, false, $lang['code'] ) ) {
							if ( $link = get_permalink( $icl_id ) ) {
								$node['lang'][ $lang['tag'] ] = $link;
							}
						}
					}
				}
			}

			$nodes[] = $node;
		}

		$taxonomies = get_taxonomies( [
			'public'             => true,
			'publicly_queryable' => true,
		] );

		$terms = \get_terms( [
			'taxonomy'   => $taxonomies,
			'hide_empty' => true, // hide empty terms
		] );

		foreach ( $terms as &$term ) {
			$lastest_post = \get_posts( [
				'numberposts' => 1,
				'orderby'     => [ 'modified' => 'DESC' ],
				'tax_query'   => [
					[
						'taxonomy'         => $term->taxonomy,
						'field'            => 'id',
						'terms'            => $term->ID,
						'include_children' => false,
					],
				],
			] );

			// has posts
			if ( $lastest_post ) {
				$node = [
					'loc'        => get_term_link( $term ),
					'lastmod'    => get_post_modified_time( 'c', false,
						$lastest_post ),
					'changefreq' => $this->change_frequency,
					// TODO compare recent posts instead for terms
					'priority'   => $this->get_priority( $term ),
				];

				// get translations if WPML
				if ( function_exists( 'wpml_get_active_languages_filter' ) ) {
					if ( count( $langs ) > 1 ) {
						foreach ( $langs as &$lang ) {
							if ( $icl_id = wpml_object_id_filter( $term->ID,
								$term->taxonomy, false, $lang['code'] ) ) {
								if ( $link = get_term_link( $icl_id,
									$term->taxonomy ) ) {
									$node['lang'][ $lang['tag'] ] = $link;
								}
							}
						}
					}
				}

				$nodes[] = $node;
			}
		}

		$data = [
			'nodes' => $nodes,
		];

		$sitemap = \Timber\Timber::compile( 'sitemap-xml.twig', $data );

		$path = $this->path();

		if ( $fp = fopen( $path, 'w' ) ) {
			fwrite( $fp, $sitemap );
			fclose( $fp );
		}
	}

	/**
	 * get_post_change_frequency
	 *
	 * @param $post
	 *
	 * @return string
	 */
	private function get_post_change_frequency( $post ) {
		// see if a custom field has been set for the post change frequency
		$change_frequency = function_exists( 'get_field' ) ? get_field( 'change_frequency',
			$post->ID ) : null;

		if ( ! in_array( $change_frequency, $this->frequencies ) ) {
			// otherwise determine a value for the change frequency based on the last modified param
			$interval = date_diff( new \DateTime( $post->post_modified ),
				new \DateTime( "now" ) );

			$change_frequency = 'never';

			if ( $interval->y > 0 ) {
				$change_frequency = 'yearly';
			} elseif ( $interval->m > 0 ) {
				$change_frequency = 'monthly';
			} elseif ( $interval->d > 6 ) {
				$change_frequency = 'weekly';
			} elseif ( $interval->d > 0 ) {
				$change_frequency = 'daily';
			} elseif ( $interval->h > 0 ) {
				$change_frequency = 'hourly';
			} elseif ( $interval->i > 0 ) {
				$change_frequency = 'always';
			}
		}

		$val = apply_filters( 'sitemap_post_change_frequency',
			$change_frequency, $post );

		if ( in_array( $val, $this->frequencies ) ) {
			$change_frequency = $val;
		}

		$index = array_search( $change_frequency, $this->frequencies );

		// set the most recent change_frequency
		if ( $index > array_search( $this->change_frequency,
				$this->frequencies ) ) {
			$this->change_frequency = $change_frequency;
		}

		return $change_frequency;
	}

	/**
	 * get_priority
	 *
	 * Check the $object (WP_Post or WP_Term) for a custom priority (check ACF)
	 *
	 * @param $object
	 */
	private function get_priority( $object ) {
		// see if a custom field has been set for the post change frequency
		$priority = function_exists( 'get_field' ) ? get_field( 'priority',
			$object->ID ) : null;

		if ( ! $priority ) {
			// otherwise set it to default
			$priority = $this->priority;
		}

		$priority = number_format( apply_filters( 'sitemap_post_priority',
			$priority, $object ), 1 );

		return $priority;
	}

	/**
	 * filename
	 *
	 * @return string
	 */
	private function filename() {
		$filename = "sitemap.xml";

		if ( is_multisite() ) {
			$filename = sprintf( "%s-sitemap.xml", get_blog_details()->domain );
		}

		return $filename;
	}

	/**
	 * path
	 *
	 * @return string
	 */
	private function path() {
		return sprintf( "%s%s", ABSPATH, $this->filename() );
	}

	/**
	 * add_rewrite
	 *
	 */
	public function add_rewrite() {
		global $wp_rewrite;
		add_rewrite_rule( 'sitemap\.xml$', $this->filename() );
		$wp_rewrite->flush_rules();
	}

}

new Sitemap();
