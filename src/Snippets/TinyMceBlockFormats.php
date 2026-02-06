<?php

declare(strict_types=1);

namespace PressGang\Snippets;

/**
 * Customises the block-format dropdown in the classic TinyMCE editor.
 * By default H1 is omitted (since it should be reserved for the post title)
 * and only Paragraph, H2, H3, and H4 are available.
 *
 * Pass an associative array of Label => tag pairs via $args to override the
 * defaults.
 *
 * @see https://developer.wordpress.org/reference/hooks/tiny_mce_before_init/
 */
class TinyMceBlockFormats implements SnippetInterface {

	/**
	 * Map of display labels to HTML tag names.
	 *
	 * @var array<string, string>
	 */
	protected array $block_formats;

	/**
	 * Merges provided formats with defaults and hooks into
	 * tiny_mce_before_init.
	 *
	 * @param array<string, string> $args Label => tag pairs to add or override.
	 */
	public function __construct( array $args = [] ) {
		$defaults = [
			'Paragraph' => 'p',
			'Heading 2' => 'h2',
			'Heading 3' => 'h3',
			'Heading 4' => 'h4',
		];

		$this->block_formats = \wp_parse_args( $args, $defaults );

		\add_filter( 'tiny_mce_before_init', [ $this, 'customize_block_formats' ] );
	}

	/**
	 * Sets the block_formats key in the TinyMCE configuration array.
	 *
	 * @param array<string, mixed> $in The TinyMCE configuration.
	 *
	 * @return array<string, mixed> The modified configuration.
	 */
	public function customize_block_formats( array $in ): array {
		$formats = array_map(
			fn( string $tag, string $label ) => "{$label}={$tag}",
			$this->block_formats,
			array_keys( $this->block_formats )
		);

		$in['block_formats'] = implode( '; ', $formats ) . ';';

		return $in;
	}
}