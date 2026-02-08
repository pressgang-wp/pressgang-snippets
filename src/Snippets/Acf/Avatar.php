<?php

namespace PressGang\Snippets\Acf;

use PressGang\Snippets\SnippetInterface;

/**
 * Replaces WordPress avatars with a user-specific ACF image field, allowing
 * editors to upload custom profile images while disabling Gravatar.
 *
 * Enable this snippet to manage avatars locally via ACF. It adds an "Avatar"
 * image field to user profiles and filters get_avatar_* to use that image
 * when available.
 */
class Avatar implements SnippetInterface {

	/**
	 * Disables default avatars, registers the ACF field group, and hooks avatar
	 * filters.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		$this->disable_default_avatars();
		$this->add_acf_field_group();

		\add_filter( 'get_avatar_data', [ $this, 'get_avatar_data' ], 10, 2 );
		\add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ], 10, 3 );
	}

	/**
	 * Disable the default Gravatar-based avatars.
	 *
	 * @return void
	 */
	private function disable_default_avatars(): void {
		\update_option( 'show_avatars', 0 );
	}

	/**
	 * Register the ACF field group for user avatar uploads.
	 *
	 * @return void
	 */
	private function add_acf_field_group(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

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
	}

	/**
	 * Filters avatar data to use the ACF avatar URL when present.
	 *
	 * @param array<string, mixed> $args Avatar data array.
	 * @param mixed                $id_or_email User ID or email.
	 *
	 * @return array<string, mixed> Updated avatar data.
	 */
	public function get_avatar_data( array $args, mixed $id_or_email ): array {
		$avatar = $this->get_avatar( $id_or_email );
		if ( $avatar ) {
			$args['url'] = $avatar['url'];
		}

		return $args;
	}

	/**
	 * Filters the avatar URL to use the ACF avatar image when present.
	 *
	 * @param string $url Existing avatar URL.
	 * @param mixed  $id_or_email User ID or email.
	 * @param array  $args Avatar args.
	 *
	 * @return string Updated avatar URL.
	 */
	public function get_avatar_url( string $url, mixed $id_or_email, array $args ): string {
		$avatar = $this->get_avatar( $id_or_email );
		if ( $avatar ) {
			return $avatar['url'];
		}

		return $url;
	}

	/**
	 * Fetches the ACF avatar field for a user.
	 *
	 * @param mixed $id_or_email User ID or email.
	 *
	 * @return array{url: string}|false Avatar field array or false if none found.
	 */
	private function get_avatar( mixed $id_or_email ) {
		$user = \get_user_by( is_numeric( $id_or_email ) ? 'id' : 'email', $id_or_email );

		if ( ! function_exists( 'get_field' ) || ! $user ) {
			return false;
		}

		$avatar = \get_field( 'avatar', "user_{$user->ID}" );
		if ( $avatar ) {
			return $avatar;
		}

		return false;
	}
}
