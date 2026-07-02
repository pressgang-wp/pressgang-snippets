<?php

namespace PressGang\Tests\Snippets\Unit\Snippets\Seo;

use Brain\Monkey\Filters;
use PressGang\Snippets\Seo\RobotsTxt;
use PressGang\Tests\Snippets\Unit\TestCase;

/**
 * @covers \PressGang\Snippets\Seo\RobotsTxt
 */
class RobotsTxtTest extends TestCase {

	public function test_constructor_registers_filter(): void {
		Filters\expectAdded( 'robots_txt' )->once();

		new RobotsTxt( [] );
	}

	public function test_filter_robots_txt_has_no_site_specific_default_rules(): void {
		$snippet = new RobotsTxt( [] );

		$output = $snippet->filter_robots_txt( '', true );

		$this->assertSame( 'User-agent: *', $output );
	}

	public function test_filter_robots_txt_uses_configured_rules(): void {
		$snippet = new RobotsTxt(
			[
				'allow'       => '/wp-admin/admin-ajax.php',
				'disallow'    => [
					'/calendar',
					'/*?*yith_wcan=1',
					'/*?add-to-cart=',
				],
				'sitemap_url' => 'https://example.com/sitemap_index.xml',
			]
		);

		$output = $snippet->filter_robots_txt( '', true );

		$this->assertSame(
			"User-agent: *\nDisallow: /calendar\nDisallow: /*?*yith_wcan=1\nDisallow: /*?add-to-cart=\nAllow: /wp-admin/admin-ajax.php\nSitemap: https://example.com/sitemap_index.xml",
			$output
		);
	}
}
