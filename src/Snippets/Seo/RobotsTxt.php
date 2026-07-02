<?php

namespace PressGang\Snippets\Seo;

use PressGang\Snippets\SnippetInterface;

/**
 * Replaces WordPress' virtual robots.txt output with a configured ruleset.
 *
 * Use this when a theme needs deploy-managed robots rules instead of an
 * unmanaged web-root `robots.txt` file. This class is deliberately only a
 * renderer: it only supplies WordPress' standard admin/admin-ajax defaults
 * and does not assume WooCommerce paths, sitemap plugins, or site-specific
 * crawl policy. Keep project-specific rules in the consuming theme's
 * `config/snippets.php`.
 *
 * Important: WordPress only serves the virtual robots output when no physical
 * `robots.txt` exists in the site root. If a server-level file exists, remove
 * or rename it before expecting this snippet to affect `/robots.txt`.
 *
 * Supported args:
 * - `allow` list|string: rules emitted as `Allow: ...`; defaults to
 *   `/wp-admin/admin-ajax.php`.
 * - `disallow` list|string: rules emitted as `Disallow: ...`; defaults to
 *   `/wp-admin/`.
 * - `sitemap_url` string: sitemap URL to emit. Empty string omits it.
 * - `user_agent` string: user-agent token; defaults to `*`.
 */
class RobotsTxt implements SnippetInterface {

	/**
	 * @var list<string>
	 */
	private const DEFAULT_ALLOW = [ '/wp-admin/admin-ajax.php' ];

	/**
	 * @var list<string>
	 */
	private const DEFAULT_DISALLOW = [ '/wp-admin/' ];

	/**
	 * @var list<string>
	 */
	private array $allow;

	/**
	 * @var list<string>
	 */
	private array $disallow;

	/**
	 * Sitemap URL emitted at the end of the file; empty string omits it.
	 */
	private string $sitemap_url;

	/**
	 * User-agent token emitted in the first line.
	 */
	private string $user_agent;

	/**
	 * Registers the late robots filter and stores normalized config.
	 *
	 * @param array<string, mixed> $args Robots.txt configuration. See class
	 *                                  docblock for supported keys.
	 */
	public function __construct( array $args = [] ) {
		$this->allow       = $this->normalize_rules( $args['allow'] ?? self::DEFAULT_ALLOW );
		$this->disallow    = $this->normalize_rules( $args['disallow'] ?? self::DEFAULT_DISALLOW );
		$this->sitemap_url = \trim( (string) ( $args['sitemap_url'] ?? '' ) );
		$this->user_agent  = (string) ( $args['user_agent'] ?? '*' );

		\add_filter( 'robots_txt', [ $this, 'filter_robots_txt' ], PHP_INT_MAX, 2 );
	}

	/**
	 * Builds the full virtual robots.txt response.
	 *
	 * This intentionally replaces earlier output instead of appending to it, so
	 * a theme can own the complete file in version-controlled config. The filter
	 * runs at `PHP_INT_MAX` to win over most plugin defaults.
	 *
	 * @param string $output Existing robots.txt content. Ignored by design.
	 * @param bool   $public Whether the site is public. Ignored because callers
	 *                       can explicitly configure the desired output.
	 *
	 * @return string
	 */
	public function filter_robots_txt( string $output, bool $public ): string {
		unset( $output, $public );

		$lines = [ 'User-agent: ' . $this->user_agent ];

		foreach ( $this->disallow as $rule ) {
			$lines[] = 'Disallow: ' . $rule;
		}

		foreach ( $this->allow as $rule ) {
			$lines[] = 'Allow: ' . $rule;
		}

		if ( '' !== $this->sitemap_url ) {
			$lines[] = 'Sitemap: ' . $this->sitemap_url;
		}

		return \implode( "\n", $lines );
	}

	/**
	 * Normalizes caller-provided rules into clean, non-empty strings.
	 *
	 * Accepts a single string for simple one-rule configs and silently drops
	 * non-scalar values. Rules are emitted exactly as configured after trimming,
	 * so callers can use robots wildcards/query patterns without escaping.
	 *
	 * @param mixed $rules Rules as a string or list.
	 *
	 * @return list<string>
	 */
	private function normalize_rules( mixed $rules ): array {
		if ( \is_string( $rules ) ) {
			$rules = [ $rules ];
		}

		if ( ! \is_array( $rules ) ) {
			return [];
		}

		$normalized = [];

		foreach ( $rules as $rule ) {
			if ( ! \is_scalar( $rule ) ) {
				continue;
			}

			$rule = \trim( (string) $rule );

			if ( '' !== $rule ) {
				$normalized[] = $rule;
			}
		}

		return $normalized;
	}
}
