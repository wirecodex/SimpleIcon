<?php

declare(strict_types=1);

namespace ProcessWire;

/** @property \ProcessWire\ProcessWire $wire */
class SimpleIcon extends WireData implements Module, ConfigurableModule
{
    /** @var \SimpleWire\Icon\Icon */
    protected $icon;

    public static function getModuleInfo(): array
    {
        return [
            'title'    => 'SimpleIcon',
            'version'  => '0.1.0',
            'summary'  => 'Tabler Icons renderer with local SVG caching. Supports data URI, inline SVG, and image tag output formats.',
            'icon'     => 'flag',
            'author'   => 'WireCodex',
            'autoload' => true,
            'singular' => true,
            'requires' => 'ProcessWire>=3.0.200,PHP>=8.1,SimpleClient',
        ];
    }

    // ========================================
    // Lifecycle
    // ========================================

    public function init(): void
    {
        spl_autoload_register(function (string $class): void {
            $prefix = 'SimpleWire\\Icon\\';
            if (!str_starts_with($class, $prefix)) return;
            $file = __DIR__ . '/classes/' . substr($class, strlen($prefix)) . '.php';
            if (file_exists($file)) require_once $file;
        });

        $config = array_merge(
            \SimpleWire\Icon\Icon::getDefaults(),
            (array) $this->wire('modules')->getConfig($this)
        );

        $this->icon = new \SimpleWire\Icon\Icon($this->wire, $config);

        $this->wire('simpleicon', $this->icon);

        require_once __DIR__ . '/functions.php';
    }

    // ========================================
    // Config UI
    // ========================================

    public static function getModuleConfigInputfields(array $data): InputfieldWrapper
    {
        $modules = wire()->modules;

        /** @var InputfieldWrapper $wrapper */
        $wrapper = $modules->get('InputfieldWrapper');

        // ---- Icon Settings ----

        /** @var InputfieldFieldset $fieldset */
        $fieldset              = $modules->get('InputfieldFieldset');
        $fieldset->label       = 'Icon Settings';
        $fieldset->description = 'Tabler Icons configuration';
        $fieldset->icon        = 'flag';

        /** @var InputfieldText $field */
        $field              = $modules->get('InputfieldText');
        $field->name        = 'icon_version';
        $field->label       = 'Default Tabler Icons Version';
        $field->description = 'Version tag for the Tabler Icons CDN (e.g. "latest", "3.31.0")';
        $field->columnWidth = 34;
        $field->value       = $data['icon_version'] ?? 'latest';
        $fieldset->add($field);

        /** @var InputfieldSelect $field */
        $field              = $modules->get('InputfieldSelect');
        $field->name        = 'icon_size';
        $field->label       = 'Default Icon Size';
        $field->columnWidth = 33;
        $field->value       = $data['icon_size'] ?? 'size-3';
        $field->addOptions([
            'size-1' => 'size-1 (40px)',
            'size-2' => 'size-2 (32px)',
            'size-3' => 'size-3 (28px)',
            'size-4' => 'size-4 (24px)',
            'size-5' => 'size-5 (20px)',
            'size-6' => 'size-6 (16px)',
            'size-7' => 'size-7 (12px)',
        ]);
        $fieldset->add($field);

        /** @var InputfieldSelect $field */
        $field              = $modules->get('InputfieldSelect');
        $field->name        = 'icon_format';
        $field->label       = 'Default Render Format';
        $field->columnWidth = 33;
        $field->value       = $data['icon_format'] ?? 'data';
        $field->addOptions([
            'data'   => 'Data URI (base64 img)',
            'inline' => 'Inline SVG',
            'image'  => 'Image tag (file URL)',
        ]);
        $fieldset->add($field);

        // Cache info
        $iconPath     = wire()->config->paths->assets . 'SimpleWire/icons/';
        $totalOutline = 0;
        $totalFilled  = 0;
        foreach (['outline', 'filled'] as $style) {
            $dir = $iconPath . $style;
            if (is_dir($dir)) {
                $count = count(glob($dir . '/*.svg') ?: []);
                if ($style === 'outline') $totalOutline = $count;
                else $totalFilled = $count;
            }
        }

        /** @var InputfieldMarkup $field */
        $field        = $modules->get('InputfieldMarkup');
        $field->label = 'Cached Icons';
        $field->value = "<p>Outline: <strong>{$totalOutline}</strong> icons | Filled: <strong>{$totalFilled}</strong> icons</p>
            <p>Cache location: <code>site/assets/SimpleWire/icons/</code></p>";
        $fieldset->add($field);

        $wrapper->add($fieldset);

        return $wrapper;
    }
}
