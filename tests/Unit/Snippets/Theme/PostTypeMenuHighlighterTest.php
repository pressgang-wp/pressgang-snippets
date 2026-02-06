<?php
namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\PostTypeMenuHighlighter;
use PressGang\Tests\Snippets\Unit\TestCase;

class PostTypeMenuHighlighterTest extends TestCase {
    public function test_constructor_registers_filter(): void {
        Filters\expectAdded('nav_menu_css_class')->once();
        new PostTypeMenuHighlighter([]);
    }

    public function test_removes_managed_classes(): void {
        $snippet = new PostTypeMenuHighlighter([]);

        Functions\expect('get_post_type')->andReturn('post');
        Functions\expect('is_singular')->andReturn(false);
        Functions\expect('is_post_type_archive')->andReturn(false);

        $item = \Mockery::mock('\WP_Post');
        $item->url = 'https://example.com/blog/';

        $classes = ['menu-item', 'current_page_parent', 'current_page_item'];
        $result = $snippet->custom_menu_classes($classes, $item);

        $this->assertNotContains('current_page_parent', $result);
        $this->assertNotContains('current_page_item', $result);
        $this->assertContains('menu-item', $result);
    }

    public function test_adds_current_page_parent_on_singular_view(): void {
        $snippet = new PostTypeMenuHighlighter([]);

        $wp = new \stdClass();
        $wp->request = 'projects/my-project';
        $GLOBALS['wp'] = $wp;

        Functions\expect('get_post_type')->andReturn('project');
        Functions\expect('is_singular')->with('project')->andReturn(true);
        Functions\expect('home_url')->with('projects/my-project')->andReturn('https://example.com/projects/my-project');

        $item = \Mockery::mock('\WP_Post');
        $item->url = 'https://example.com/projects/';

        $result = $snippet->custom_menu_classes(['menu-item'], $item);

        $this->assertContains('current_page_parent', $result);
    }

    public function test_adds_current_page_item_on_archive_view(): void {
        $snippet = new PostTypeMenuHighlighter([]);

        Functions\expect('get_post_type')->andReturn('project');
        Functions\expect('is_singular')->with('project')->andReturn(false);
        Functions\expect('is_post_type_archive')->with('project')->andReturn(true);
        Functions\expect('get_post_type_archive_link')->with('project')->andReturn('https://example.com/projects/');

        $item = \Mockery::mock('\WP_Post');
        $item->url = 'https://example.com/projects/';

        $result = $snippet->custom_menu_classes(['menu-item'], $item);

        $this->assertContains('current_page_item', $result);
    }
}
