<?php

namespace PressGang\Snippets\Seo;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;

/**
 * Outputs JSON-LD schema markup for common site and content types.
 *
 * Enable this snippet to add schema.org JSON-LD for Organization, WebSite,
 * WebPage, BlogPosting, Person, JobPosting, Event, and CreativeWork.
 * Some schemas rely on ACF fields if available.
 */
class Schema implements SnippetInterface {

	/**
	 * Registers schema render hooks in wp_head.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_action( 'wp_head', [ $this, 'organization' ] );
		\add_action( 'wp_head', [ $this, 'website' ] );
		\add_action( 'wp_head', [ $this, 'creative_work' ] );
		\add_action( 'wp_head', [ $this, 'webpage' ] );
		\add_action( 'wp_head', [ $this, 'blog_posting' ] );
		\add_action( 'wp_head', [ $this, 'person' ] );
		\add_action( 'wp_head', [ $this, 'job_posting' ] );
		\add_action( 'wp_head', [ $this, 'event' ] );
	}

	/**
	 * Outputs Organization schema.
	 *
	 * @return void
	 */
	public function organization(): void {
		$social_networks = function_exists( 'get_field' ) ? \get_field( 'social_networks', 'option' ) : [];
		$same_as = is_array( $social_networks ) ? array_column( $social_networks, 'url' ) : [];

		$data = [
			'id'               => \get_bloginfo( 'url' ),
			'name'             => \get_bloginfo( 'name' ),
			'url'              => \get_bloginfo( 'url' ),
			'logo'             => \get_theme_mod( 'logo' ),
			'same_as'          => $same_as,
			'address_locality' => function_exists( 'get_field' ) ? \get_field( 'address_city', 'option' ) : null,
			'address_region'   => function_exists( 'get_field' ) ? \get_field( 'address_region', 'option' ) : null,
			'postal_code'      => function_exists( 'get_field' ) ? \get_field( 'address_post_code', 'option' ) : null,
			'street_address'   => function_exists( 'get_field' ) ? implode( ', ', array_filter( [
				\get_field( 'address_line_1', 'option' ),
				\get_field( 'address_line_2', 'option' ),
				\get_field( 'address_city', 'option' ),
				\get_field( 'address_post_code', 'option' ),
			] ) ) : null,
			'email'            => function_exists( 'get_field' ) ? \get_field( 'email', 'option' ) : null,
			'telephone'        => function_exists( 'get_field' ) ? \get_field( 'phone', 'option' ) : null,
			'vat_id'           => function_exists( 'get_field' ) ? \get_field( 'vat_registration_number', 'option' ) : null,
		];

		Timber::render( 'snippets/seo/json-ld/organization.twig', $data );
	}

	/**
	 * Outputs WebSite schema with SearchAction.
	 *
	 * @return void
	 */
	public function website(): void {
		$data = [
			'id'   => \get_bloginfo( 'url' ),
			'name' => \get_bloginfo( 'name' ),
			'url'  => \get_bloginfo( 'url' ),
		];

		Timber::render( 'snippets/seo/json-ld/website.twig', $data );
	}

	/**
	 * Outputs CreativeWork schema for the "project" post type.
	 *
	 * @return void
	 */
	public function creative_work(): void {
		if ( ! \is_singular( 'project' ) ) {
			return;
		}

		$post = Timber::get_post();
		if ( ! $post ) {
			return;
		}

		$contributors = [];
		if ( $project_leaders = $post->meta( 'project_leaders' ) ) {
			foreach ( $project_leaders as $project_leader ) {
				$contributors[] = [ 'name' => \esc_html( $project_leader->name ) ];
			}
		}

		$data = [
			'organization'  => [
				'name'    => \get_bloginfo( 'name' ),
				'same_as' => \get_bloginfo( 'url' ),
			],
			'url'           => $post->link,
			'headline'      => $post->title,
			'description'   => $post->get_preview( 20, false, false ),
			'contributors'  => $contributors,
			'thumbnail_url' => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : '',
			'keywords'      => implode( ', ', $post->terms( 'sector' ) ),
		];

		Timber::render( 'snippets/seo/json-ld/creative-work.twig', $data );
	}

	/**
	 * Outputs WebPage schema for static pages.
	 *
	 * @return void
	 */
	public function webpage(): void {
		if ( ! \is_page() ) {
			return;
		}

		$post = Timber::get_post();
		if ( ! $post ) {
			return;
		}

		$data = [
			'publisher'             => \get_bloginfo( 'url' ),
			'headline'              => $post->title,
			'image'                 => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : '',
			'main_content_of_page'  => $post->link,
			'primary_image_of_page' => isset( $post->thumbnail ) ? $post->thumbnail->src : '',
			'last_reviewed'         => $post->modified_date( 'Y-m-d H:i:s' ),
		];

		Timber::render( 'snippets/seo/json-ld/webpage.twig', $data );
	}

	/**
	 * Outputs BlogPosting schema for posts.
	 *
	 * @return void
	 */
	public function blog_posting(): void {
		if ( ! \is_single() || \get_post_type() !== 'post' ) {
			return;
		}

		$post = Timber::get_post();
		if ( ! $post ) {
			return;
		}

		$data = [
			'publisher'           => \get_bloginfo( 'url' ),
			'author'              => $post->author,
			'headline'            => $post->post_title,
			'article_body'        => $post->post_content,
			'date_published'      => $post->date( 'Y-m-d H:i:s' ),
			'image'               => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : \get_template_directory_uri() . '/dist/images/1x/logo-all.png',
			'date_modified'       => $post->modified_date( 'Y-m-d H:i:s' ),
			'main_entity_of_page' => $post->link,
		];

		Timber::render( 'snippets/seo/json-ld/blog-posting.twig', $data );
	}

	/**
	 * Outputs Person schema for team members.
	 *
	 * @return void
	 */
	public function person(): void {
		if ( ! \is_single() || \get_post_type() !== 'team_member' ) {
			return;
		}

		$post = Timber::get_post();
		if ( ! $post ) {
			return;
		}

		$data = [
			'given_name'    => $post->meta( 'person_firstname' ),
			'family_name'   => $post->meta( 'person_surname' ),
			'awards'        => $post->meta( 'person_qualifications' ),
			'image'         => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : '',
			'url'           => $post->link,
			'job_title'     => implode( ', ', $post->terms( 'person_postition' ) ),
			'works_for'     => \get_bloginfo( 'url' ),
			'work_location' => function_exists( 'meta' ) ? \meta( 'address', 'option' ) : '',
		];

		Timber::render( 'snippets/seo/json-ld/person.twig', $data );
	}

	/**
	 * Outputs JobPosting schema for job posts.
	 *
	 * @return void
	 */
	public function job_posting(): void {
		if ( ! \is_single() || \get_post_type() !== 'job' ) {
			return;
		}

		$post = Timber::get_post();
		if ( ! $post ) {
			return;
		}

		$data = [
			'title'               => $post->title,
			'description'         => \wp_strip_all_tags( $post->post_content ),
			'employment_type'     => $post->meta( 'job_type' ),
			'base_salary'         => $post->meta( 'job_salary' ),
			'valid_through'       => $post->meta( 'date_end' ),
			'hiring_organization' => \get_bloginfo( 'url' ),
			'date_posted'         => $post->date,
		];

		Timber::render( 'snippets/seo/json-ld/job-posting.twig', $data );
	}

	/**
	 * Outputs Event schema for event posts.
	 *
	 * @return void
	 */
	public function event(): void {
		if ( ! \is_single() || \get_post_type() !== 'event' ) {
			return;
		}

		$post = Timber::get_post();
		if ( ! $post ) {
			return;
		}

		$data = [
			'start_date'  => $post->meta( 'start_date' ),
			'end_date'    => $post->meta( 'end_date' ),
			'url'         => $post->link,
			'name'        => $post->title,
			'description' => \wp_strip_all_tags( $post->post_content ),
			'image'       => ! empty( $post->thumbnail() ) ? $post->thumbnail->src : '',
			'location'    => $post->meta( 'post_map' ) ? $post->meta( 'post_map' )['address'] : $post->meta( 'custom_location' ),
		];

		Timber::render( 'snippets/seo/json-ld/event.twig', $data );
	}
}
