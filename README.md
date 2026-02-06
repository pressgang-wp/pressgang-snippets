# PressGang Snippets

**Stop copy-pasting from `functions.php`. Start composing.**

PressGang Snippets is a library of 45+ self-contained, Composer-installable WordPress theme components for [PressGang](https://github.com/pressgang-wp/pressgang) themes. Each snippet is a single PHP class that does one thing well — from injecting Google Analytics to disabling emojis to generating breadcrumbs — and can be enabled or disabled with a single line of config.

## 🤔 Why Snippets?

Every WordPress developer has done this: you need Google Analytics on a new site, so you open `functions.php`, paste in 30 lines you copied from the last project, tweak a couple of values, and move on. Six months later that file is 800 lines of unrelated code that nobody wants to touch.

**Snippets fix this.** Instead of a monolithic `functions.php`, each piece of functionality lives in its own class:

```php
// config/snippets.php — your entire "functions.php" replacement
return [
    'DisableEmojis'    => [],
    'GoogleAnalytics'  => [],
    'ImageSizes'       => [
        'hero' => ['width' => 1920, 'height' => 600, 'crop' => true],
    ],
    'DuplicatePost'    => [],
    'OpenGraph'        => [],
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
    'DisableEmojis' => [],

    // Pass configuration via the args array
    'ImageSizes' => [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'medium'    => ['width' => 600, 'height' => 600, 'crop' => false],
        'hero'      => ['width' => 1920, 'height' => 600, 'crop' => true],
    ],

    // Customizer-based snippets — the user enters values in the WP Customizer
    'GoogleAnalytics' => [],
    'GoogleTagManager' => [],

    // Fully qualified class names work too
    'PressGang\\Snippets\\Breadcrumb' => [],

    // Or your own child theme snippets
    'MyTheme\\Snippets\\CustomFeature' => ['enabled' => true],
];
```

**Namespace resolution:** PressGang checks your child theme namespace first (`YourTheme\Snippets\SnippetName`), then falls back to `PressGang\Snippets\SnippetName`. Use short names for convenience, or fully qualified names for clarity.

## 📖 Available Snippets

### 📊 Analytics & Tracking

| Snippet | Description |
|---|---|
| `GoogleAnalytics` | Google Analytics (gtag.js) with Customizer controls and logged-in user toggle |
| `GoogleTagManager` | Google Tag Manager container — injects into `<head>` and `<body>` |
| `GoogleAdsTag` | Google Ads global site tag |
| `GoogleConversionTracking` | Google Ads conversion tracking pixel |
| `GoogleAnalyticsWoocommerce` | GA e-commerce event tracking for WooCommerce |
| `FacebookPixel` | Meta (Facebook) Pixel |
| `Pinterest` | Pinterest Tag |
| `Hotjar` | Hotjar behaviour analytics |
| `Tawkto` | Tawk.to live chat widget |
| `CookieYes` | CookieYes consent management |
| `Trustpilot` | Trustpilot review widget |

### 🔍 SEO & Meta

| Snippet | Description |
|---|---|
| `OpenGraph` | Open Graph meta tags for social sharing (title, description, image) |
| `SeoTitle` | SEO-friendly `<title>` tag management |
| `Sitemap` | XML sitemap generation with support for CPTs, taxonomies, and WPML |
| `Breadcrumb` | Breadcrumb navigation — registers a `{{ breadcrumb() }}` Twig function |
| `GoogleWebmaster` | Google Search Console verification meta tag |
| `FacebookVerify` | Facebook domain verification meta tag |

### 🖼️ Media & Assets

| Snippet | Description |
|---|---|
| `ImageSizes` | Configure, add, and disable WordPress image sizes |
| `BigImageScaling` | Set the threshold for WordPress big image scaling |
| `Logo` | Logo image Customizer control |
| `LogoSvg` | SVG logo support via Customizer |
| `EditorStyles` | Add editor stylesheet support |

### ⚡ Performance

| Snippet | Description |
|---|---|
| `DisableEmojis` | Remove WordPress emoji scripts, styles, and DNS prefetch (~15 KB saved) |
| `RemoveOembedAuthor` | Strip author info from oEmbed responses |

### 🛠️ Admin & Editor

| Snippet | Description |
|---|---|
| `DuplicatePost` | "Duplicate" link on post/page rows — clones content, meta, and taxonomies |
| `AdminLogo` | Custom logo on the WordPress login page |
| `TinyMceBlockFormats` | Customise TinyMCE block format dropdown |
| `RemovePosts` | Remove the default "Posts" menu from the admin |
| `PostTypeMenuHighlighter` | Fix admin menu highlighting for custom post types |
| `Copyright` | Copyright notice Customizer control |
| `ArchiveTitles` | Customiser controls for archive page titles |

### 🔧 Theme Utilities

| Snippet | Description |
|---|---|
| `Permalinks` | Custom rewrite rules for CSS, JS, images, and fonts |
| `AddQueryVars` | Register custom query variables |
| `SearchExcludePostTypes` | Exclude specific post types from search results |
| `PasswordProtection` | Custom template for password-protected posts |
| `GoogleRecaptcha` | Google reCAPTCHA site + secret key management |

### 🎨 ACF Integration

| Snippet | Description |
|---|---|
| `AcfColorPickerThemeSync` | Sync theme.json colour palette to ACF colour picker |
| `AcfGoogleMaps` | Google Maps API key for ACF map fields |
| `AcfWysiwygMin` | Minimal toolbar for ACF WYSIWYG fields |

### 🛒 WooCommerce

| Snippet | Description |
|---|---|
| `WoocommerceAjaxCartCount` | AJAX-powered cart count updates |
| `WoocommerceBackorders` | Enable backorder support |
| `WooCommerceDequeueStyles` | Remove default WooCommerce stylesheets |
| `WooCommerceDequeueSelectWoo` | Remove the SelectWoo library |
| `WooCommerceRemoveUncategorized` | Hide the default "Uncategorized" product category |
| `WooCommerceRemoveDownloads` | Remove the "Downloads" endpoint from My Account |

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

## 📋 Requirements

- **PHP** 8.3+
- **WordPress** 6.4+
- **Timber** 2.0+
- **PressGang** parent theme

## 📄 License

MIT — use it, fork it, ship it.
