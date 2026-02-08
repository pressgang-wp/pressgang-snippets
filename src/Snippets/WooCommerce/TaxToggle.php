<?php

namespace PressGang\Snippets\WooCommerce;

use PressGang\Snippets\SnippetInterface;
use Timber\Timber;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Adds a front-end toggle to switch WooCommerce price display between
 * inclusive and exclusive tax.
 *
 * Enable this snippet to give users control over tax display. Requires
 * WooCommerce to be active. Renders a Twig function `tax_toggle()`.
 */
class TaxToggle implements SnippetInterface {

	public const COOKIE_NAME = 'woocommerce_tax_display';

	private string $default_shop_display;

	private string $default_cart_display;

	/**
	 * Registers hooks, loads defaults, and registers the Twig function.
	 *
	 * @param array<string, mixed> $args Unused; required by SnippetInterface.
	 */
	public function __construct( array $args ) {
		$this->default_shop_display = (string) \get_option( 'woocommerce_tax_display_shop' );
		$this->default_cart_display = (string) \get_option( 'woocommerce_tax_display_cart' );

		\add_filter( 'timber/twig', [ $this, 'add_to_twig' ], 100, 1 );
		\add_filter( 'option_woocommerce_tax_display_shop', [ $this, 'filter_tax_display' ], 10, 1 );
		\add_filter( 'option_woocommerce_tax_display_cart', [ $this, 'filter_tax_display' ], 10, 1 );
		\add_action( 'init', [ $this, 'set_tax_display' ] );
	}

	/**
	 * Handles toggle submissions and sets the tax display cookie.
	 *
	 * @return void
	 */
	public function set_tax_display(): void {
		if ( ! isset( $_POST[ self::COOKIE_NAME . '_toggle' ] ) ) {
			return;
		}

		$display_tax = filter_input( INPUT_POST, self::COOKIE_NAME, FILTER_VALIDATE_BOOLEAN );
		$display_tax = $display_tax ? 'incl' : 'excl';

		$this->set_tax_cookie( $display_tax );

		if ( \wp_safe_redirect( \add_query_arg( 'vat', $display_tax, \Timber\UrlHelper::get_current_url() ) ) ) {
			exit;
		}
	}

	/**
	 * Registers the tax_toggle Twig function.
	 *
	 * @param Environment $twig
	 *
	 * @return Environment
	 */
	public function add_to_twig( Environment $twig ): Environment {
		$twig->addFunction( new TwigFunction( 'tax_toggle', [ $this, 'render_tax_toggle' ] ) );

		return $twig;
	}

	/**
	 * Renders the tax toggle UI.
	 *
	 * @return void
	 */
	public function render_tax_toggle(): void {
		Timber::render( 'snippets/woocommerce/tax-toggle.twig', [
			'display_tax' => ( $this->get_tax_display() === 'incl' ),
			'cookie_name' => self::COOKIE_NAME,
		] );
	}

	/**
	 * Filters WooCommerce tax display options to use the cookie.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function filter_tax_display( string $value ): string {
		return $this->get_tax_display();
	}

	/**
	 * Get the current tax display mode from cookie or defaults.
	 *
	 * @return string
	 */
	public function get_tax_display(): string {
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return (string) $_COOKIE[ self::COOKIE_NAME ];
		}

		$this->set_tax_cookie( $this->default_shop_display );

		return $this->default_shop_display;
	}

	/**
	 * Set the cookie for tax display.
	 *
	 * @param string $display_tax
	 *
	 * @return void
	 */
	protected function set_tax_cookie( string $display_tax ): void {
		setcookie( self::COOKIE_NAME, $display_tax, time() + 365 * DAY_IN_SECONDS, '/' );
	}
}
