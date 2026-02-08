<?php

namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Requires a featured image for specific post types by forcing posts back
 * to draft and showing an admin notice when missing.
 *
 * Enable this snippet to enforce featured images on publish. Configure post
 * types via $args['post_types'].
 */
class RequireFeaturedImage implements SnippetInterface {

	/**
	 * @var array<int, string>
	 */
	protected array $post_types = [ 'post' ];

	private const TRANSIENT_KEY = 'pressgang_has_post_thumbnail';

	/**
	 * Registers hooks for featured image enforcement.
	 *
	 * @param array{post_types?: array<int, string>} $args
	 */
	public function __construct( array $args ) {
		$this->post_types = $args['post_types'] ?? $this->post_types;

		\add_action( 'save_post', [ $this, 'check_featured_image' ] );
		\add_action( 'admin_notices', [ $this, 'featured_image_error' ] );
	}

	/**
	 * Ensures the post has a featured image or forces draft.
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function check_featured_image( int $post_id ): void {
		if ( \wp_is_post_autosave( $post_id ) || \wp_is_post_revision( $post_id ) ) {
			return;
		}

		$post_type = \get_post_type( $post_id );
		if ( ! $post_type || ! in_array( $post_type, $this->post_types, true ) ) {
			return;
		}

		if ( ! \has_post_thumbnail( $post_id ) ) {
			\set_transient( self::TRANSIENT_KEY, 'no', MINUTE_IN_SECONDS );

			\remove_action( 'save_post', [ $this, 'check_featured_image' ] );
			\wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );
			\add_action( 'save_post', [ $this, 'check_featured_image' ] );

			return;
		}

		\delete_transient( self::TRANSIENT_KEY );
	}

	/**
	 * Shows an admin notice when a featured image is required.
	 *
	 * @return void
	 */
	public function featured_image_error(): void {
		if ( \get_transient( self::TRANSIENT_KEY ) !== 'no' ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			\esc_html__( 'You must select a Featured Image. Your post was saved as a draft and cannot be published.', THEMENAME )
		);

		\delete_transient( self::TRANSIENT_KEY );
	}
}
