# PressGang Snippets Agent Notes

Use `CLAUDE.md` as the canonical authoring guide. This file is a short index for agents that look for `AGENTS.md` first.

## Shared Snippets To Check First

Before adding a site-local snippet to a PressGang child theme, check `src/Snippets/` here and prefer a shared class when the behaviour is reusable.

Current commerce crawl/performance helpers:

- `PressGang\Snippets\Seo\RobotsTxt` — configurable virtual `robots.txt` output. Args: `allow`, `disallow`, `sitemap_url`, `user_agent`. Defaults only to WordPress' admin/admin-ajax rules; put all site-specific rules in theme config. A physical root `robots.txt` bypasses this.
- `PressGang\Snippets\WooCommerce\Cart\DecrawlAddToCartLinks` — removes crawlable product-loop `?add-to-cart=` hrefs and redirects direct GET/HEAD add-to-cart requests early.
- `PressGang\Snippets\WooCommerce\Cart\DisableEmptyCartFragments` — prevents empty-cart browsing views from localizing `wc-cart-fragments`, avoiding the cold-load fragments AJAX request.

Theme/menu utilities added 2026-07:

- `PressGang\Snippets\Theme\DisableBlockStyles` — dequeue block library/global styles on classic-editor themes. Args: optional `handles` list.
- `PressGang\Snippets\Theme\MenuActiveClasses` — active/current classes for custom-link menu items so Timber `MenuItem::current` works.
- `PressGang\Snippets\Theme\MenuItemClassMap` — map menu locations to custom Timber MenuItem classes. Args: `location => FQCN`.
- `PressGang\Snippets\Theme\RemoveGalleryStyle` — suppress default gallery inline CSS (redundant when html5 gallery support is declared).
- `PressGang\Snippets\Theme\RemoveDefaultPresets` — remove core default presets (palette, gradients, font sizes, spacing, shadows, aspect ratios) from the global styles CSS for whichever groups theme.json switches off via its `default*` flags. WordPress applies those flags to the editor UI only, so the CSS is printed regardless. Args: optional `presets` list to override the theme.json detection.
- `PressGang\Snippets\Content\AllowSvgUploads` — allow SVG uploads.
- `PressGang\Snippets\Content\RemoveTaxonomyUi` — hide a taxonomy's admin submenu/meta box. Args: `taxonomy`, `post_type`.

## Update Flow

1. Add or update the shared snippet in this package.
2. Add/adjust unit tests under `tests/Unit/Snippets/...`.
3. Run the targeted PHPUnit test, then commit and push `pressgang-snippets`.
4. In each consuming theme, run `composer update pressgang-wp/pressgang-snippets` and register the fully-qualified class in `config/snippets.php`.
5. Keep site-specific args in the theme config, not in the shared class.
