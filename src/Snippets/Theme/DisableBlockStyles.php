<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Dequeues the block library and global styles for themes that do not use
 * the block editor (e.g. Classic Editor + pattern-library CSS themes).
 *
 * Args:
 * - 'handles' (optional): style handles to dequeue, replacing the defaults.
 */
class DisableBlockStyles implements SnippetInterface {

	/**
	 * Default style handles to dequeue.
	 *
	 * @var array<int, string>
	 */
	protected const DEFAULT_HANDLES = [
		'wp-block-library',        // WordPress core block CSS
		'wp-block-library-theme',  // WordPress theme block CSS
		'classic-theme-styles',    // Classic theme inline CSS
		'wc-block-style',          // WooCommerce block CSS
		'global-styles',           // theme.json CSS
		'global-styles-inline-css',
	];

	/**
	 * Style handles to dequeue.
	 *
	 * @var array<int, string>
	 */
	protected array $handles;

	/**
	 * Hooks the dequeue late so it runs after the styles are enqueued.
	 *
	 * @param array $args {
	 *     @type array<int, string> $handles Style handles to dequeue (optional).
	 * }
	 */
	public function __construct( array $args ) {
		$this->handles = $args['handles'] ?? self::DEFAULT_HANDLES;

		\add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_block_styles' ], 999 );
	}

	/**
	 * Dequeues and deregisters the configured style handles.
	 *
	 * @return void
	 */
	public function dequeue_block_styles(): void {
		foreach ( $this->handles as $handle ) {
			\wp_dequeue_style( $handle );
			\wp_deregister_style( $handle );
		}
	}
}
