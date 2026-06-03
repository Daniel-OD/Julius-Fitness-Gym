# Julius Fitness Gym

Gym and fitness club management application built on Laravel 13, with a Filament admin panel, REST API, and bilingual UI (English / Romanian).

**Repository:** [github.com/Daniel-OD/Julius-Fitness-Gym](https://github.com/Daniel-OD/Julius-Fitness-Gym)

**Author / studio:** [Daniel-OD](https://github.com/Daniel-OD) — signature `Daniel-OD/Julius-Fitness-Gym` (see `config/studio.php`, `php artisan about`)

## Table of Contents

- [Stack](#stack)
- [Features](#features)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Development](#development)
- [Project Layout](#project-layout)
- [API](#api)
- [Localization](#localization)
- [Branching](#branching)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

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
| Database | SQLite (dev) / MySQL / PostgreSQL |
| Build tool | Inno Setup 6 (Windows installer) |

## Features

- **Members** — profiles, status, subscriptions
- **Plans & services** — membership catalog
- **Subscriptions & invoices** — billing, PDF, email notifications
- **Enquiries & follow-ups** — sales pipeline
- **Expenses** — spending analytics
- **Settings** — JSON-backed gym configuration (`storage/data/settingsData.json`)
- **Localization** — locale switcher in admin; `en` / `ro` translations
- **Admin Dashboard** — key metrics and activity overview
- **REST API** — full API v1 with Sanctum authentication
- **Permission Management** — role-based access control via Filament Shield

## Requirements

- **PHP** 8.4+
- **Composer** 2
- **Node.js** 20+ and npm
- **Database** — SQLite (default dev) or MySQL/PostgreSQL
- **Optional** — [Laravel Herd](https://herd.laravel.com/) (recommended on macOS, site name: `julius-fitness-gym`)

## Quick Start

### 1. Clone and setup

```bash
git clone https://github.com/Daniel-OD/Julius-Fitness-Gym.git
cd Julius-Fitness-Gym
git checkout 2026-06-01-byv4   # active development branch

composer run setup              # install, .env, key, migrate, npm build
```

### 2. Populate database

```bash
php -d memory_limit=512M artisan db:seed --class=WorldSeeder
```

### 3. Create first admin user

```bash
php artisan filament:make-user
```

### 4. Configure settings

```bash
cp storage/data/settingsData.json.example storage/data/settingsData.json
```

### 5. Set environment (if not done by `composer run setup`)

```bash
cp .env.example .env
php artisan key:generate
```

**Default dev database:** SQLite at `database/database.sqlite`. Adjust `DB_*` in `.env` for MySQL/PostgreSQL.

## Development

### Run all services

```bash
composer run dev    # PHP server, queue, logs (Pail), Vite — concurrently
```

### Run separately

```bash
php artisan serve
npm run dev
```

### Routes & URLs

| Route | Description |
|-------|-------------|
| `/` | Public landing page |
| `/login` | Breeze authentication |
| `/dashboard` | Authenticated app shell (Breeze) |
| `/admin` | Filament admin panel ⭐ |

> After login, use **Admin** at `/admin` for day-to-day operations.

### Frontend Assets

```bash
npm run build       # production build
npm run dev         # watch mode
```

**Custom theme:** Filament uses a custom iOS-style theme:
- `resources/css/filament/admin/theme.css`
- Registered via `->viteTheme(...)` in `AdminPanelProvider`

**Styles not updating?** Hard refresh (Cmd+Shift+R / Ctrl+Shift+R) or run:
```bash
php artisan optimize:clear
```

### Code Quality

```bash
# Format code
vendor/bin/pint --dirty --format agent

# Run all tests
php artisan test --compact

# Run specific test
php artisan test --compact --filter=TestName

# List routes
php artisan route:list
php artisan route:list --path=api
```

## Project Layout

```
app/                           # Models, Filament resources, services, API
├── Filament/                  # Admin resources & pages
├── Models/                    # Eloquent models
├── Services/                  # Business logic
└── Http/Controllers/          # API controllers

database/
├── migrations/                # Database schema
├── factories/                 # Model factories
└── seeders/                   # Database seeders

resources/
├── css/
│   └── filament/admin/        # Custom Filament theme
├── js/                        # JavaScript assets
├── views/                     # Blade templates (Breeze, Filament overrides, emails)
└── lang/                      # en/ro app translations (app.php)

routes/
├── web.php                    # Web routes (Breeze, Admin)
└── api.php                    # REST API routes (Sanctum)

storage/
└── data/                      # settingsData.json (runtime settings)

tests/                         # Pest feature & unit tests

CLAUDE.md                      # Agent-oriented contributor notes
AGENTS.md                      # Laravel Boost guidelines
```

## API

REST API v1 routes are defined in `routes/api.php` (Sanctum authentication).

### Inspect endpoints

```bash
php artisan route:list --path=api
```

### Authentication

Use **Laravel Sanctum** tokens for API requests:
```bash
# Generate token for user
php artisan tinker
>>> $user = User::first();
>>> $user->createToken('api-token')->plainTextToken;
```

## Localization

### Supported Locales

- `en` (English)
- `ro` (Romanian)

### Configuration

- **App locales:** `config/app.php` → `supported_locales`
- **User preference:** `general.locale` in `storage/data/settingsData.json`
- **Admin translations:** `resources/lang/{en,ro}/app.php`
- **Filament vendor translations:** Romanian packs under `vendor/filament/**/lang/ro/`

### Adding a new translation key

1. Add key-value pair to `resources/lang/en/app.php`
2. Add Romanian translation to `resources/lang/ro/app.php`
3. Use in Blade: `{{ __('app.key_name') }}`

## Branching

| Branch | Purpose |
|--------|---------|
| `main` | Stable baseline (production-ready) |
| `2026-06-01-byv4` | Active development (Filament v5, i18n, iOS UI) |

### Contributing

Work on **feature branches** off `2026-06-01-byv4` and open PRs unless agreed otherwise:

```bash
git checkout 2026-06-01-byv4
git pull origin 2026-06-01-byv4
git checkout -b feature/your-feature-name
# ... make changes ...
git push origin feature/your-feature-name
# Open PR on GitHub
```

## Troubleshooting

### Database Issues

**SQLite locked?**
```bash
rm database/database.sqlite
php artisan migrate --seed
```

**Need fresh database?**
```bash
php artisan migrate:fresh --seed
```

### Artisan commands not found

```bash
composer install
php artisan optimize:clear
```

### Vite/npm build issues

```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Styles not applying in Filament

```bash
php artisan optimize:clear
# Hard refresh browser (Cmd+Shift+R or Ctrl+Shift+R)
npm run build
```

### Permission denied on artisan

```bash
chmod +x artisan
php artisan serve
```

### Laravel Herd site not accessible

1. Ensure site name matches folder: `julius-fitness-gym`
2. Check Herd dashboard for the site
3. Verify `.env` `APP_URL`: `http://julius-fitness-gym.test`
4. Restart Herd service

## Desktop installers (Windows & macOS)

Packaging for machines with [Laravel Herd](https://herd.laravel.com), Composer, and Node.js.

| Platform | Build command | Output |
|----------|---------------|--------|
| Windows | `installer\build-installer.bat` | `dist/Julius-Fitness-Gym-Setup-v1.0.exe` |
| macOS | `./installer/build-dmg.sh` | `dist/Julius-Fitness-Gym-Setup-v1.0.dmg` |

Quick install from a git clone on Mac:

```bash
./install.sh
./open.command
```

See [installer/README.md](installer/README.md) for full client setup steps.

## Contributing

Please ensure:
1. Code follows [Laravel best practices](https://laravel.com/docs/guidelines)
2. Tests pass: `php artisan test --compact`
3. Code is formatted: `vendor/bin/pint --dirty`
4. Commit messages are descriptive
5. PRs target the active development branch

For detailed contributor guidelines, see `CLAUDE.md` and `AGENTS.md`.

## License

MIT — see [LICENSE](LICENSE).

---

**Questions?** Open an issue or check the [discussions](../../discussions).
