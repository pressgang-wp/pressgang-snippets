<?php


namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Loads a custom stylesheet into the WordPress editor so that the editing
 * experience visually matches the front-end theme styles.
 *
 * By default, styles are loaded via add_editor_style() which applies to both
 * the block editor and the classic (TinyMCE) editor. Set 'block_editor_only'
 * to true to load styles exclusively in the block editor — useful when full
 * theme CSS (Bootstrap, grid, etc.) would break the classic editor UI for
 * post types that use show_in_rest=false.
 *
 * @see https://developer.wordpress.org/reference/functions/add_editor_style/
 * @see https://developer.wordpress.org/reference/hooks/enqueue_block_assets/
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
	 * Whether to restrict styles to the block editor only.
	 *
	 * @var bool
	 */
	protected bool $block_editor_only;

	/**
	 * Registers the editor stylesheet using the appropriate loading strategy.
	 *
	 * When 'block_editor_only' is false (default), styles are registered via
	 * add_editor_style() on admin_init, which loads them into both the block
	 * editor and the classic TinyMCE editor. WordPress automatically prefixes
	 * selectors with .editor-styles-wrapper in the block editor.
	 *
	 * When 'block_editor_only' is true, styles are enqueued via
	 * enqueue_block_assets with an is_admin() guard, which loads them inside
	 * the block editor iframe only. The classic editor is not affected.
	 *
	 * @param array<string, mixed> $args Optional. Supports:
	 *     - 'path': string — path to the editor stylesheet relative to the
	 *       theme root (default '/css/editor-styles.css').
	 *     - 'block_editor_only': bool — when true, styles load only in the
	 *       block editor, not the classic editor (default false).
	 */
	public function __construct( array $args ) {
		$this->editor_styles_path = $args['path'] ?? self::DEFAULT_PATH;
		$this->block_editor_only  = (bool) ( $args['block_editor_only'] ?? false );

		if ( ! $this->editor_styles_path ) {
			return;
		}

		if ( $this->block_editor_only ) {
			\add_action( 'enqueue_block_assets', [ $this, 'enqueue_block_editor_styles' ] );
		} else {
			\add_action( 'admin_init', [ $this, 'add_editor_styles' ] );
		}
	}

	/**
	 * Declares editor-styles theme support and registers the configured
	 * stylesheet with WordPress via add_editor_style().
	 *
	 * This loads the stylesheet into both the block editor and the classic
	 * editor. WordPress automatically prefixes selectors with
	 * .editor-styles-wrapper in the block editor.
	 *
	 * @return void
	 */
	public function add_editor_styles(): void {
		\add_theme_support( 'editor-styles' );
		\add_editor_style( $this->editor_styles_path );
	}

	/**
	 * Enqueues the stylesheet inside the block editor iframe only.
	 *
	 * The enqueue_block_assets hook fires for both the front-end and the
	 * block editor. The is_admin() guard restricts loading to the editor.
	 * Styles enqueued here appear inside the editor iframe alongside block
	 * content, but do not affect the classic TinyMCE editor.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_styles(): void {
		if ( ! \is_admin() ) {
			return;
		}

		$file = \get_stylesheet_directory() . $this->editor_styles_path;

		if ( ! \file_exists( $file ) ) {
			return;
		}

		\wp_enqueue_style(
			'theme-editor-styles',
			\get_stylesheet_directory_uri() . $this->editor_styles_path,
			[],
			(string) \filemtime( $file )
		);
	}
}
