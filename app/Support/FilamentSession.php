<?php

namespace App\Support;

/**
 * Tracks which Filament panel the current browser session authenticated against.
 *
 * Prevents a front-desk PC from reusing an admin session (and vice versa) without
 * an explicit logout — the next user must sign in on that panel's login page.
 */
final class FilamentSession
{
    public const string AUTHENTICATED_PANEL_KEY = 'filament.authenticated_panel_id';

    public static function lockToPanel(string $panelId): void
    {
        session([self::AUTHENTICATED_PANEL_KEY => $panelId]);
    }

    public static function authenticatedPanelId(): ?string
    {
        $panelId = session(self::AUTHENTICATED_PANEL_KEY);

        return is_string($panelId) && $panelId !== '' ? $panelId : null;
    }

    public static function forget(): void
    {
        session()->forget(self::AUTHENTICATED_PANEL_KEY);
    }
}
