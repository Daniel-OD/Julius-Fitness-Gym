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
     * @return array{title: string, summary: string, tips: list<string>, widgets: array<string, string>}|null
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

        return self::entryForKey($key);
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
     * @return array{title: string, summary: string, tips: list<string>, widgets: array<string, string>}|null
     */
    public static function entryForKey(string $key): ?array
    {
        foreach (self::candidateKeys($key) as $candidate) {
            $raw = self::pageDefinition($candidate);

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
    private static function pageDefinition(string $key): ?array
    {
        /** @var mixed $pages */
        $pages = Lang::get('admin_guide.pages');

        if (! is_array($pages) || ! array_key_exists($key, $pages) || ! is_array($pages[$key])) {
            return null;
        }

        /** @var array<string, mixed> $definition */
        $definition = $pages[$key];

        return $definition;
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
     * @return array{title: string, summary: string, tips: list<string>, widgets: array<string, string>}
     */
    private static function normalizeEntry(array $raw): array
    {
        $tips = $raw['tips'] ?? [];
        $widgets = $raw['widgets'] ?? [];

        if (! is_array($tips)) {
            $tips = [];
        }

        if (! is_array($widgets)) {
            $widgets = [];
        }

        /** @var list<string> $normalizedTips */
        $normalizedTips = array_values(array_filter(array_map(
            fn (mixed $tip): string => trim((string) $tip),
            $tips,
        ), fn (string $tip): bool => $tip !== ''));

        /** @var array<string, string> $normalizedWidgets */
        $normalizedWidgets = [];

        foreach ($widgets as $widgetKey => $description) {
            if (! is_string($widgetKey) || ! is_scalar($description)) {
                continue;
            }

            $text = trim((string) $description);

            if ($text === '') {
                continue;
            }

            $normalizedWidgets[$widgetKey] = $text;
        }

        return [
            'title' => trim((string) ($raw['title'] ?? '')),
            'summary' => trim((string) ($raw['summary'] ?? '')),
            'tips' => $normalizedTips,
            'widgets' => $normalizedWidgets,
        ];
    }
}
