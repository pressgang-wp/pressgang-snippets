<?php
namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;
use Brain\Monkey\Filters;
use PressGang\Snippets\Theme\BigImageScaling;
use PressGang\Tests\Snippets\Unit\TestCase;

class BigImageScalingTest extends TestCase {
    public function test_constructor_registers_filter(): void {
        Filters\expectAdded('big_image_size_threshold')->once();
        new BigImageScaling([]);
    }

    public function test_returns_configured_width(): void {
        $snippet = new BigImageScaling(['image_max_width' => 3200]);
        $this->assertSame(3200, $snippet->big_image_size_threshold());
    }

    public function test_returns_default_width_when_not_configured(): void {
        $snippet = new BigImageScaling([]);
        $this->assertSame(2880, $snippet->big_image_size_threshold());
    }
}
