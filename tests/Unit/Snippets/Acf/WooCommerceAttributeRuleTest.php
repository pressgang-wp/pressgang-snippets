<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Acf;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Acf\WooCommerceAttributeRule;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Acf\WooCommerceAttributeRule
 */
class WooCommerceAttributeRuleTest extends TestCase {

	/**
	 * @return void
	 */
	public function test_constructor_registers_filters(): void {
		Filters\expectAdded( 'acf/location/rule_types' )->once();
		Filters\expectAdded( 'acf/location/rule_values/wc_prod_attr' )->once();
		Filters\expectAdded( 'acf/location/rule_match/wc_prod_attr' )->once();

		new WooCommerceAttributeRule( [] );
	}

	/**
	 * @return void
	 */
	public function test_add_attribute_to_acf_adds_choice(): void {
		$snippet = new WooCommerceAttributeRule( [] );
		$choices = [ 'Other' => [] ];

		Functions\expect( '__' )
			->once()
			->with( "Other", 'acf' )
			->andReturn( 'Other' );

		$result = $snippet->add_attribute_to_acf( $choices );

		$this->assertArrayHasKey( 'wc_prod_attr', $result['Other'] );
	}

	/**
	 * @return void
	 */
	public function test_match_acf_rules_compares_taxonomy(): void {
		$snippet = new WooCommerceAttributeRule( [] );

		$rule = [
			'operator' => '==',
			'value'    => 'pa_colour',
		];

		$options = [ 'taxonomy' => 'pa_colour' ];

		$this->assertTrue( $snippet->match_acf_rules( false, $rule, $options ) );
	}
}
