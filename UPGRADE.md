# Upgrade Guide: v1 to v2

## Breaking Change: Namespace Restructure

All snippet classes have been moved from the flat `PressGang\Snippets\` namespace into category-based sub-namespaces. Twig templates have been reorganised to match.

Update your `config/snippets.php` references using the mapping below.

---

## PHP Class Migration

| Old FQCN | New FQCN |
|---|---|
| `PressGang\Snippets\AcfColorPickerThemeSync` | `PressGang\Snippets\Acf\ColorPickerThemeSync` |
| `PressGang\Snippets\AcfGoogleMaps` | `PressGang\Snippets\Acf\GoogleMaps` |
| `PressGang\Snippets\AcfWysiwygMin` | `PressGang\Snippets\Acf\WysiwygMin` |
| `PressGang\Snippets\DuplicatePost` | `PressGang\Snippets\Content\DuplicatePost` |
| `PressGang\Snippets\PasswordProtection` | `PressGang\Snippets\Content\PasswordProtection` |
| `PressGang\Snippets\RemoveOembedAuthor` | `PressGang\Snippets\Content\RemoveOembedAuthor` |
| `PressGang\Snippets\RemovePosts` | `PressGang\Snippets\Content\RemovePosts` |
| `PressGang\Snippets\SearchExcludePostTypes` | `PressGang\Snippets\Content\SearchExcludePostTypes` |
| `PressGang\Snippets\FacebookPixel` | `PressGang\Snippets\Facebook\Pixel` |
| `PressGang\Snippets\FacebookVerify` | `PressGang\Snippets\Facebook\Verify` |
| `PressGang\Snippets\GoogleAdsTag` | `PressGang\Snippets\Google\AdsTag` |
| `PressGang\Snippets\GoogleAnalytics` | `PressGang\Snippets\Google\Analytics` |
| `PressGang\Snippets\GoogleAnalyticsWoocommerce` | `PressGang\Snippets\Google\AnalyticsWoocommerce` |
| `PressGang\Snippets\GoogleConversionTracking` | `PressGang\Snippets\Google\ConversionTracking` |
| `PressGang\Snippets\GoogleRecaptcha` | `PressGang\Snippets\Google\Recaptcha` |
| `PressGang\Snippets\GoogleTagManager` | `PressGang\Snippets\Google\TagManager` |
| `PressGang\Snippets\GoogleWebmaster` | `PressGang\Snippets\Google\Webmaster` |
| `PressGang\Snippets\CookieYes` | `PressGang\Snippets\Integration\CookieYes` |
| `PressGang\Snippets\Hotjar` | `PressGang\Snippets\Integration\Hotjar` |
| `PressGang\Snippets\Pinterest` | `PressGang\Snippets\Integration\Pinterest` |
| `PressGang\Snippets\Tawkto` | `PressGang\Snippets\Integration\Tawkto` |
| `PressGang\Snippets\Trustpilot` | `PressGang\Snippets\Integration\Trustpilot` |
| `PressGang\Snippets\OpenGraph` | `PressGang\Snippets\Seo\OpenGraph` |
| `PressGang\Snippets\Sitemap` | `PressGang\Snippets\Seo\Sitemap` |
| `PressGang\Snippets\SeoTitle` | `PressGang\Snippets\Seo\Title` |
| `PressGang\Snippets\AddQueryVars` | `PressGang\Snippets\Theme\AddQueryVars` |
| `PressGang\Snippets\AdminLogo` | `PressGang\Snippets\Theme\AdminLogo` |
| `PressGang\Snippets\ArchiveTitles` | `PressGang\Snippets\Theme\ArchiveTitles` |
| `PressGang\Snippets\BigImageScaling` | `PressGang\Snippets\Theme\BigImageScaling` |
| `PressGang\Snippets\Breadcrumb` | `PressGang\Snippets\Theme\Breadcrumb` |
| `PressGang\Snippets\Copyright` | `PressGang\Snippets\Theme\Copyright` |
| `PressGang\Snippets\DisableEmojis` | `PressGang\Snippets\Theme\DisableEmojis` |
| `PressGang\Snippets\EditorStyles` | `PressGang\Snippets\Theme\EditorStyles` |
| `PressGang\Snippets\ImageSizes` | `PressGang\Snippets\Theme\ImageSizes` |
| `PressGang\Snippets\Logo` | `PressGang\Snippets\Theme\Logo` |
| `PressGang\Snippets\LogoSvg` | `PressGang\Snippets\Theme\LogoSvg` |
| `PressGang\Snippets\Permalinks` | `PressGang\Snippets\Theme\Permalinks` |
| `PressGang\Snippets\PostTypeMenuHighlighter` | `PressGang\Snippets\Theme\PostTypeMenuHighlighter` |
| `PressGang\Snippets\TinyMceBlockFormats` | `PressGang\Snippets\Theme\TinyMceBlockFormats` |
| `PressGang\Snippets\WoocommerceAjaxCartCount` | `PressGang\Snippets\WooCommerce\AjaxCartCount` |
| `PressGang\Snippets\WoocommerceBackorders` | `PressGang\Snippets\WooCommerce\Backorders` |
| `PressGang\Snippets\WooCommerceDequeueSelectWoo` | `PressGang\Snippets\WooCommerce\DequeueSelectWoo` |
| `PressGang\Snippets\WooCommerceDequeueStyles` | `PressGang\Snippets\WooCommerce\DequeueStyles` |
| `PressGang\Snippets\WooCommerceRemoveDownloads` | `PressGang\Snippets\WooCommerce\RemoveDownloads` |
| `PressGang\Snippets\WooCommerceRemoveUncategorized` | `PressGang\Snippets\WooCommerce\RemoveUncategorized` |

## Twig Template Migration

| Old Path | New Path |
|---|---|
| `snippets/breadcrumb.twig` | `snippets/theme/breadcrumb.twig` |
| `snippets/facebook-pixel.twig` | `snippets/facebook/pixel.twig` |
| `snippets/google-ads-tag.twig` | `snippets/google/ads-tag.twig` |
| `snippets/google-analytics.twig` | `snippets/google/analytics.twig` |
| `snippets/google-analytics-ecommerce.twig` | `snippets/google/analytics-ecommerce.twig` |
| `snippets/google-conversion-tracking.twig` | `snippets/google/conversion-tracking.twig` |
| `snippets/google-recaptcha.twig` | `snippets/google/recaptcha.twig` |
| `snippets/google-tag-manager.twig` | `snippets/google/tag-manager.twig` |
| `snippets/google-tag-manager-no-script.twig` | `snippets/google/tag-manager-no-script.twig` |
| `snippets/hotjar.twig` | `snippets/integration/hotjar.twig` |
| `snippets/tawkto.twig` | `snippets/integration/tawkto.twig` |
| `snippets/trustpilot-mini.twig` | `snippets/integration/trustpilot-mini.twig` |
| `snippets/open-graph.twig` | `snippets/seo/open-graph.twig` |
| `snippets/sitemap-xml.twig` | `snippets/seo/sitemap-xml.twig` |
| `snippets/json-ld/*.twig` | `snippets/seo/json-ld/*.twig` |

## Example: Updated config/snippets.php

```php
return [
    'PressGang\\Snippets\\Theme\\DisableEmojis' => [],
    'PressGang\\Snippets\\Theme\\ImageSizes' => [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'hero'      => ['width' => 1920, 'height' => 600, 'crop' => true],
    ],
    'PressGang\\Snippets\\Google\\Analytics' => [],
];
```

## Notes

- **Composer autoloading** requires no changes — PSR-4 handles subdirectories automatically.
- **Customizer setting keys** (e.g. `google-analytics-id`) are unchanged.
- If your child theme overrides any snippet Twig templates, update the template paths in your theme's `views/` directory to match the new locations.
