<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Filters;
use PressGang\Snippets\Content\RemoveOembedAuthor;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\RemoveOembedAuthor
 */
class RemoveOembedAuthorTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filter(): void {
		Filters\expectAdded( 'oembed_response_data' )->once();

		new RemoveOembedAuthor( [] );
	}

	/**
	 * @return void
	 */
	public function test_removes_author_fields(): void {
		$snippet = new RemoveOembedAuthor( [] );

		$data = [
			'title'      => 'My Post',
			'author_url' => 'https://example.com/author',
			'author_name' => 'John',
			'html'       => '<p>content</p>',
		];

		$result = $snippet->disable_embeds_filter_oembed_response_data( $data );

		$this->assertArrayNotHasKey( 'author_url', $result );
		$this->assertArrayNotHasKey( 'author_name', $result );
		$this->assertSame( 'My Post', $result['title'] );
		$this->assertSame( '<p>content</p>', $result['html'] );
	}

	/**
	 * @return void
	 */
	public function test_handles_data_without_author_fields(): void {
		$snippet = new RemoveOembedAuthor( [] );

		$data   = [ 'title' => 'My Post', 'html' => '<p>content</p>' ];
		$result = $snippet->disable_embeds_filter_oembed_response_data( $data );

		$this->assertSame( $data, $result );
	}
}
