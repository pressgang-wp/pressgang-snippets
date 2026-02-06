<?php

declare(strict_types=1);

namespace PressGang\Snippets;

/**
 * Overrides the template for password-protected posts, allowing a dedicated
 * password-protected.php template (or a post-type-specific variant like
 * password-protected-{post_type}.php).
 *
 * Enable this snippet when you want a custom template for password-protected
 * content instead of the default WordPress behaviour.
 *
 * The password form can be rendered in Twig with {{ fn('get_the_password_form') }}.
 *
 * @see https://timber.github.io/docs/v2/guides/posts/#password-protected-posts
 */
class PasswordProtection implements SnippetInterface {

	/**
	 * Template filename map.
	 *
	 * @var array{general: string, post_type_specific: string}
	 */
	private array $templates;

	/**
	 * Stores the template configuration and hooks into template_include at
	 * a late priority so it runs after most other template filters.
	 *
	 * @param array<string, string> $args Optional template overrides. Supported
	 *     keys: 'general' (default template), 'post_type_specific' (sprintf
	 *     pattern with %s for the post type).
	 */
	public function __construct( array $args = [] ) {
		$default_templates = [
			'general'            => 'password-protected.php',
			'post_type_specific' => 'password-protected-%s.php',
		];

		$this->templates = array_merge( $default_templates, $args );

		\add_filter( 'template_include', [ $this, 'get_password_protected_template' ], 99 );
	}

	/**
	 * Returns a password-protection template when the current post requires
	 * a password. Tries a post-type-specific template first, then falls back
	 * to the general template, and finally the original template.
	 *
	 * @param string $template The current template path.
	 *
	 * @return string The resolved template path.
	 */
	public function get_password_protected_template( string $template ): string {
		global $post;

		if ( ! empty( $post ) && \post_password_required( $post->ID ) ) {
			$post_type_template = sprintf( $this->templates['post_type_specific'], $post->post_type );
			$template = \locate_template( [
				$post_type_template,
				$this->templates['general'],
			] ) ?: $template;
		}

		return $template;
	}
}