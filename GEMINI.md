# Julius Fitness Gym - Gemini CLI Instructions

Foundational mandates and context for working on the Julius Fitness Gym codebase.

## Project Overview
Julius Fitness Gym is a comprehensive gym and fitness club management application. It features a robust administration panel for staff and a REST API for potential client or external integrations.

- **Main Technologies:** PHP 8.4, Laravel 13, Filament 5, Livewire 4, Tailwind CSS 4, Pest 4.
- **Key Modules:** Members, Subscriptions, Plans, Invoices, Enquiries, Expenses, and Role-Based Access Control.
- **Interfaces:**
    - **Admin Panel:** Located at `/admin`. Uses a custom iOS-inspired theme.
    - **REST API v1:** Located at `/api/v1`. Secured with Laravel Sanctum.

## Core Mandates & Conventions

### 1. Technology & Style
- **PHP 8.4+:** Utilize modern features like constructor property promotion, explicit type hints, and return types.
- **Laravel 13 Way:** Prefer Artisan commands (`php artisan make:*`) for creating components. Follow Laravel's directory structure and naming conventions.
- **Filament 5:** Adhere to the decoupled resource structure: `ResourceName/{ResourceNameResource.php, Pages/, Schemas/, Tables/}`.
- **I18n:** Support English (`en`) and Romanian (`ro`). Always use translation keys: `__('app.key')`.

### 2. Architecture & Domain
- **Soft Deletes:** All domain models use soft deletes.
- **Observers:** `Invoice` and `InvoiceTransaction` totals and status transitions are managed via Observers.
- **Settings:** App settings are JSON-backed (`storage/data/settingsData.json`), NOT stored in the database. Use `SettingsRepository` for access.
- **Sequences:** Member codes and invoice numbers are generated via `SequenceRepository`.
- **Analytics:** Use `AnalyticsService` for financial and membership metrics.

### 3. API Development
- **Versioning:** All API routes must be under `v1`.
- **Standards:** Use Eloquent API Resources and `spatie/laravel-query-builder` for filtering and sorting.
- **Auth:** Use Sanctum bearer tokens.

### 4. Testing & Quality
- **Pest 4:** Write tests using Pest. Most tests should be Feature tests.
- **Mocking:** Use factories for model creation in tests.
- **Formatting:** ALWAYS run `vendor/bin/pint --dirty --format agent` before finalizing changes.

## Critical Workflows & Commands

### Setup & Development
- **Initial Setup:** `composer run setup`
- **Seeding (World Data):** `php -d memory_limit=512M artisan db:seed --class=WorldSeeder`
- **Run All Services:** `composer run dev` (Starts PHP server, queue, logs, and Vite)
- **Clear Cache:** `php artisan optimize:clear`

### Testing & Validation
- **Run All Tests:** `php artisan test --compact`
- **Run Specific Test:** `php artisan test --compact --filter=TestName`

### Infrastructure
- **Filament Shield:** After adding a new Filament Resource, run:
  `php artisan shield:generate --resource=ResourceName --panel=admin`
- **Settings Data:** Ensure `storage/data/settingsData.json` exists (copied from `.example`).

## Specific Integration Notes
- **nnjeim/world:** If settings crash with `__PHP_Incomplete_Class`, `Helpers::worldResponse()` is designed to auto-recover by clearing the world cache.
- **Installer:** The `installer/` directory contains logic for building Windows (`.exe`) and macOS (`.dmg`) applications via Laravel Herd.

## Available Skills
- `laravel-best-practices`: Activate for any backend PHP/Laravel work.
- `pest-testing`: Activate when writing or fixing tests.
- `tailwindcss-development`: Activate for UI/Blade/Filament theme work.
