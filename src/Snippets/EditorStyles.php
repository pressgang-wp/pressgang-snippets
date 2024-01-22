<?php

namespace PressGang\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Class EditorStyles
 *
 * Handles the addition of custom styles to the WordPress editor. This class is responsible for
 * ensuring that the styles used in the WordPress editor match those on the front-end, providing
 * a consistent editing experience.
 *
 * @see https://codex.wordpress.org/Editor_Style
 */
class EditorStyles implements SnippetInterface {

	/**
	 * Default path for the editor styles.
	 */
	const DEFAULT_PATH = '/css/editor-styles.css';

	/**
	 * Path to the editor styles file.
	 *
	 * @var string
	 */
	protected string $editor_styles_path;

	/**
	 * EditorStyles constructor.
	 *
	 * Initializes the class with provided arguments and sets up the WordPress hook to add custom
	 * editor styles.
	 *
	 * @param array $args An associative array of initialization arguments. It can include:
	 *                    - 'path': The path to the editor styles file. If not set, the default path is used.
	 */
	public function __construct( array $args ) {
		$this->editor_styles_path = $args['path'] ?? self::DEFAULT_PATH;
		\add_action( 'admin_init', [ $this, 'add_editor_styles' ] );
	}

	/**
	 * Adds custom styles to the WordPress editor.
	 *
	 * This method is hooked into the 'admin_init' action and is responsible for adding
	 * theme support for editor styles and specifying the stylesheet that should be used
	 * in the editor. This stylesheet should help maintain visual consistency between
	 * the WordPress editor and the front-end of the website.
	 *
	 * @hooked admin_init
	 */
	public function add_editor_styles(): void {
		if ( $this->editor_styles_path ) {
			\add_theme_support( 'editor-styles' );
			\add_editor_style( $this->editor_styles_path );
		}
	}

}