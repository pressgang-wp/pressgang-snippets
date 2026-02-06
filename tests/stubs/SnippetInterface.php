<?php

namespace PressGang\Snippets;

/**
 * Stub of the SnippetInterface from pressgang-wp/pressgang.
 *
 * The real interface lives in the parent theme package. This stub exists so
 * unit tests can run without requiring the entire parent theme as a dependency
 * (which would create a PSR-4 namespace collision on PressGang\Snippets\).
 */
interface SnippetInterface {

	/**
	 * @param array<string, mixed> $args
	 */
	public function __construct( array $args );
}
