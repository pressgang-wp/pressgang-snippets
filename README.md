# PressGang Snippets

**Stop copy-pasting from `functions.php`. Start composing.**

PressGang Snippets is a library of 45+ self-contained, Composer-installable WordPress theme components for [PressGang](https://github.com/pressgang-wp/pressgang) themes. Each snippet is a single PHP class that does one thing well — from injecting Google Analytics to disabling emojis to generating breadcrumbs — and can be enabled or disabled with a single line of config.

## 🤔 Why Snippets?

Every WordPress developer has done this: you need Google Analytics on a new site, so you open `functions.php`, paste in 30 lines you copied from the last project, tweak a couple of values, and move on. Six months later that file is 800 lines of unrelated code that nobody wants to touch.

**Snippets fix this.** Instead of a monolithic `functions.php`, each piece of functionality lives in its own class:

```php
// config/snippets.php — your entire "functions.php" replacement
return [
    'PressGang\\Snippets\\Theme\\DisableEmojis'    => [],
    'PressGang\\Snippets\\Google\\Analytics'      => [],
    'PressGang\\Snippets\\Theme\\ImageSizes'       => [
        'hero' => ['width' => 1920, 'height' => 600, 'crop' => true],
    ],
    'PressGang\\Snippets\\Content\\DuplicatePost'  => [],
    'PressGang\\Snippets\\Seo\\OpenGraph'          => [],
];
```

That's it. Five features enabled, zero boilerplate, fully reversible by deleting a line.

| Traditional approach | PressGang Snippets |
|---|---|
| Copy-paste between projects | `composer require` once, use everywhere |
| Enable/disable by commenting code | Add or remove a config line |
| One massive `functions.php` | One focused class per concern |
| Arguments buried in code | Configuration passed explicitly |
| No standard structure | Every snippet implements `SnippetInterface` |

## 📦 Installation

```bash
composer require pressgang-wp/pressgang-snippets
```

That's all. PressGang automatically discovers the package's Twig templates — no path configuration needed.

## ⚙️ Configuration

Activate snippets in your child theme's `config/snippets.php`. Each entry maps a class name to an arguments array:

```php
<?php

return [
    // No args needed — just enable it
    'PressGang\\Snippets\\Theme\\DisableEmojis' => [],

    // Pass configuration via the args array
    'PressGang\\Snippets\\Theme\\ImageSizes' => [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'medium'    => ['width' => 600, 'height' => 600, 'crop' => false],
        'hero'      => ['width' => 1920, 'height' => 600, 'crop' => true],
    ],

    // Customizer-based snippets — the user enters values in the WP Customizer
    'PressGang\\Snippets\\Google\\Analytics'  => [],
    'PressGang\\Snippets\\Google\\TagManager' => [],

    // Parameterised behaviour snippets
    'PressGang\\Snippets\\Content\\RemoveTaxonomyUi' => ['taxonomy' => 'post_tag', 'post_type' => 'post'],
    'PressGang\\Snippets\\Theme\\MenuItemClassMap'   => ['primary' => \MyTheme\Models\MainMenuItem::class],

    // Or your own child theme snippets
    'MyTheme\\Snippets\\CustomFeature' => ['enabled' => true],
];
```

**Namespace resolution:** Snippets are grouped by category namespace (e.g. `PressGang\\Snippets\\Google\\Analytics`). Use fully qualified class names for clarity.

## 📖 Available Snippets

### 📊 Analytics & Tracking

| Snippet | Description |
|---|---|
| `Google\\Analytics` | Google Analytics (gtag.js) with Customizer controls and logged-in user toggle |
| `Google\\TagManager` | Google Tag Manager container — injects into `<head>` and `<body>` |
| `Google\\AdsTag` | Google Ads global site tag |
| `Google\\ConversionTracking` | Google Ads conversion tracking pixel |
| `Google\\AnalyticsWoocommerce` | GA e-commerce event tracking for WooCommerce |
| `Facebook\\Pixel` | Meta (Facebook) Pixel |
| `Integration\\Pinterest` | Pinterest Tag |
| `Integration\\Hotjar` | Hotjar behaviour analytics |
| `Integration\\Tawkto` | Tawk.to live chat widget |
| `Integration\\CookieYes` | CookieYes consent management |
| `Integration\\Trustpilot` | Trustpilot review widget |
| `Integration\\DisableCf7Assets` | Disable Contact Form 7 JS and CSS on the frontend |

### 🔍 SEO & Meta

| Snippet | Description |
|---|---|
| `Seo\\OpenGraph` | Open Graph meta tags for social sharing (title, description, image) |
| `Seo\\RobotsTxt` | Deploy-managed virtual `robots.txt` renderer with generic WordPress defaults; theme config can override allow/disallow/sitemap rules |
| `Seo\\Title` | SEO-friendly `<title>` tag management |
| `Seo\\Sitemap` | XML sitemap generation with support for CPTs, taxonomies, and WPML |
| `Theme\\Breadcrumb` | Breadcrumb navigation — registers a `{{ breadcrumb() }}` Twig function |
| `Google\\Webmaster` | Google Search Console verification meta tag |
| `Facebook\\Verify` | Facebook domain verification meta tag |

### 🖼️ Media & Assets

| Snippet | Description |
|---|---|
| `Theme\\ImageSizes` | Configure, add, and disable WordPress image sizes |
| `Theme\\BigImageScaling` | Set the threshold for WordPress big image scaling |
| `Theme\\Logo` | Logo image Customizer control |
| `Content\\AllowSvgUploads` | Allow SVG files in the media library |
| `Theme\\RemoveGalleryStyle` | Suppress the default gallery inline CSS (for themes without html5 gallery support) |
| `Theme\\LogoSvg` | SVG logo support via Customizer |
| `Theme\\EditorStyles` | Add editor stylesheet support |

### ⚡ Performance

| Snippet | Description |
|---|---|
| `Theme\\DisableEmojis` | Remove WordPress emoji scripts, styles, and DNS prefetch (~15 KB saved) |
| `Theme\\DisableBlockStyles` | Dequeue block library and global styles on non-block themes |
| `Content\\RemoveOembedAuthor` | Strip author info from oEmbed responses |
| `WooCommerce\\Cart\\DisableEmptyCartFragments` | Keep WooCommerce cart fragments inert for anonymous/empty-cart browsing views |
| `WooCommerce\\Cart\\DecrawlAddToCartLinks` | Replace crawlable loop add-to-cart hrefs and redirect direct GET add-to-cart hits |

### 🛠️ Admin & Editor

| Snippet | Description |
|---|---|
| `Content\\DuplicatePost` | "Duplicate" link on post/page rows — clones content, meta, and taxonomies |
| `Theme\\AdminLogo` | Custom logo on the WordPress login page |
| `Theme\\TinyMceBlockFormats` | Customise TinyMCE block format dropdown |
| `Content\\RemovePosts` | Remove the default "Posts" menu from the admin |
| `Theme\\PostTypeMenuHighlighter` | Fix admin menu highlighting for custom post types |
| `Content\\RemoveTaxonomyUi` | Hide a taxonomy's admin submenu and meta box (default: post tags) |
| `Theme\\Copyright` | Copyright notice Customizer control |
| `Theme\\ArchiveTitles` | Customiser controls for archive page titles |

### 🔧 Theme Utilities

| Snippet | Description |
|---|---|
| `Theme\\Permalinks` | Custom rewrite rules for CSS, JS, images, and fonts |
| `Theme\\AddQueryVars` | Register custom query variables |
| `Theme\\MenuActiveClasses` | Add active/current classes to custom-link menu items (Timber MenuItem::current) |
| `Theme\\MenuItemClassMap` | Register custom Timber MenuItem classes per menu location |
| `Content\\SearchExcludePostTypes` | Exclude specific post types from search results |
| `Content\\PasswordProtection` | Custom template for password-protected posts |
| `Content\\AddPostTypeSupport` | Add a feature (excerpt, thumbnail, etc.) to an existing post type |
| `Content\\RegisterTaxonomyForPostType` | Associate an existing taxonomy with a post type |
| `Google\\Recaptcha` | Google reCAPTCHA site + secret key management |

### 🎨 ACF Integration

| Snippet | Description |
|---|---|
| `Acf\\ColorPickerThemeSync` | Sync theme.json colour palette to ACF colour picker |
| `Acf\\GoogleMaps` | Google Maps API key for ACF map fields |
| `Acf\\WysiwygMin` | Minimal toolbar for ACF WYSIWYG fields |

### 🛒 WooCommerce

| Snippet | Description |
|---|---|
| `WooCommerce\\AjaxCartCount` | AJAX-powered cart count updates |
| `WooCommerce\\Backorders` | Enable backorder support |
| `WooCommerce\\DequeueStyles` | Remove default WooCommerce stylesheets |
| `WooCommerce\\DequeueSelectWoo` | Remove the SelectWoo library |
| `WooCommerce\\Cart\\DecrawlAddToCartLinks` | Product-loop add-to-cart crawl hygiene and GET request redirects |
| `WooCommerce\\Cart\\DisableEmptyCartFragments` | Disable empty-cart `wc-cart-fragments` AJAX cold loads |
| `WooCommerce\\RemoveUncategorized` | Hide the default "Uncategorized" product category |
| `WooCommerce\\RemoveDownloads` | Remove the "Downloads" endpoint from My Account |

## ✍️ Writing Your Own Snippets

The real power of the snippet pattern is that **your own code follows the same conventions**. Instead of adding custom functionality to `functions.php`, create a snippet class in your child theme:

```php
// src/Snippets/StaffDirectory.php
namespace MyTheme\Snippets;

use PressGang\Snippets\SnippetInterface;

/**
 * Registers a "Staff" custom post type with a configurable slug and enables
 * the archive page. Designed for company/team directory pages.
 */
class StaffDirectory implements SnippetInterface {

    public function __construct(array $args) {
        $this->slug = $args['slug'] ?? 'staff';
        \add_action('init', [$this, 'register']);
    }

    public function register(): void {
        \register_post_type('staff', [
            'label'       => \__('Staff', THEMENAME),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => ['slug' => $this->slug],
            'supports'    => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
        ]);
    }
}
```

Then enable it with one line:

```php
// config/snippets.php
return [
    'StaffDirectory' => ['slug' => 'team'],
];
```

Every custom feature you build this way is:
- **Discoverable** — one class, one file, one purpose
- **Configurable** — args passed from config, not hardcoded
- **Portable** — move it to another project or extract it into a Composer package
- **Removable** — delete one config line, feature gone

## 🏗️ How It Works Under the Hood

1. PressGang reads `config/snippets.php` during boot
2. Each class name is resolved (child theme namespace first, then `PressGang\Snippets\`)
3. The class is instantiated with `new $class($args)`
4. The constructor registers WordPress hooks — `add_action`, `add_filter`, etc.
5. WordPress fires those hooks at the appropriate time during the request lifecycle

Snippets never need a second method call to "activate". Construction **is** activation.

## 🧪 Testing

The library uses **PHPUnit 9.6** with **BrainMonkey** (via [yoast/wp-test-utils](https://github.com/Yoast/wp-test-utils)) for unit testing — the same stack as the PressGang parent theme. Tests mock WordPress functions without a running WordPress installation.

### Running tests

```bash
composer test              # run the full unit suite
composer test:unit         # same as above

# Single class or test
vendor/bin/phpunit --filter DisableEmojisTest
vendor/bin/phpunit --filter test_script_skips_rendering_when_tracking_id_empty
```

### Writing tests

Tests live in `tests/Unit/` and mirror the `src/Snippets/` directory structure:

```
tests/
├── bootstrap.php
├── stubs/
│   └── SnippetInterface.php
└── Unit/
    ├── TestCase.php
    └── Snippets/
        ├── Content/
        │   └── DuplicatePostTest.php
        ├── Google/
        │   └── AnalyticsTest.php
        ├── Theme/
        │   ├── BreadcrumbTest.php
        │   ├── DisableEmojisTest.php
        │   └── ImageSizesTest.php
        └── WooCommerce/
            └── DequeueStylesTest.php
```

New test classes should extend `PressGang\Tests\Snippets\Unit\TestCase` and use `Brain\Monkey\Functions\expect()` to mock WordPress functions. For snippets that call `Timber::render()`, extract the call to a protected `render_template()` method and override it in a named test subclass (see `AnalyticsTest` for the pattern).

## 📋 Requirements

- **PHP** 8.3+
- **WordPress** 6.4+
- **Timber** 2.0+
- **PressGang** parent theme

## 📄 License

MIT — use it, fork it, ship it.
