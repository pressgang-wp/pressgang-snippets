<?php

namespace PressGang\Snippets;


/**
 * TinyMceBlockFormats Class
 *
 * Allows for customizing the block formats in the TinyMCE editor.
 * By default, it hides the H1 heading and enables other specified formats.
 */
class TinyMceBlockFormats implements SnippetInterface {

	/**
	 * The block formats to enable in the TinyMCE editor.
	 *
	 * @var array
	 */
	protected array $block_formats;

	/**
	 * Constructor
	 *
	 * Registers the filter to modify TinyMCE editor settings.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/tiny_mce_before_init/
	 */
	public function __construct( array $args = [] ) {

		// Default block formats
		$defaults = [
			'Paragraph' => 'p',
			'Heading 2' => 'h2',
			'Heading 3' => 'h3',
			'Heading 4' => 'h4',
			// H1 is intentionally omitted to hide it
		];

		$this->block_formats = \wp_parse_args($args, $defaults);

		\add_filter( 'tiny_mce_before_init', [ $this, 'customize_block_formats' ] );
	}

	/**
	 * Customize Block Formats in TinyMCE Editor
	 *
	 * Sets the block formats in the TinyMCE editor based on constructor arguments.
	 *
	 * @hooked tiny_mce_before_init
	 * @param array $in The initial editor configuration.
	 * @return array The modified editor configuration.
	 */
	public function customize_block_formats( $in ): array {
		$formats = array_map(function ($tag, $label) {
			return "$label=$tag";
		}, $this->block_formats, array_keys($this->block_formats));

		$in['block_formats'] = implode('; ', $formats) . ';';

		return $in;
	}

}