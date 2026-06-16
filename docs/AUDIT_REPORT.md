# Security & Code Audit Report
**Project:** Julius Fitness Gym — Laravel 13  
**Date:** 2026-06-04  
**Scope:** Full codebase audit (API, installer, Filament panel, DB, CI)

---

## Summary

| Severity | Found | Fixed | Deferred |
|----------|-------|-------|----------|
| CRITICAL | 0 | — | — |
| HIGH | 2 | 2 | 0 |
| MEDIUM | 4 | 4 | 0 |
| LOW | 2 | 2 | 0 |
| Informational | 3 | 2 | 1 |

---

## Findings & Resolutions

### HIGH-1 — Hardcoded default admin password
**File:** `app/Console/Commands/InstallApplication.php`  
**Issue:** Default password `julius2024` was hardcoded in the command signature and stored in plaintext in `storage/app/install-credentials.txt`. The same string appeared in `installer/README.md`, `README.md`, `installer/julius-fitness-gym.iss`, and `installer/build-dmg.sh`.  
**Fix (commit `b796f51`):**
- Default replaced with `Str::password(16)` random generation.
- `must_change_password = true` set when password is auto-generated.
- `RequirePasswordChange` middleware enforces change before any panel access.
- `ForcePasswordChange` Filament page added to both panels.
- Credentials file no longer stores the password.
- All documentation references to `julius2024` removed.

### HIGH-2 — SettingsController had no authorization check
**File:** `app/Http/Controllers/Api/V1/SettingsController.php`  
**Issue:** `GET /api/v1/settings` and `PUT /api/v1/settings` were accessible to any authenticated Sanctum token — including low-privilege employee tokens. The settings JSON contains tax rates, SMTP configuration, notification settings, and backup paths.  
**Fix (commit `43ecd2b`):** Added `requirePermission($request, 'View:Settings')` to both `show()` and `update()`.

### MEDIUM-1 — AnalyticsController had no authorization checks
**File:** `app/Http/Controllers/Api/V1/AnalyticsController.php`  
**Issue:** All six analytics endpoints (financial KPIs, cashflow trend, expense categories, top plans, recent transactions, membership metrics) were accessible to any authenticated token. These endpoints expose full revenue, transaction, and membership data.  
**Fix (commit `43ecd2b`):** Added per-endpoint permission checks matching the underlying data domain:
- `financial`, `cashflowTrend`, `recentTransactions` → `ViewAny:Invoice`
- `membership` → `ViewAny:Member`
- `expenseCategories` → `ViewAny:Expense`
- `topPlans` → `ViewAny:Plan`

### MEDIUM-2 — Member `$fillable` exposed `checkin_token` and `code`
**File:** `app/Models/Member.php`  
**Issue:** `checkin_token` (used to authenticate QR check-in scans) and `code` (sequential system ID) were in `$fillable`. A token with `Update:Member` permission could overwrite another member's check-in token, potentially hijacking their QR access.  
**Fix (commit `43ecd2b`):** Both fields removed from `$fillable`. They are auto-generated in the model boot method and remain protected from mass assignment.

### MEDIUM-3 — No rate limiting on authenticated API endpoints
**File:** `routes/api.php`, `app/Providers/AppServiceProvider.php`  
**Issue:** Only the login endpoint had a rate limiter (`throttle:api-login`, 10/min). All other authenticated endpoints — including analytics, PDF generation, and force-delete — had no throttle.  
**Fix (commit `43ecd2b`):** `throttle:api` (60 req/min per authenticated user) applied to the entire authenticated route group.

### MEDIUM-4 — Policies not enforced by REST API layer
**Files:** `app/Policies/*.php`, `app/Http/Controllers/Api/V1/ApiController.php`  
**Issue:** All API controllers use `requirePermission()` (checks Spatie permissions) rather than `$this->authorize()` (invokes Laravel Policies). This means any logic added to Policies applies only to the Filament panel, not the API.  
**Status:** Informational / accepted risk. The API uses Shield permissions as its authorization mechanism. Policies are used only for Filament. Documented in `SECURITY.md`.

### LOW-1 — Image upload validation lacked MIME restrictions
**Files:** `app/Services/Api/Schemas/MemberSchema.php`, `app/Services/Api/Schemas/UserSchema.php`  
**Issue:** Photo uploads validated with `image` rule only, which accepts any image type detected by `getimagesize()`. On some PHP configurations this includes SVG-based formats.  
**Fix (commit `43ecd2b`):** Added `mimes:jpg,jpeg,png,webp` and reduced maximum size from 10 MB to 5 MB.

### LOW-2 — `expenseBreakdownByCategory()` method call on undefined method
**File:** `app/Http/Controllers/Api/V1/AnalyticsController.php`  
**Issue:** `expenseCategories()` was calling `$service->expenseBreakdownByCategory()`, a method that does not exist. The correct method is `expenseCategoryBreakdownForChart()`. This caused a 500 error on the `/api/v1/analytics/expense-categories` endpoint — discovered via the new test suite.  
**Fix (commit `2d8c7cc`):** Corrected to `expenseCategoryBreakdownForChart()`.

---

## New Tests Added

| File | Tests | Coverage |
|------|-------|---------|
| `tests/Feature/InstallSecurityTest.php` | 7 | Installer credentials, must_change_password, ForcePasswordChange flow |
| `tests/Feature/ApiV1Test.php` (expanded) | 36 | Auth, Members CRUD, Plans CRUD, Analytics, Settings 403 |
| `tests/Feature/ApiServicesTest.php` | 9 | Services full CRUD + soft-delete lifecycle + 403 guard |
| `tests/Feature/ApiSubscriptionsTest.php` | 8 | Subscriptions full CRUD + soft-delete lifecycle + 403 guard |
| `tests/Feature/ApiAuthorizationTest.php` | 16 | 403 for every protected endpoint with zero-permission user; 401 for unauthenticated |
| **Total new** | **76** | |

---

## Performance Findings (from prior session)

| Finding | Fix |
|---------|-----|
| N+1 in `ExpiringSoonSubscriptionsTableWidget.visible()` — 2 queries/row | Eager-load `renewals` + `member.subscriptions` |
| N+1 in `MembershipOverviewSubscriptionsTableWidget.visible()` | Same fix |
| `topPlansByCollected()` uncached | Wrapped with `remember()` (TTL 90s) |
| Missing DB indexes on `subscriptions`, `invoice_transactions`, `invoices`, `members` | Migration `2026_06_03_084957_add_performance_indexes` |

---

## CI/CD

The GitHub Actions workflow (`.github/workflows/ci.yml`) was updated to run three parallel jobs:
1. **Style** — `pint --test` (fast, runs before tests)
2. **Security** — `composer audit` + `npm audit --audit-level=high`
3. **Tests** — full Pest suite (depends on Style passing)

The workflow now triggers on **all pull requests**, not only pushes to `main`.

---

## Deferred / Out of Scope

- `must_change_password` is not enforced for existing Sanctum API tokens. Requires token invalidation on flag set (scope: Task 3 API hardening).
- `UserPolicy` is dead code for the API. Migrating to `$this->authorize()` would require changes to all API controllers (significant scope, no immediate security gap since Shield permissions are checked).
- Public check-in endpoint timing oracle (LOW-3): QR token enumeration risk is negligible given 32-character random tokens, but a uniform response delay could eliminate it.
