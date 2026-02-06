<?php


namespace PressGang\Snippets\Acf;

use PressGang\Snippets\SnippetInterface;

/**
 * Syncs the ACF colour-picker palette with colours defined in theme.json and
 * any additional custom colours passed via configuration.
 *
 * Enable this snippet when you use ACF colour-picker fields and want the
 * palette options to automatically reflect the theme's design tokens — so
 * content editors pick from on-brand colours instead of free-form values.
 */
class ColorPickerThemeSync implements SnippetInterface {

	/**
	 * Additional hex colour codes to include in the palette alongside
	 * theme.json colours.
	 *
	 * @var array<int, string>
	 */
	private array $custom_colors;

	/**
	 * Stores any custom colours and hooks into the ACF admin footer to inject
	 * the colour-picker palette script.
	 *
	 * @param array<int, string> $args Flat array of hex colour codes
	 *     (e.g. ['#ff0000', '#00ff00']). Merged with colours extracted from
	 *     theme.json at render time.
	 */
	public function __construct( array $args ) {
		$this->custom_colors = $args;

		\add_action( 'acf/input/admin_footer', [ $this, 'acf_color_palette' ] );
	}

	/**
	 * Outputs an inline script that feeds merged theme.json + custom colours
	 * into the ACF colour-picker via the `color_picker_args` JS filter.
	 *
	 * @return void
	 */
	public function acf_color_palette(): void {
		$theme_colors = $this->get_theme_colors();

		$custom_colors = json_encode( $this->custom_colors );
		$theme_colors  = json_encode( $theme_colors ); ?>

        <script type="text/javascript">
            (function () {
                acf.add_filter('color_picker_args', function (args) {
                    var theme_colors = <?php echo $theme_colors; ?>;
                    var custom_colors = <?php echo $custom_colors; ?>;
                    var palette_colors = theme_colors.length ? theme_colors : [];
                    if (custom_colors.length > 0) {
                        palette_colors = palette_colors.concat(custom_colors);
                    }
                    args.palettes = palette_colors;
                    return args;
                });
            })();
        </script>
	<?php }

	/**
	 * Reads the active theme's theme.json and extracts colour hex codes from
	 * settings.color.palette.
	 *
	 * @return array<int, string> Hex colour codes, or an empty array if
	 *     theme.json does not exist or contains no palette.
	 */
	private function get_theme_colors(): array {
		$theme_json_path = \get_stylesheet_directory() . '/theme.json';
		$colors          = [];

		if ( file_exists( $theme_json_path ) ) {
			$theme_json = json_decode( file_get_contents( $theme_json_path ), true );

			if ( isset( $theme_json['settings']['color']['palette'] ) ) {
				foreach ( $theme_json['settings']['color']['palette'] as $color ) {
					if ( isset( $color['color'] ) ) {
						$colors[] = $color['color'];
					}
				}
			}
		}

		return $colors;
	}
}
