<?php

namespace PressGang\Snippets;

/**
 * Customizes ACF WYSIWYG fields by adding a minimal toolbar and allowing the specification of the editor's height.
 *
 * This class hooks into ACF's filters and actions to customize the WYSIWYG toolbars and add a field setting for the
 * editor height. It aims to enhance the flexibility and usability of ACF's WYSIWYG editor for specific use cases.
 *
 * @see https://www.advancedcustomfields.com/resources/customize-the-wysiwyg-toolbars/
 * @see https://gist.github.com/stianandreassen/6dc87c88c43b2bc43d0ea1a94bd5cd1e
 */
class AcfWysiwygMin implements SnippetInterface {

	/**
	 * Custom toolbar configurations.
	 *
	 * @var array
	 */
	protected array $toolbars = [ 'Minimal' => [ 'bold', 'italic', 'underline' ] ];

	/**
	 * AcfWysiwygMin constructor.
	 *
	 * Sets up filters and actions to customize ACF WYSIWYG fields.
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
	 * Customizes the ACF WYSIWYG toolbars to add a 'Minimal' toolbar option.
	 *
	 * @param array $toolbars Existing toolbars.
	 *
	 * @return array Modified toolbars with the 'Minimal' option added.
	 */
	public function toolbars( array $toolbars ): array {
		foreach ( $this->toolbars as $toolbar_name => $buttons ) {
			$toolbars[ $toolbar_name ]    = [];
			$toolbars[ $toolbar_name ][1] = $buttons;
		}

		return $toolbars;
	}

	/**
	 * Adds a setting for specifying the height of the WYSIWYG editor in the ACF field settings.
	 *
	 * @param array $field The field settings array.
	 */
	public function wysiwyg_render_field_settings( array $field ): void {
		\acf_render_field_setting( $field, [
			'label'        => \__( "Height of Editor", "text-domain" ),
			'instructions' => \__( "Specify the height of the WYSIWYG editor in pixels.", "text-domain" ),
			'name'         => 'wysiwyg_height',
			'type'         => 'number',
			'placeholder'  => '300',
		] );
	}

	/**
	 * Applies the custom height to the WYSIWYG editor using inline styles and JavaScript.
	 *
	 * @param array $field The field being rendered.
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
