<?php

namespace PressGang\Snippets\Seo;

use PressGang\Snippets\SnippetInterface;

/**
 * Replaces WordPress' virtual robots.txt output with a configured ruleset.
 */
class RobotsTxt implements SnippetInterface {

	/**
	 * @var list<string>
	 */
	private array $allow;

	/**
	 * @var list<string>
	 */
	private array $disallow;

	private string $sitemap_url;

	private string $user_agent;

	/**
	 * @param array<string, mixed> $args Robots.txt configuration.
	 */
	public function __construct( array $args = [] ) {
		$this->allow       = $this->normalize_rules( $args['allow'] ?? [ '/wp-admin/admin-ajax.php' ] );
		$this->disallow    = $this->normalize_rules( $args['disallow'] ?? $this->get_default_disallow_rules() );
		$this->sitemap_url = (string) ( $args['sitemap_url'] ?? \home_url( '/sitemap_index.xml' ) );
		$this->user_agent  = (string) ( $args['user_agent'] ?? '*' );

		\add_filter( 'robots_txt', [ $this, 'filter_robots_txt' ], PHP_INT_MAX, 2 );
	}

	/**
	 * @param string $output Existing robots.txt content.
	 * @param bool   $public Whether the site is public.
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
	 * @return list<string>
	 */
	private function get_default_disallow_rules(): array {
		return [
			'/wp-content/uploads/wc-logs/',
			'/wp-content/uploads/woocommerce_transient_files/',
			'/wp-content/uploads/woocommerce_uploads/',
			'/wp-admin/',
			'/*?add-to-cart=*',
		];
	}

	/**
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
