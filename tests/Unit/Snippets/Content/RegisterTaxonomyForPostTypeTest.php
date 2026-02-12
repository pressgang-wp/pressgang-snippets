<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\RegisterTaxonomyForPostType;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\RegisterTaxonomyForPostType
 */
class RegisterTaxonomyForPostTypeTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_init_hook(): void {
		Actions\expectAdded( 'init' )->once();

		new RegisterTaxonomyForPostType( [ 'taxonomy' => 'category', 'post_type' => 'page' ] );
	}

	/**
	 * @return void
	 */
	public function test_init_callback_calls_register_taxonomy_for_object_type(): void {
		Functions\expect( 'add_action' )
			->once()
			->with( 'init', \Mockery::type( 'Closure' ) )
			->andReturnUsing( function ( string $hook, callable $callback ) {
				// Immediately invoke the callback to test its behaviour.
				Functions\expect( 'register_taxonomy_for_object_type' )
					->once()
					->with( 'post_tag', 'product' );

				$callback();
			} );

		new RegisterTaxonomyForPostType( [ 'taxonomy' => 'post_tag', 'post_type' => 'product' ] );
	}
}
