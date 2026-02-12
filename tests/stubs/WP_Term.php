<?php

/**
 * Minimal WP_Term stub for unit tests.
 *
 * Provides the properties used by snippet code that type-hints \WP_Term.
 */
#[\AllowDynamicProperties]
class WP_Term {

	public int $term_id = 0;

	public string $name = '';

	public string $slug = '';
}
