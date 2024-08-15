<?php

namespace PressGang\Snippets;

/**
 * Class Sitemap
 *
 * Generates a sitemap.xml file for a WordPress site, including posts, pages, and terms.
 *
 * @package PressGang\Snippets
 */
class Sitemap implements SnippetInterface {

	/**
	 * Default change frequency for sitemap entries.
	 */
	private const DEFAULT_CHANGE_FREQUENCY = 'weekly';

	/**
	 * Default priority for sitemap entries.
	 */
	private const DEFAULT_PRIORITY = '0.5';

	/**
	 * Available change frequencies for sitemap entries.
	 *
	 * @var array
	 */
	private array $frequencies = [
		'never',
		'yearly',
		'monthly',
		'weekly',
		'daily',
		'hourly',
		'always',
	];

	/**
	 * Change frequency for the sitemap.
	 *
	 * @var string
	 */
	private string $change_frequency;

	/**
	 * Priority for the sitemap.
	 *
	 * @var string
	 */
	private string $priority;

	/**
	 * Constructor to initialize sitemap generation.
	 *
	 * @param array $args Arguments for customization of change frequency and priority.
	 */
	public function __construct( array $args = [] ) {
		$this->initialize_config( $args );
		\add_action( 'save_post', [ $this, 'create_sitemap' ], 10, 3 );

		if ( \is_multisite() ) {
			// TODO - Implement or fix the redirect manually
			// \add_action('init', [ $this, 'add_rewrite' ]);
		}
	}

	/**
	 * Initialize configuration based on args.
	 *
	 * @param array $args Configuration arguments.
	 *
	 * @return void
	 */
	private function initialize_config( array $args ): void {
		$this->change_frequency = $args['change_frequency'] ?? self::DEFAULT_CHANGE_FREQUENCY;
		$this->priority         = $args['priority'] ?? self::DEFAULT_PRIORITY;

		if ( ! in_array( $this->change_frequency, $this->frequencies, true ) ) {
			$this->change_frequency = self::DEFAULT_CHANGE_FREQUENCY;
		}

		$this->priority = number_format( $this->priority, 1 );
	}

	/**
	 * Create sitemap.xml file.
	 *
	 * @param int $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @param bool $update Whether this is an existing post being updated.
	 */
	public function create_sitemap( int $post_id, \WP_Post $post, bool $update ): void {
		$nodes = array_merge(
			$this->get_post_nodes(),
			$this->get_term_nodes()
		);

		$this->write_sitemap( [ 'nodes' => $nodes ] );
	}

	/**
	 * Get nodes for posts and pages.
	 *
	 * @return array List of nodes.
	 */
	private function get_post_nodes(): array {
		$post_types = $this->get_public_post_types();
		$posts      = $this->query_posts( $post_types );
		$nodes      = [];

		foreach ( $posts as $post ) {
			$nodes[] = $this->build_post_node( $post );
		}

		return $nodes;
	}

	/**
	 * Get nodes for terms (categories, tags, etc.).
	 *
	 * @return array List of nodes.
	 */
	private function get_term_nodes(): array {
		$terms = $this->query_terms();
		$nodes = [];

		foreach ( $terms as $term ) {
			$latest_post = $this->get_latest_post_for_term( $term );

			if ( $latest_post ) {
				$nodes[] = $this->build_term_node( $term, $latest_post );
			}
		}

		return $nodes;
	}

	/**
	 * Get public post types.
	 *
	 * @return array List of public post types.
	 */
	private function get_public_post_types(): array {
		$post_types = \get_post_types( [
			'public'             => true,
			'publicly_queryable' => true,
		] );

		$post_types[] = 'page';

		return $post_types;
	}

	/**
	 * Query posts for sitemap.
	 *
	 * @param array $post_types List of post types to query.
	 *
	 * @return array List of posts.
	 */
	private function query_posts( array $post_types ): array {
		return \get_posts( [
			'numberposts' => - 1,
			'orderby'     => 'modified',
			'post_type'   => $post_types,
			'post_status' => 'publish',
		] );
	}

	/**
	 * Build a node for a post.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return array Node data.
	 */
	private function build_post_node( \WP_Post $post ): array {
		$node = [
			'loc'        => \get_permalink( $post->ID ),
			'lastmod'    => \get_post_modified_time( 'c', false, $post ),
			'changefreq' => $this->get_post_change_frequency( $post ),
			'priority'   => $this->get_priority( $post ),
		];

		return $this->add_wpml_translations( $node, $post );
	}

	/**
	 * Query terms for sitemap.
	 *
	 * @return array List of terms.
	 */
	private function query_terms(): array {
		return \get_terms( [
			'taxonomy'   => \get_taxonomies( [
				'public'             => true,
				'publicly_queryable' => true,
			] ),
			'hide_empty' => true,
		] );
	}

	/**
	 * Get the latest post associated with a term.
	 *
	 * @param \WP_Term $term Term object.
	 *
	 * @return \WP_Post|null Latest post object or null if none found.
	 */
	private function get_latest_post_for_term( \WP_Term $term ): ?\WP_Post {
		$posts = \get_posts( [
			'numberposts' => 1,
			'orderby'     => 'modified',
			'tax_query'   => [
				[
					'taxonomy'         => $term->taxonomy,
					'field'            => 'id',
					'terms'            => $term->ID,
					'include_children' => false,
				],
			],
		] );

		return $posts ? $posts[0] : null;
	}

	/**
	 * Build a node for a term.
	 *
	 * @param \WP_Term $term Term object.
	 * @param \WP_Post $latest_post Latest post object for the term.
	 *
	 * @return array Node data.
	 */
	private function build_term_node( \WP_Term $term, \WP_Post $latest_post ): array {
		$node = [
			'loc'        => \get_term_link( $term ),
			'lastmod'    => \get_post_modified_time( 'c', false, $latest_post ),
			'changefreq' => $this->change_frequency,
			'priority'   => $this->get_priority( $term ),
		];

		return $this->add_wpml_translations( $node, $term );
	}

	/**
	 * Add WPML translations to a node.
	 *
	 * @param array $node Node data.
	 * @param \WP_Term|\WP_Post $object WP_Term or WP_Post object.
	 *
	 * @return array Node data with translations.
	 */
	private function add_wpml_translations( array $node, $object ): array {
		if ( function_exists( 'wpml_get_active_languages_filter' ) ) {
			$languages = \wpml_get_active_languages_filter( null, [ 'skip_missing' => true ] );

			if ( count( $languages ) > 1 ) {
				foreach ( $languages as $language ) {
					$icl_id = \wpml_object_id_filter( $object->ID, $object->post_type ?? $object->taxonomy, false, $language['code'] );
					$link   = isset( $object->post_type ) ? \get_permalink( $icl_id ) : \get_term_link( $icl_id, $object->taxonomy );

					if ( $icl_id && $link ) {
						$node['lang'][ $language['tag'] ] = $link;
					}
				}
			}
		}

		return $node;
	}

	/**
	 * Write sitemap data to file.
	 *
	 * @param array $data Sitemap data.
	 *
	 * @return void
	 */
	private function write_sitemap( array $data ): void {
		$sitemap = \Timber\Timber::compile( 'sitemap-xml.twig', $data );
		$path    = $this->path();

		if ( $fp = fopen( $path, 'w' ) ) {
			fwrite( $fp, $sitemap );
			fclose( $fp );
		} else {
			\error_log( "Failed to write sitemap to $path" );
		}
	}

	/**
	 * Determine the change frequency for a given post.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return string Change frequency.
	 */
	private function get_post_change_frequency( \WP_Post $post ): string {
		$change_frequency = function_exists( 'get_field' ) ? \get_field( 'change_frequency', $post->ID ) : null;

		if ( ! in_array( $change_frequency, $this->frequencies, true ) ) {
			$change_frequency = $this->calculate_change_frequency( $post );
		}

		$change_frequency = \apply_filters( 'sitemap_post_change_frequency', $change_frequency, $post );

		if ( in_array( $change_frequency, $this->frequencies, true ) ) {
			$this->change_frequency = $change_frequency;
		}

		return $change_frequency;
	}

	/**
	 * Calculate the change frequency based on post modification date.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return string Calculated change frequency.
	 * @throws \Exception
	 */
	private function calculate_change_frequency( \WP_Post $post ): string {
		$interval = \date_diff( new \DateTime( $post->post_modified ), new \DateTime( 'now' ) );

		if ( $interval->y > 0 ) {
			return 'yearly';
		} elseif ( $interval->m > 0 ) {
			return 'monthly';
		} elseif ( $interval->d > 6 ) {
			return 'weekly';
		} elseif ( $interval->d > 0 ) {
			return 'daily';
		} elseif ( $interval->h > 0 ) {
			return 'hourly';
		} elseif ( $interval->i > 0 ) {
			return 'always';
		}

		return 'never';
	}

	/**
	 * Determine the priority for a given object (post or term).
	 *
	 * @param \WP_Term|\WP_Post $object WP_Post or WP_Term object.
	 *
	 * @return string Priority.
	 */
	private function get_priority( $object ): string {
		$priority = function_exists( 'get_field' ) ? \get_field( 'priority', $object->ID ) : null;

		if ( ! $priority ) {
			$priority = $this->priority;
		}

		return number_format( \apply_filters( 'sitemap_post_priority', $priority, $object ), 1 );
	}

	/**
	 * Generate the sitemap filename.
	 *
	 * @return string
	 */
	private function filename(): string {
		$filename = 'sitemap.xml';

		if ( \is_multisite() ) {
			$filename = sprintf( '%s-sitemap.xml', \get_blog_details()->domain );
		}

		return $filename;
	}

	/**
	 * Generate the full path for the sitemap file.
	 *
	 * @return string Full file path.
	 */
	private function path(): string {
		return ABSPATH . $this->filename();
	}

	/**
	 * Add a rewrite rule for the sitemap (if required).
	 *
	 * @return void
	 */
	public function add_rewrite(): void {
		global $wp_rewrite;
		\add_rewrite_rule( 'sitemap\.xml$', $this->filename() );
		$wp_rewrite->flush_rules();
	}
}