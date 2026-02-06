<?php

namespace PressGang\Tests\Snippets\Unit;

use Yoast\WPTestUtils\BrainMonkey\YoastTestCase;

/**
 * Base test case for PressGang Snippets unit tests.
 *
 * Extends YoastTestCase which sets up BrainMonkey and pre-stubs common
 * WordPress functions (wp_parse_args, esc_html__, etc.).
 */
abstract class TestCase extends YoastTestCase {
}
