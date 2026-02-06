<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\ImageSizes;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\ImageSizes
 */
class ImageSizesTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_init_hook(): void {
		Actions\expectAdded( 'init' )->once();

		new ImageSizes( [] );
	}

	/**
	 * @return void
	 */
	public function test_setup_updates_default_sizes_via_options(): void {
		$snippet = new ImageSizes( [
			'thumbnail' => [ 'width' => 200, 'height' => 200, 'crop' => true ],
		] );

		Functions\expect( 'update_option' )
			->once()
			->with( 'thumbnail_size_w', 200 );

		Functions\expect( 'update_option' )
			->once()
			->with( 'thumbnail_size_h', 200 );

		Functions\expect( 'update_option' )
			->once()
			->with( 'thumbnail_crop', true );

		$snippet->setup_image_sizes();
	}

	/**
	 * @return void
	 */
	public function test_setup_registers_custom_sizes(): void {
		$snippet = new ImageSizes( [
			'hero' => [ 'width' => 1920, 'height' => 600, 'crop' => true ],
		] );

		Functions\expect( 'add_image_size' )
			->once()
			->with( 'hero', 1920, 600, true );

		$snippet->setup_image_sizes();
	}

	/**
	 * @return void
	 */
	public function test_setup_disables_default_size_by_zeroing_options(): void {
		$snippet = new ImageSizes( [
			'medium' => false,
		] );

		Functions\expect( 'update_option' )
			->once()
			->with( 'medium_size_w', 0 );

		Functions\expect( 'update_option' )
			->once()
			->with( 'medium_size_h', 0 );

		Functions\expect( 'update_option' )
			->once()
			->with( 'medium_crop', 0 );

		$snippet->setup_image_sizes();
	}

	/**
	 * @return void
	 */
	public function test_setup_disables_custom_size_by_removing(): void {
		$snippet = new ImageSizes( [
			'hero' => false,
		] );

		Functions\expect( 'remove_image_size' )
			->once()
			->with( 'hero' );

		$snippet->setup_image_sizes();
	}

	/**
	 * @return void
	 */
	public function test_defaults_for_missing_width_height_crop(): void {
		$snippet = new ImageSizes( [
			'banner' => [ 'width' => 800 ],
		] );

		// width=800, height defaults to 0, crop defaults to false
		// But since height=0 the custom size won't be added (width > 0 && height > 0 check)
		// So no add_image_size call expected
		Functions\expect( 'add_image_size' )->never();

		$snippet->setup_image_sizes();
	}

	/**
	 * @return void
	 */
	public function test_default_sizes_use_fallback_values(): void {
		$snippet = new ImageSizes( [
			'thumbnail' => [ 'width' => 150 ],
		] );

		// Default sizes always call update_option, even with height=0
		Functions\expect( 'update_option' )
			->once()
			->with( 'thumbnail_size_w', 150 );

		Functions\expect( 'update_option' )
			->once()
			->with( 'thumbnail_size_h', 0 );

		Functions\expect( 'update_option' )
			->once()
			->with( 'thumbnail_crop', false );

		$snippet->setup_image_sizes();
	}
}
