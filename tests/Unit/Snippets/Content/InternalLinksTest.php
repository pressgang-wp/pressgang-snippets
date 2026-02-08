<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\InternalLinks;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\InternalLinks
 */
class InternalLinksTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filter(): void {
		Filters\expectAdded( 'the_content' )->once();

		new InternalLinks( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_internal_links_returns_content_when_no_terms(): void {
		$snippet = new InternalLinks( [] );

		Functions\expect( 'get_terms' )
			->once()
			->andReturn( [] );

		$this->assertSame( 'Hello world', $snippet->add_internal_links( 'Hello world' ) );
	}
}
