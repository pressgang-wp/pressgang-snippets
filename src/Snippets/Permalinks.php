<?php

declare(strict_types=1);

namespace PressGang\Snippets;

/**
 * Rewrites URLs for theme static assets (CSS, JS, images, fonts) to cleaner
 * root-relative paths (e.g. /css/*, /js/*) instead of the full
 * /wp-content/themes/{theme}/ path.
 *
 * Enable this snippet for prettier asset URLs. Pass an associative array of
 * folder names mapped to booleans to enable/disable specific rewrites.
 */
class Permalinks implements SnippetInterface {

	/**
	 * Map of folder names to enabled/disabled status.
	 *
	 * @var array<string, bool>
	 */
	protected array $rewrite_rules;

	/**
	 * Merges the provided args with defaults and hooks into
	 * generate_rewrite_rules.
	 *
	 * @param array<string, bool> $args Folder names mapped to booleans.
	 *     Defaults: css => true, js => true, img => true, fonts => true,
	 *     plugins => false.
	 */
	public function __construct( array $args ) {
		$defaults = [
			'css'     => true,
			'js'      => true,
			'img'     => true,
			'fonts'   => true,
			'plugins' => false,
		];

		$this->rewrite_rules = \wp_parse_args( $args, $defaults );

		\add_action( 'generate_rewrite_rules', [ $this, 'add_rewrites' ] );
	}

	/**
	 * Adds non-WP rewrite rules for each enabled asset folder, mapping
	 * clean root-relative URLs to the actual theme directory paths.
	 *
	 * @param \WP_Rewrite $wp_rewrite The WordPress rewrite instance.
	 *
	 * @return void
	 */
	public function add_rewrites( \WP_Rewrite $wp_rewrite ): void {
		$var        = explode( '/themes/', \get_stylesheet_directory() );
		$theme_name = next( $var );

		$new_non_wp_rules = [];

		foreach ( $this->rewrite_rules as $folder => $enabled ) {
			if ( $enabled ) {
				$new_non_wp_rules[ $folder . '/(.*)' ] = 'wp-content/themes/' . $theme_name . '/' . $folder . '/$1';
			}
		}

		$wp_rewrite->non_wp_rules += $new_non_wp_rules;
	}
}