<?php


namespace PressGang\Snippets;

/**
 * Loads a custom stylesheet into the WordPress block and classic editors so
 * that the editing experience visually matches the front-end theme styles.
 *
 * Enable this snippet to ensure WYSIWYG consistency between the editor and the
 * live site. By default, it loads /css/editor-styles.css from the active theme.
 *
 * @see https://developer.wordpress.org/reference/functions/add_editor_style/
 */
class EditorStyles implements SnippetInterface {

	/**
	 * Default path for the editor styles file (relative to theme root).
	 */
	const DEFAULT_PATH = '/css/editor-styles.css';

	/**
	 * Path to the editor styles file.
	 *
	 * @var string
	 */
	protected string $editor_styles_path;

	/**
	 * Stores the stylesheet path and hooks into admin_init to register editor
	 * styles.
	 *
	 * @param array<string, mixed> $args Optional. Supports:
	 *     - 'path': string — path to the editor stylesheet relative to the
	 *       theme root (default '/css/editor-styles.css').
	 */
	public function __construct( array $args ) {
		$this->editor_styles_path = $args['path'] ?? self::DEFAULT_PATH;
		\add_action( 'admin_init', [ $this, 'add_editor_styles' ] );
	}

	/**
	 * Declares editor-styles theme support and registers the configured
	 * stylesheet with WordPress.
	 *
	 * @return void
	 */
	public function add_editor_styles(): void {
		if ( $this->editor_styles_path ) {
			\add_theme_support( 'editor-styles' );
			\add_editor_style( $this->editor_styles_path );
		}
	}
}
