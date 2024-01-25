<?php

namespace PressGang\Snippets;


/**
 * Class Permalinks
 *
 * Handles custom permalink rewrites for a WordPress theme. It rewrites URLs for static assets
 * like CSS, JS, and images to more user-friendly and cleaner URLs. For instance, it rewrites
 * URLs from /wp-content/themes/theme-name/... to /css/, /js/, /img/, etc.
 */
class Permalinks implements SnippetInterface {

	protected $rewrite_rules;

	/**
	 * Constructor for the Permalinks class.
	 *
	 * Initializes the permalink rewrites by adding a hook to 'generate_rewrite_rules'.
	 *
	 * @param array $args Arguments for the constructor. Currently not used in implementation.
	 */
	public function __construct( array $args ) {

		// Define default folders to rewrite
		$defaults = [
			'css'     => true,
			'js'      => true,
			'img'     => true,
			'fonts'   => true,
			'plugins' => false,
		];

		// Merge passed arguments with defaults
		$this->rewrite_rules = \wp_parse_args( $args, $defaults );

		\add_action( 'generate_rewrite_rules', [ $this, 'add_rewrites' ] );
	}

	/**
	 * Add custom rewrite rules.
	 *
	 * Adds rewrite rules for static assets (CSS, JS, images, fonts) and plugins, translating
	 * standard WordPress paths to simplified, cleaner URLs. This method modifies the global
	 * $wp_rewrite rules to implement the custom rewrites.
	 *
	 * @hooked generate_rewrite_rules
	 * @see https://developer.wordpress.org/reference/hooks/generate_rewrite_rules/
	 *
	 * @param object $wp_rewrite The WP_Rewrite object containing WordPress's rewrite rules.
	 */
	public function add_rewrites( object $wp_rewrite ): void {

		$var        = explode( '/themes/', \get_stylesheet_directory() );
		$theme_name = next( $var );

		$new_non_wp_rules = [];

		// Add rewrite rules based on enabled options
		if ( $this->rewrite_rules['css'] ) {
			$new_non_wp_rules['css/(.*)'] = 'wp-content/themes/' . $theme_name . '/css/$1';
		}

		if ( $this->rewrite_rules['js'] ) {
			$new_non_wp_rules['js/(.*)'] = 'wp-content/themes/' . $theme_name . '/js/$1';
		}

		if ( $this->rewrite_rules['img'] ) {
			$new_non_wp_rules['img/(.*)'] = 'wp-content/themes/' . $theme_name . '/img/$1';
		}

		if ( $this->rewrite_rules['fonts'] ) {
			$new_non_wp_rules['fonts/(.*)'] = 'wp-content/themes/' . $theme_name . '/fonts/$1';
		}

		if ( $this->rewrite_rules['plugins'] ) {
			$new_non_wp_rules['plugins/(.*)'] = 'wp-content/themes/' . $theme_name . '/plugins/$1';
		}

		$wp_rewrite->non_wp_rules += $new_non_wp_rules;
	}

}