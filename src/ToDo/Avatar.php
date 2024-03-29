<?php

namespace PressGang\ToDo;

class Avatar {

	/**
	 * __construct
	 *
	 * Avatar constructor.
	 */
	public function __construct() {
		// hide the default
		\update_option( 'show_avatars', 0 );

		$this->add_acf_field_group();
		\add_filter( 'get_avatar_data', [ $this, 'get_avatar_data' ], 10, 2 );
		\add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 10, 3 );
	}

	/**
	 * add_acf_field_group
	 *
	 */
	private function add_acf_field_group() {
		if ( function_exists( 'acf_add_local_field_group' ) ):

			\acf_add_local_field_group( [
				'key'                   => 'group_57ebe40ebbabe',
				'title'                 => 'Avatar',
				'fields'                => [
					[
						'key'               => 'field_57ebe4158ac9d',
						'label'             => 'Avatar',
						'name'              => 'avatar',
						'type'              => 'image',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => [
							'width' => '',
							'class' => '',
							'id'    => '',
						],
						'return_format'     => 'array',
						'preview_size'      => 'thumbnail',
						'library'           => 'all',
						'min_width'         => '',
						'min_height'        => '',
						'min_size'          => '',
						'max_width'         => '',
						'max_height'        => '',
						'max_size'          => '',
						'mime_types'        => '',
					],
				],
				'location'              => [
					[
						[
							'param'    => 'user_form',
							'operator' => '==',
							'value'    => 'all',
						],
					],
				],
				'menu_order'            => 0,
				'position'              => 'acf_after_title',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => 1,
				'description'           => '',
			] );

		endif;
	}

	/**
	 * get_avatar_data
	 *
	 * Filters the avatar data
	 *
	 * @param $args
	 * @param $id_or_email
	 *
	 * @return mixed
	 */
	public function get_avatar_data( $args, $id_or_email ) {
		if ( $avatar = $this->get_avatar( $id_or_email ) ) {
			$args['url'] = $avatar['url'];
		}

		return $args;
	}

	/**
	 * get_avatar_url
	 *
	 * Filters the avatar URL
	 *
	 * @param $url
	 * @param $id_or_email
	 * @param $args
	 *
	 * @return mixed
	 */
	public function get_avatar_url( $url, $id_or_email, $args ) {
		if ( $avatar = $this->get_avatar( $id_or_email ) ) {
			$url = $avatar['url'];
		}

		return $url;
	}

	/**
	 * get_avatar
	 *
	 * Get the ACF Avatar field
	 *
	 * @param $id_or_email
	 *
	 * @return bool|mixed|null|void
	 */
	private function get_avatar( $id_or_email ) {
		$user = \get_user_by( is_numeric( $id_or_email ) ? 'id' : 'email',
			$id_or_email );

		if ( function_exists( 'get_field' ) && $user ) {
			if ( $avatar = \get_field( 'avatar', "user_{$user->ID}" ) ) {
				return $avatar;
			}
		}

		return false;
	}

}

new Avatar();
