<?php


namespace PressGang\Snippets;

/**
 * Registers additional public query variables with WordPress so they can be
 * used in WP_Query and recognised by the rewrite system.
 *
 * Enable this snippet when your theme needs custom query parameters (e.g. for
 * filtering or sorting) that WordPress should treat as first-class query vars
 * rather than ignoring them.
 */
class AddQueryVars implements SnippetInterface {

	/**
	 * Query variable names to register.
	 *
	 * @var array<int, string>
	 */
	protected array $query_vars;

	/**
	 * Stores the requested query variable names and hooks into the 'query_vars'
	 * filter to register them.
	 *
	 * @param array<int, string> $args Flat array of query variable names to
	 *     register (e.g. ['colour', 'size']).
	 *
	 * @see https://developer.wordpress.org/reference/hooks/query_vars/
	 */
	public function __construct( array $args ) {
		$this->query_vars = $args;
		\add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
	}

	/**
	 * Merges the configured query variable names into the WordPress query vars
	 * whitelist, making them available via get_query_var().
	 *
	 * @param array<int, string> $vars The existing registered query variables.
	 *
	 * @return array<int, string> The expanded list including custom variables.
	 */
	public function add_query_vars( array $vars ): array {
		return array_merge( $vars, $this->query_vars );
	}
}
