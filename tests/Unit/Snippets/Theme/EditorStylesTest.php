<?php
namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use PressGang\Snippets\Theme\EditorStyles;
use PressGang\Tests\Snippets\Unit\TestCase;

class EditorStylesTest extends TestCase {
    public function test_constructor_registers_admin_init_hook(): void {
        Actions\expectAdded('admin_init')->once();
        new EditorStyles([]);
    }

    public function test_add_editor_styles_with_default_path(): void {
        $snippet = new EditorStyles([]);
        Functions\expect('add_theme_support')->once()->with('editor-styles');
        Functions\expect('add_editor_style')->once()->with('/css/editor-styles.css');
        $snippet->add_editor_styles();
    }

    public function test_add_editor_styles_with_custom_path(): void {
        $snippet = new EditorStyles(['path' => '/css/custom-editor.css']);
        Functions\expect('add_theme_support')->once()->with('editor-styles');
        Functions\expect('add_editor_style')->once()->with('/css/custom-editor.css');
        $snippet->add_editor_styles();
    }

    public function test_block_editor_only_registers_enqueue_block_assets_hook(): void {
        Actions\expectAdded('admin_init')->never();
        Actions\expectAdded('enqueue_block_assets')->once();
        new EditorStyles(['block_editor_only' => true]);
    }

    public function test_default_registers_admin_init_not_enqueue_block_assets(): void {
        Actions\expectAdded('admin_init')->once();
        Actions\expectAdded('enqueue_block_assets')->never();
        new EditorStyles([]);
    }

    public function test_empty_path_registers_no_hooks(): void {
        Actions\expectAdded('admin_init')->never();
        Actions\expectAdded('enqueue_block_assets')->never();
        new EditorStyles(['path' => '']);
    }

    public function test_enqueue_block_editor_styles_skips_frontend(): void {
        $snippet = new EditorStyles(['block_editor_only' => true]);

        Functions\expect('is_admin')->once()->andReturn(false);
        Functions\expect('wp_enqueue_style')->never();

        $snippet->enqueue_block_editor_styles();
    }

    public function test_enqueue_block_editor_styles_loads_in_admin(): void {
        $tmp_dir = sys_get_temp_dir() . '/pressgang-test-' . uniqid();
        mkdir($tmp_dir . '/css', 0777, true);
        file_put_contents($tmp_dir . '/css/editor-styles.css', 'body{}');

        $snippet = new EditorStyles(['block_editor_only' => true]);

        Functions\expect('is_admin')->once()->andReturn(true);
        Functions\expect('get_stylesheet_directory')->once()->andReturn($tmp_dir);
        Functions\expect('get_stylesheet_directory_uri')->once()->andReturn('https://example.com/theme');
        Functions\expect('wp_enqueue_style')->once()->with(
            'theme-editor-styles',
            'https://example.com/theme/css/editor-styles.css',
            [],
            \Mockery::type('string')
        );

        $snippet->enqueue_block_editor_styles();

        unlink($tmp_dir . '/css/editor-styles.css');
        rmdir($tmp_dir . '/css');
        rmdir($tmp_dir);
    }

    public function test_enqueue_block_editor_styles_skips_missing_file(): void {
        $tmp_dir = sys_get_temp_dir() . '/pressgang-test-' . uniqid();
        mkdir($tmp_dir, 0777, true);

        $snippet = new EditorStyles(['block_editor_only' => true]);

        Functions\expect('is_admin')->once()->andReturn(true);
        Functions\expect('get_stylesheet_directory')->once()->andReturn($tmp_dir);
        Functions\expect('wp_enqueue_style')->never();

        $snippet->enqueue_block_editor_styles();

        rmdir($tmp_dir);
    }
}
