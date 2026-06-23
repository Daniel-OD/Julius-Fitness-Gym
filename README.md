# Julius Fitness Gym

A gym and fitness club management application built with Laravel 13 and Filament 5.

[![PHP](https://img.shields.io/badge/PHP-8.4-blue)]()
[![Laravel](https://img.shields.io/badge/Laravel-13-red)]()
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen)]()

## Features

- **Member management** — profiles, QR codes, check-ins
- **Subscriptions & plans** — renewals, expiry tracking, status sync
- **Invoicing** — PDF invoices, payment transactions, overdue handling
- **Enquiries & follow-ups** — lead tracking for prospective members
- **Expenses** — gym expense tracking
- **Email notifications** — invoices, receipts, subscription expiry reminders
- **REST API v1** — full CRUD with Sanctum bearer token auth
- **Roles & permissions** — Filament Shield (super_admin, owner, employee, client, member)
- **Multi-locale** — English and Romanian
- **Backup & restore** — ZIP export of database and settings

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.4 |
| Admin UI | Filament 5, Livewire 4 |
| Frontend | Tailwind CSS v4, Alpine.js, Vite |
| Auth | Laravel Sanctum, Filament Shield |
| Testing | Pest 4, PHPUnit 12 |
| Database | SQLite (dev) / MySQL (production) |
| PDF | DomPDF |
| Email | Resend |

## Interfaces

| Interface | URL | Audience |
|---|---|---|
| Admin panel | `/admin` | Staff, managers |
| Office panel | `/office` | Reception (check-ins only) |
| Member portal | `/member` | Self-service for gym members |
| Client portal | `/client` | Linked client accounts |
| Reception scan | `/reception/scan` | QR scanner for front desk |
| REST API | `/api/v1` | External integrations |

## Getting Started

```bash
git clone <repository-url>
cd julius-fitness-gym

composer run setup
php -d memory_limit=512M artisan db:seed --class=WorldSeeder
cp storage/data/settingsData.json.example storage/data/settingsData.json
php artisan app:install

composer run dev
```

## Testing

```bash
php artisan test --compact
```

356 tests across `tests/Feature/` and `tests/Unit/`, running on SQLite in-memory.

## License

MIT
