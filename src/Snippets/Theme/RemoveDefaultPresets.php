<?php

namespace PressGang\Snippets\Theme;

use PressGang\Snippets\SnippetInterface;

/**
 * Strips WordPress' built-in design presets (the core colour palette, gradients,
 * font sizes, and so on) out of the global styles CSS when a theme has already
 * opted out of them in theme.json.
 *
 * A theme that sets `settings.color.defaultPalette` to false expects the core
 * colours to be gone. They are — but only from the editor's colour picker, which
 * is where WordPress applies the flag. The frontend still prints every core
 * preset, because `wp_get_global_stylesheet()` always renders the `default`
 * origin regardless of the opt-out. On a theme with a small custom palette that
 * is typically half the weight of the inline `global-styles` block, for
 * variables and utility classes nothing on the site references.
 *
 * This snippet closes that gap. It reads the theme's own `default*` opt-out
 * flags and removes the matching presets from the default origin before the
 * stylesheet is generated, so the CSS matches what the theme asked for. Themes
 * that have not opted out of anything are left untouched.
 *
 * Enable it on any theme that defines its own palette, font sizes, or spacing
 * scale and disables the core equivalents. Note that core blocks, block patterns,
 * and existing post content referencing a removed preset (for example the
 * `has-vivid-red-color` class) will lose that styling — remove only what the
 * site genuinely does not use.
 */
class RemoveDefaultPresets implements SnippetInterface {

	/**
	 * Maps each theme.json opt-out flag to the preset group it governs.
	 *
	 * Both sides are dot-delimited paths within `settings`. The pairs mirror the
	 * `prevent_override` relationships in WordPress' own `WP_Theme_JSON::PRESETS_METADATA`,
	 * which is what makes a flag and a preset group correspond in the first place.
	 *
	 * @var array<string, string>
	 */
	protected const PRESET_FLAGS = [
		'color.defaultPalette'           => 'color.palette',
		'color.defaultGradients'         => 'color.gradients',
		'color.defaultDuotone'           => 'color.duotone',
		'typography.defaultFontSizes'    => 'typography.fontSizes',
		'spacing.defaultSpacingSizes'    => 'spacing.spacingSizes',
		'shadow.defaultPresets'          => 'shadow.presets',
		'dimensions.defaultAspectRatios' => 'dimensions.aspectRatios',
	];

	/**
	 * Preset groups to remove, as dot-delimited paths within `settings`
	 * (e.g. 'color.palette'), or null to derive them from theme.json.
	 *
	 * @var array<int, string>|null
	 */
	protected ?array $presets;

	/**
	 * Hooks the removal into the default global styles data, which is the last
	 * point at which core's presets can be edited before the stylesheet is built.
	 *
	 * @param array $args {
	 *     @type array<int, string> $presets Optional. Preset groups to remove, as
	 *         dot-delimited paths within `settings` — 'color.palette',
	 *         'typography.fontSizes', 'color.gradients', 'color.duotone',
	 *         'spacing.spacingSizes', 'shadow.presets', 'dimensions.aspectRatios'.
	 *         Omit this to remove whichever groups the theme has switched off via
	 *         the corresponding theme.json flag, which is usually what you want.
	 * }
	 */
	public function __construct( array $args ) {
		$this->presets = isset( $args['presets'] ) ? (array) $args['presets'] : null;

		\add_filter( 'wp_theme_json_data_default', [ $this, 'remove_default_presets' ] );
	}

	/**
	 * Removes the configured preset groups from WordPress' default global styles data.
	 *
	 * Presets are stored keyed by origin, and this filter only ever sees the
	 * `default` origin, so removing the 'default' key leaves any theme or user
	 * presets of the same kind fully intact.
	 *
	 * @param \WP_Theme_JSON_Data $theme_json Core's default global styles data.
	 *
	 * @return \WP_Theme_JSON_Data The data with the configured presets removed, or
	 *                             the original object when there is nothing to remove.
	 */
	public function remove_default_presets( \WP_Theme_JSON_Data $theme_json ): \WP_Theme_JSON_Data {
		$presets = $this->presets ?? $this->get_disabled_presets();

		if ( ! $presets ) {
			return $theme_json;
		}

		$data = $theme_json->get_data();

		foreach ( $presets as $preset ) {
			$path = \explode( '.', $preset );
			unset( $data['settings'][ $path[0] ][ $path[1] ]['default'] );

			// spacingSizes is regenerated from spacingScale, so clear that too.
			if ( 'spacing.spacingSizes' === $preset ) {
				unset( $data['settings']['spacing']['spacingScale']['default'] );
			}
		}

		return new \WP_Theme_JSON_Data( $data, 'default' );
	}

	/**
	 * Finds the preset groups the theme has opted out of, by reading the
	 * `default*` flags from its theme.json.
	 *
	 * Both the parent and child theme.json are read, with the child winning, so a
	 * child theme can opt in or out independently of the framework it extends.
	 * The files are read directly rather than through WP_Theme_JSON_Resolver
	 * because this runs inside the resolver's own default-data filter.
	 *
	 * Only an explicit `false` counts as opting out; an absent flag means the
	 * theme never expressed a preference and its presets are left alone.
	 *
	 * @return array<int, string> Dot-delimited preset paths to remove, possibly empty.
	 */
	protected function get_disabled_presets(): array {
		$settings = [];

		foreach ( [ \get_template_directory(), \get_stylesheet_directory() ] as $directory ) {
			$file = $directory . '/theme.json';

			if ( ! \is_readable( $file ) ) {
				continue;
			}

			$config = \json_decode( (string) \file_get_contents( $file ), true );

			if ( \is_array( $config ) && isset( $config['settings'] ) ) {
				$settings = \array_replace_recursive( $settings, $config['settings'] );
			}
		}

		$presets = [];

		foreach ( self::PRESET_FLAGS as $flag => $preset ) {
			[ $group, $key ] = \explode( '.', $flag );

			if ( false === ( $settings[ $group ][ $key ] ?? null ) ) {
				$presets[] = $preset;
			}
		}

		return $presets;
	}
}
