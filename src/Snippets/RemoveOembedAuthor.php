<?php

declare(strict_types=1);

namespace PressGang\Snippets;

/**
 * Strips author details (author_url, author_name) from the oEmbed response
 * data so that embeds of your content on other sites do not expose author
 * information.
 *
 * Enable this snippet when you want to remove author attribution from oEmbed
 * previews of your posts.
 */
class RemoveOembedAuthor implements SnippetInterface {

	/**
	 * Hooks the oembed_response_data filter.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'oembed_response_data', [ $this, 'disable_embeds_filter_oembed_response_data' ] );
	}

	/**
	 * Removes the author_url and author_name keys from the oEmbed response.
	 *
	 * @param array<string, mixed> $data The oEmbed response data.
	 *
	 * @return array<string, mixed> The filtered response with author details removed.
	 */
	public function disable_embeds_filter_oembed_response_data( array $data ): array {
		unset( $data['author_url'], $data['author_name'] );

		return $data;
	}
}