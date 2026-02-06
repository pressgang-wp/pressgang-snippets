<?php
namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\Permalinks;
use PressGang\Tests\Snippets\Unit\TestCase;

class PermalinksTest extends TestCase {
    public function test_constructor_registers_hook(): void {
        Actions\expectAdded('generate_rewrite_rules')->once();
        new Permalinks([]);
    }

    public function test_add_rewrites_adds_rules_for_enabled_folders(): void {
        $snippet = new Permalinks([]);

        Functions\expect('get_stylesheet_directory')
            ->once()
            ->andReturn('/var/www/wp-content/themes/my-theme');

        $wp_rewrite = \Mockery::mock('WP_Rewrite');
        $wp_rewrite->non_wp_rules = [];

        $snippet->add_rewrites($wp_rewrite);

        $this->assertArrayHasKey('css/(.*)', $wp_rewrite->non_wp_rules);
        $this->assertArrayHasKey('js/(.*)', $wp_rewrite->non_wp_rules);
        $this->assertArrayHasKey('img/(.*)', $wp_rewrite->non_wp_rules);
        $this->assertArrayHasKey('fonts/(.*)', $wp_rewrite->non_wp_rules);
        $this->assertArrayNotHasKey('plugins/(.*)', $wp_rewrite->non_wp_rules);
    }

    public function test_add_rewrites_respects_custom_config(): void {
        $snippet = new Permalinks(['css' => false, 'plugins' => true]);

        Functions\expect('get_stylesheet_directory')
            ->once()
            ->andReturn('/var/www/wp-content/themes/my-theme');

        $wp_rewrite = \Mockery::mock('WP_Rewrite');
        $wp_rewrite->non_wp_rules = [];

        $snippet->add_rewrites($wp_rewrite);

        $this->assertArrayNotHasKey('css/(.*)', $wp_rewrite->non_wp_rules);
        $this->assertArrayHasKey('plugins/(.*)', $wp_rewrite->non_wp_rules);
    }
}
