<?php

declare(strict_types=1);

namespace SimpleWire\Icon;

use ProcessWire\WireException;

class Icon
{
    /** @var \ProcessWire\ProcessWire */
    protected $wire;

    private string $cdnUrl = "https://unpkg.com/@tabler/icons@{version}/icons/{style}/{name}.svg";
    private array $sizes = [
        'size-1' => 40,
        'size-2' => 32,
        'size-3' => 28,
        'size-4' => 24,
        'size-5' => 20,
        'size-6' => 16,
        'size-7' => 12
    ];

    private string $basePath;
    private string $baseUrl;
    private array $config;

    public function __construct(\ProcessWire\ProcessWire $wire, array $config = [])
    {
        $this->wire = $wire;
        $this->config = array_merge(static::getDefaults(), $config);
        $this->basePath = $this->wire->config->paths->assets . "SimpleWire/icons/";
        $this->baseUrl = $this->wire->config->urls->assets . "SimpleWire/icons/";
    }

    // ========================================
    // Defaults
    // ========================================

    public static function getDefaults(): array
    {
        return [
            'icon_version' => 'latest',
            'icon_size' => 'size-3',
            'icon_format' => 'data',
        ];
    }

    // ========================================
    // Rendering
    // ========================================

    public function render(string $name, ?string $size = null, ?string $version = null, ?string $format = null, string $attributes = ''): string
    {
        if (!$this->isValidName($name)) return '';

        $size = $size ?? $this->config['icon_size'];
        $version = $version ?? $this->config['icon_version'];
        $format = $format ?? $this->config['icon_format'];

        $style = str_ends_with($name, '-filled') ? 'filled' : 'outline';
        $sizeValue = $this->sizes[$size] ?? $this->sizes['size-3'];
        $filePath = $this->basePath . "{$style}/{$name}.svg";

        if (!file_exists($filePath)) {
            if (!$this->download($name, $style, $version, $filePath)) {
                return '';
            }
        }

        return match ($format) {
            'data' => $this->renderData($name, $filePath, $sizeValue, $attributes),
            'inline' => $this->renderInline($filePath, $sizeValue, $attributes),
            'image' => $this->renderImage($name, $style, $sizeValue, $attributes),
            default => throw new WireException("Invalid format specified: {$format}"),
        };
    }

    // ========================================
    // Download & Cache
    // ========================================

    public function download(string $name, string $style, string $version, string $filePath): bool
    {
        if (!$this->isValidName($name)) return false;

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            $this->wire->files->mkdir($dir, true);
        }

        $cdnUrl = str_replace(
            ['{version}', '{style}', '{name}'],
            [$version, $style, $name],
            $this->cdnUrl
        );

        try {
            $http     = $this->wire->simpleclient->newClient();
            $response = $http->get($cdnUrl);
            $content  = $response->body();
        } catch (\SimpleWire\Client\ClientException $e) {
            return false;
        }

        if (!str_starts_with(trim($content), '<svg')) {
            return false;
        }

        return file_put_contents($filePath, $content) !== false;
    }

    public function isCached(string $name): bool
    {
        if (!$this->isValidName($name)) return false;
        $style = str_ends_with($name, '-filled') ? 'filled' : 'outline';
        return file_exists($this->basePath . "{$style}/{$name}.svg");
    }

    public function preload(array $names, ?string $version = null): array
    {
        $version = $version ?? $this->config['icon_version'];
        $results = [];
        foreach ($names as $name) {
            if (!is_string($name) || !$this->isValidName($name)) {
                $results[$name] = false;
                continue;
            }
            if ($this->isCached($name)) {
                $results[$name] = true;
                continue;
            }
            $style = str_ends_with($name, '-filled') ? 'filled' : 'outline';
            $filePath = $this->basePath . "{$style}/{$name}.svg";
            $results[$name] = $this->download($name, $style, $version, $filePath);
        }
        return $results;
    }

    public function getCachedIcons(): array
    {
        $icons = [];
        foreach (['outline', 'filled'] as $style) {
            $dir = $this->basePath . $style;
            if (!is_dir($dir)) continue;
            foreach (glob($dir . '/*.svg') ?: [] as $file) {
                $icons[$style][] = basename($file, '.svg');
            }
        }
        return $icons;
    }

    public function clearCache(?string $style = null): int
    {
        $count = 0;
        $styles = $style ? [$style] : ['outline', 'filled'];

        foreach ($styles as $s) {
            $dir = $this->basePath . $s;
            if (!is_dir($dir)) continue;
            foreach (glob($dir . '/*.svg') ?: [] as $file) {
                if (unlink($file)) $count++;
            }
        }
        return $count;
    }

    // ========================================
    // Private Renderers
    // ========================================

    private function renderData(string $name, string $filePath, int $size, string $attributes): string
    {
        $svgContent = file_get_contents($filePath);
        if ($svgContent === false) return '';

        $base64 = base64_encode($svgContent);
        return "<img src='data:image/svg+xml;base64,{$base64}' width='{$size}' height='{$size}' alt='icon {$name}' {$attributes}/>";
    }

    private function renderInline(string $filePath, int $size, string $attributes): string
    {
        $svgContent = file_get_contents($filePath);
        if ($svgContent === false) return '';

        $result = preg_replace_callback(
            '/<svg\b([^>]*)>/s',
            function (array $m) use ($size, $attributes): string {
                $attrs = preg_replace('/\s+(?:width|height)=["\'][^"\']*["\']/', '', $m[1]) ?? $m[1];
                return "<svg width='{$size}' height='{$size}' {$attributes}{$attrs}>";
            },
            $svgContent,
            1
        );

        return $result ?? '';
    }

    private function renderImage(string $name, string $style, int $size, string $attributes): string
    {
        $url = $this->baseUrl . "{$style}/{$name}.svg";
        return "<img src='{$url}' width='{$size}' height='{$size}' alt='icon {$name}' {$attributes}/>";
    }

    private function isValidName(string $name): bool
    {
        return (bool) preg_match('/^[a-z0-9][a-z0-9-]*$/', $name);
    }
}
