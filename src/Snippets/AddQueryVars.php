<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class AddQueryVars
 *
 * This class is responsible for modifying the query variables used in WordPress.
 * It is designed to add custom query variables for specific filtering purposes in WP_Query.
 */
class AddQueryVars implements SnippetInterface {

	/**
	 * The Query Vars to enable in WP_Query.
	 *
	 * @var array
	 */
	protected array $query_vars;

	/**
	 * Constructor for the QueryVars class.
	 *
	 * Adds a query_vars hook to modify the query variables in WordPress.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/query_vars/
	 * @param array $args Custom query variables to be added.
	 */
	public function __construct( array $args ) {
		$this->query_vars = $args;
		\add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
	}

	/**
	 * Adds custom query variables.
	 *
	 * This function is hooked to the 'query_vars' filter. It adds custom query variables
	 * to the WordPress query variables, allowing them to be used in WordPress queries for filtering WP_Query.
	 *
	 * @hooked query_vars
	 * @param array $vars The array of existing query variables.
	 * @return array The modified array of query variables.
	 */
	public function add_query_vars( $vars ): array {
		$vars = array_merge( $vars, $this->query_vars );

		return $vars;
	}

}
