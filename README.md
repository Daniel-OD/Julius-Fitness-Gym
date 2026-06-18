# Julius Fitness Gym

> Aplicație de management pentru săli de fitness — construită cu Laravel 13, Filament 5 și Pest 4.

[![Tests](https://img.shields.io/badge/tests-passing-green)]()
[![PHP](https://img.shields.io/badge/PHP-8.4-blue)]()
[![Laravel](https://img.shields.io/badge/Laravel-13-red)]()

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13, PHP 8.4 |
| Admin UI | Filament 5, Livewire 4 |
| Frontend | Tailwind CSS v4, Vite, Alpine.js |
| Testing | Pest 4, PHPUnit 12 |
| Database | SQLite (dev) / MySQL (production) |
| API auth | Laravel Sanctum 4 |
| Permissions | Filament Shield 4 (Spatie) |
| PDF | DomPDF |
| Email | Resend |
| Countries / currencies | nnjeim/world |
| API filtering | spatie/laravel-query-builder |

## Arhitectură

3 interfețe paralele, același model layer:

| Interfață | URL | Utilizatori |
|-----------|-----|-------------|
| **Filament Admin** | `/admin` | Admin, staff — panou principal (membri, abonamente, facturi, setări) |
| **REST API v1** | `/api/v1` | Integrări externe — Sanctum bearer token |
| **Member Portal** | `/member` | Self-service clienți — login, dashboard, QR, facturi PDF |

**Interfețe suplimentare** (același backend):

| Interfață | URL | Rol |
|-----------|-----|-----|
| Staff login | `/staff/login` | Autentificare Filament pentru admin/office |
| Office panel | `/office` | Recepție — check-in, dashboard minimal |
| Reception scan | `/reception/scan` | Scanare QR cu webcam (staff) |
| Client portal | `/client` | Dashboard Breeze pentru rol `client` |
| Public check-in | `/checkin/{token}` | Check-in/checkout fără auth (QR membru) |

Setările aplicației sunt persistate în `storage/data/settingsData.json` (nu în DB). Secvențele (cod membru, număr factură) folosesc `JsonSequenceRepository`.

## Module (A–Z)

| Modul | Descriere | Implementare |
|-------|-----------|--------------|
| **Analytics** | Metrici financiare și membership (cashflow, top planuri, tranzacții) | `app/Services/Analytics/`, `app/Filament/Widgets/Analytics/`, `Api/V1/AnalyticsController` |
| **API v1** | REST CRUD + renew, PDF, tranzacții, analytics | `routes/api.php`, `app/Http/Controllers/Api/V1/` |
| **Backup & Restore** | Backup ZIP DB + settings; restore din arhivă | `app/Console/Commands/BackupApplication.php`, `RestoreApplication.php` |
| **Check-ins** | Prezență sală, check-in/out manual și QR | `app/Models/CheckIn.php`, `CheckInResource`, `CheckInService` |
| **Client portal** | Dashboard QR pentru utilizatori Breeze cu rol `client` | `ClientPortalController`, `/client` |
| **Email** | Facturi, chitanțe, expirare abonament, reset parolă | `app/Jobs/`, `app/Mail/`, `app/Services/Email/` |
| **Enquiries** | Lead-uri / prospecti noi | `EnquiryResource`, `EnquiryController`, `Api/V1/EnquiriesController` |
| **Expenses** | Cheltuieli sală | `ExpenseResource`, `Api/V1/ExpensesController` |
| **Follow-ups** | Programări de urmărire pe enquiry-uri | `FollowUpResource`, `EnquiryFollowUpsRelationManager` |
| **Installation** | Setup inițial: env, admin, credentials | `app/Console/Commands/InstallApplication.php` |
| **Invoices** | Facturare, PDF, plăți, tranzacții, overdue | `InvoiceResource`, `InvoiceObserver`, `InvoiceEmailService` |
| **Member import** | Import bulk Excel/CSV cu mapare coloane | `member-import-wizard` Livewire, `MemberImportService` |
| **Member portal** | Self-service: login, parolă, QR, facturi | `app/Http/Controllers/Member/`, `/member` |
| **Members** | Profile membri, cod, QR token, abonamente | `MemberResource`, `MemberForm`, `MemberOnboardingService` |
| **Office panel** | Panou recepție minimal (check-ins only) | `OfficePanelProvider`, `/office` |
| **Plans** | Catalog planuri abonament | `PlanResource`, `Api/V1/PlansController` |
| **Reception scan** | Scanner QR front-desk (jsQR + webcam) | `ReceptionScanController`, `/reception/scan` |
| **Roles & permissions** | RBAC Filament Shield | `bezhansalleh/filament-shield`, `Api/V1/RolesController` |
| **Services** | Servicii add-on opționale | `ServiceResource`, `Api/V1/ServicesController` |
| **Settings** | Configurare gym (monedă, taxe, email, backup, import) | `app/Filament/Pages/Settings.php`, `JsonSettingsRepository` |
| **Subscriptions** | Abonamente, reînnoiri, status sync | `SubscriptionResource`, `SubscriptionRenewalService`, `SubscriptionObserver` |
| **Users** | Conturi admin/staff, reset parolă | `UserResource`, `ResetUserPasswordAction` |

## Domain Model

```
Member ──< Subscription >── Plan
  │              │
  │              └── Service (optional add-on on subscription)
  │
  ├──< CheckIn
  ├──< Enquiry ──< FollowUp
  └── (optional) User (portal link)

Subscription ──< Invoice ──< InvoiceTransaction
Subscription ──< Subscription (renewed_from_subscription_id)

Expense (standalone)
User (admin accounts, Spatie roles via Filament Shield)
SubscriptionExpirationNotificationRead (read state for expiry alerts)
```

Toate modelele de domeniu folosesc **soft deletes**. `Invoice` și `InvoiceTransaction` au observers — totalurile se sincronizează automat, email-urile pleacă via queue jobs.

## Artisan Commands

| Comandă | Descriere | Când rulează |
|---------|-----------|--------------|
| `gym:invoices --mark-overdue` | Marchează facturile restante ca overdue după due date | Zilnic 00:05 (scheduler) |
| `gym:subscriptions` | Marchează abonamente expiring/expired; sync status membri | Zilnic 00:10 (scheduler) |
| `gym:subscription-expiry-notifications` | Notificări in-app la 7, 3, 1 și 0 zile înainte de expirare | Zilnic 09:00 (scheduler) |
| `gym:send-expiring-emails` | Trimite emailuri pentru abonamente care expiră în 7 sau 3 zile | Zilnic 09:00 (scheduler) |

**Comenzi suplimentare** (non-`gym:`):

| Comandă | Descriere |
|---------|-----------|
| `app:install` | Finalizează instalarea (env, admin, fișier credentials) |
| `app:backup` | Backup ZIP DB + settings |
| `app:restore` | Restore din backup |
| `app:cache` | Warm/clear cache Laravel, Filament, rute |

## Instalare

```bash
git clone https://github.com/Daniel-OD/Julius-Fitness-Gym.git
cd Julius-Fitness-Gym
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

**Setup complet (recomandat):**

```bash
composer run setup
php -d memory_limit=512M artisan db:seed --class=WorldSeeder   # țări/monede (512 MB RAM)
php artisan filament:make-user                                  # primul admin
cp storage/data/settingsData.json.example storage/data/settingsData.json
php artisan db:seed --class=EmployeeRoleSeeder                  # rol recepție (opțional)
php artisan db:seed --class=ClientRoleSeeder                    # rol portal client (opțional)
```

**Development (server + queue + logs + Vite):**

```bash
composer run dev
```

## Variabile de environment importante

| Variabilă | Descriere |
|-----------|-----------|
| `APP_NAME` | Numele sălii (folosit în email-uri și UI) |
| `APP_URL` | URL public al aplicației (link-uri, email-uri) |
| `APP_ENV` | `local` / `production` |
| `APP_DEBUG` | `true` doar în dev — **false** în production |
| `APP_LOCALE` | Limba default (`en` / `ro`) |
| `DB_CONNECTION` | `sqlite` (dev) sau `mysql` (production) |
| `DB_DATABASE` | Cale SQLite sau nume DB MySQL |
| `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` | Conexiune MySQL (production) |
| `SESSION_DRIVER` | `database` (default) |
| `QUEUE_CONNECTION` | `database` — necesar pentru email-uri queued |
| `CACHE_STORE` | `database` (default) |
| `MAIL_MAILER` | `log` (dev) sau `resend` (production) |
| `RESEND_API_KEY` | Cheie API Resend pentru trimitere email reală |
| `MAIL_FROM_ADDRESS` | Adresa expeditor (domeniu verificat în Resend) |
| `MAIL_FROM_NAME` | Nume expeditor (default: `APP_NAME`) |

## Teste

```bash
php artisan test
# sau
php artisan test --compact
php artisan test --compact --filter=TestName
php artisan test tests/Feature/InvoicesTest.php
```

**356 teste** (Pest 4) în `tests/Feature/` și `tests/Unit/`. Baza de test: SQLite in-memory (`phpunit.xml`).

| Zonă | Fișiere reprezentative |
|------|------------------------|
| API v1 | `ApiV1Test`, `ApiAuthorizationTest`, `ApiSubscriptionsTest`, `ApiServicesTest` |
| Auth | `AuthenticationTest`, `StaffLoginTest`, `PasswordResetTest`, `MemberPasswordResetTest` |
| Membri | `MembersTest`, `MemberImportServiceTest`, `MemberImportWizardTest`, `MemberOnboardingServiceTest` |
| Abonamente & facturi | `SubscriptionsTest`, `InvoicesTest`, `SubscriptionInvoiceNumberTest` |
| Check-in | `CheckInGraceTest`, `CheckInPresenceTest`, `ReceptionScanTest`, `DualVisibilityAndCheckInTest` |
| Portal membru | `Member/AuthTest`, `Member/DashboardTest`, `ClientPortalTest` |
| Admin Filament | `SettingsPageTest`, `AdminPanelLocaleTest`, `OfficePanelAccessTest`, `DashboardNavigationTest` |
| Comenzi | `SendSubscriptionExpiringEmailsTest`, `SubscriptionExpiryNotificationsTest`, `InstallApplicationCommandTest` |
| Email & setări | `MailSettingsTest`, `SettingsTest` |

## Roluri

| Rol | Acces |
|-----|-------|
| **super_admin** | Acces complet `/admin` — toate resursele Filament Shield; dashboard admin, office, client |
| **owner** | Echivalent super_admin pentru dashboard-uri și permisiuni |
| **employee** | Doar `/office` — check-ins, dashboard recepție, link scan QR; fără financiar/management |
| **client** | Portal Breeze `/client` — QR, dashboard client (User legat de Member) |
| **Member** (guard `member`) | Portal self-service `/member` — login separat, dashboard, QR, facturi PDF, schimbare parolă |

Permisiunile granulare Filament se generează cu:

```bash
php artisan shield:generate --resource=ResourceName --panel=admin
```

## Deploy

**Railway.app** — https://julius-fitness-gym-production-b755.up.railway.app/

- Branch `main` → deploy automat
- Production: MySQL + Resend pentru email
- Setări runtime: `storage/data/settingsData.json` (persistență volume)
- După deploy: `php artisan migrate --force`, `npm run build`

Documentație suplimentară: [`docs/`](docs/) · [`TESTING-BRIEF.md`](TESTING-BRIEF.md) · [`CLAUDE.md`](CLAUDE.md)

---

**Repository:** [github.com/Daniel-OD/Julius-Fitness-Gym](https://github.com/Daniel-OD/Julius-Fitness-Gym) · **License:** MIT
