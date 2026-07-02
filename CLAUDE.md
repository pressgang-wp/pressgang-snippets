# PressGang Snippets — Authoring Guide

This is the **authoritative guide** for the PressGang Snippets library.
All new and modified snippets **must conform to these rules**.

---

## What This Repo Is

A Composer-installable library of **self-contained, reusable snippet classes** for PressGang WordPress themes. Each snippet is a small unit of functionality that a child theme can enable via `config/snippets.php`.

This repo is **not** part of the PressGang parent theme — it is a separate package installed via `composer require pressgang-wp/pressgang-snippets`.

---

## How Snippets Are Activated

A child theme lists snippets in `config/snippets.php`:

```php
return [
    'PressGang\\Snippets\\DisableEmojis' => [],
    'PressGang\\Snippets\\ImageSizes' => [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'hero'      => ['width' => 1920, 'height' => 600, 'crop' => true],
    ],
    'PressGang\\Snippets\\GoogleAnalytics' => [],
];
```

The PressGang `Loader` instantiates each class with `new $class($args)`.

---

## The SnippetInterface Contract

Every snippet **must** implement `PressGang\Snippets\SnippetInterface`:

```php
interface SnippetInterface {
    public function __construct(array $args);
}
```

This means:
- Constructor accepts `array $args` (may be empty).
- All hooks are registered in the constructor.
- No additional `boot()` or `init()` call is required by the theme.
- The snippet is fully operational after construction.

---

## Snippet Categories

Snippets fall into recurring patterns. Follow the established pattern for the category.

### Customizer + Render

Adds a Customizer setting, then renders output via a Twig template on a hook like `wp_head` or `wp_body_open`.

**Examples:** `GoogleAnalytics`, `GoogleTagManager`, `FacebookPixel`, `Hotjar`, `Tawkto`, `CookieYes`, `GoogleRecaptcha`

```php
public function __construct(array $args) {
    \add_action('customize_register', [$this, 'add_to_customizer']);
    \add_action('wp_head', [$this, 'script']);
}
```

**Conventions:**
- Group related settings under a shared Customizer section (e.g. `google`).
- Guard the section creation: `if (!isset($wp_customize->sections['google']))`.
- Always set `'sanitize_callback' => 'sanitize_text_field'` on text settings.
- Render via `Timber::render('snippets/<category>/<name>.twig', $context)`.
- Check the theme mod has a value before rendering — don't output empty script tags.

### Hook Filtering

Modifies WordPress behaviour via actions and filters. No UI, no templates.

**Examples:** `DisableEmojis`, `BigImageScaling`, `SearchExcludePostTypes`, `RemovePosts`, `AddQueryVars`

```php
public function __construct(array $args) {
    $this->exclude = $args['exclude'] ?? [];
    \add_filter('pre_get_posts', [$this, 'modify_query']);
}
```

**Conventions:**
- Store `$args` values in typed properties.
- Provide sensible defaults for all args.
- Document supported args in the constructor docblock.

### Shared Crawl And Performance Snippets

These snippets are intentionally shared across commerce themes and should be
reused before creating site-local equivalents:

| Class | Purpose |
|---|---|
| `PressGang\Snippets\Seo\RobotsTxt` | Own WordPress' virtual `robots.txt` via config. Supports `allow`, `disallow`, `sitemap_url`, and `user_agent` args. Defaults only to WordPress' admin/admin-ajax rules; keep site-specific rules in theme config. Remember that a physical web-root `robots.txt` bypasses WordPress. |
| `PressGang\Snippets\WooCommerce\Cart\DecrawlAddToCartLinks` | Remove crawlable `?add-to-cart=` hrefs from product loops and redirect direct GET/HEAD add-to-cart hits before WooCommerce creates cart/session work. |
| `PressGang\Snippets\WooCommerce\Cart\DisableEmptyCartFragments` | Suppress `wc-cart-fragments` localization on empty-cart browsing views so WooCommerce does not make a cold-load fragments AJAX request. |

When a theme needs similar behaviour, enable these with fully-qualified
`PressGang\Snippets\...` keys in `config/snippets.php`. If a change would help
more than one project, update this package first, push it, then update the
theme lockfiles to the new `pressgang-wp/pressgang-snippets` commit.

### Admin Features

Adds functionality to the WordPress admin — row actions, admin notices, editor customisation.

**Examples:** `DuplicatePost`, `AdminLogo`, `TinyMceBlockFormats`, `EditorStyles`

**Conventions:**
- Always check capabilities: `\current_user_can('edit_posts')`.
- Always verify nonces for state-changing actions.
- Use `\wp_safe_redirect()` after admin actions, never `\wp_redirect()`.

### Twig Function Registration

Registers a function into the Twig environment via the `timber/twig` filter.

**Examples:** `Breadcrumb`

```php
public function __construct(array $args) {
    \add_filter('timber/twig', [$this, 'add_to_twig']);
}

public function add_to_twig(Environment $twig): Environment {
    $twig->addFunction(new TwigFunction('breadcrumb', [$this, 'render']));
    return $twig;
}
```

### Config-Driven Registration

Receives structured args and registers WordPress resources (image sizes, rewrite rules, etc.).

**Examples:** `ImageSizes`, `Permalinks`, `AddPostTypeSupport`, `RegisterTaxonomyForPostType`

**Conventions:**
- The args array shape should mirror WordPress API conventions where possible.
- Support disabling items by passing `false` as the value.

### ACF Integration

Extends Advanced Custom Fields admin UI.

**Examples:** `AcfColorPickerThemeSync`, `AcfGoogleMaps`, `AcfWysiwygMin`

**Conventions:**
- Hook into ACF-specific actions (e.g. `acf/input/admin_footer`).
- Guard with `function_exists('acf_register_block_type')` or similar if the snippet can be loaded without ACF.

### WooCommerce Extensions

Modifies WooCommerce behaviour.

**Examples:** `WooCommerceRemoveUncategorized`, `WooCommerceDequeueStyles`, `WoocommerceAjaxCartCount`, `WooCommerce\Cart\DecrawlAddToCartLinks`, `WooCommerce\Cart\DisableEmptyCartFragments`

**Conventions:**
- Guard with `class_exists('WooCommerce')` if the snippet could be loaded on a non-WooCommerce site.

---

## Directory Layout

```
src/
├── Snippets/          # Active, autoloaded snippet classes (PressGang\Snippets\)
│   ├── Google/        # Google-related snippets (PressGang\Snippets\Google\)
│   ├── Seo/           # SEO-related snippets (PressGang\Snippets\Seo\)
│   ├── Theme/         # Theme utilities (PressGang\Snippets\Theme\)
│   ├── Content/       # Content/admin tweaks (PressGang\Snippets\Content\)
│   ├── Integration/   # Third-party integrations
│   ├── Facebook/      # Facebook/Meta snippets
│   ├── Acf/           # ACF integrations
│   └── WooCommerce/   # WooCommerce snippets
└── ToDo/              # Draft/incomplete snippets (NOT autoloaded)
views/
└── snippets/          # Twig templates used by snippets
    ├── seo/           # SEO-related templates
    │   └── json-ld/   # JSON-LD schema templates
    ├── google/        # Google templates
    ├── facebook/      # Facebook templates
    ├── integration/   # Integration templates
    └── theme/         # Theme templates
composer.json
```

- `src/Snippets/` is PSR-4 autoloaded as `PressGang\Snippets\`.
- `src/ToDo/` uses `PressGang\ToDo\` namespace and is **not** in the Composer autoload map. These are incomplete and should not be used.
- One class per file. File name matches class name.

---

## Twig Templates

Templates live in `views/snippets/` and are rendered via `Timber::render()`.

### Rules

- **Presentation only.** No database queries, no business logic, no mutations.
- **Auto-escaping is on.** Use `{{ value }}` for content, `{{ value|e('html_attr') }}` for attributes, `{{ value|e('esc_js') }}` for JavaScript contexts.
- **`|raw` only when sanitised in PHP** and explicitly intended to contain HTML.
- Template file names use kebab-case matching the snippet purpose (e.g. `google-analytics.twig`).
- Always pass context as a plain associative array from PHP.

### JSON-LD Templates

JSON-LD schema templates live under `views/snippets/seo/json-ld/` and extend
`snippets/seo/json-ld/script.twig`, which provides the
`<script type="application/ld+json">` wrapper with a `{% block schema %}`.

**Always build a schema array and output it with `|json_encode`.** Do not hand-roll JSON strings.

---

## Coding Standards

### PHP

- **PHP 8.3+** (match PressGang parent theme).
- Typed properties and return types everywhere.
- Short array syntax `[]`.
- Fully-qualified global WP function calls: `\add_action()`, `\get_theme_mod()`, `\is_admin()`.
- One class per file, PSR-4 naming.

### Naming

- Class names: PascalCase, descriptive and domain-oriented (`DisableEmojis`, `BigImageScaling`, `GoogleAnalytics`).
- Method names: snake_case, matching the hook name where applicable (`add_to_customizer` for `customize_register`).
- Customizer setting keys: kebab-case (`google-analytics-id`).

### Security

- **Sanitise input**: `sanitize_text_field()`, `sanitize_email()`, `\absint()`.
- **Escape output**: `\esc_html()`, `\esc_attr()`, `\esc_url()` in PHP; Twig auto-escaping in templates.
- **Capability checks** before admin actions: `\current_user_can()`.
- **Nonce verification** for state-changing operations: `\wp_verify_nonce()` / `\wp_nonce_url()`.
- Guard for missing dependencies (ACF, WooCommerce) before calling their APIs.

### i18n

All translatable strings use the `THEMENAME` constant as the text domain:

```php
__('Google Analytics ID', THEMENAME)
_x('Home', 'Breadcrumb', THEMENAME)
```

Do not hardcode text domains. Do not translate proper nouns.

---

## Doc Blocks (critical for third-party understanding)

Doc blocks in this repository are **not decorative**. They exist to help a developer who:

- Is unfamiliar with PressGang
- Is reasonably familiar with WordPress
- Has opened a single file in isolation

Assume the reader does **not** know:
- How snippets are instantiated
- Where configuration lives
- What lifecycle guarantees exist

A good doc block should explain **what the code does**, **why it exists**, and **how it is expected to be used**, without requiring the reader to search elsewhere in the framework.

### General principles

- Explain **intent and behaviour**, not just the method name.
- Be concise, but never cryptic.
- Prefer plain language over internal PressGang shorthand.
- Avoid references like "as per PressGang convention" — explain the behaviour instead.
- Write doc blocks as if the reader is seeing this file for the first time.

### Class doc blocks

Every snippet class **must** have a class-level doc block that answers:

- What problem does this snippet solve?
- What WordPress behaviour does it modify or introduce?
- When would a theme author choose to enable it?

Do **not** include `Class ClassName` headers or `@package` tags.

**Good:**

```php
/**
 * Removes WordPress emoji support — the detection script, print styles, TinyMCE
 * plugin, and DNS prefetch hint — to reduce page weight on sites that don't use emojis.
 *
 * Enable this snippet when you want to eliminate the ~15 KB emoji payload from
 * every page load. No configuration required.
 */
class DisableEmojis implements SnippetInterface {
```

```php
/**
 * Manages WordPress image sizes: updates default sizes (thumbnail, medium, large),
 * registers custom sizes, and can disable sizes by setting them to false.
 *
 * Configured via the $args array passed from config/snippets.php. Each key is a
 * size name and each value is either an array of {width, height, crop} or false
 * to disable the size.
 */
class ImageSizes implements SnippetInterface {
```

```php
/**
 * Adds a CSS class to the <body> element when the current page contains a hero block.
 *
 * This allows themes to adjust global layout or styling based on the presence
 * of a hero component, without duplicating logic across templates.
 */
class HasHeroBlock implements SnippetInterface {
```

**Bad:**

```php
/** Handles image sizes. */

/** Class ImageSizes @package PressGang\Snippets */
```

### Constructor doc blocks

The constructor is the most important method to document. It should explain:

- What hooks are registered and why.
- What `$args` keys are supported, with types and defaults.
- Any preconditions or dependencies (e.g. "requires ACF to be active").

```php
/**
 * Registers Customizer controls for the Google Analytics tracking ID and a
 * "track logged-in users" toggle, and hooks the tracking script into wp_head.
 *
 * The tracking ID is entered via the Customizer — no $args configuration needed.
 *
 * @param array $args Unused; required by SnippetInterface.
 */
```

When `$args` carries configuration, document each key:

```php
/**
 * Registers image size adjustments on the 'init' hook.
 *
 * @param array $args Associative array of image size definitions. Each key is
 *     a size name (string) and each value is one of:
 *     - array{width: int, height: int, crop?: bool} to set/add the size
 *     - false to disable/remove the size
 */
```

### Method doc blocks

Always include `@param` and `@return`. The description should explain **what the method does in context**, not just restate the method name.

**Good:**

```php
/**
 * Removes the emoji CDN hostname (s.w.org) from the list of DNS prefetch hints
 * that WordPress injects into <head>. Only modifies 'dns-prefetch' hints.
 *
 * @param array  $urls          URLs WordPress will print as resource hints.
 * @param string $relation_type The hint type ('dns-prefetch', 'preconnect', etc.).
 *
 * @return array The filtered URL list with the emoji CDN removed.
 */
```

**Bad:**

```php
/**
 * Filters DNS prefetch.
 *
 * @param array  $urls          Urls.
 * @param string $relation_type Type.
 *
 * @return array
 */
```

### Inline comments

Use sparingly. Prefer them for:
- Non-obvious WordPress API behaviour ("WordPress returns false, not null, when no field objects exist")
- Security rationale ("Nonce verified above; safe to proceed with post duplication")
- Intentional deviations ("Using wp_redirect() here because the target is an external URL")

---

## Authoring a New Snippet

### 1. Choose the right category

Refer to the Snippet Categories section above. Follow the established pattern.

### 2. Create the class

```php
<?php

namespace PressGang\Snippets;

/**
 * One-sentence description of what this snippet does.
 */
class MySnippet implements SnippetInterface {

    public function __construct(array $args) {
        // Register hooks here — nothing else
    }
}
```

### 3. Add a Twig template (if rendering output)

Place it in `views/snippets/<category>/<snippet-name>.twig`. Keep it presentational.

### 4. Customizer sections

If adding a Customizer control, reuse an existing section where it fits:
- `google` — Google services (Analytics, Tag Manager, Webmaster, Ads, reCAPTCHA)
- `facebook` — Facebook services (Pixel, Verify)
- `social-media` — General social integrations

Only create a new section if none of the above apply.

### 5. Test activation

Verify the snippet works when added to `config/snippets.php` with:
- Empty args: `'PressGang\\Snippets\\MySnippet' => []`
- Typical args
- Missing optional args (defaults should apply)

---

## Snippet Checklist

A new or modified snippet should satisfy:

- [ ] Implements `SnippetInterface`
- [ ] Constructor accepts `array $args` and documents supported keys
- [ ] All hooks registered in the constructor
- [ ] Does one thing — not a bucket of unrelated tweaks
- [ ] Defensive guards for context and missing dependencies
- [ ] No heavy queries in global scope or early hooks
- [ ] Output escaped; input sanitised
- [ ] Capability checks and nonces for any state changes
- [ ] Doc blocks: concise class description, `@param`/`@return` on methods
- [ ] Fully-qualified WP function calls (`\add_action()`, not `add_action()`)
- [ ] No hard dependency on specific child themes
- [ ] Twig template (if any) is presentational only with auto-escaping

---

## Known Issues

### `src/ToDo/` directory
Currently empty. Legacy items that originated in `src/ToDo/` are tracked in `LEGACY.md` for review.

---

## Working in This Repo

1. **Prefer minimal, mechanical changes** over rewrites.
2. **Preserve public APIs and config shapes** unless explicitly updating them.
3. **Keep diffs easy to review** — one concern per commit, no unrelated formatting churn.
4. If introducing a new snippet, include a usage example in the README.
5. **Do not move or rename** existing snippets without a major version bump — child themes depend on class names.
6. When unsure between magic and explicit, choose explicit.
