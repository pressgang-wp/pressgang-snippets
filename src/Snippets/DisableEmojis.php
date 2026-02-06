<?php

declare(strict_types=1);

namespace PressGang\Snippets;

/**
 * Removes WordPress emoji support — the detection script, print styles,
 * TinyMCE plugin, and DNS prefetch hint — to reduce page weight on sites
 * that don't use emojis.
 *
 * Enable this snippet to eliminate the ~15 KB emoji payload from every page
 * load. No configuration required.
 */
class DisableEmojis implements SnippetInterface {

	/**
	 * Registers actions and filters to strip emoji scripts, styles, TinyMCE
	 * plugin, and DNS prefetch from all pages.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'init', [ $this, 'disable_emojis' ] );
		\add_filter( 'tiny_mce_plugins', [ $this, 'disable_emojis_tinymce' ] );
		\add_filter( 'wp_resource_hints', [ $this, 'disable_emojis_remove_dns_prefetch' ], 10, 2 );
	}

	/**
	 * Removes the emoji detection script, print styles, content-feed filter,
	 * comment-feed filter, and email staticisation from WordPress.
	 *
	 * @return void
	 */
	public function disable_emojis(): void {
		\remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		\remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		\remove_action( 'wp_print_styles', 'print_emoji_styles' );
		\remove_action( 'admin_print_styles', 'print_emoji_styles' );
		\remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		\remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		\remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}

	/**
	 * Removes the wpemoji TinyMCE plugin from the editor plugin list.
	 *
	 * @param array<int, string> $plugins TinyMCE plugins.
	 *
	 * @return array<int, string> Modified list with wpemoji removed.
	 */
	public function disable_emojis_tinymce( $plugins ): array {
		return is_array( $plugins ) ? array_diff( $plugins, [ 'wpemoji' ] ) : [];
	}

	/**
	 * Removes the emoji CDN hostname (s.w.org) from the list of DNS prefetch
	 * hints that WordPress injects into <head>. Only modifies 'dns-prefetch'
	 * hints.
	 *
	 * @param array<int, string> $urls          URLs WordPress will print as resource hints.
	 * @param string             $relation_type The hint type ('dns-prefetch', 'preconnect', etc.).
	 *
	 * @return array<int, string> The filtered URL list with the emoji CDN removed.
	 */
	public function disable_emojis_remove_dns_prefetch( array $urls, string $relation_type ): array {
		if ( $relation_type === 'dns-prefetch' ) {
			$emoji_svg_url = \apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
			$urls          = array_diff( $urls, [ $emoji_svg_url ] );
		}

		return $urls;
	}
}
