<?php
namespace PressGang\Tests\Snippets\Unit\Snippets\Theme;
use Brain\Monkey\Filters;
use PressGang\Snippets\Theme\TinyMceBlockFormats;
use PressGang\Tests\Snippets\Unit\TestCase;

class TinyMceBlockFormatsTest extends TestCase {
    public function test_constructor_registers_filter(): void {
        Filters\expectAdded('tiny_mce_before_init')->once();
        new TinyMceBlockFormats([]);
    }

    public function test_customize_block_formats_builds_format_string(): void {
        $snippet = new TinyMceBlockFormats([]);
        $result = $snippet->customize_block_formats([]);
        $this->assertArrayHasKey('block_formats', $result);
        $this->assertStringContainsString('Paragraph=p', $result['block_formats']);
        $this->assertStringContainsString('Heading 2=h2', $result['block_formats']);
        $this->assertStringContainsString('Heading 3=h3', $result['block_formats']);
        $this->assertStringContainsString('Heading 4=h4', $result['block_formats']);
    }

    public function test_custom_args_override_defaults(): void {
        $snippet = new TinyMceBlockFormats(['Heading 5' => 'h5']);
        $result = $snippet->customize_block_formats([]);
        $this->assertStringContainsString('Heading 5=h5', $result['block_formats']);
        $this->assertStringContainsString('Paragraph=p', $result['block_formats']);
    }

    public function test_preserves_existing_tinymce_settings(): void {
        $snippet = new TinyMceBlockFormats([]);
        $result = $snippet->customize_block_formats(['toolbar1' => 'bold italic']);
        $this->assertSame('bold italic', $result['toolbar1']);
        $this->assertArrayHasKey('block_formats', $result);
    }
}
