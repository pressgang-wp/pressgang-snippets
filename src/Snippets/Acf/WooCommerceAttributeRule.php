<?php

namespace PressGang\Snippets\Acf;

use PressGang\Snippets\SnippetInterface;

/**
 * Adds WooCommerce product attribute taxonomies as location rules in ACF.
 *
 * Enable this snippet to allow ACF field groups to target specific product
 * attributes. Requires WooCommerce and ACF to be active.
 */
class WooCommerceAttributeRule implements SnippetInterface {

	/**
	 * Registers ACF location rule filters.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		\add_filter( 'acf/location/rule_types', [ $this, 'add_attribute_to_acf' ] );
		\add_filter( 'acf/location/rule_values/wc_prod_attr', [ $this, 'add_attribute_rules' ] );
		\add_filter( 'acf/location/rule_match/wc_prod_attr', [ $this, 'match_acf_rules' ], 10, 3 );
	}

	/**
	 * Adds the WooCommerce Product Attribute rule group to ACF.
	 *
	 * @param array<string, array<string, string>> $choices
	 *
	 * @return array<string, array<string, string>>
	 */
	public function add_attribute_to_acf( array $choices ): array {
		$choices[ \__( "Other", 'acf' ) ]['wc_prod_attr'] = 'WC Product Attribute';

		return $choices;
	}

	/**
	 * Adds available WooCommerce product attributes as rule values.
	 *
	 * @return array<string, string>
	 */
	public function add_attribute_rules(): array {
		$choices = [];

		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) || ! function_exists( 'wc_attribute_taxonomy_name' ) ) {
			return $choices;
		}

		foreach ( \wc_get_attribute_taxonomies() as $attr ) {
			$pa_name             = \wc_attribute_taxonomy_name( $attr->attribute_name );
			$choices[ $pa_name ] = $attr->attribute_label;
		}

		return $choices;
	}

	/**
	 * Matches the rule against the current ACF location options.
	 *
	 * @param bool  $match
	 * @param array $rule
	 * @param array $options
	 *
	 * @return bool
	 */
	public function match_acf_rules( bool $match, array $rule, array $options ): bool {
		if ( isset( $options['taxonomy'] ) ) {
			if ( '==' === $rule['operator'] ) {
				$match = $rule['value'] === $options['taxonomy'];
			} elseif ( '!=' === $rule['operator'] ) {
				$match = $rule['value'] !== $options['taxonomy'];
			}
		}

		return $match;
	}
}
