<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\RemoveDefaultPresets;
use PressGang\Tests\Snippets\Unit\TestCase;
use WP_Theme_JSON_Data;

/**
 * @covers \PressGang\Snippets\Theme\RemoveDefaultPresets
 */
class RemoveDefaultPresetsTest extends TestCase {

	/**
	 * Points the theme directory lookups at the fixture theme.json files.
	 *
	 * @return void
	 */
	protected function stub_theme_directories(): void {
		Functions\when( 'get_template_directory' )->justReturn( __DIR__ . '/fixtures/parent-theme' );
		Functions\when( 'get_stylesheet_directory' )->justReturn( __DIR__ . '/fixtures/child-theme' );
	}

	/**
	 * Builds default global styles data with presets keyed by origin, as core does.
	 *
	 * @return WP_Theme_JSON_Data
	 */
	protected function default_data(): WP_Theme_JSON_Data {
		return new WP_Theme_JSON_Data(
			[
				'version'  => 3,
				'settings' => [
					'color'      => [
						'palette'   => [ 'default' => [ [ 'slug' => 'vivid-red' ] ] ],
						'gradients' => [ 'default' => [ [ 'slug' => 'vivid-cyan-blue-to-vivid-purple' ] ] ],
					],
					'typography' => [
						'fontSizes'    => [ 'default' => [ [ 'slug' => 'large' ] ] ],
						'fontFamilies' => [ 'default' => [ [ 'slug' => 'system-font' ] ] ],
					],
					'spacing'    => [
						'spacingSizes' => [ 'default' => [ [ 'slug' => '50' ] ] ],
						'spacingScale' => [ 'default' => [ 'steps' => 7 ] ],
					],
				],
			],
			'default'
		);
	}

	/**
	 * @return void
	 */
	public function test_constructor_registers_default_global_styles_filter(): void {
		Filters\expectAdded( 'wp_theme_json_data_default' )
			->once()
			->with( \Mockery::type( 'array' ) );

		new RemoveDefaultPresets( [] );
	}

	/**
	 * @return void
	 */
	public function test_explicit_presets_arg_removes_only_those_groups(): void {
		$snippet = new RemoveDefaultPresets( [ 'presets' => [ 'color.palette' ] ] );

		$settings = $snippet->remove_default_presets( $this->default_data() )->get_data()['settings'];

		$this->assertArrayNotHasKey( 'default', $settings['color']['palette'] );
		$this->assertArrayHasKey( 'default', $settings['color']['gradients'] );
		$this->assertArrayHasKey( 'default', $settings['typography']['fontSizes'] );
	}

	/**
	 * @return void
	 */
	public function test_presets_are_derived_from_theme_json_opt_outs(): void {
		$this->stub_theme_directories();

		$snippet = new RemoveDefaultPresets( [] );

		$settings = $snippet->remove_default_presets( $this->default_data() )->get_data()['settings'];

		// Child theme sets color.defaultPalette false.
		$this->assertArrayNotHasKey( 'default', $settings['color']['palette'] );

		// Child theme sets color.defaultGradients true, so the defaults stay.
		$this->assertArrayHasKey( 'default', $settings['color']['gradients'] );

		// Parent disables font sizes but the child re-enables them; the child wins.
		$this->assertArrayHasKey( 'default', $settings['typography']['fontSizes'] );

		// Nothing opted out of font families, so they are untouched.
		$this->assertArrayHasKey( 'default', $settings['typography']['fontFamilies'] );
	}

	/**
	 * @return void
	 */
	public function test_removing_spacing_sizes_also_removes_the_generating_scale(): void {
		$snippet = new RemoveDefaultPresets( [ 'presets' => [ 'spacing.spacingSizes' ] ] );

		$settings = $snippet->remove_default_presets( $this->default_data() )->get_data()['settings'];

		$this->assertArrayNotHasKey( 'default', $settings['spacing']['spacingSizes'] );
		$this->assertArrayNotHasKey( 'default', $settings['spacing']['spacingScale'] );
	}

	/**
	 * @return void
	 */
	public function test_data_is_returned_untouched_when_the_theme_opts_out_of_nothing(): void {
		Functions\when( 'get_template_directory' )->justReturn( __DIR__ . '/fixtures/missing-theme' );
		Functions\when( 'get_stylesheet_directory' )->justReturn( __DIR__ . '/fixtures/missing-theme' );

		$snippet = new RemoveDefaultPresets( [] );
		$data    = $this->default_data();

		$this->assertSame( $data, $snippet->remove_default_presets( $data ) );
	}
}
