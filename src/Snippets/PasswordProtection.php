<?php

namespace PressGang\Snippets;

/**
 * Class PasswordProtection
 *
 * Handles password protection for posts, allowing specific templates for password protected posts.
 * Implements SnippetInterface.
 */
class PasswordProtection implements SnippetInterface {

	/**
	 * @var array $templates
	 */
	private array $templates;

	/**
	 * Constructor.
	 *
	 * Initializes the PasswordProtection class and adds a filter to use specific templates
	 * for password protected posts.
	 *
	 * By default, this will use the **password-protected.php** template file.
	 * If you want password templates specific to a post type, use
	 * **password-protected-$posttype.php**.
	 *
	 * The form can be rendered with {{ fn('get_the_password_form') }}
	 *
	 * @param array $args Arguments to initialize the class, including optional templates.
	 * @see https://timber.github.io/docs/v2/guides/posts/#password-protected-posts
	 */
	public function __construct( array $args = [] ) {
		$default_templates = [
			'general' => 'password-protected.php',
			'post_type_specific' => 'password-protected-%s.php'
		];

		$this->templates = array_merge($default_templates, $args ?? []);

		\add_filter( 'template_include', [ $this, 'get_password_protected_template' ], 99 );
	}

	/**
	 * Get Password Protected Template.
	 *
	 * Determines the appropriate template to use for password protected posts.
	 * If a specific template for the post type exists, it will use that; otherwise,
	 * it defaults to the general password-protected.php template.
	 *
	 * @param string $template The path to the current template.
	 *
	 * @return string The path to the template to use.
	 */
	public function get_password_protected_template( string $template ): mixed {
		global $post;

		if ( ! empty( $post ) && \post_password_required( $post->ID ) ) {
			$post_type_template = sprintf($this->templates['post_type_specific'], $post->post_type);
			$template = \locate_template( [
				$this->templates['general'],
				$post_type_template,
			] ) ?: $template;
		}

		return $template;
	}

}