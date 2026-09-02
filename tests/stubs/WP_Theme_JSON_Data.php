<?php

/**
 * Minimal stand-in for WordPress' WP_Theme_JSON_Data, which snippets receive
 * from the global styles filters. Stores the array it is given and hands it
 * back, which is all the unit tests need to observe.
 */
class WP_Theme_JSON_Data {

	/**
	 * @var array
	 */
	private array $data;

	/**
	 * @var string
	 */
	private string $origin;

	/**
	 * @param array  $data
	 * @param string $origin
	 */
	public function __construct( array $data = [], string $origin = 'theme' ) {
		$this->data   = $data;
		$this->origin = $origin;
	}

	/**
	 * @return array
	 */
	public function get_data(): array {
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function get_origin(): string {
		return $this->origin;
	}
}
