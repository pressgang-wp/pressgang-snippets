<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds a "Custom Styles" group to TinyMCE's style dropdown using configured
 * style formats.
 *
 * Enable this snippet to expose custom editor styles in the WYSIWYG. Styles
 * can be passed via $args['styles'] (array of TinyMCE format definitions). If
 * a Config class is available, it will fall back to Config::get('wysiwyg_styles').
 */
class WysiwygStyles implements SnippetInterface {

	/**
	 * @var array<int, array<string, mixed>>
	 */
	private array $styles;

	/**
	 * Registers TinyMCE filters and stores style configuration.
	 *
	 * @param array{styles?: array<int, array<string, mixed>>} $args
	 */
	public function __construct( array $args ) {
		$this->styles = $args['styles'] ?? $this->get_config_styles();

		\add_filter( 'mce_buttons_2', [ $this, 'show_tinymce_format' ] );
		\add_filter( 'tiny_mce_before_init', [ $this, 'show_custom_styles_dropdown' ] );
	}

	/**
	 * Adds the style selector to the TinyMCE toolbar.
	 *
	 * @param array<int, string> $buttons
	 *
	 * @return array<int, string>
	 */
	public function show_tinymce_format( array $buttons ): array {
		array_unshift( $buttons, 'styleselect' );

		return $buttons;
	}

	/**
	 * Adds custom styles to the TinyMCE style_formats list.
	 *
	 * @param array<string, mixed> $settings
	 *
	 * @return array<string, mixed>
	 */
	public function show_custom_styles_dropdown( array $settings ): array {
		if ( empty( $this->styles ) ) {
			return $settings;
		}

		$style_formats = [
			[
				'title' => "Custom Styles",
				'items' => $this->styles,
			],
		];

		$settings['style_formats_merge'] = true;
		$settings['style_formats']       = \wp_json_encode( $style_formats );

		return $settings;
	}

	/**
	 * Resolve style formats from Config when available.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_config_styles(): array {
		if ( class_exists( 'Config' ) && method_exists( 'Config', 'get' ) ) {
			$styles = \Config::get( 'wysiwyg_styles' );
			if ( is_array( $styles ) ) {
				return $styles;
			}
		}

		return [];
	}
}
