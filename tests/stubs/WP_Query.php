<?php

/**
 * Minimal WP_Query stub for unit tests.
 *
 * Provides the subset of WP_Query's API used by snippet tests (is_search, set).
 */
class WP_Query {

	public bool $is_search = true;

	/** @var array<string, mixed> */
	public array $set_data = [];

	public function is_search(): bool {
		return $this->is_search;
	}

	public function set( string $key, $value ): void {
		$this->set_data[ $key ] = $value;
	}
}
