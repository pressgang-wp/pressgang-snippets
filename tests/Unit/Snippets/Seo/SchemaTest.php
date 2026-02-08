<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Seo;

use Brain\Monkey\Actions;
use PressGang\Snippets\Seo\Schema;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Seo\Schema
 */
class SchemaTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'wp_head' )->times( 8 );

		new Schema( [] );
	}
}
