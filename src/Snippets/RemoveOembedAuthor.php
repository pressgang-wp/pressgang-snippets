<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class RemoveOembedAuthor
 *
 * This class implements the SnippetInterface to remove the author details
 * (author URL and author name) from the oEmbed response data.
 *
 * @package PressGang\Snippets
 */
class RemoveOembedAuthor implements SnippetInterface {

	/**
	 * Constructor
	 *
	 * Registers the filter to remove author information from oEmbed response data.
	 *
	 * @param array $args An array of arguments, currently unused.
	 *
	 * @return void
	 */
	public function __construct( array $args ) {
		\add_filter( 'oembed_response_data', [ $this, 'disable_embeds_filter_oembed_response_data' ] );
	}

	/**
	 * Filters the oEmbed response data to remove the author details.
	 *
	 * This method unsets the `author_url` and `author_name` fields from the oEmbed response data.
	 *
	 * @param array $data The oEmbed response data.
	 *
	 * @return array The filtered oEmbed response data with author details removed.
	 */
	public function disable_embeds_filter_oembed_response_data( array $data ): array {
		unset( $data['author_url'] );
		unset( $data['author_name'] );

		return $data;
	}
}