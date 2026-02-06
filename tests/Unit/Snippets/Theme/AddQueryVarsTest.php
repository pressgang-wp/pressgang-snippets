<?php
namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\AddQueryVars;
use PressGang\Tests\Snippets\Unit\TestCase;

class AddQueryVarsTest extends TestCase {
    public function test_constructor_registers_filter(): void {
        Filters\expectAdded('query_vars')->once();
        new AddQueryVars(['colour', 'size']);
    }

    public function test_add_query_vars_merges_custom_vars(): void {
        $snippet = new AddQueryVars(['colour', 'size']);
        $result = $snippet->add_query_vars(['s', 'p']);
        $this->assertSame(['s', 'p', 'colour', 'size'], $result);
    }

    public function test_add_query_vars_with_empty_args(): void {
        $snippet = new AddQueryVars([]);
        $result = $snippet->add_query_vars(['s', 'p']);
        $this->assertSame(['s', 'p'], $result);
    }
}
