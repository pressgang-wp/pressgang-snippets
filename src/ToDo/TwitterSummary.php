<?php

namespace PressGang\ToDo;

use Site;
use function PressGang\Snippets\add_action;
use function PressGang\Snippets\esc_attr;
use function PressGang\Snippets\esc_url;
use function PressGang\Snippets\get_permalink;
use function PressGang\Snippets\get_post_type_archive_link;
use function PressGang\Snippets\get_term_link;
use function PressGang\Snippets\get_the_archive_title;
use function PressGang\Snippets\get_the_title;
use function PressGang\Snippets\get_theme_mod;
use function PressGang\Snippets\has_post_thumbnail;
use function PressGang\Snippets\is_post_type_archive;
use function PressGang\Snippets\is_tax;
use function PressGang\Snippets\single_term_title;
use function PressGang\Snippets\wp_get_attachment_image_src;

/**
 * Class OpenGraph
 *
 * @package PressGang
 */
class TwitterSummary {

	/**
	 * init
	 *
	 */
	public function __construct() {
		add_action( 'wp_head',
			[ 'PressGang\Libarary\TwitterSummary', 'twitter_summary' ], 5 );
	}

	/**
	 * twitter_summary
	 *
	 */
	public static function twitter_summary() {
		$post = \Timber::get_post();

		$img = has_post_thumbnail( $post->ID )
			? wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ),
				'large' )[0]
			: ( get_theme_mod( 'og_img' )
				? get_theme_mod( 'og_img' )
				: esc_url( get_theme_mod( 'logo' ) ) );

		$description = Site::meta_description();

		if ( is_tax() ) {
			$url   = get_term_link( get_query_var( 'term' ),
				get_query_var( 'taxonomy' ) );
			$title = single_term_title( '', false );
		} elseif ( is_post_type_archive() ) {
			$url   = get_post_type_archive_link( get_query_var( 'post_type' ) );
			$title = get_the_archive_title();
		} else {
			$url   = get_permalink();
			$title = get_the_title();
		}

		$url = rtrim( esc_url( apply_filters( 'og_url', $url ) ) );
		if ( ! substr( $url, - 1 ) === '/' ) {
			$url .= '/'; // slash fixes Facebook Debugger "Circular Redirect Path"
		}

		$context = [
			'site'        => '', // TODO
			'title'       => esc_attr( apply_filters( 'og_title', $title ) ),
			'description' => esc_attr( wp_strip_all_tags( apply_filters( 'og_description',
				$description ) ) ),
			'url'         => $url,
			'image'       => esc_url( apply_filters( 'og_image', $img ) ),
		];

		\Timber\Timber::render( 'twitter-summary.twig', $context );
	}

}

new TwitterSummary();
