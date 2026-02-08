<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Tracks post views via post meta and exposes helpers for accessing view counts.
 *
 * Enable this snippet to count views for single posts and to query the most
 * viewed posts. Includes an optional cache-breaker comment for page caching
 * systems that support mfunc-style execution.
 */
class TrackPostViews implements SnippetInterface {

	public const COUNT_KEY = 'pressgang_post_views_count';

	/**
	 * Registers hooks for tracking post views and enriching posts with view counts.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'wp_head', [ $this, 'track_post_views' ] );
		\add_action( 'wp_head', [ $this, 'cache_breaker' ] );
		\add_action( 'the_post', [ $this, 'add_views_to_post' ] );

		// To keep the count accurate, remove adjacent link prefetching.
		\remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
	}

	/**
	 * Add the view count to the post object.
	 *
	 * @param \WP_Post $post
	 *
	 * @return \WP_Post
	 */
	public function add_views_to_post( \WP_Post $post ): \WP_Post {
		$post->views = $this->get_post_views( $post->ID );

		return $post;
	}

	/**
	 * Get the view count for a post.
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function get_post_views( int $post_id ): int {
		return (int) \get_post_meta( $post_id, self::COUNT_KEY, true );
	}

	/**
	 * Increment the view count for a post.
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function set_post_views( int $post_id ): void {
		$count = (int) \get_post_meta( $post_id, self::COUNT_KEY, true );
		$count++;

		if ( ! $count ) {
			\delete_post_meta( $post_id, self::COUNT_KEY );
			\add_post_meta( $post_id, self::COUNT_KEY, $count );
		} else {
			\update_post_meta( $post_id, self::COUNT_KEY, $count );
		}
	}

	/**
	 * Track views for the current single post.
	 *
	 * @param int $post_id Optional post ID override.
	 *
	 * @return void
	 */
	public function track_post_views( int $post_id = 0 ): void {
		if ( ! \is_single() ) {
			return;
		}

		if ( ! $post_id ) {
			global $post;
			$post_id = $post ? (int) $post->ID : 0;
		}

		if ( $post_id ) {
			$this->set_post_views( $post_id );
		}
	}

	/**
	 * Get the most viewed posts for a post type.
	 *
	 * @param string $post_type
	 * @param int    $number
	 * @param int    $paged
	 *
	 * @return \Timber\PostCollectionInterface
	 */
	public function get_most_popular( string $post_type = 'post', int $number = 0, int $paged = 0 ) {
		$paged  = $paged ?: 0;
		$number = $number ?: (int) \get_option( 'posts_per_page' );

		return \Timber\Timber::get_posts( [
			'post_type'      => $post_type,
			'posts_per_page' => $number,
			'meta_key'       => self::COUNT_KEY,
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'paged'          => $paged,
		] );
	}

	/**
	 * Outputs a cache-breaker comment for caching systems that support mfunc.
	 *
	 * @return void
	 */
	public function cache_breaker(): void {
		if ( ! \is_single() ) {
			return;
		}

		echo '<!-- mfunc \\PressGang\\Snippets\\Content\\TrackPostViews::set_post_views($post_id); --><!-- /mfunc -->';
	}
}
