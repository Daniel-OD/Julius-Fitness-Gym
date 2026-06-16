# Julius Fitness Gym — Ghid de deploy

Ghid practic pentru instalarea aplicației în diferite medii, cu focus pe **test în sală** și **demo Docker**.

---

## 1. Laptop Windows (test în sală)

### Varianta A — Docker Desktop (recomandat)

1. Instalează [Docker Desktop](https://www.docker.com/products/docker-desktop/)
2. Clonează repo-ul sau copiază pe stick
3. Urmează [DOCKER_SETUP.md](./DOCKER_SETUP.md):
   ```bat
   copy env\docker.env.example .env
   docker compose up -d --build
   docker compose exec app php artisan app:install --force --email=admin@julius.test --password=GymTest2026! --url=http://localhost
   docker compose exec app php artisan db:seed --force
   ```
4. Deschide **http://localhost/admin/login**

### Varianta B — Fără Docker (development local)

Vezi [GYM_TEST_README.md](./GYM_TEST_README.md):

```bat
scripts\gym-field-setup.bat
php artisan serve
```

### Varianta C — Installer Windows (Herd)

```bat
scripts\install.bat
```

Necesită [Laravel Herd](https://herd.laravel.com/windows).

---

## 2. Linux VPS (producție / staging)

### Cerințe VPS

- Ubuntu 22.04+ / Debian 12+
- 2 GB RAM minim (4 GB recomandat cu WorldSeeder)
- Docker Engine + Compose plugin

### Instalare Docker (Ubuntu)

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
# relogin
```

### Deploy

```bash
git clone https://github.com/Daniel-OD/Julius-Fitness-Gym.git
cd Julius-Fitness-Gym
cp env/docker.env.example .env
nano .env   # APP_URL=https://gym.domeniu.ro, parole sigure
docker compose up -d --build
docker compose exec app php artisan app:install --force \
  --email=admin@domeniu.ro \
  --password='ParolaSigura2026!' \
  --url=https://gym.domeniu.ro
```

### Firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### HTTPS

Folosește **Nginx Proxy Manager** sau **Caddy** în fața containerului (secțiunea 4).

---

## 3. Docker Desktop (Windows / macOS)

1. **Settings → Resources:** alocă minim 4 GB RAM
2. **Settings → General:** enable Docker Compose V2
3. Proiectul poate sta în orice folder; pe Windows evită path-uri foarte lungi
4. `docker compose up -d --build` din folderul proiectului
5. Acces: http://localhost

### macOS Apple Silicon

Imaginile folosesc `linux/amd64` implicit; Docker Desktop emulează ARM→x86. Build-ul poate dura mai mult — normal.

---

## 4. Nginx Proxy Manager (NPM)

Expunere HTTPS pe rețea locală sau internet:

```
┌──────────┐     ┌─────────────────┐     ┌──────────────────┐
│ Internet │────►│ NPM (443/80)    │────►│ julius nginx :80 │
└──────────┘     │ gym.domeniu.ro  │     └──────────────────┘
                 └─────────────────┘
```

### Pași NPM

1. Instalează [Nginx Proxy Manager](https://nginxproxymanager.com/) (Docker sau LXC)
2. **Hosts → Proxy Hosts → Add**
   - Domain: `gym.exemplu.ro`
   - Forward Hostname: IP serverului Julius (ex. `192.168.1.50`)
   - Forward Port: `80`
   - SSL: Let's Encrypt (dacă domeniul e public)
3. În `.env` pe serverul Julius:
   ```env
   APP_URL=https://gym.exemplu.ro
   ```
4. Recache:
   ```bash
   docker compose exec app php artisan config:cache
   ```

---

## 5. Cloudflare Tunnel (fără port forwarding)

Util când sală are internet dar **fără IP public** sau fără deschidere porturi.

1. Cont [Cloudflare](https://cloudflare.com) + domeniu
2. Instalează `cloudflared` pe PC-ul cu Docker:
   ```bash
   cloudflared tunnel create julius-gym
   cloudflared tunnel route dns julius-gym gym.exemplu.ro
   ```
3. Config `config.yml`:
   ```yaml
   tunnel: <TUNNEL-ID>
   credentials-file: /path/credentials.json
   ingress:
     - hostname: gym.exemplu.ro
       service: http://localhost:80
     - service: http_status:404
   ```
4. `APP_URL=https://gym.exemplu.ro` în `.env`
5. Pornește tunnel + Docker compose

> Tunnel-ul expune aplicația public — folosește parole puternice și schimbă credențialele implicite.

---

## 6. Setup recomandat pentru test în sală

| Aspect | Recomandare |
|--------|-------------|
| **Hardware** | Laptop dedicat, 8 GB RAM, SSD |
| **OS** | Windows 11 + Docker Desktop SAU Windows + `scripts\gym-field-setup.bat` |
| **Rețea** | Wi‑Fi sală; optional Cloudflare Tunnel pentru acces remote |
| **Backup** | Stick USB zilnic: dump MySQL + `settingsData.json` |
| **Recepție** | Bookmark **/office/login**; PC separat de admin |
| **Admin** | Bookmark **/admin/login**; logout la schimb tură |
| **Parolă** | `GymTest2026!` doar demo — schimbă în prima zi |
| **Queue** | Container `queue` activ în Docker; local: `php artisan queue:work` |
| **Email** | Resend (`MAIL_MAILER=resend`, `RESEND_API_KEY` în Environment); local: `MAIL_MAILER=log` |

### Flux zi de test

1. Dimineața: `docker compose up -d` (sau pornește laptopul)
2. Verifică http://localhost/admin
3. Check-in recepție pe /office
4. Seara: backup MySQL + oprește: `docker compose down` (volume păstrează datele)

---

## Documente relacionate

- [DOCKER_SETUP.md](./DOCKER_SETUP.md) — detalii tehnice containere
- [GYM_TEST_README.md](./GYM_TEST_README.md) — test fără Docker
- [DOCKER_AUDIT_REPORT.md](./DOCKER_AUDIT_REPORT.md) — audit modificări Docker

---

*Julius Fitness Gym · Daniel-OD*
