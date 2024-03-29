<?php

namespace PressGang\ToDo;

use function PressGang\Snippets\__;
use function PressGang\Snippets\add_action;
use function PressGang\Snippets\delete_transient;
use function PressGang\Snippets\get_transient;
use function PressGang\Snippets\has_post_thumbnail;
use function PressGang\Snippets\remove_action;
use function PressGang\Snippets\set_transient;
use function PressGang\Snippets\wp_update_post;

/**
 * Class RequiredFeaturedImage
 *
 * @package PressGang
 */
class RequiredFeaturedImage {

	// TODO this causes an auto draft to be created - needs debugging!

	protected $post_types = [ 'post' ];

	/**
	 * __construct
	 *
	 */
	public function __construct() {
		add_action( 'save_post', [ $this, 'check_featured_image' ] );
		add_action( 'admin_notices', [ $this, 'featured_image_error' ] );
	}

	/**
	 * check_featured_image
	 *
	 * Set a transient to show the users an admin message if no featured image
	 * when saving the post
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function check_featured_image( $post_id ) {
		if ( ! in_array( get_post_type( $post_id ), $this->post_types ) ) {
			return false;
		}

		if ( ! has_post_thumbnail( $post_id ) ) {
			set_transient( 'has_post_thumbnail', false );
			// unhook this function so it doesn't loop infintely when saving the draft
			remove_action( 'save_post', [ $this, 'check_featured_image' ] );
			// update the post set it to draft
			wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );
			add_action( 'save_post', [ $this, 'check_featured_image' ] );
		} else {
			delete_transient( 'has_post_thumbnail' );
		}
	}

	/**
	 * featured_image_error
	 *
	 */
	public function featured_image_error() {
		// TODO add a pressgang feature for notices
		if ( get_transient( 'has_post_thumbnail' ) == "no" ) { ?>
            <div class="error">
            <p><?php __( "You must select a Featured Image. Your Post has been saved but it cannot be published.",
					THEMENAME ) ?></p>
            </div><?php
		}
		delete_transient( 'has_post_thumbnail' );
	}

}

new RequiredFeaturedImage();
