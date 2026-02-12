<?php

/**
 * PHPUnit bootstrap for PressGang Snippets unit tests.
 *
 * Loads the Composer autoloader and defines constants that snippet classes
 * expect at runtime (normally set by WordPress / the parent theme).
 *
 * The SnippetInterface stub is loaded before the Composer autoloader maps
 * PressGang\Snippets\, so the interface is available without pulling in the
 * entire parent theme package.
 */

// Load the SnippetInterface stub before the Composer autoloader, so snippet
// classes can resolve `use PressGang\Snippets\SnippetInterface` without the
// parent theme installed.
require_once __DIR__ . '/stubs/SnippetInterface.php';
require_once __DIR__ . '/stubs/WooCommerce.php';
require_once __DIR__ . '/stubs/WP_Query.php';
require_once __DIR__ . '/stubs/WP_Post.php';
require_once __DIR__ . '/stubs/WP_Term.php';

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! defined( 'THEMENAME' ) ) {
	define( 'THEMENAME', 'pressgang-snippets' );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

// WordPress time constants used by snippet code.
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}
