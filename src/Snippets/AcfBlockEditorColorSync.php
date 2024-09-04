<?php

namespace PressGang\Snippets;

class AcfBlockEditorColorSync implements SnippetInterface {

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
	 * Outputs JavaScript to sync Block Editor and custom color palettes with ACF color picker.
	 *
	 * @return void
	 */
	public function acf_color_palette() {
		// Encode the custom colors array to JSON for safe use in JavaScript.
		$custom_colors = json_encode( $this->custom_colors ); ?>
        <script type="text/javascript">
            (function () {
                // Check if wp.data is available before running the script.
                if (typeof wp !== 'undefined' && typeof wp.data !== 'undefined') {
                    acf.add_filter('color_picker_args', function (args) {
                        // Get Block Editor colors from block editor settings.
                        var block_editor_colors = wp.data.select('core/block-editor').getSettings().colors || [];

                        // Map Block Editor colors to an array of color values.
                        var palette_colors = block_editor_colors.map(function (color) {
                            return color.color;
                        });

                        // Add custom colors passed from PHP (from $args).
                        var custom_colors = <?php echo $custom_colors; ?>;
                        if (custom_colors.length > 0) {
                            palette_colors = palette_colors.concat(custom_colors);
                        }

                        // Set the color palette for the ACF color picker.
                        args.palettes = palette_colors;
                        return args;
                    });
                }
            })();
        </script>
	<?php }

}