<?php


namespace PressGang\Snippets\Content;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds a "Duplicate" action link to the post/page list tables in the WordPress
 * admin, allowing editors to clone any post (including taxonomies and meta)
 * as a new draft with one click.
 *
 * Enable this snippet to provide quick content duplication without a
 * third-party plugin. Requires the edit_posts capability.
 */
class DuplicatePost implements SnippetInterface {

	/**
	 * Registers row action links for posts and pages, the admin action handler
	 * for duplication, and the success admin notice.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args = [] ) {
		\add_filter( 'post_row_actions', [ $this, 'duplicate_post_link' ], 10, 2 );
		\add_filter( 'page_row_actions', [ $this, 'duplicate_post_link' ], 10, 2 );
		\add_action( 'admin_action_duplicate_post_as_draft', [ $this, 'duplicate_post_as_draft' ] );
		\add_action( 'admin_notices', [ $this, 'duplication_admin_notice' ] );
	}

	/**
	 * Appends a "Duplicate" link to the row actions for a post or page.
	 * Only shown to users with the edit_posts capability.
	 *
	 * @param array<string, string> $actions The existing row action links.
	 * @param \WP_Post              $post    The current post object.
	 *
	 * @return array<string, string> The actions array with the duplicate link added.
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
			\esc_url( $url ),
			\esc_attr__( 'Duplicate this item', 'pressgang' ),
			\esc_html__( 'Duplicate', 'pressgang' )
		);

		return $actions;
	}

	/**
	 * Handles the duplicate_post_as_draft admin action: verifies the nonce,
	 * creates a copy of the post (including taxonomies and meta) as a draft,
	 * and redirects to the new post's edit screen.
	 *
	 * @return void
	 */
	public function duplicate_post_as_draft(): void {
		$post_id = $this->get_post_id_from_request();
		$this->verify_duplicate_nonce();

		$post = $this->get_original_post( $post_id );
		$new_post_id = $this->create_post_clone( $post );

		$this->duplicate_taxonomies( $post_id, $post, $new_post_id );
		$this->duplicate_meta( $post_id, $new_post_id );
		$this->redirect_to_edit_screen( $new_post_id );
	}

	/**
	 * Shows a success admin notice after a post has been duplicated.
	 *
	 * @return void
	 */
	public function duplication_admin_notice(): void {
		if ( ! $this->should_show_notice() ) {
			return;
		}

		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'post_duplication_created' ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				\esc_html__( 'Post copy created.', 'pressgang' )
			);
		}
	}

	/**
	 * Resolve the post ID from the request or stop with a user-friendly error.
	 *
	 * @return int
	 */
	private function get_post_id_from_request(): int {
		if ( empty( $_GET['post'] ) ) {
			\wp_die( \esc_html__( 'No post to duplicate has been provided!', 'pressgang' ) );
		}

		return \absint( $_GET['post'] );
	}

	/**
	 * Verify the duplication nonce or stop with an error.
	 *
	 * @return void
	 */
	private function verify_duplicate_nonce(): void {
		if (
			! isset( $_GET['duplicate_nonce'] ) ||
			! \wp_verify_nonce( $_GET['duplicate_nonce'], 'duplicate_post_nonce' )
		) {
			\wp_die( \esc_html__( 'Nonce verification failed.', 'pressgang' ) );
		}
	}

	/**
	 * Fetch the original post or stop if it cannot be found.
	 *
	 * @param int $post_id
	 *
	 * @return \WP_Post
	 */
	private function get_original_post( int $post_id ): \WP_Post {
		$post = \get_post( $post_id );
		if ( ! $post ) {
			\wp_die( \esc_html__( 'Post creation failed, could not find original post.', 'pressgang' ) );
		}

		return $post;
	}

	/**
	 * Create the draft clone of the original post.
	 *
	 * @param \WP_Post $post
	 *
	 * @return int New post ID.
	 */
	private function create_post_clone( \WP_Post $post ): int {
		$current_user = \wp_get_current_user();
		$new_post_author = $current_user->ID;

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

		return \wp_insert_post( $args );
	}

	/**
	 * Duplicate taxonomy terms from the original post.
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 * @param int $new_post_id
	 *
	 * @return void
	 */
	private function duplicate_taxonomies( int $post_id, \WP_Post $post, int $new_post_id ): void {
		$taxonomies = \get_object_taxonomies( \get_post_type( $post ) );
		if ( empty( $taxonomies ) ) {
			return;
		}

		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = \wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'slugs' ] );
			\wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
		}
	}

	/**
	 * Duplicate meta values from the original post.
	 *
	 * @param int $post_id
	 * @param int $new_post_id
	 *
	 * @return void
	 */
	private function duplicate_meta( int $post_id, int $new_post_id ): void {
		$post_meta = \get_post_meta( $post_id );
		if ( empty( $post_meta ) ) {
			return;
		}

		foreach ( $post_meta as $meta_key => $meta_values ) {
			if ( $meta_key === '_wp_old_slug' ) {
				continue;
			}
			foreach ( $meta_values as $meta_value ) {
				\add_post_meta( $new_post_id, $meta_key, \maybe_unserialize( $meta_value ) );
			}
		}
	}

	/**
	 * Redirect to the new post edit screen and terminate the request.
	 *
	 * @param int $new_post_id
	 *
	 * @return void
	 */
	private function redirect_to_edit_screen( int $new_post_id ): void {
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
	 * Determine whether the duplication notice can be displayed.
	 *
	 * @return bool
	 */
	private function should_show_notice(): bool {
		$screen = \get_current_screen();

		return $screen->base === 'edit';
	}
}
