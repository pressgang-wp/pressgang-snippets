<?php
namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\AdminLogo;
use PressGang\Tests\Snippets\Unit\TestCase;

class AdminLogoTest extends TestCase {
    public function test_constructor_registers_hook(): void {
        Actions\expectAdded('login_enqueue_scripts')->once();
        new AdminLogo([]);
    }

    public function test_add_login_logo_outputs_style_when_logo_set(): void {
        $snippet = new AdminLogo([]);
        Functions\expect('get_theme_mod')->with('logo')->andReturn('https://example.com/logo.png');
        Functions\expect('esc_url')->andReturnFirstArg();

        $this->expectOutputRegex('/background-image:\s*url\(https:\/\/example\.com\/logo\.png\)/');
        $snippet->add_login_logo();
    }

    public function test_add_login_logo_outputs_nothing_when_no_logo(): void {
        $snippet = new AdminLogo([]);
        Functions\expect('get_theme_mod')->andReturnUsing(function (string $mod) {
            return '';
        });

        $this->expectOutputString('');
        $snippet->add_login_logo();
    }

    public function test_add_login_logo_falls_back_to_svg(): void {
        $snippet = new AdminLogo([]);
        Functions\expect('get_theme_mod')->andReturnUsing(function (string $mod) {
            return match ($mod) {
                'logo' => '',
                'logo_svg_url' => 'https://example.com/logo.svg',
                default => '',
            };
        });
        Functions\expect('esc_url')->andReturnFirstArg();

        $this->expectOutputRegex('/background-image:\s*url\(https:\/\/example\.com\/logo\.svg\)/');
        $snippet->add_login_logo();
    }
}
