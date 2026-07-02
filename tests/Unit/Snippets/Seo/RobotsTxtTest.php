<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Seo;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Seo\RobotsTxt;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Seo\RobotsTxt
 */
class RobotsTxtTest extends TestCase {

	public function test_constructor_registers_filter(): void {
		Functions\when( 'home_url' )->justReturn( 'https://example.com/sitemap_index.xml' );

		Filters\expectAdded( 'robots_txt' )->once();

		new RobotsTxt( [] );
	}

	public function test_filter_robots_txt_uses_default_woocommerce_rules(): void {
		$snippet = new RobotsTxt( [ 'sitemap_url' => 'https://example.com/sitemap.xml' ] );

		$output = $snippet->filter_robots_txt( '', true );

		$this->assertStringContainsString( 'Disallow: /wp-content/uploads/wc-logs/', $output );
		$this->assertStringContainsString( 'Disallow: /*?add-to-cart=*', $output );
		$this->assertStringContainsString( 'Allow: /wp-admin/admin-ajax.php', $output );
		$this->assertStringContainsString( 'Sitemap: https://example.com/sitemap.xml', $output );
	}

	public function test_filter_robots_txt_uses_configured_rules(): void {
		$snippet = new RobotsTxt(
			[
				'allow'       => [],
				'disallow'    => [
					'/calendar',
					'/*?*yith_wcan=1',
					'/*?add-to-cart=',
				],
				'sitemap_url' => '',
			]
		);

		$output = $snippet->filter_robots_txt( '', true );

		$this->assertSame(
			"User-agent: *\nDisallow: /calendar\nDisallow: /*?*yith_wcan=1\nDisallow: /*?add-to-cart=",
			$output
		);
	}
}
