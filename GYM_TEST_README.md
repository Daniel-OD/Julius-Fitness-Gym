# Julius Fitness Gym — Ghid test în sală

Document pentru instalarea și testarea aplicației **Julius Fitness Gym** pe un PC din sală (Windows sau Mac), fără acces la internet permanent sau fără echipă de dezvoltare la fața locului.

---

## Scopul aplicației

Julius Fitness Gym este o aplicație de management pentru săli de fitness. Permite:

- gestionarea **membrilor** și a **abonamentelor**
- **facturare** și **încasări** (facturi + tranzacții)
- **check-in / check-out** la recepție (QR sau manual)
- **lead-uri** (enquiries) și **follow-up-uri**
- **cheltuieli** și rapoarte financiare pe dashboard
- **setări** ale clubului (nume, monedă, facturi, backup)
- interfață **admin** (management complet) și **office** (recepție)
- **API REST v1** (Sanctum) pentru integrări viitoare
- **roluri și permisiuni** (Filament Shield): `super_admin`, `owner`, `employee`

---

## Cerințe tehnice

| Componentă | Minim | Recomandat |
|------------|-------|------------|
| **PHP** | 8.4+ | Laravel Herd (include PHP + SQLite) |
| **Extensii PHP** | `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip` | Vezi `php.ini.example` pe Windows |
| **Composer** | 2.x | [getcomposer.org](https://getcomposer.org) |
| **Node.js** | 20+ LTS | [nodejs.org](https://nodejs.org) — necesar pentru `npm run build` |
| **Bază de date** | SQLite (`database/database.sqlite`) | Nu necesită MySQL pentru test local |
| **Browser** | Chrome / Edge / Firefox recent | — |
| **Spațiu disk** | ~500 MB (cu `vendor` + `node_modules`) | — |

**Opțional:** [Laravel Herd](https://herd.laravel.com) (Windows/Mac) — simplifică PHP, domeniu local (`julius-fitness-gym.test`).

**Nu este necesar** pentru testul de bază: MySQL, Redis, server de email (mail merge în `log`).

---

## Instalare rapidă

### Variantă A — Script dedicat test sală (recomandat)

**Windows** (din folderul proiectului):

```bat
scripts\gym-field-setup.bat
```

**Mac / Linux:**

```bash
chmod +x scripts/gym-field-setup.sh
./scripts/gym-field-setup.sh
```

Scriptul: `composer install`, `.env`, migrări, `npm run build` (dacă Node există), admin de test, date demo (`db:seed`).

### Variantă B — Composer setup + install manual

```bash
composer run setup
php artisan app:install --force --email=admin@julius.test --password=GymTest2026! --url=http://127.0.0.1:8000
php artisan db:seed
php artisan storage:link
```

### Variantă C — Installer existent (Herd / Inno Setup)

```bat
install.bat
```

sau pe Mac: `./install.sh`

Instalatorul rulează `installer/post-install.*`: migrări, `app:install`, link Herd. **Atenție:** fără `--password`, `app:install` generează o **parolă aleatorie** afișată o singură dată în terminal — noteaz-o imediat.

### Date demo (opțional, după setup)

```bash
php artisan db:seed
```

Încarcă: 3 planuri (Lunar / Trimestrial / Anual), 5 membri, 5 abonamente (activ, expirat, expiră curând).

### Țări / monede (setări avansate)

Doar dacă ai nevoie de listă completă țări/monede în Setări:

```bash
php -d memory_limit=512M artisan db:seed --class=WorldSeeder
```

---

## Pornire aplicație

### Cu `php artisan serve` (simplu, orice PC)

```bash
php artisan serve
```

Deschide:

- **Admin:** http://127.0.0.1:8000/admin/login  
- **Recepție:** http://127.0.0.1:8000/office/login  

Shortcut Windows: dublu-click `open-local.bat`

### Cu Herd (dacă e instalat)

```bash
herd link julius-fitness-gym
```

Apoi: http://julius-fitness-gym.test/admin/login (sau `open.bat` / `open.command`)

### Development complet (server + Vite + queue + loguri)

```bash
composer run dev
```

Folosește-l doar dacă modifici CSS/JS; pentru test în sală e suficient `php artisan serve` **după** `npm run build`.

### Queue (email facturi, notificări)

Pentru test local, joburile pot rămâne în coadă. Pentru procesare:

```bash
php artisan queue:work
```

---

## Date de acces pentru test

| Rol | URL login | Email (setup sală) | Parolă |
|-----|-----------|-------------------|--------|
| **Administrator** | `/admin/login` | `admin@julius.test` | `GymTest2026!` |

> **Parolă temporară:** `GymTest2026!` este doar pentru test local. **Schimb-o imediat** după primul login (Setări cont sau pagina de schimbare forțată dacă parola a fost generată aleator).

### Cont angajat recepție (de creat manual)

1. Login ca admin → **Users** → utilizator nou  
2. Rol: **`employee`** (creat automat de `app:install` / `EmployeeRoleSeeder`)  
3. Login angajat: **http://127.0.0.1:8000/office/login**  
4. Angajatul **nu** are acces la `/admin`

### super_admin la recepție

Dacă adminul ține recepția: login la **`/office/login`** (sesiune legată de panoul office). Pentru management: logout → login la **`/admin/login`**.

---

## Funcționalități disponibile în prezent

### Panou Admin (`/admin`)

| Modul | Ce poți face |
|-------|----------------|
| **Dashboard** | Statistici check-in azi, overview sală, metrici financiare/membership, grafice, tranzacții recente, abonamente fără factură |
| **Membri** | CRUD, cod membru, QR check-in, import CSV, abonamente asociate |
| **Planuri** | Planuri abonament (zile, preț, status) |
| **Servicii** | Servicii add-on |
| **Abonamente** | Creare, reînnoire, status (ongoing, expiring, expired, etc.), tip official/internal |
| **Check-in-uri** | Listă check-in/out, check-in manual din toolbar |
| **Facturi** | Emitere, PDF, plăți, tranzacții, status overdue |
| **Cheltuieli** | Înregistrare cheltuieli, categorii |
| **Enquiries** | Lead-uri + follow-up-uri |
| **Follow-ups** | Urmărire lead-uri |
| **Utilizatori** | Conturi staff |
| **Roluri** (Shield) | Permisiuni pe resurse |
| **Setări** | Club, facturi, membri, check-in, backup, notificări, localizare EN/RO |

### Panou Office (`/office`)

| Modul | Ce poți face |
|-------|----------------|
| **Dashboard recepție** | Check-in-uri azi, prezenți acum, abonamente expirate / expiră curând |
| **Check-in-uri** | Înregistrare manuală, listă zilnică |

### Check-in public (QR)

- URL membru: `/checkin/{token}` (generat automat la crearea membrului)
- Check-out: POST `/checkin/{token}/checkout`
- Configurabil în **Setări → Check-in** (abonament activ obligatoriu, auto check-out, alertă expirat)

### API REST (`/api/v1`)

Autentificare Sanctum (`POST /api/v1/auth/login`). Resurse: users, members, plans, services, subscriptions, invoices, transactions, expenses, enquiries, follow-ups, analytics, settings, roles, permissions.

### Comenzi programate (cron / Task Scheduler)

| Comandă | Rol |
|---------|-----|
| `gymie:invoices --mark-overdue` | Facturi restante |
| `gymie:subscriptions` | Actualizare status abonamente |
| `gymie:subscription-expiry-notifications` | Notificări expirare |
| `app:backup` | Backup ZIP (dacă activat în Setări) |

Pentru test de o zi, rulează manual dacă e nevoie:

```bash
php artisan gymie:subscriptions
php artisan gymie:invoices --mark-overdue
```

---

## Flux recomandat pentru testul la sală

### Înainte de deschidere (30 min)

1. Rulează `scripts/gym-field-setup.bat` (sau Variantă B).  
2. Verifică login admin — pagina **trebuie să aibă stiluri** (dark theme Filament). Dacă e HTML simplu → rulează `npm run build`.  
3. **Setări:** nume sală, monedă (RON), timezone, logo (opțional).  
4. **Backup:** Setări → Backup → activează, folder ex. `D:\JuliusBackup`, rulează `php artisan app:backup --force`.  
5. Creează 1 cont **employee** pentru recepție.  
6. Printează / salvează QR pentru 1–2 membri demo.

### În timpul programului (2–4 ore)

1. **Recepție:** check-in manual + scan QR (telefon → URL check-in).  
2. **Admin:** membru nou → plan → abonament → factură → încasare.  
3. **Lead:** enquiry → follow-up.  
4. **Cheltuială:** o intrare simplă.  
5. **Schimb tură:** **Deconectare** la schimbarea personalului (sesiune legată de panou).  
6. Notează orice bug, ecran lent, mesaj neclar.

### La final

1. `php artisan app:backup --force`  
2. Copiază `database/database.sqlite` + `storage/data/settingsData.json` pe stick/cloud.  
3. Completează checklist-ul de mai jos.

---

## Ce trebuie verificat în timpul testului

- [ ] Login admin + schimbare parolă temporară  
- [ ] Login employee doar pe `/office`  
- [ ] Dashboard afișează date după check-in / factură  
- [ ] Creare membru + abonament + factură + plată parțială/totală  
- [ ] PDF factură se deschide  
- [ ] Check-in QR de pe telefon  
- [ ] Check-in manual la recepție  
- [ ] Check-out  
- [ ] Membru fără abonament activ — mesaj așteptat (warning)  
- [ ] Enquiry + follow-up  
- [ ] Cheltuială  
- [ ] Setări salvate (nume sală, monedă)  
- [ ] Comutare limbă EN/RO (header admin)  
- [ ] Logout la schimb de persoană pe același PC  
- [ ] Backup creat și fișier ZIP valid  

---

## Limitări cunoscute

| Limitare | Detaliu |
|----------|---------|
| **SQLite** | Un singur fișier DB — pot apărea lock-uri rare la trafic mare; OK pentru demo. |
| **Email** | `MAIL_MAILER=log` — emailurile nu se trimit real; apar în `storage/logs/laravel.log`. |
| **Queue** | Fără `queue:work`, emailurile/PDF-urile queued pot întârzia. |
| **CSS lipsă** | Fără `npm run build`, panourile Filament apar fără stil. |
| **Parolă installer** | `install.bat` fără `--password` → parolă aleatorie, o singură afișare. |
| **Herd vs serve** | `APP_URL` trebuie să corespundă URL-ului folosit (127.0.0.1 vs julius-fitness-gym.test). |
| **WorldSeeder** | Necesită ~512 MB RAM; opțional pentru test minim. |
| **PC partajat** | Dacă admin rămâne logat pe `/admin`, angajatul poate accesa admin dacă știe URL-ul — folosește logout sau PC separat la recepție. |
| **Producție** | Această configurație este pentru **test local**, nu pentru hosting public fără hardening suplimentar. |

---

## Backup și restore

### Ce se salvează

- `database/database.sqlite` — toate datele  
- `storage/data/settingsData.json` — setări club (nume, monedă, backup, check-in)  
- Opțional: `storage/app/public/` — poze membri  

### Backup manual (recomandat în test)

```bash
php artisan app:backup --force
```

Configurează calea în **Admin → Setări → Backup** sau editează `settingsData.json`:

```json
"backup": {
    "enabled": true,
    "path": "D:/JuliusBackup",
    "keep_backups": 7
}
```

### Backup rapid (copiere fișiere)

Oprește aplicația, copiază:

```
database/database.sqlite
storage/data/settingsData.json
```

### Restore din ZIP

```bash
php artisan app:restore "D:\JuliusBackup\julius-gym-backup-2026-06-03_22-00-00.zip"
```

Cu setări:

```bash
php artisan app:restore "cale\backup.zip" --include-settings
```

Restore-ul creează automat un backup de siguranță înainte (dacă backup-ul e configurat).

---

## Troubleshooting

| Problemă | Soluție |
|----------|---------|
| **403 Forbidden** la `/admin` | Cont `employee` — folosește `/office`. Admin: `super_admin` via `app:install`. |
| **Pagină fără design** | `npm install && npm run build` |
| **could not find driver (sqlite)** | Activează `pdo_sqlite` în PHP; pe Windows vezi `php.ini.example` |
| **Vite manifest missing** | `npm run build` |
| **500 la Setări / țări** | Rulează `WorldSeeder` sau completează manual moneda în setări |
| **Parolă uitată** | `php artisan app:install --force --email=admin@julius.test --password=GymTest2026!` |
| **Migrări eșuate** | `php artisan migrate --force` |
| **Permisiuni resurse 404** | `php artisan shield:generate --all --panel=admin --option=permissions` |
| **Check-in nu merge** | Setări → Check-in enabled; membru activ; token QR valid |
| **APP_URL greșit** | Actualizează `.env` `APP_URL=` apoi `php artisan config:clear` |

### Verificare PHP

```bash
php -m | findstr /i sqlite mbstring
php artisan about
```

### Teste automate (pe PC dev)

```bash
php artisan test --compact
```

Necesită `pdo_sqlite` activ în PHP CLI.

---

## Checklist final pentru ziua testului

### Pregătire (acasă / birou)

- [ ] Repo clonat / copiat pe stick  
- [ ] `scripts/gym-field-setup.bat` rulat cu succes  
- [ ] `public/build/manifest.json` există  
- [ ] `php artisan test --compact` — trecut (ideal)  
- [ ] Laptop încărcat, încărcător, stick backup  

### La sală — dimineața

- [ ] Wi‑Fi / rețea locală OK  
- [ ] `php artisan serve --host=0.0.0.0 --port=8000` (dacă test de pe telefon în aceeași rețea)  
- [ ] Login admin OK  
- [ ] Parolă temporară schimbată  
- [ ] Setări club completate  
- [ ] Employee creat  
- [ ] Backup inițial  

### În timpul testului

- [ ] Flux membru → abonament → factură → plată  
- [ ] Check-in QR + manual  
- [ ] Recepție pe `/office`  
- [ ] Logout la schimb tură  

### După test

- [ ] Backup final  
- [ ] Copiere DB + settings pe stick  
- [ ] Notițe bug-uri / feedback  

---

## Comenzi utile (referință rapidă)

```bash
composer run setup
php artisan app:install --force --email=admin@julius.test --password=GymTest2026! --url=http://127.0.0.1:8000
php artisan db:seed
php artisan serve
php artisan app:backup --force
php artisan app:restore "cale\backup.zip" --include-settings
php artisan gymie:subscriptions
php artisan optimize:clear
```

---

*Document generat pentru test de teren — Julius Fitness Gym. Versiune stack: Laravel 13, Filament 5, PHP 8.4+, SQLite.*
