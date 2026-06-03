# Julius Fitness Gym

Gym and fitness club management application built on Laravel 13, with a Filament admin panel, REST API, and bilingual UI (English / Romanian).

**Repository:** [github.com/Daniel-OD/Julius-Fitness-Gym](https://github.com/Daniel-OD/Julius-Fitness-Gym)

**Author / studio:** [Daniel-OD](https://github.com/Daniel-OD) — signature `Daniel-OD/Julius-Fitness-Gym` (see `config/studio.php`, `php artisan about`)

## Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13, PHP 8.4 |
| Admin UI | Filament v5, Livewire v4 |
| Auth (web) | Laravel Breeze |
| API | Laravel Sanctum (v1) |
| Frontend | Tailwind CSS v4, Vite |
| Tests | Pest v4, SQLite in-memory |
| Permissions | Filament Shield |

## Features

- **Members** — profiles, status, subscriptions
- **Plans & services** — membership catalog
- **Subscriptions & invoices** — billing, PDF, email notifications
- **Enquiries & follow-ups** — sales pipeline
- **Expenses** — spending analytics
- **Settings** — JSON-backed gym configuration (`storage/data/settingsData.json`)
- **Localization** — locale switcher in admin; `en` / `ro` translations

## Requirements

- PHP 8.4+
- Composer 2
- Node.js 20+ and npm
- SQLite (default dev) or MySQL/PostgreSQL

[Laravel Herd](https://herd.laravel.com/) is recommended on macOS (site name: `julius-fitness-gym`).

## Quick start

```bash
git clone https://github.com/Daniel-OD/Julius-Fitness-Gym.git
cd Julius-Fitness-Gym
git checkout 2026-06-01-byv4   # active development branch

composer run setup              # install, .env, key, migrate, npm build
```

Populate countries/currencies (required for Settings page):

```bash
php -d memory_limit=512M artisan db:seed --class=WorldSeeder
```

Create the first admin user:

```bash
php artisan filament:make-user
```

Copy settings template:

```bash
cp storage/data/settingsData.json.example storage/data/settingsData.json
```

### Environment

```bash
cp .env.example .env
php artisan key:generate
```

Default dev database is SQLite at `database/database.sqlite`. Adjust `DB_*` in `.env` for MySQL.

## Development

```bash
composer run dev    # PHP server, queue, logs (Pail), Vite — concurrently
```

Or run separately:

```bash
php artisan serve
npm run dev
```

### URLs

| Route | Description |
|-------|-------------|
| `/` | Public landing |
| `/login` | Breeze authentication |
| `/dashboard` | Authenticated app shell (Breeze) |
| `/admin` | Filament admin panel |

After login, use **Admin** at `/admin` for day-to-day operations.

### Frontend assets

```bash
npm run build       # production
npm run dev         # watch mode
```

Filament uses a custom iOS-style theme:

- `resources/css/filament/admin/theme.css`
- Registered via `->viteTheme(...)` in `AdminPanelProvider`

If styles do not update: hard refresh (Cmd+Shift+R) or `php artisan optimize:clear`.

### Code style & tests

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
php artisan test --compact --filter=TestName
```

## Project layout

```
app/                    # Models, Filament resources, services, API
database/               # Migrations, factories, seeders
resources/
  css/                  # app.css + Filament admin theme
  js/
  views/                # Blade (Breeze, Filament overrides, emails)
routes/                 # web.php, api.php
resources/lang/         # en/ro app translations (app.php)
storage/data/           # settingsData.json (runtime settings)
tests/                  # Pest feature & unit tests
```

Agent-oriented notes for contributors live in `CLAUDE.md` and `AGENTS.md` (Laravel Boost guidelines).

## API

REST API v1 routes are defined in `routes/api.php` (Sanctum). Use `php artisan route:list --path=api` to inspect endpoints.

## Localization

- Supported locales: `en`, `ro` (`config/app.php` → `supported_locales`)
- User preference: `general.locale` in `storage/data/settingsData.json`
- Admin strings: `resources/lang/{en,ro}/app.php`
- Filament vendor translations: Romanian packs under `vendor/filament/**/lang/ro/`

## Branching

| Branch | Purpose |
|--------|---------|
| `main` | Stable baseline |
| `2026-06-01-byv4` | Active development (Filament v5, i18n, iOS UI) |

Work on feature branches and open PRs against `2026-06-01-byv4` unless agreed otherwise.

## License

MIT — see [LICENSE](LICENSE).
