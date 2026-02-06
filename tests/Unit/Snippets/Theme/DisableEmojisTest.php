<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\DisableEmojis;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\DisableEmojis
 */
class DisableEmojisTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_hooks(): void {
		Actions\expectAdded( 'init' )->once();
		Filters\expectAdded( 'tiny_mce_plugins' )->once();
		Filters\expectAdded( 'wp_resource_hints' )->once();

		new DisableEmojis( [] );
	}

	/**
	 * @return void
	 */
	public function test_disable_emojis_removes_actions_and_filters(): void {
		$snippet = new DisableEmojis( [] );

		Functions\expect( 'remove_action' )
			->times( 4 );

		Functions\expect( 'remove_filter' )
			->times( 3 );

		$snippet->disable_emojis();
	}

	/**
	 * @return void
	 */
	public function test_disable_emojis_tinymce_removes_wpemoji_plugin(): void {
		$snippet = new DisableEmojis( [] );

		$result = $snippet->disable_emojis_tinymce( [ 'wordcount', 'wpemoji', 'paste' ] );

		$this->assertNotContains( 'wpemoji', $result );
		$this->assertContains( 'wordcount', $result );
		$this->assertContains( 'paste', $result );
	}

	/**
	 * @return void
	 */
	public function test_disable_emojis_tinymce_returns_empty_array_for_non_array(): void {
		$snippet = new DisableEmojis( [] );

		$result = $snippet->disable_emojis_tinymce( 'not-an-array' );

		$this->assertSame( [], $result );
	}

	/**
	 * @return void
	 */
	public function test_remove_dns_prefetch_removes_emoji_url(): void {
		$snippet = new DisableEmojis( [] );

		$emoji_url = 'https://s.w.org/images/core/emoji/2/svg/';

		Functions\expect( 'apply_filters' )
			->once()
			->with( 'emoji_svg_url', $emoji_url )
			->andReturn( $emoji_url );

		$urls   = [ 'https://example.com', $emoji_url, 'https://fonts.googleapis.com' ];
		$result = $snippet->disable_emojis_remove_dns_prefetch( $urls, 'dns-prefetch' );

		$this->assertNotContains( $emoji_url, $result );
		$this->assertContains( 'https://example.com', $result );
		$this->assertContains( 'https://fonts.googleapis.com', $result );
	}

	/**
	 * @return void
	 */
	public function test_remove_dns_prefetch_ignores_non_dns_prefetch_hints(): void {
		$snippet = new DisableEmojis( [] );

		$emoji_url = 'https://s.w.org/images/core/emoji/2/svg/';
		$urls      = [ 'https://example.com', $emoji_url ];
		$result    = $snippet->disable_emojis_remove_dns_prefetch( $urls, 'preconnect' );

		$this->assertSame( $urls, $result );
	}
}
