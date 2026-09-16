<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Removes selected built-in WordPress content routes and returns a genuine 404
 * when they are requested through either pretty permalinks or query strings.
 *
 * Enable this snippet when a site does not publish some of WordPress's default
 * post, category, tag, author, or date views. Removing the rewrite rules keeps
 * those URLs out of the site's public route inventory; the request guard also
 * covers previously cached rules and query-string forms such as `?author=1`.
 */
class DisableCoreArchives implements SnippetInterface {

	/** @var list<string> */
	private array $routes;

	/**
	 * Registers rewrite-rule filters and WordPress's status-handling filter.
	 *
	 * Supported route names are `post`, `category`, `tag`, `author`, and `date`.
	 * Author and date archives are disabled by default. Pass an explicit `routes`
	 * list when the site also needs to disable built-in posts or taxonomies.
	 * Rewrite rules must be flushed once after this configuration changes.
	 *
	 * @param array{routes?: list<string>} $args Snippet configuration.
	 */
	public function __construct( array $args ) {
		$supported    = [ 'post', 'category', 'tag', 'author', 'date' ];
		$configured   = $args['routes'] ?? [ 'author', 'date' ];
		$this->routes = array_values( array_intersect( $supported, $configured ) );

		$rewrite_filters = [
			'post'     => 'post_rewrite_rules',
			'category' => 'category_rewrite_rules',
			'tag'      => 'post_tag_rewrite_rules',
			'author'   => 'author_rewrite_rules',
			'date'     => 'date_rewrite_rules',
		];

		foreach ( $this->routes as $route ) {
			\add_filter( $rewrite_filters[ $route ], [ $this, 'remove_rewrite_rules' ] );
		}

		\add_filter( 'pre_handle_404', [ $this, 'maybe_set_404' ], 10, 2 );
	}

	/**
	 * Removes every generated rewrite rule for a disabled route type.
	 *
	 * @param array<string, string> $rules WordPress rewrite rules for one route type.
	 *
	 * @return array<string, string> An empty rule set.
	 */
	public function remove_rewrite_rules( array $rules ): array {
		return [];
	}

	/**
	 * Converts a disabled route query to WordPress's normal non-cacheable 404
	 * response before core decides which HTTP status to send.
	 *
	 * @param bool      $preempt Whether another callback already handled the status.
	 * @param \WP_Query $query   The main WordPress query being status-checked.
	 *
	 * @return bool True when this snippet handled the response; otherwise the
	 *              existing filter value.
	 */
	public function maybe_set_404( bool $preempt, \WP_Query $query ): bool {
		if ( $preempt || ! $this->is_disabled_route( $query ) ) {
			return $preempt;
		}

		$query->set_404();
		\status_header( 404 );
		\nocache_headers();

		return true;
	}

	/**
	 * Determines whether the current main query represents a disabled route.
	 *
	 * @param \WP_Query $query The main WordPress query.
	 *
	 * @return bool Whether the query should return a 404.
	 */
	private function is_disabled_route( \WP_Query $query ): bool {
		foreach ( $this->routes as $route ) {
			$matches = match ( $route ) {
				'post'     => $query->is_singular( 'post' ),
				'category' => $query->is_category(),
				'tag'      => $query->is_tag(),
				'author'   => $query->is_author(),
				'date'     => $query->is_date(),
			};

			if ( $matches ) {
				return true;
			}
		}

		return false;
	}
}
