<?php
namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;
use Brain\Monkey\Actions;
use PressGang\Snippets\Theme\Copyright;
use PressGang\Tests\Snippets\Unit\TestCase;

class CopyrightTest extends TestCase {
    public function test_constructor_registers_customizer_hook(): void {
        Actions\expectAdded('customize_register')->once();
        new Copyright([]);
    }
}
