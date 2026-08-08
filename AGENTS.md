# Julius Fitness Gym

Gym and fitness club management application built on Laravel 13, Filament 5, Livewire 4, and PHP 8.4.

## Key Developer Commands

```bash
# Verification Flow (run before finalizing PHP changes)
vendor/bin/pint --dirty --format agent                       # Code style formatter
vendor/bin/phpstan analyse                                   # Static analysis (level 5)
php artisan test --compact                                   # Full test suite (SQLite in-memory)
php artisan test --compact --filter=TestName                 # Run single test by name
php artisan test tests/Feature/SomeTest.php                  # Run single test file

# Setup & Development
composer run setup                                           # Full setup (deps, env, sqlite, key, migrations, assets)
php -d memory_limit=512M artisan db:seed --class=WorldSeeder  # Requires >=512M RAM for nnjeim/world country data
php artisan app:install                                      # Finalize install: create admin user & permissions
composer run dev                                             # Concurrently runs serve, queue, pail, and vite

# Filament Shield (REQUIRED when adding new Filament resources)
php artisan shield:generate --resource=ResourceName --panel=admin

# Scheduled & Maintenance Commands
php artisan gym:invoices --mark-overdue                      # Mark overdue invoices
php artisan gym:subscriptions --mark-expired --mark-expiring  # Update subscription statuses
php artisan gym:send-expiring-emails                         # Queue member expiry notifications (7 & 3 days)
php artisan app:backup                                       # Backup DB + settings JSON to zip
php artisan app:restore {zip} --include-settings             # Restore DB + settings from zip backup
```

## Architecture & Codebase Gotchas

- **Filament Shield Permissions**: Newly created Filament resources return 404 until permissions are generated via `shield:generate` and assigned to `super_admin`.
- **Filament Resource Structure**: Keep resource classes clean by placing form/infolist definitions in `Schemas/` and table definitions in `Tables/` subdirectories (`ResourceName/{ResourceNameResource.php, Pages/, Schemas/, Tables/}`).
- **Multi-Panel Boundaries**:
  - `/admin`: Full admin panel (`web` guard, auto-discovered resources).
  - `/office`: Front-desk panel (`web` guard). `OfficePanelProvider` extends `AdminPanelProvider` but explicitly lists allowed resources (no auto-discovery) to block staff from admin routes.
  - `/member/*`: Member portal uses separate `member` guard and `Member` model (`Authenticatable`). Distinct from `User` table.
  - `/api/v1`: Sanctum token auth with `spatie/laravel-query-builder` schemas in `app/Services/Api/Schemas/`.
- **Settings & Sequence Storage**: App configuration and sequence counters (member codes, invoice numbers) are persisted in `storage/data/settingsData.json` (gitignored, copy from `storage/data/settingsData.json.example`). Access via `SettingsRepository` / `SequenceRepository` singletons.
- **Model Observers & Soft Deletes**: All domain models use soft deletes. `Invoice` and `InvoiceTransaction` use PHP `#[ObservedBy]` attributes to auto-recalculate totals and queue notification emails (`SendInvoiceIssuedEmail`, `SendInvoicePaymentReceiptEmail`).
- **Localization (i18n)**: Supported locales are `en` and `ro`. All UI text uses `__('app.key')` translation keys (`resources/lang/{en,ro}/app.php`). `SetAppLocale` middleware applies locale resolution.
- **`nnjeim/world` Cache Recovery**: Country/currency data is seeded via `WorldSeeder` (requires 512M RAM). If settings crash with `__PHP_Incomplete_Class`, `Helpers::worldResponse()` auto-clears the world cache.

