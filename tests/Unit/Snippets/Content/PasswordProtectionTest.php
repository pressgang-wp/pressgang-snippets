<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Content;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Content\PasswordProtection;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Content\PasswordProtection
 */
class PasswordProtectionTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_template_include_filter(): void {
		Filters\expectAdded( 'template_include' )->once();

		new PasswordProtection( [] );
	}

	/**
	 * @return void
	 */
	public function test_returns_original_template_when_no_post(): void {
		$snippet = new PasswordProtection( [] );

		$GLOBALS['post'] = null;

		$result = $snippet->get_password_protected_template( '/path/to/single.php' );

		$this->assertSame( '/path/to/single.php', $result );
	}

	/**
	 * @return void
	 */
	public function test_returns_original_template_when_password_not_required(): void {
		$snippet = new PasswordProtection( [] );

		$post             = \Mockery::mock( '\WP_Post' );
		$post->ID         = 1;
		$post->post_type  = 'post';
		$GLOBALS['post']  = $post;

		Functions\expect( 'post_password_required' )
			->once()
			->with( 1 )
			->andReturn( false );

		$result = $snippet->get_password_protected_template( '/path/to/single.php' );

		$this->assertSame( '/path/to/single.php', $result );
	}

	/**
	 * @return void
	 */
	public function test_returns_located_template_when_password_required(): void {
		$snippet = new PasswordProtection( [] );

		$post             = \Mockery::mock( '\WP_Post' );
		$post->ID         = 1;
		$post->post_type  = 'post';
		$GLOBALS['post']  = $post;

		Functions\expect( 'post_password_required' )
			->once()
			->with( 1 )
			->andReturn( true );

		Functions\expect( 'locate_template' )
			->once()
			->with( [ 'password-protected-post.php', 'password-protected.php' ] )
			->andReturn( '/theme/password-protected.php' );

		$result = $snippet->get_password_protected_template( '/path/to/single.php' );

		$this->assertSame( '/theme/password-protected.php', $result );
	}

	/**
	 * @return void
	 */
	public function test_falls_back_to_original_when_no_template_found(): void {
		$snippet = new PasswordProtection( [] );

		$post             = \Mockery::mock( '\WP_Post' );
		$post->ID         = 1;
		$post->post_type  = 'page';
		$GLOBALS['post']  = $post;

		Functions\expect( 'post_password_required' )
			->once()
			->with( 1 )
			->andReturn( true );

		Functions\expect( 'locate_template' )
			->once()
			->with( [ 'password-protected-page.php', 'password-protected.php' ] )
			->andReturn( '' );

		$result = $snippet->get_password_protected_template( '/path/to/page.php' );

		$this->assertSame( '/path/to/page.php', $result );
	}
}
