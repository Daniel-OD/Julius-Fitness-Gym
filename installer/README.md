# Installers — Julius Fitness Gym

Instalare automată pentru **Windows** (`.exe`) și **macOS** (`.dmg` + `.app`), cu [Laravel Herd](https://herd.laravel.com).

## Ce face instalarea automat

| Pas | Automat |
|-----|---------|
| Verificare PHP / Herd | ✅ |
| Composer + NPM | ✅ doar dacă lipsește `vendor` / `public/build` din pachet |
| `.env`, cheie app, migrări | ✅ |
| `storage:link` | ✅ |
| `herd link` + `herd init` | ✅ dacă CLI Herd e disponibil |
| User admin Filament (`super_admin`) | ✅ `php artisan app:install` |
| Credențiale în fișier | ✅ `storage/app/install-credentials.txt` |
| Shortcut desktop (Windows) | ✅ Inno Setup |
| Aplicație `.app` (macOS) | ✅ în DMG — deschide /admin, rulează install la prima utilizare |

**Implicit admin:** `admin@julius.test` / `julius2024` — schimbă parola după login.

**Pe mașina client:** doar **Laravel Herd** este obligatoriu (pachetul include `vendor` + assets build-uite).

---

## Build pachet distribuție

### Windows (pe PC cu Inno Setup)

```bat
installer\build-installer.bat
```

→ `dist/Julius-Fitness-Gym-Setup-v1.0.exe`

### macOS (pe Mac)

```bash
chmod +x installer/build-dmg.sh install.sh installer/*.sh
./installer/build-dmg.sh
```

→ `dist/Julius-Fitness-Gym-Setup-v1.0.dmg` + `dist/Julius Fitness Gym.app`

---

## Instalare client Windows

1. Instalează [Herd pentru Windows](https://herd.laravel.com/windows)
2. Rulează `Julius-Fitness-Gym-Setup-v1.0.exe`
3. Așteaptă finalizarea (fără ferestre — rulează ascuns)
4. Dublu-click shortcut **Julius Fitness Gym** pe Desktop

---

## Instalare client macOS

1. Instalează [Herd pentru Mac](https://herd.laravel.com)
2. Deschide DMG, copiază tot în `~/Herd/julius-fitness-gym`
3. Dublu-click **Julius Fitness Gym.app** (instalează la prima rulare)
4. Opțional: trage `.app` pe Desktop

---

## Instalare din git (dezvoltare)

```bash
cd ~/Herd/julius-fitness-gym
./install.sh
./open.command
```

Sau: `composer run setup` apoi `php artisan app:install`.

---

## Comenzi utile

```bash
php artisan app:install --force          # resetează parola admin
php artisan app:install --email=... --password=...
```

---

## Fișiere cheie

| Fișier | Rol |
|--------|-----|
| `install.bat` / `install.sh` | Intrare instalare |
| `installer/post-install.*` | Logică completă |
| `installer/check-prerequisites.*` | Verificări (Herd obligatoriu) |
| `app/Console/Commands/InstallApplication.php` | Admin + `.env` + credențiale |
| `herd.yml` | Config Herd (`herd init`) |
| `open.bat` / `open.command` | Deschide `/admin` |
