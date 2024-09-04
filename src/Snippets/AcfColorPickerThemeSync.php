<?php

namespace PressGang\Snippets;

class AcfColorPickerThemeSync implements SnippetInterface {

	/**
	 * @var array
	 */
	private $custom_colors;

	/**
	 * Constructor that accepts a flat array of custom colors.
	 *
	 * @param array $args Array of custom colors (hex codes).
	 */
	public function __construct( array $args ) {
		// Store custom colors provided in the flat $args array.
		$this->custom_colors = $args;

		// Hook into the admin footer to output the color picker script.
		\add_action( 'acf/input/admin_footer', [ $this, 'acf_color_palette' ] );
	}

	/**
	 * Outputs JavaScript to sync theme.json and custom color palettes with ACF color picker.
	 *
	 * @return void
	 */
	public function acf_color_palette() {
		// Get theme colors from theme.json.
		$theme_colors = $this->get_theme_colors();

		// Encode the custom colors array and theme colors to JSON for safe use in JavaScript.
		$custom_colors = json_encode( $this->custom_colors );
		$theme_colors  = json_encode( $theme_colors ); ?>

        <script type="text/javascript">
            (function () {
                acf.add_filter('color_picker_args', function (args) {
                    // Get theme colors from PHP (from theme.json).
                    var theme_colors = <?php echo $theme_colors; ?>;

                    // Add custom colors passed from PHP.
                    var custom_colors = <?php echo $custom_colors; ?>;

                    // Merge theme colors and custom colors.
                    var palette_colors = theme_colors.length ? theme_colors : [];
                    if (custom_colors.length > 0) {
                        palette_colors = palette_colors.concat(custom_colors);
                    }

                    // Set the color palette for the ACF color picker.
                    args.palettes = palette_colors;
                    return args;
                });
            })();
        </script>
	<?php }

	/**
	 * Get theme colors from the theme.json file.
	 *
	 * @return array Array of color hex codes from theme.json.
	 */
	private function get_theme_colors() {
		// Check if the theme.json file exists in the active theme.
		$theme_json_path = \get_stylesheet_directory() . '/theme.json';
		$colors          = [];

		if ( file_exists( $theme_json_path ) ) {
			// Read the theme.json file and parse it.
			$theme_json = json_decode( file_get_contents( $theme_json_path ), true );

			// Extract colors if they exist in the theme.json file.
			if ( isset( $theme_json['settings']['color']['palette'] ) ) {
				foreach ( $theme_json['settings']['color']['palette'] as $color ) {
					if ( isset( $color['color'] ) ) {
						$colors[] = $color['color']; // Get the hex code.
					}
				}
			}
		}

		return $colors;
	}

}