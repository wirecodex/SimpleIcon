# SimpleIcon

**Alpha — v0.1.0.** This module is in early testing. The API may change before a stable release. Feedback and bug reports are welcome.

A simple Tabler Icons integration for ProcessWire. Automatically downloads, caches, and renders SVG icons from the [Tabler Icons](https://tabler.io/icons) library with multiple output formats.

The Icon class lives at `SimpleWire\Icon\Icon`.

## Features

*   **Auto-Download:** Icons are fetched from CDN on first use and cached locally
*   **Three Render Formats:** Data URI (base64), inline SVG, or image tag with file URL
*   **Seven Size Presets:** From 12px to 40px with semantic size keys
*   **Outline & Filled Styles:** Append `-filled` to the icon name for the filled variant
*   **Preloading:** Batch-download icons ahead of time
*   **Cache Management:** View, clear, and manage cached icon files
*   **Configurable Defaults:** Set default size, format, and version in the module config

## Installation

Install **SimpleClient** first (required for icon downloading), then install **SimpleIcon**.

## Quick Access

```php
// Global helper function (always available after module loads)
echo icon('home');

// Named helper — returns the Icon instance
$icon = simpleicon();

// Direct API variable
$icon = wire()->simpleicon;
```

## Quick Start

```php
<?php
namespace ProcessWire;

// Basic usage — renders with configured defaults
echo icon('home');

// Filled variant
echo icon('home-filled');

// Custom size
echo icon('settings', 'size-1');

// Inline SVG format
echo icon('user', format: 'inline');

// Image tag format
echo icon('star', format: 'image');

// With custom HTML attributes
echo icon('heart', attributes: 'class="text-red" id="fav-icon"');

// Full control
echo icon('bell', 'size-5', '3.31.0', 'inline', 'class="notification-icon"');
```

## Render Formats

### Data URI (default)

Renders an `<img>` tag with the SVG encoded as a base64 data URI. Best for simple usage — no extra HTTP requests, works everywhere.

```php
echo icon('home');
// Output: <img src='data:image/svg+xml;base64,...' width='28' height='28' alt='icon home' />
```

### Inline SVG

Injects the SVG markup directly into the HTML. Useful when you need to style the icon with CSS (colors, strokes, etc.).

```php
echo icon('home', format: 'inline');
// Output: <svg width='28' height='28' ...>...</svg>
```

### Image Tag (file URL)

Renders an `<img>` tag pointing to the cached SVG file. Best for HTTP/2 environments where parallel loading is efficient.

```php
echo icon('home', format: 'image');
// Output: <img src='/site/assets/SimpleWire/icons/outline/home.svg' width='28' height='28' alt='icon home' />
```

## Size Presets

| Key      | Pixels |
|----------|--------|
| `size-1` | 40px   |
| `size-2` | 32px   |
| `size-3` | 28px (default) |
| `size-4` | 24px   |
| `size-5` | 20px   |
| `size-6` | 16px   |
| `size-7` | 12px   |

```php
echo icon('home', 'size-1'); // 40px
echo icon('home', 'size-4'); // 24px
echo icon('home', 'size-7'); // 12px
```

## Icon Styles

Icons come in two styles based on the name suffix:

```php
// Outline (default)
echo icon('heart');          // outline/heart.svg

// Filled — append '-filled'
echo icon('heart-filled');   // filled/heart-filled.svg
```

## Direct API Usage

For more control, use the Icon instance directly:

```php
$icon = simpleicon();

// Render
echo $icon->render('home', 'size-4', 'latest', 'inline', 'class="nav-icon"');

// Check if an icon is cached
if ($icon->isCached('home')) {
    // Already downloaded
}

// Preload multiple icons
$results = $icon->preload(['home', 'settings', 'user', 'heart-filled']);
// Returns: ['home' => true, 'settings' => true, ...]

// Download a specific icon manually
$icon->download('search', 'outline', 'latest', '/path/to/search.svg');

// Get all cached icons
$cached = $icon->getCachedIcons();
// Returns: ['outline' => ['home', 'settings', ...], 'filled' => ['heart-filled', ...]]

// Clear cache
$count = $icon->clearCache();           // Clear all
$count = $icon->clearCache('outline');   // Clear only outline icons
$count = $icon->clearCache('filled');    // Clear only filled icons
```

## Module Configuration

Icon settings are configured in the **SimpleIcon** module configuration screen.

### Configuration Options

*   **Default Tabler Icons Version:** CDN version tag (e.g. `latest`, `3.31.0`)
*   **Default Icon Size:** One of the seven size presets
*   **Default Render Format:** `data`, `inline`, or `image`

The config screen also shows a count of cached outline and filled icons.

## Cache

Icons are cached locally at `site/assets/SimpleWire/icons/` with the structure:

```
site/assets/SimpleWire/icons/
├── outline/
│   ├── home.svg
│   ├── settings.svg
│   └── ...
└── filled/
    ├── heart-filled.svg
    └── ...
```

Icons are downloaded from the Tabler Icons CDN on first use and served locally from cache thereafter.

## Complete Examples

### Navigation with Icons

```php
<?php
namespace ProcessWire;
?>
<nav>
    <a href="/"><?= icon('home', 'size-5') ?> Home</a>
    <a href="/products"><?= icon('package', 'size-5') ?> Products</a>
    <a href="/settings"><?= icon('settings', 'size-5') ?> Settings</a>
    <a href="/profile"><?= icon('user', 'size-5') ?> Profile</a>
</nav>
```

### Icon Buttons

```php
<?php
namespace ProcessWire;
?>
<button type="submit">
    <?= icon('device-floppy', 'size-5', format: 'inline') ?> Save
</button>
<button type="button" class="danger">
    <?= icon('trash', 'size-5', format: 'inline') ?> Delete
</button>
```

### Preloading Icons for a Page

```php
<?php
namespace ProcessWire;

// Preload all icons used on this page for best performance
simpleicon()->preload(['home', 'settings', 'user', 'bell', 'heart-filled', 'star-filled']);

// Now render — all icons are already cached
echo icon('home');
echo icon('heart-filled');
```

### Dynamic Icon from Page Field

```php
<?php
namespace ProcessWire;

// Assuming a text field 'icon_name' on the page
foreach ($pages->find("template=category") as $cat) {
    echo "<div class='category'>";
    echo icon($cat->icon_name, 'size-4');
    echo "<span>{$cat->title}</span>";
    echo "</div>";
}
```

## Troubleshooting

#### Icon not rendering:

*   Check that the icon name is valid at [tabler.io/icons](https://tabler.io/icons)
*   Verify the `site/assets/SimpleWire/icons/` directory is writable
*   Check if `file_get_contents()` with URLs is enabled (`allow_url_fopen = On`)

#### Wrong icon style:

*   Outline is the default. Append `-filled` for the filled variant
*   Check the icon exists in the desired style on the Tabler Icons site

#### Cache issues:

*   Clear the cache to force re-download: `simpleicon()->clearCache()`
*   Check file permissions on `site/assets/SimpleWire/icons/`

## API Reference

### Global Functions

```php
icon(string $name, ?string $size = null, ?string $version = null, ?string $format = null, string $attributes = ''): string
simpleicon(): \SimpleWire\Icon\Icon
```

### Instance Methods

*   `render(string $name, ?string $size = null, ?string $version = null, ?string $format = null, string $attributes = ''): string`
*   `download(string $name, string $style, string $version, string $filePath): bool`
*   `isCached(string $name): bool`
*   `preload(array $names, ?string $version = null): array`
*   `getCachedIcons(): array`
*   `clearCache(?string $style = null): int`

## License

This module is released under the MIT License.
