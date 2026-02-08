<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;

use Brain\Monkey\Filters;
use PressGang\Snippets\Theme\TaxonomyFilter;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Theme\TaxonomyFilter
 */
class TaxonomyFilterTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filters(): void {
		Filters\expectAdded( 'timber/twig' )->once();
		Filters\expectAdded( 'timber/context' )->once();

		new TaxonomyFilter( [] );
	}

	/**
	 * @return void
	 */
	public function test_inputs_use_configured_taxonomies(): void {
		$snippet = new TaxonomyFilter( [
			'taxonomies' => [ 'genre' ],
		] );

		$inputs = $this->get_property( $snippet, 'inputs' );

		$this->assertArrayHasKey( 'genre', $inputs );
		$this->assertArrayNotHasKey( 'tag', $inputs );
		$this->assertArrayNotHasKey( 'category', $inputs );
	}

	/**
	 * @return void
	 */
	public function test_add_filter_inputs_to_context(): void {
		$snippet = new TaxonomyFilter( [
			'taxonomies' => [ 'genre' ],
		] );

		$this->set_property( $snippet, 'inputs', [ 'genre' => [ 'noir' ] ] );

		$context = $snippet->add_filter_inputs_to_context( [] );

		$this->assertSame( [ 'genre' => [ 'noir' ] ], $context['inputs'] );
	}

	/**
	 * @return void
	 */
	public function test_add_taxonomy_lookups_to_twig_adds_function(): void {
		$snippet = new TaxonomyFilter( [] );

		$twig = \Mockery::mock( \Twig\Environment::class );
		$twig->shouldReceive( 'addFunction' )
			->once()
			->with( \Mockery::on( function ( $fn ) {
				return $fn instanceof \Twig\TwigFunction && $fn->getName() === 'taxonomy_lookup';
			} ) );

		$result = $snippet->add_taxonomy_lookups_to_twig( $twig );

		$this->assertSame( $twig, $result );
	}

	/**
	 * @return void
	 */
	public function test_taxonomy_lookup_returns_cached_terms(): void {
		$snippet = new TaxonomyFilter( [] );

		$this->set_property( $snippet, 'taxonomies', [ 'category' ] );
		$this->set_property( $snippet, 'terms', [ 'category' => [ 'term-a', 'term-b' ] ] );

		$this->assertSame( [ 'term-a', 'term-b' ], $snippet->taxonomy_lookup( 'category' ) );
	}

	/**
	 * @return void
	 */
	public function test_taxonomy_lookup_returns_null_for_unknown_taxonomy(): void {
		$snippet = new TaxonomyFilter( [] );

		$this->set_property( $snippet, 'taxonomies', [] );

		$this->assertNull( $snippet->taxonomy_lookup( 'unknown' ) );
	}

	/**
	 * @param object $object
	 * @param string $property
	 *
	 * @return mixed
	 */
	private function get_property( object $object, string $property ) {
		$reflection = new \ReflectionProperty( $object, $property );
		$reflection->setAccessible( true );

		return $reflection->getValue( $object );
	}

	/**
	 * @param object $object
	 * @param string $property
	 * @param mixed  $value
	 *
	 * @return void
	 */
	private function set_property( object $object, string $property, $value ): void {
		$reflection = new \ReflectionProperty( $object, $property );
		$reflection->setAccessible( true );
		$reflection->setValue( $object, $value );
	}
}
