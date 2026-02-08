<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Acf;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Acf\Avatar;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Acf\Avatar
 */
class AvatarTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'customize_register' )->never();
		Filters\expectAdded( 'get_avatar_data' )->once();
		Filters\expectAdded( 'get_avatar_url' )->once();

		Functions\expect( 'update_option' )
			->once()
			->with( 'show_avatars', 0 );

		new Avatar( [] );
	}

	/**
	 * @return void
	 */
	public function test_get_avatar_data_uses_acf_avatar(): void {
		Functions\expect( 'update_option' )
			->once()
			->with( 'show_avatars', 0 );

		$snippet = new Avatar( [] );

		$user = (object) [ 'ID' => 123 ];

		Functions\expect( 'get_user_by' )
			->once()
			->with( 'id', 123 )
			->andReturn( $user );
		Functions\expect( 'get_field' )
			->once()
			->with( 'avatar', 'user_123' )
			->andReturn( [ 'url' => 'https://example.test/avatar.jpg' ] );

		$args = [ 'url' => 'https://example.test/default.jpg' ];
		$result = $snippet->get_avatar_data( $args, 123 );

		$this->assertSame( 'https://example.test/avatar.jpg', $result['url'] );
	}

	/**
	 * @return void
	 */
	public function test_get_avatar_url_falls_back_when_no_acf_avatar(): void {
		Functions\expect( 'update_option' )
			->once()
			->with( 'show_avatars', 0 );

		$snippet = new Avatar( [] );

		Functions\expect( 'get_user_by' )
			->once()
			->with( 'email', 'test@example.test' )
			->andReturn( false );

		$url = $snippet->get_avatar_url( 'https://example.test/default.jpg', 'test@example.test', [] );

		$this->assertSame( 'https://example.test/default.jpg', $url );
	}
}
