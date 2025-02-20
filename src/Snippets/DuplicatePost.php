<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class DuplicatePost
 *
 * Allows duplicating posts and pages without requiring a plugin.
 */
class DuplicatePost implements SnippetInterface {

	/**
	 * Constructor.
	 *
	 * @param array $args Unused, but required by the interface.
	 */
	public function __construct( array $args = [] ) {
		\add_filter( 'post_row_actions', [ $this, 'duplicate_post_link' ], 10, 2 );
		\add_filter( 'page_row_actions', [ $this, 'duplicate_post_link' ], 10, 2 );
		\add_action( 'admin_action_duplicate_post_as_draft', [ $this, 'duplicate_post_as_draft' ] );
		\add_action( 'admin_notices', [ $this, 'duplication_admin_notice' ] );
	}

	/**
	 * Adds the duplicate link to the post actions row.
	 *
	 * @param array $actions The available actions.
	 * @param \WP_Post $post The current post.
	 *
	 * @return array Updated actions array.
	 */
	public function duplicate_post_link( array $actions, \WP_Post $post ): array {
		if ( ! \current_user_can( 'edit_posts' ) ) {
			return $actions;
		}

		$url = \wp_nonce_url(
			\add_query_arg(
				[
					'action' => 'duplicate_post_as_draft',
					'post'   => $post->ID,
				],
				'admin.php'
			),
			'duplicate_post_nonce',
			'duplicate_nonce'
		);

		$actions['duplicate'] = sprintf(
			'<a href="%s" title="%s">%s</a>',
			esc_url( $url ),
			esc_attr__( 'Duplicate this item', 'pressgang' ),
			esc_html__( 'Duplicate', 'pressgang' )
		);

		return $actions;
	}

	/**
	 * Creates a duplicate post as a draft and redirects to the edit screen.
	 */
	public function duplicate_post_as_draft(): void {
		if ( empty( $_GET['post'] ) ) {
			\wp_die( \esc_html__( 'No post to duplicate has been provided!', 'pressgang' ) );
		}

		$post_id = \absint( $_GET['post'] );

		if (
			! isset( $_GET['duplicate_nonce'] ) ||
			! \wp_verify_nonce( $_GET['duplicate_nonce'], 'duplicate_post_nonce' )
		) {
			\wp_die( \esc_html__( 'Nonce verification failed.', 'pressgang' ) );
		}

		$current_user    = \wp_get_current_user();
		$new_post_author = $current_user->ID;

		$post = \get_post( $post_id );
		if ( ! $post ) {
			\wp_die( \esc_html__( 'Post creation failed, could not find original post.', 'pressgang' ) );
		}

		$args = [
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order,
		];

		$new_post_id = \wp_insert_post( $args );

		// Duplicate taxonomies
		$taxonomies = \get_object_taxonomies( \get_post_type( $post ) );
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = \wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'slugs' ] );
				\wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
			}
		}

		// Duplicate post meta
		$post_meta = \get_post_meta( $post_id );
		if ( ! empty( $post_meta ) ) {
			foreach ( $post_meta as $meta_key => $meta_values ) {
				if ( $meta_key === '_wp_old_slug' ) {
					continue;
				}
				foreach ( $meta_values as $meta_value ) {
					\add_post_meta( $new_post_id, $meta_key, \maybe_unserialize( $meta_value ) );
				}
			}
		}

		// Redirect to the edit screen for the new draft
		\wp_safe_redirect(
			\add_query_arg(
				[
					'action' => 'edit',
					'post'   => $new_post_id,
				],
				\admin_url( 'post.php' )
			)
		);
		exit;
	}

	/**
	 * Displays an admin notice after duplicating a post.
	 */
	public function duplication_admin_notice(): void {
		$screen = \get_current_screen();

		if ( $screen->base !== 'edit' ) {
			return;
		}

		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'post_duplication_created' ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html__( 'Post copy created.', 'pressgang' )
			);
		}
	}
}