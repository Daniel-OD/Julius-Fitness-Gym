# Installers — Julius Fitness Gym

Pachete de instalare pentru **Windows** (Inno Setup `.exe`) și **macOS** (DMG), pentru medii cu [Laravel Herd](https://herd.laravel.com).

Ambele variante copiază codul sursă (fără `vendor` / `node_modules`) și rulează instalarea Laravel la destinație.

## Cerințe pe mașina țintă (Windows și macOS)

| Componentă | Link |
|------------|------|
| Laravel Herd | https://herd.laravel.com |
| Composer | https://getcomposer.org |
| Node.js | https://nodejs.org |
| Internet | La prima rulare (`composer install`, `npm install`) |

**Folder recomandat:** `~/Herd/julius-fitness-gym` (Mac) sau `%USERPROFILE%\Herd\julius-fitness-gym` (Windows).

**URL:** http://julius-fitness-gym.test — numele folderului trebuie să corespundă în Herd.

După instalare, creează un utilizator admin Filament:

```bash
php artisan make:filament-user
```

---

## Windows — `.exe` (Inno Setup)

### Build (pe Windows)

1. [Inno Setup 6](https://jrsoftware.org/isinfo.php)
2. Node.js
3. `public/favicon.ico` (generat automat din SVG dacă ai ImageMagick `magick`)

```bat
installer\build-installer.bat
```

**Output:** `dist/Julius-Fitness-Gym-Setup-v1.0.exe`

### Instalare client

1. Instalează Herd, Composer, Node.
2. Rulează `.exe` → copiază în `%USERPROFILE%\Herd\julius-fitness-gym`.
3. `install.bat` rulează automat (post-install).
4. Shortcut desktop / Start Menu → `open.bat`.

### Fișiere

| Fișier | Rol |
|--------|-----|
| `julius-fitness-gym.iss` | Script Inno Setup |
| `build-installer.bat` | Compilează `.exe` |
| `check-prerequisites.bat` | Verifică PHP, Composer, Node |
| `../install.bat` | Setup Laravel |
| `../open.bat` | Deschide site-ul |

---

## macOS — DMG

### Build (pe Mac)

```bash
chmod +x installer/build-dmg.sh install.sh open.command open.sh installer/check-prerequisites.sh
./installer/build-dmg.sh
```

**Output:** `dist/Julius-Fitness-Gym-Setup-v1.0.dmg`

### Instalare client

1. Instalează Herd, Composer, Node.
2. Deschide DMG, citește `INSTALARE-macOS.txt`.
3. Copiază folderul în `~/Herd/julius-fitness-gym`.
4. În Terminal:

```bash
cd ~/Herd/julius-fitness-gym
./install.sh
```

5. Dublu-click `open.command` sau deschide http://julius-fitness-gym.test

### Instalare fără DMG (dezvoltare / clone git)

```bash
cd ~/Herd/julius-fitness-gym
./install.sh
./open.command
```

### Fișiere

| Fișier | Rol |
|--------|-----|
| `build-dmg.sh` | Creează DMG în `dist/` |
| `check-prerequisites.sh` | Verifică PHP, Composer, Node |
| `../install.sh` | Setup Laravel |
| `../open.command` | Deschide site-ul (Finder) |
| `../open.sh` | Deschide site-ul (Terminal) |

---

## Ce face instalarea

1. Verifică prerequisituri
2. Creează `database/database.sqlite` dacă lipsește
3. `composer install`
4. Copiază `.env.example` → `.env` dacă lipsește
5. `php artisan key:generate --force`
6. `php artisan migrate --force`
7. `npm install` + `npm run build`

**Nu rulează** `db:seed` — datele demo / admin trebuie create manual.

---

## Excluderi la packaging

Nu se copiază: `vendor/`, `node_modules/`, `.git/`, `.env`, cache-uri `storage/framework/*`, `dist/`.

Scripturile de build (`build-installer.bat`, `build-dmg.sh`, `julius-fitness-gym.iss`) nu intră în pachetul client macOS; pe Windows, `check-prerequisites.bat` este inclus explicit în `.exe`.
