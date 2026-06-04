# Security Policy — Julius Fitness Gym

## Supported Versions

| Version | Supported |
|---------|-----------|
| main    | ✅        |

## Reporting a Vulnerability

Please **do not** open a public GitHub issue for security vulnerabilities.

Report vulnerabilities privately to the maintainer:
- **Email:** Use GitHub's private vulnerability reporting feature (`Security → Report a vulnerability`) on this repository.
- Expect an acknowledgement within 48 hours.
- We aim to release a fix within 14 days for critical issues.

## Security Practices

### Authentication & Access Control

- **API:** Sanctum bearer-token authentication on all `/api/v1` routes except `/auth/login`.
- **Admin panel:** Filament Shield (Spatie Permission) with explicit role/permission grants. `super_admin.define_via_gate = false` — no implicit gate bypass.
- **Office panel:** Role-based access (`employee` role restricts access to `/office` only).
- **First-login:** Users created via `app:install` with a generated password are forced to change it before accessing any panel page (`RequirePasswordChange` middleware).

### Credentials

- Installation no longer uses a default password. A random 16-character password (`Str::password(16)`) is generated at install time, displayed **once** in the terminal, and **not stored** in plaintext anywhere.
- `storage/app/install-credentials.txt` contains only the admin email and URL — no password.
- The credentials file is gitignored via `storage/app/.gitignore`.

### Rate Limiting

| Endpoint | Limit |
|----------|-------|
| `POST /api/v1/auth/login` | 10 req/min per IP |
| All other authenticated API routes | 60 req/min per user |
| Filament login | Filament built-in throttle |

### File Uploads

Photo uploads (Member, User) accept only `jpg`, `jpeg`, `png`, `webp` with a 5 MB maximum. The `mimes` rule validates the actual file content, not just the extension.

### Database

- All user-controlled inputs pass through Laravel form request validation before reaching the database.
- `checkin_token` and `code` (auto-generated fields on `Member`) are excluded from `$fillable` and cannot be overwritten via API.
- All models use `SoftDeletes`; permanent deletion requires an explicit `ForceDelete:*` permission.

### Dependency Scanning

`composer audit` is run on every CI push/PR. A failing audit blocks the security CI job.

## Known Limitations

- `policies` (Spatie) are registered but not invoked by the REST API layer — the API uses `requirePermission()` directly. Policy logic (e.g. custom ownership checks) applies only to the Filament panel.
- `must_change_password` is not enforced for Sanctum API tokens — a token minted before the flag is set remains valid. This is noted as a future hardening item.
