<?php

namespace Snippets;

/**
 * Class Schema
 *
 * See -
 * https://iamsteve.me/blog/entry/how-to-use-json-ld-to-replace-microdata-with-wordpress
 *
 * @package PressGang
 */
class Schema {

	/**
	 * __construct
	 *
	 */
	public function __construct() {
		add_action( 'wp_head', [ $this, 'organization' ] );
		add_action( 'wp_head', [ $this, 'website' ] );
		add_action( 'wp_head', [ $this, 'creative_work' ] );
		add_action( 'wp_head', [ $this, 'webpage' ] );
		add_action( 'wp_head', [ $this, 'blog_posting' ] );
		add_action( 'wp_head', [ $this, 'person' ] );
		add_action( 'wp_head', [ $this, 'job_posting' ] );
		add_action( 'wp_head', [ $this, 'event' ] );
	}

	/**
	 * organization
	 *
	 * http://schema.org/Organization
	 *
	 */
	public function organization() {
		$data = [
			'id'               => get_bloginfo( 'url' ),
			'name'             => get_bloginfo( 'name' ),
			'url'              => get_bloginfo( 'url' ),
			'logo'             => get_theme_mod( 'logo' ),
			'same_as'          => array_column( get_field( 'social_networks',
				'option' ), 'url' ),
			'address_locality' => get_field( 'address_city', 'option' ),
			'address_region'   => get_field( 'address_region', 'option' ),
			'postal_code'      => get_field( 'address_post_code', 'option' ),
			'street_address'   => implode( ', ', array_filter( [
				get_field( 'address_line_1', 'option' ),
				get_field( 'address_line_2', 'option' ),
				get_field( 'address_city', 'option' ),
				get_field( 'address_post_code', 'option' ),
			] ) ),
			'email'            => get_field( 'email', 'option' ),
			'telephone'        => get_field( 'phone', 'option' ),
			'vat_id'           => get_field( 'vat_registration_number',
				'option' ),
		];

		\Timber\Timber::render( 'json-ld/organization.twig', $data );
	}

	/**
	 * website
	 *
	 * http://schema.org/Website
	 *
	 * With SearchAction
	 * https://developers.google.com/search/docs/data-types/sitelinks-searchbox
	 * http://schema.org/SearchAction
	 *
	 * @return mixed
	 */
	public function website() {
		$data = [
			'id'   => get_bloginfo( 'url' ),
			'name' => get_bloginfo( 'name' ),
			'url'  => get_bloginfo( 'url' ),
		];

		\Timber\Timber::render( 'json-ld/website.twig', $data );
	}

	/**
	 * creative Work
	 *
	 * http://schema.org/CreativeWork
	 *
	 */
	public function creative_work() {
		if ( is_singular( 'project' ) ) {
			$post = \Timber\Timber::get_post();

			$contributors = [];

			if ( $project_leaders = $post->meta( 'project_leaders' ) ) {
				foreach ( $project_leaders as $project_leader ) {
					$contributors[] = [
						'name' => esc_html( $project_leader->name ),
					];
				}
			}
			if ( $post ) {
				$data = [
					'organization'  => [
						'name'    => get_bloginfo( 'name' ),
						'same_as' => get_bloginfo( 'url' ),
					],
					'url'           => $post->link,
					'headline'      => $post->title,
					'description'   => $post->get_preview( 20, false, false ),
					'contributors'  => $contributors,
					'thumbnail_url' => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : '',
					'keywords'      => implode( ', ',
						$post->terms( 'sector' ) ),
				];
			}

			\Timber\Timber::render( 'json-ld/creative-work.twig', $data );
		}
	}

	/**
	 * web_page
	 *
	 * http://schema.org/WebPage
	 *
	 * @return mixed
	 */
	public function webpage() {
		if ( is_page() ) {
			$post = \Timber\Timber::get_post();

			$data = [
				'publisher'             => get_bloginfo( 'url' ),
				'headline'              => $post->title,
				'image'                 => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : '',
				'main_content_of_page'  => $post->link,
				'primary_image_of_page' => isset( $post->thumbnail ) ? $post->thumbnail->src : '',
				'last_reviewed'         => $post->modified_date( 'Y-m-d H:i:s' ),
			];

			\Timber\Timber::render( 'json-ld/webpage.twig', $data );
		}
	}

	/**
	 * blogposting
	 *
	 * http://schema.org/BlogPosting
	 *
	 */
	public function blog_posting() {
		if ( is_single() && get_post_type() === 'post' ) {
			$post = \Timber\Timber::get_post();
			self::add_blog_posting( $post );
		}
	}

	/**
	 * add_blog_posting
	 *
	 * http://schema.org/BlogPosting
	 *
	 * @param $post
	 */
	public static function add_blog_posting( $post ) {
		$data = [
			'publisher'           => get_bloginfo( 'url' ),
			'author'              => $post->author,
			'headline'            => $post->post_title,
			'article_body'        => $post->post_content,
			'date_published'      => $post->date( 'Y-m-d H:i:s' ),
			'image'               => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : get_template_directory_uri() . '/dist/images/1x/logo-all.png',
			'date_modified'       => $post->modified_date( 'Y-m-d H:i:s' ),
			'main_entity_of_page' => $post->link,
		];

		\Timber\Timber::render( 'json-ld/blog-posting.twig', $data );
	}

	/**
	 * person
	 *
	 * http://schema.org/Person
	 *
	 */
	public function person() {
		if ( is_single() && get_post_type() === 'team_member' ) {
			$post = \Timber\Timber::get_post();
			self::add_person( $post );
		}
	}

	/**
	 * add_person
	 *
	 * @param $post
	 */
	public static function add_person( $post ) {
		$data = [
			'given_name'    => $post->meta( 'person_firstname' ),
			'family_name'   => $post->meta( 'person_surname' ),
			'awards'        => $post->meta( 'person_qualifications' ),
			'image'         => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : '',
			'url'           => $post->link,
			'job_title'     => implode( ', ',
				$post->terms( 'person_postition' ) ),
			'works_for'     => get_bloginfo( 'url' ),
			'work_location' => meta( 'address', 'option' ),
		];

		\Timber\Timber::render( 'json-ld/person.twig', $data );
	}

	/**
	 * job_posting
	 *
	 * http://schema.org/JobPosting
	 *
	 */
	public function job_posting() {
		if ( is_single() && get_post_type() === 'job' ) {
			$post = \Timber\Timber::get_post();

			$data = [
				'title'               => $post->title,
				'description'         => wp_strip_all_tags( $post->post_content ),
				'employment_type'     => $post->meta( 'job_type' ),
				'base_salary'         => $post->meta( 'job_salary' ),
				'valid_through'       => $post->meta( 'date_end' ),
				'hiring_organization' => get_bloginfo( 'url' ),
				'date_posted'         => $post->date,
			];

			\Timber\Timber::render( 'json-ld/job-posting.twig', $data );
		}
	}

	/**
	 * event
	 *
	 * http://schema.org/Event
	 *
	 */
	public function event() {
		if ( is_single() && get_post_type() === 'event' ) {
			$post = \Timber\Timber::get_post();

			$data = [
				'start_date'  => $post->meta( 'start_date' ),
				'end_date'    => $post->meta( 'end_date' ),
				'url'         => $post->link,
				'name'        => $post->title,
				'description' => wp_strip_all_tags( $post->post_content ),
				'image'       => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : '',
				'location'    => $post->meta( 'post_map' ) ? $post->meta( 'post_map' )['address'] : $post->meta( 'custom_location' ),
			];

			\Timber\Timber::render( 'json-ld/event.twig', $data );
		}
	}

}

new Schema();
