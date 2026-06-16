# Julius Fitness Gym — Render Deployment Guide

Deploy **Julius Fitness Gym** (Laravel 13 + Filament 5) as a **Render Web Service** using Docker.

---

## Architecture

```
                    ┌─────────────────────────────┐
  HTTPS ──────────► │  Render Web Service       │
  (onrender.com)    │  Docker: production stage │
                    │  nginx → PHP 8.4-FPM      │
                    │  listens on $PORT         │
                    └─────────────┬─────────────┘
                                  │
                    ┌─────────────▼─────────────┐
                    │  Render PostgreSQL        │
                    │  (julius-gym-db)          │
                    └───────────────────────────┘

Optional:
  julius-gym-queue (Worker) → php artisan queue:work
```

| Component | Description |
|-----------|-------------|
| **Dockerfile** | Multi-stage: Node (Vite) → Composer → PHP 8.4-FPM → **production** (nginx + supervisor) |
| **render.yaml** | Blueprint: web service + PostgreSQL + optional queue worker |
| **entrypoint** | Migrations, `APP_KEY`, storage link, permissions, nginx `$PORT` config |

---

## Prerequisites

- [Render](https://render.com) account
- GitHub repository connected to Render
- No local `.env` required on Render — variables are set in the Dashboard or `render.yaml`

---

## Quick deploy (Blueprint)

1. Push this repository to GitHub.
2. In Render: **New → Blueprint**.
3. Select the repo — Render reads `render.yaml`.
4. Review resources:
   - `julius-fitness-gym` (Web Service, Docker)
   - `julius-gym-db` (PostgreSQL)
   - `julius-gym-queue` (Worker, optional)
5. Click **Apply**.

First deploy takes ~5–10 minutes (Docker build includes `composer install` + `npm run build`).

---

## Manual deploy (Dashboard)

1. **New → Web Service** → connect repo.
2. **Runtime:** Docker
3. **Dockerfile path:** `./Dockerfile`
4. **Docker target:** `production`
5. **Health check path:** `/up`
6. Create a **PostgreSQL** database and link env vars (see below).
7. Deploy.

---

## Required environment variables

| Variable | Value / source |
|----------|----------------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Generate in Render (or `php artisan key:generate --show` locally) |
| `APP_URL` | `https://your-service.onrender.com` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | From Render PostgreSQL (`host`) |
| `DB_PORT` | From Render PostgreSQL (`port`) |
| `DB_DATABASE` | From Render PostgreSQL (`database`) |
| `DB_USERNAME` | From Render PostgreSQL (`user`) |
| `DB_PASSWORD` | From Render PostgreSQL (`password`) |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `LOG_CHANNEL` | `stderr` (logs appear in Render dashboard) |

Render sets automatically:

| Variable | Purpose |
|----------|---------|
| `PORT` | HTTP port nginx listens on (do **not** override) |
| `RENDER` | Detected by entrypoint — skips local `env/docker.env.example` |

See `env/render.env.example` for a full template.

---

## First startup (automatic)

The Docker **entrypoint** runs on every container start:

1. Waits for PostgreSQL
2. Generates `APP_KEY` if missing
3. Creates `storage/data/settingsData.json` from example
4. Sets storage/bootstrap permissions (`www-data`, `775`)
5. `php artisan storage:link`
6. `php artisan migrate --force`
7. `php artisan app:cache` (production)
8. Configures nginx to listen on **`$PORT`**

---

## Create admin user

After the first successful deploy, open **Shell** on the web service:

```bash
php artisan app:install \
  --force \
  --email=admin@yourdomain.com \
  --password='YourSecurePassword123!' \
  --url=https://your-service.onrender.com
```

Then visit:

- Admin: `https://your-service.onrender.com/admin/login`
- Office: `https://your-service.onrender.com/office/login`

---

## Optional: demo data

```bash
php artisan db:seed --force
```

Countries/currencies (optional, ~512 MB RAM):

```bash
php -d memory_limit=512M artisan db:seed --class=WorldSeeder --force
```

---

## Queue worker

The blueprint includes `julius-gym-queue` (Worker service) using the same Docker image with:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --no-interaction
```

Required for queued invoice emails and PDF generation. On the **free plan**, workers spin down when idle — acceptable for demo, not for production email SLA.

---

## Storage & uploads

Render web services use an **ephemeral filesystem**. Files in `storage/app/public` (member photos) are **lost on redeploy** unless you:

- Attach a [Render Disk](https://render.com/docs/disks) mounted at `/var/www/html/storage/app`, or
- Configure S3-compatible object storage (future enhancement).

`storage/data/settingsData.json` (club settings) is recreated from example on fresh deploy if missing — back up via `php artisan app:backup` and store off-platform.

---

## Local Docker build test

Simulate the Render image locally:

```bash
docker build --target production -t julius-gym-render .
```

Build order in the `assets` stage: **composer install → npm ci → npm run build** (vendor must exist before Vite compiles `theme.css`).

```bash
docker run --rm -p 10000:10000 \
  -e PORT=10000 \
  -e APP_KEY=base64:YOUR_KEY_HERE \
  -e APP_ENV=production \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=host.docker.internal \
  -e DB_PORT=5432 \
  -e DB_DATABASE=julius_gym \
  -e DB_USERNAME=julius \
  -e DB_PASSWORD=secret \
  julius-gym-render
```

Open `http://localhost:10000/up` — should return `200 OK`.

---

## Updating from GitHub

Render auto-deploys on push to the connected branch (if enabled). Each deploy:

1. Rebuilds Docker image (Composer + npm build in image)
2. Runs entrypoint (migrate + cache)

After deploy, verify:

```bash
php artisan migrate:status
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| **Build fails at npm run build** | Ensure `package-lock.json` is committed |
| **No open ports / port scan timeout** | Ensure **Docker target = `production`**. HTTP must start before DB wait — see `docker/start-web.sh`. Check logs for `[start-web] Binding HTTP on 0.0.0.0:...` |
| **500 on first request** | Verify `APP_KEY` and PostgreSQL env vars |
| **CSS missing** | Assets are built in Docker image — rebuild service |
| **Session lost after deploy** | Expected on ephemeral disk; use `SESSION_DRIVER=database` (default in blueprint) |
| **Queue emails not sent** | Ensure `julius-gym-queue` worker is running |
| **APP_URL mismatch** | Set `APP_URL` to exact Render URL, then redeploy |

### View logs

Render Dashboard → your service → **Logs** (stdout/stderr from nginx, PHP-FPM, Laravel).

### Clear cache (Shell)

```bash
php artisan app:cache --clear
php artisan app:cache
```

---

## Files reference

| File | Purpose |
|------|---------|
| `Dockerfile` | Multi-stage build; **`assets`** stage runs composer then npm; **`production`** target for Render |
| `render.yaml` | Render Blueprint (web + DB + worker) |
| `.dockerignore` | Excludes dev files from build context |
| `docker/entrypoint.sh` | Startup: DB wait, migrate, permissions, `$PORT` nginx |
| `docker/start-web.sh` | Starts php-fpm (background) + nginx on `$PORT` (foreground) |
| `docker/nginx/render.conf.template` | Nginx config with `${PORT}` |
| `env/render.env.example` | Environment variable reference |

---

## Local development vs Render

| | Local (`docker compose`) | Render |
|--|--------------------------|--------|
| Database | MySQL 8 | PostgreSQL |
| Docker target | `app` + separate nginx | `production` (all-in-one) |
| Port | 80 | `$PORT` (Render-assigned) |
| Env file | `env/docker.env.example` | Render Dashboard / `render.yaml` |

Use `docker compose up` for local multi-container dev; use Render Blueprint for cloud deployment.

---

*Stack: PHP 8.4 · Laravel 13 · Filament 5 · PostgreSQL · Nginx · Docker*
