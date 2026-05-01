<?php

declare(strict_types=1);

namespace ProcessWire;

if (!function_exists('ProcessWire\simpleicon')) {
    /**
     * Return the Icon singleton instance.
     *
     * @return \SimpleWire\Icon\Icon
     */
    function simpleicon(): \SimpleWire\Icon\Icon
    {
        return wire()->simpleicon;
    }
}

if (!function_exists('ProcessWire\icon')) {
    /**
     * Render an icon by name
     *
     * Usage:
     *   icon('home')                                     // outline, default size, data URI
     *   icon('home-filled', 'size-1')                    // filled, size-1, data URI
     *   icon('settings', format: 'inline')               // inline SVG
     *   icon('user', 'size-5', format: 'image')          // img tag with file URL
     *   icon('star', attributes: 'class="my-icon"')      // with custom attributes
     *
     * @param string $name Icon name (append '-filled' for filled style)
     * @param string|null $size Size key: size-1 (40px) through size-7 (12px)
     * @param string|null $version Tabler Icons version
     * @param string|null $format Render format: 'data', 'inline', or 'image'
     * @param string $attributes Extra HTML attributes
     * @return string Rendered HTML
     */
    function icon(string $name, ?string $size = null, ?string $version = null, ?string $format = null, string $attributes = ''): string
    {
        return wire()->simpleicon->render($name, $size, $version, $format, $attributes);
    }
}
