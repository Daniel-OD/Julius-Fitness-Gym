# Julius Fitness Gym — Configurare Docker

Ghid pentru rularea aplicației **Julius Fitness Gym** (Laravel 13 + Filament 5) în containere Docker pe **Windows**, **Linux** și **macOS**.

---

## Prezentare arhitectură

```
                    ┌─────────────┐
   Browser :80 ───► │    nginx    │  (servește public/, proxy PHP)
                    └──────┬──────┘
                           │ FastCGI :9000
                    ┌──────▼──────┐
                    │  app (FPM)  │  Laravel + Filament + API
                    └──────┬──────┘
                           │
         ┌─────────────────┼─────────────────┐
         │                 │                 │
  ┌──────▼──────┐   ┌──────▼──────┐   ┌──────▼──────┐
  │    mysql    │   │    queue    │   │ redis (opt) │
  │   (date)    │   │ queue:work  │   │   profil    │
  └─────────────┘   └─────────────┘   └─────────────┘

Volume persistent: julius_gym_mysql_data, julius_gym_storage
```

| Serviciu | Rol |
|----------|-----|
| **nginx** | Web server, port **80**, fișiere statice + PHP via FastCGI |
| **app** | PHP 8.4-FPM, Laravel, migrări automate la pornire |
| **mysql** | MySQL 8.0, baza de date principală |
| **queue** | `php artisan queue:work` — emailuri facturi, notificări |
| **redis** | Opțional (`--profile redis`) — cache/coadă alternativă |

---

## Cerințe

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows/macOS) sau Docker Engine + Compose v2 (Linux)
- Git
- ~2 GB spațiu disk (imagini + volume)

---

## Instalare

### 1. Clonează repository-ul

```bash
git clone https://github.com/Daniel-OD/Julius-Fitness-Gym.git
cd Julius-Fitness-Gym
```

### 2. Creează fișierul `.env`

```bash
cp .env.docker.example .env
```

Editează dacă e nevoie (parole MySQL, `APP_URL`).

### 3. Pornește containerele

```bash
docker compose up -d --build
```

Prima pornire durează câteva minute (build imagine + migrări).

### 4. Verifică statusul

```bash
docker compose ps
docker compose logs -f app
```

Aplicația: **http://localhost/admin/login**

---

## Ce face entrypoint-ul automat

La pornirea containerului **app**:

1. Creează `.env` din `.env.docker.example` (dacă lipsește)
2. Așteaptă MySQL
3. Restaurează `vendor/` și `public/build/` din imagine (dacă bind-mount-ul le golește)
4. Generează `APP_KEY` (dacă lipsește)
5. Creează `storage/data/settingsData.json`
6. `php artisan storage:link`
7. Șterge `public/hot` (forțează asset-uri compilate)
8. `php artisan migrate --force`
9. Cache config/rute/view (production)

---

## Configurare bază de date

Variabile în `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=julius_gym
DB_USERNAME=julius
DB_PASSWORD=julius_secret
MYSQL_ROOT_PASSWORD=root_secret
```

Datele MySQL persistă în volume-ul **`julius_gym_mysql_data`**.

### Acces direct MySQL (debug)

```bash
docker compose exec mysql mysql -ujulius -pjulius_secret julius_gym
```

---

## Migrări

Rulează automat la pornire. Manual:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan migrate:status
```

---

## Creare utilizator admin (`app:install`)

După prima pornire:

```bash
docker compose exec app php artisan app:install \
  --force \
  --email=admin@julius.test \
  --password='GymTest2026!' \
  --url=http://localhost
```

> **Parolă temporară pentru test:** schimb-o după primul login.  
> `app:install` **nu suprascrie** `DB_CONNECTION=mysql` dacă e deja setat.

### Date demo (planuri, membri, abonamente)

```bash
docker compose exec app php artisan db:seed --force
```

### Țări/monede (WorldSeeder — opțional, ~512 MB RAM)

```bash
docker compose exec app php -d memory_limit=512M artisan db:seed --class=WorldSeeder --force
```

---

## Comenzi utile

```bash
# Oprește
docker compose down

# Oprește + șterge volume (ATENȚIE: pierzi datele!)
docker compose down -v

# Rebuild după modificări cod
docker compose up -d --build

# Shell în container
docker compose exec app bash

# Queue manual (serviciul queue rulează deja)
docker compose exec app php artisan queue:work

# Scheduler (cron extern sau manual)
docker compose exec app php artisan schedule:run

# Loguri
docker compose logs -f nginx app queue mysql
```

---

## Backup

### Backup aplicație (ZIP — DB SQLite nu se aplică; folosește dump MySQL)

**Dump MySQL:**

```bash
docker compose exec mysql mysqldump -ujulius -pjulius_secret julius_gym > backup-$(date +%F).sql
```

**Setări club:**

```bash
docker compose cp app:/var/www/html/storage/data/settingsData.json ./settingsData-backup.json
```

**Upload-uri membri:**

```bash
docker compose cp app:/var/www/html/storage/app/public ./storage-app-public-backup
```

### Backup via artisan (dacă backup activat în Setări)

```bash
docker compose exec app php artisan app:backup --force
```

---

## Restore

### MySQL din dump

```bash
docker compose exec -T mysql mysql -ujulius -pjulius_secret julius_gym < backup-2026-06-03.sql
```

### Restore din ZIP (comandă Laravel)

```bash
docker compose exec app php artisan app:restore /var/www/html/storage/app/backup.zip --include-settings
```

---

## Actualizare din GitHub

```bash
git pull origin main
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## Redis (opțional)

```bash
# Pornește cu Redis
docker compose --profile redis up -d

# În .env:
# CACHE_STORE=redis
# QUEUE_CONNECTION=redis
# SESSION_DRIVER=redis
```

---

## Troubleshooting

| Problemă | Soluție |
|----------|---------|
| **Port 80 ocupat** | Setează `APP_PORT=8080` în `.env`, accesează http://localhost:8080 |
| **Pagină fără CSS** | Verifică `public/build/manifest.json`; rebuild: `docker compose up -d --build` |
| **502 Bad Gateway** | Așteaptă migrările: `docker compose logs app` |
| **SQLSTATE connection refused** | MySQL încă pornește; `docker compose ps` — healthcheck |
| **Permission denied storage** | `docker compose exec app chmod -R ug+rwx storage bootstrap/cache` |
| **APP_KEY missing** | `docker compose exec app php artisan key:generate --force` |
| **Windows: linii CRLF entrypoint** | `git config core.autocrlf input` și re-clonează sau convertește LF |
| **Filament 403** | Rulează `app:install`, verifică rol `super_admin` |

---

## Fișiere Docker

| Fișier | Descriere |
|--------|-----------|
| `Dockerfile` | Multi-stage: Node (Vite) + Composer + PHP 8.4-FPM |
| `docker-compose.yml` | Servicii app, nginx, mysql, queue, redis |
| `.dockerignore` | Exclude fișiere inutile din build |
| `docker/nginx/default.conf` | Config nginx Laravel |
| `docker/php/php.ini` | PHP 512M, opcache, upload 64M |
| `docker/entrypoint.sh` | Setup automat la pornire |
| `.env.docker.example` | Template mediu Docker |

---

*Stack: PHP 8.4 · Laravel 13 · Filament 5 · MySQL 8 · Nginx · Sanctum*
