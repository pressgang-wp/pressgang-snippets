<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Provides Twig helpers and context for building taxonomy filter forms.
 *
 * Enable this snippet to expose selected filter inputs and lazy taxonomy
 * lookups in Twig. Taxonomies can be configured via $args['taxonomies'] or
 * via Config::get('filter-taxonomies') if available.
 */
class TaxonomyFilter implements SnippetInterface {

	/**
	 * @var array<string, mixed>
	 */
	protected array $inputs = [];

	/**
	 * @var array<int, string>
	 */
	protected array $taxonomies = [];

	/**
	 * @var array<string, array<int, \Timber\Term>>
	 */
	protected array $terms = [];

	/**
	 * Magic accessor for lazy-loading taxonomy terms.
	 *
	 * @param string $taxonomy
	 *
	 * @return array<int, \Timber\Term>|null
	 */
	public function __get( string $taxonomy ) {
		if ( in_array( $taxonomy, $this->taxonomies, true ) ) {
			if ( ! array_key_exists( $taxonomy, $this->terms ) ) {
				$this->terms[ $taxonomy ] = Timber::get_terms( [
					'taxonomy' => [ $taxonomy ],
				] );
			}

			return $this->terms[ $taxonomy ];
		}

		return null;
	}

	/**
	 * Stores taxonomies and hooks into Timber context/Twig filters.
	 *
	 * @param array{taxonomies?: array<int, string>, custom_taxonomies?: array<int, string>} $args
	 */
	public function __construct( array $args ) {
		$this->taxonomies = $this->resolve_taxonomies( $args );

		foreach ( $this->taxonomies as $taxonomy ) {
			$slugs = filter_input( INPUT_GET, $taxonomy, FILTER_DEFAULT );
			$this->inputs[ $taxonomy ] = $slugs;
		}

		if ( ! empty( $this->inputs ) ) {
			\add_filter( 'timber/context', [ $this, 'add_filter_inputs_to_context' ] );
		}

		\add_filter( 'timber/twig', [ $this, 'add_taxonomy_lookups_to_twig' ], 100 );
	}

	/**
	 * Adds filter inputs to the Twig context.
	 *
	 * @param array<string, mixed> $context
	 *
	 * @return array<string, mixed>
	 */
	public function add_filter_inputs_to_context( array $context ): array {
		$context['inputs'] = $this->inputs;

		return $context;
	}

	/**
	 * Adds taxonomy_lookup() to Twig for lazy term lookups.
	 *
	 * @param Environment $twig
	 *
	 * @return Environment
	 */
	public function add_taxonomy_lookups_to_twig( Environment $twig ): Environment {
		$twig->addFunction( new TwigFunction( 'taxonomy_lookup', [ $this, 'taxonomy_lookup' ] ) );

		return $twig;
	}

	/**
	 * Returns terms for a taxonomy using lazy loading.
	 *
	 * @param string $taxonomy
	 *
	 * @return array<int, \Timber\Term>|null
	 */
	public function taxonomy_lookup( string $taxonomy ) {
		return $this->__get( $taxonomy );
	}

	/**
	 * Resolve the taxonomy list from args or Config fallback.
	 *
	 * @param array{taxonomies?: array<int, string>, custom_taxonomies?: array<int, string>} $args
	 *
	 * @return array<int, string>
	 */
	private function resolve_taxonomies( array $args ): array {
		if ( ! empty( $args['taxonomies'] ) && is_array( $args['taxonomies'] ) ) {
			return $args['taxonomies'];
		}

		if ( class_exists( 'Config' ) && method_exists( 'Config', 'get' ) ) {
			$taxonomies = \Config::get( 'filter-taxonomies' );
			if ( ! empty( $taxonomies ) ) {
				return $taxonomies;
			}

			$custom_taxonomies = \Config::get( 'custom-taxonomies' );
			if ( is_array( $custom_taxonomies ) ) {
				return array_merge( array_keys( $custom_taxonomies ), [ 'tag', 'category' ] );
			}
		}

		if ( ! empty( $args['custom_taxonomies'] ) && is_array( $args['custom_taxonomies'] ) ) {
			return array_merge( $args['custom_taxonomies'], [ 'tag', 'category' ] );
		}

		return [ 'tag', 'category' ];
	}
}
