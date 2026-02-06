<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Google;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Google\TagManager;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * Testable subclass that captures render_template calls instead of calling
 * Timber::render(). Named class avoids BrainMonkey's anonymous-class name
 * parsing issue.
 */
class TestableTagManager extends TagManager {

	/** @var array<int, array{template: string, context: array<string, mixed>}> */
	public array $rendered = [];

	protected function render_template( string $template, array $context ): void {
		$this->rendered[] = [ 'template' => $template, 'context' => $context ];
	}
}

/**
 * @covers \PressGang\Snippets\Google\TagManager
 */
class TagManagerTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->once();
		Actions\expectAdded( 'wp_head' )->once();
		Actions\expectAdded( 'wp_body_open' )->once();

		new TagManager( [] );
	}

	/**
	 * @return void
	 */
	public function test_script_renders_when_container_id_set(): void {
		$snippet = new TestableTagManager( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-tag-manager-id' )
			->andReturn( 'GTM-XXXXX' );

		$snippet->script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/google/tag-manager.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( 'GTM-XXXXX', $snippet->rendered[0]['context']['google_tag_manager_id'] );
	}

	/**
	 * @return void
	 */
	public function test_script_skips_when_container_id_empty(): void {
		$snippet = new TestableTagManager( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-tag-manager-id' )
			->andReturn( '' );

		$snippet->script();

		$this->assertCount( 0, $snippet->rendered );
	}

	/**
	 * @return void
	 */
	public function test_no_script_renders_for_logged_out_user(): void {
		$snippet = new TestableTagManager( [] );

		Functions\expect( 'get_theme_mod' )
			->andReturnUsing( function ( string $key ) {
				return match ( $key ) {
					'google-tag-manager-track-logged-in' => false,
					'google-tag-manager-id'              => 'GTM-XXXXX',
					default                              => null,
				};
			} );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( false );

		$snippet->no_script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'snippets/google/tag-manager-no-script.twig', $snippet->rendered[0]['template'] );
		$this->assertSame( 'GTM-XXXXX', $snippet->rendered[0]['context']['google_tag_manager_id'] );
	}

	/**
	 * @return void
	 */
	public function test_no_script_skips_for_logged_in_user_when_toggle_off(): void {
		$snippet = new TestableTagManager( [] );

		Functions\expect( 'get_theme_mod' )
			->with( 'google-tag-manager-track-logged-in' )
			->andReturn( false );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( true );

		$snippet->no_script();

		$this->assertCount( 0, $snippet->rendered );
	}

	/**
	 * @return void
	 */
	public function test_no_script_renders_for_logged_in_user_when_toggle_on(): void {
		$snippet = new TestableTagManager( [] );

		Functions\expect( 'get_theme_mod' )
			->andReturnUsing( function ( string $key ) {
				return match ( $key ) {
					'google-tag-manager-track-logged-in' => true,
					'google-tag-manager-id'              => 'GTM-XXXXX',
					default                              => null,
				};
			} );

		Functions\expect( 'is_user_logged_in' )
			->andReturn( true );

		$snippet->no_script();

		$this->assertCount( 1, $snippet->rendered );
		$this->assertSame( 'GTM-XXXXX', $snippet->rendered[0]['context']['google_tag_manager_id'] );
	}
}
