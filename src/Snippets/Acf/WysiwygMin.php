<?php


namespace PressGang\Snippets\Acf;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds a minimal toolbar option to ACF WYSIWYG fields and a per-field height
 * setting, giving content editors a streamlined editing experience for fields
 * that only need basic formatting.
 *
 * Enable this snippet when you want a "Minimal" toolbar (bold, italic,
 * underline) available in the ACF WYSIWYG toolbar dropdown, and/or when you
 * need to control the editor height per field.
 *
 * @see https://www.advancedcustomfields.com/resources/customize-the-wysiwyg-toolbars/
 */
class WysiwygMin implements SnippetInterface {

	/**
	 * Custom toolbar definitions. Each key is a toolbar name and each value is
	 * an array of button identifiers for the first row.
	 *
	 * @var array<string, array<int, string>>
	 */
	protected array $toolbars = [ 'Minimal' => [ 'bold', 'italic', 'underline' ] ];

	/**
	 * Registers ACF filters and actions to add custom toolbars and a height
	 * setting to WYSIWYG fields.
	 *
	 * @param array<string, array<int, string>> $args Optional. Supports:
	 *     - 'toolbars': array<string, array<int, string>> — custom toolbar
	 *       definitions keyed by toolbar name. Replaces the default "Minimal"
	 *       toolbar if provided.
	 */
	public function __construct( array $args ) {

		if ( ! empty( $args['toolbars'] ) ) {
			$this->toolbars = $args['toolbars'];
		}

		\add_filter( 'acf/fields/wysiwyg/toolbars', [ $this, 'toolbars' ] );
		\add_action( 'acf/render_field_settings/type=wysiwyg', [ $this, 'wysiwyg_render_field_settings' ], 10, 1 );
		\add_action( 'acf/render_field/type=wysiwyg', [ $this, 'wysiwyg_render_field' ], 10, 1 );
	}

	/**
	 * Registers custom toolbar configurations into the ACF toolbar list.
	 *
	 * @param array<string, array<int, array<int, string>>> $toolbars Existing ACF toolbar definitions.
	 *
	 * @return array<string, array<int, array<int, string>>> Modified toolbars with custom entries added.
	 */
	public function toolbars( array $toolbars ): array {
		foreach ( $this->toolbars as $toolbar_name => $buttons ) {
			$toolbars[ $toolbar_name ]    = [];
			$toolbars[ $toolbar_name ][1] = $buttons;
		}

		return $toolbars;
	}

	/**
	 * Renders a "Height of Editor" number field in the ACF WYSIWYG field
	 * settings panel, allowing per-field height customisation.
	 *
	 * @param array<string, mixed> $field The ACF field settings array.
	 *
	 * @return void
	 */
	public function wysiwyg_render_field_settings( array $field ): void {
		\acf_render_field_setting( $field, [
			'label'        => \__( "Height of Editor", THEMENAME ),
			'instructions' => \__( "Specify the height of the WYSIWYG editor in pixels.", THEMENAME ),
			'name'         => 'wysiwyg_height',
			'type'         => 'number',
			'placeholder'  => '300',
		] );
	}

	/**
	 * Applies the configured height to the rendered WYSIWYG editor iframe via
	 * inline CSS and a jQuery fallback script.
	 *
	 * @param array<string, mixed> $field The ACF field being rendered.
	 *
	 * @return void
	 */
	public function wysiwyg_render_field( array $field ): void {
		if ( ! empty( $field['wysiwyg_height'] ) ) {
			$field_class = '.acf-' . str_replace( '_', '-', \esc_attr( $field['key'] ) );
			$height      = intval( $field['wysiwyg_height'] );

			echo "<style>{$field_class} iframe { min-height: {$height}px; }</style>";
			echo "<script type=\"text/javascript\">
                jQuery(document).ready(function () {
                    jQuery('{$field_class}').find('iframe').height({$height});
                });
            </script>";
		}
	}
}
