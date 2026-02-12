<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Functions;
use PressGang\Snippets\Content\AddPostTypeSupport;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\AddPostTypeSupport
 */
class AddPostTypeSupportTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_calls_add_post_type_support(): void {
		Functions\expect( 'add_post_type_support' )
			->once()
			->with( 'page', 'excerpt' );

		new AddPostTypeSupport( [ 'post_type' => 'page', 'feature' => 'excerpt' ] );
	}

	/**
	 * @return void
	 */
	public function test_constructor_passes_custom_post_type_and_feature(): void {
		Functions\expect( 'add_post_type_support' )
			->once()
			->with( 'product', 'custom-fields' );

		new AddPostTypeSupport( [ 'post_type' => 'product', 'feature' => 'custom-fields' ] );
	}
}
