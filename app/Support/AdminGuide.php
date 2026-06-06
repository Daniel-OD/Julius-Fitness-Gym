<?php

namespace App\Support;

use App\Helpers\Helpers;
use Illuminate\Support\Facades\Lang;

/**
 * Contextual admin guide shown on Filament pages when enabled in settings.
 */
final class AdminGuide
{
    public static function isEnabled(): bool
    {
        return Helpers::isAdminGuideEnabled();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function forCurrentPage(): ?array
    {
        if (! self::isEnabled()) {
            return null;
        }

        $key = self::resolveKey(request()->route()?->getName());

        if ($key === null) {
            return null;
        }

        if ($key === 'admin.settings') {
            return self::entryForKey('admin.settings.overview');
        }

        return self::entryForKey($key);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function forContext(string $contextKey): ?array
    {
        if (! self::isEnabled()) {
            return null;
        }

        return self::entryForKey($contextKey);
    }

    public static function resolveKey(?string $routeName): ?string
    {
        if ($routeName === null || ! str_starts_with($routeName, 'filament.')) {
            return null;
        }

        if (
            str_contains($routeName, '.auth.')
            || str_contains($routeName, 'force-password')
            || str_contains($routeName, 'password-reset')
        ) {
            return null;
        }

        $parts = explode('.', $routeName);

        if (count($parts) < 4) {
            return null;
        }

        $panel = $parts[1];
        $type = $parts[2];

        if ($type === 'pages') {
            return "{$panel}.{$parts[3]}";
        }

        if ($type === 'resources') {
            $resource = $parts[3];
            $action = $parts[4] ?? 'index';

            return "{$panel}.{$resource}.{$action}";
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function entryForKey(string $key): ?array
    {
        foreach (self::candidateKeys($key) as $candidate) {
            $raw = self::definition($candidate);

            if ($raw === null) {
                continue;
            }

            return self::normalizeEntry($raw);
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function definition(string $key): ?array
    {
        /** @var mixed $pages */
        $pages = Lang::get('admin_guide.pages');

        if (is_array($pages) && array_key_exists($key, $pages) && is_array($pages[$key])) {
            /** @var array<string, mixed> $definition */
            $definition = $pages[$key];

            return $definition;
        }

        /** @var mixed $tabs */
        $tabs = Lang::get('admin_guide_tabs');

        if (is_array($tabs) && array_key_exists($key, $tabs) && is_array($tabs[$key])) {
            /** @var array<string, mixed> $definition */
            $definition = $tabs[$key];

            return $definition;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function candidateKeys(string $key): array
    {
        $candidates = [$key];

        if (preg_match('/^(.+)\.(create|edit|view)$/', $key, $matches) === 1) {
            $candidates[] = $matches[1].'.index';
            $candidates[] = $matches[1];
        } elseif (str_ends_with($key, '.index')) {
            $candidates[] = substr($key, 0, -strlen('.index'));
        }

        return array_values(array_unique($candidates));
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private static function normalizeEntry(array $raw): array
    {
        return [
            'title' => trim((string) ($raw['title'] ?? '')),
            'summary' => trim((string) ($raw['summary'] ?? '')),
            'greeting' => trim((string) ($raw['greeting'] ?? '')),
            'save_reminder' => trim((string) ($raw['save_reminder'] ?? '')),
            'steps' => self::normalizeSteps($raw['steps'] ?? []),
            'checklist' => self::normalizeStringList($raw['checklist'] ?? []),
            'tips' => self::normalizeStringList($raw['tips'] ?? []),
            'widgets' => self::normalizeWidgets($raw['widgets'] ?? []),
        ];
    }

    /**
     * @return list<array{title: string, body: string, fields: list<array{name: string, hint: string}>}>
     */
    private static function normalizeSteps(mixed $steps): array
    {
        if (! is_array($steps)) {
            return [];
        }

        $normalized = [];

        foreach ($steps as $step) {
            if (! is_array($step)) {
                continue;
            }

            $title = trim((string) ($step['title'] ?? ''));
            $body = trim((string) ($step['body'] ?? ''));

            if ($title === '' && $body === '') {
                continue;
            }

            $fields = [];

            if (is_array($step['fields'] ?? null)) {
                foreach ($step['fields'] as $field) {
                    if (! is_array($field)) {
                        continue;
                    }

                    $name = trim((string) ($field['name'] ?? ''));
                    $hint = trim((string) ($field['hint'] ?? ''));

                    if ($name === '') {
                        continue;
                    }

                    $fields[] = [
                        'name' => $name,
                        'hint' => $hint,
                    ];
                }
            }

            $normalized[] = [
                'title' => $title,
                'body' => $body,
                'fields' => $fields,
            ];
        }

        return $normalized;
    }

    /**
     * @return list<string>
     */
    private static function normalizeStringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): string => trim((string) $value),
            $values,
        ), fn (string $value): bool => $value !== ''));
    }

    /**
     * @return array<string, string>
     */
    private static function normalizeWidgets(mixed $widgets): array
    {
        if (! is_array($widgets)) {
            return [];
        }

        $normalized = [];

        foreach ($widgets as $widgetKey => $description) {
            if (! is_string($widgetKey) || ! is_scalar($description)) {
                continue;
            }

            $text = trim((string) $description);

            if ($text === '') {
                continue;
            }

            $normalized[$widgetKey] = $text;
        }

        return $normalized;
    }
}
