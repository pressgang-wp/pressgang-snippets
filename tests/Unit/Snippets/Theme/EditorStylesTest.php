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
}
