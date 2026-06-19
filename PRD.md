# Julius Fitness Gym — Product Requirements Document

**Versiune:** 1.0
**Data:** 2026-06-19
**Stack:** Laravel 13 · PHP 8.4 · Filament 5 · Livewire 4 · Tailwind CSS v4 · Pest 4
**Status aplicație:** ✅ Producție-ready — 12/13 module complet implementate, 1 stub (public website)

---

## 1. Scopul aplicației

Sistem complet de management pentru sala de fitness Julius Gym. Acoperă întreg ciclul de viață al unui client: de la primul contact (enquiry/lead) până la abonament activ, check-in zilnic, facturare și notificări de expirare.

**Utilizatori primari:**
- **Admin** — configurează și supraveghează întreaga activitate
- **Employee (recepție)** — înregistrează membri, procesează check-in, reînnoiește abonamente
- **Member (client)** — acces self-service la abonament, factură și cod QR personal

---

## 2. Roluri și acces

| Rol | Interfață | Poate face |
|-----|-----------|-----------|
| **Super Admin** | `/admin` — acces complet | Tot, inclusiv Settings, Users, Roles |
| **Admin** | `/admin` — acces complet | Toată gestiunea operațională + rapoarte |
| **Employee** | `/admin` — acces limitat prin Shield | Check-in, Members, Subscriptions, Enquiries |
| **Member** | `/member` — portal dedicat | Vizualizare profil propriu, cod QR, abonament activ, descărcare PDF factură, schimbare parolă |
| **Guest** | `/member/login`, `/member/register` | Autentificare, înregistrare self-service, forgot password |

Permisiunile sunt gestionate prin **Filament Shield** (spatie/laravel-permission). Fiecare resursă are permisiuni granulare: `view`, `create`, `update`, `delete`, `restore`, `force_delete`.

---

## 3. Module și status

### 3.1 Members (Membri)
**Status:** ✅ Complet

**Descriere:** Gestionarea completă a bazei de membri — de la înregistrare până la dezactivare.

**Funcționalități implementate:**
- [x] CRUD complet cu soft delete, restore, force delete
- [x] Wizard modal "Membru nou" din ListMembers (2 pași: date personale → abonament+factură)
- [x] Pre-fill din enquiry la crearea din `CreateMember?enquiry_id=X` (cale fallback)
- [x] Auto-generare cod membru (prefix + sequence din Settings)
- [x] Auto-generare token check-in QR unic
- [x] Status auto-sincronizat (active/inactive) prin `SubscriptionObserver` după orice modificare de abonament
- [x] Upload foto cu editor de imagine
- [x] Filtre tabs: All / Active / Inactive
- [x] Acțiuni pe rând: mark active/inactive, send portal invitation, reset password, view QR
- [x] Import CSV bulk via Settings → Import tab (`MemberImportService` cu analyze + importChunk)
- [x] RelationManager abonamente și check-in-uri din ViewMember
- [x] API v1 CRUD complet cu `spatie/laravel-query-builder`

**Funcționalități lipsă:** —

---

### 3.2 Subscriptions (Abonamente)
**Status:** ✅ Complet

**Descriere:** Gestionarea ciclului de viață al abonamentelor — creare, reînnoire, expirare, anulare.

**Funcționalități implementate:**
- [x] CRUD complet cu soft delete
- [x] Creare abonament cu factură inclusă (nested Repeater în formular)
- [x] Auto-calcul dată de end bazat pe planul ales (`Helpers::calculateSubscriptionEndDate`)
- [x] Reînnoire din modal wizard (plan, date, factură) prin `SubscriptionRenewalService`
- [x] Status transitions: ongoing → expiring → expired → renewed / cancelled
- [x] Notificări automate de expirare la 7, 3, 1, 0 zile (la admini + email la client)
- [x] Acțiune `notify_expiration` manuală per abonament sau bulk
- [x] Filtrare per panel: `/admin` vede abonamente "official", `/office` vede toate
- [x] RelationManager facturi din ViewSubscription
- [x] Acțiune reînnoire și din widget "Expiring Soon" pe Dashboard
- [x] API v1 CRUD + endpoint `renew`

**Funcționalități lipsă:** —

---

### 3.3 Plans & Services (Planuri și Servicii)
**Status:** ✅ Complet

**Descriere:** Catalogul de planuri de abonament grupate pe servicii (Fitness, Yoga, Înot etc.).

**Funcționalități implementate:**
- [x] CRUD Services cu soft delete (name, description)
- [x] CRUD Plans cu soft delete (name, code unic, service, days, amount, description)
- [x] Status planuri: active/inactive cu toggle rapid
- [x] Auto-afișare simbol valută din Settings în câmpul amount
- [x] Cascaded soft delete: ștergerea unui Service șterge planurile asociate
- [x] Planurile active apar în selector abonament, wizard onboarding și portal membre
- [x] API v1 CRUD complet pentru ambele

**Funcționalități lipsă:** —

---

### 3.4 Invoices & Payments (Facturi și Plăți)
**Status:** ✅ Complet

**Descriere:** Facturare automată legată de abonamente, tranzacții de plată/rambursare, PDF, email.

**Funcționalități implementate:**
- [x] Creare factură automată la crearea abonamentului
- [x] Auto-generare număr factură (prefix + sequence din Settings, per dată)
- [x] Auto-calcul tax, total, due_amount prin `InvoiceCalculator` în `Invoice::boot()`
- [x] Auto-creare `InvoiceTransaction` la prima plată (cash) prin `Invoice::boot()`
- [x] Status factură auto-calculat: issued → partial → paid / overdue / cancelled
- [x] Suport reduceri (% din planuri disponibile în Settings sau sumă fixă)
- [x] Suport metode de plată: cash, card, online/Stripe, cheque, bank transfer
- [x] Adăugare plăți parțiale (`add_payment` action)
- [x] Rambursare (`refund` action)
- [x] Anulare factură (`cancel_invoice` action)
- [x] Generare PDF (DomPDF) — preview și download
- [x] Trimitere email factură și chitanță (manual sau auto prin Settings)
- [x] Auto-trimitere email la emitere (`InvoiceObserver`) și la plată (`InvoiceTransactionObserver`)
- [x] Marcare automată "overdue" prin `gym:invoices` comandă zilnică
- [x] Tabs: All / Issued / Partial / Overdue / Paid / Refund / Cancelled
- [x] API v1 CRUD + PDF inline/download + transactions CRUD

**Funcționalități lipsă:** —

---

### 3.5 Check-in (QR & Manual)
**Status:** ✅ Complet

**Descriere:** Înregistrarea prezenței membrilor prin scanare QR sau acțiune manuală la recepție.

**Funcționalități implementate:**
- [x] Scanare QR prin URL semnat (`/checkin/{token}`) — validare abonament activ
- [x] Sistem grace entry: un check-in permis după expirare cu notificare admin
- [x] Check-in manual din interfața admin (modal cu selector membri activi)
- [x] Check-out manual
- [x] Rate limiting per membru (previne scan duplicat)
- [x] Calcul durata sesiunii (check-in → check-out în minute)
- [x] Cod QR vizibil în portal membre (SVG) și descărcabil
- [x] Filtre: perioadă (azi, săptămână, lună), metodă (QR/manual), status
- [x] Export CSV check-in-uri cu filtre aplicate
- [x] Notificare admin la grace entry (email)
- [x] Setare grace minutes în Settings (default 15 minute "present now")

**Funcționalități lipsă:** —

---

### 3.6 Enquiries & Follow-ups (Lead Management)
**Status:** ⚠️ Parțial — inconsistență UX minoră

**Descriere:** Urmărirea potențialilor clienți (leads) de la primul contact până la conversie în membri.

**Funcționalități implementate:**
- [x] CRUD enquiries cu soft delete (name, email, contact, dob, gender, interested_in, source, goal, start_by)
- [x] Status lifecycle: lead → member / lost
- [x] Wizard modal "Convert to Member" în ViewEnquiry (2 pași: date personale pre-fill → abonament+factură)
- [x] Acțiune `mark_as_lost` în tabel
- [x] CRUD follow-ups cu soft delete (method, schedule_date, outcome)
- [x] Acțiune `mark_as_done` follow-up cu câmp outcome
- [x] Default schedule_date = today + 7 zile
- [x] RelationManager FollowUps în ViewEnquiry
- [x] Tabs: All / Lead / Member / Lost
- [x] API v1 CRUD complet pentru enquiries și follow-ups

**Funcționalități lipsă:**
- [ ] **Acțiunea `convert_to_member` din tabelul de enquiry** încă face redirect full-page la `CreateMember?enquiry_id=X` (calea veche), în timp ce aceeași acțiune din `ViewEnquiry` deschide wizard modal. UX inconsistent. (`EnquiryTable.php`) — Efort: Mic (< 1h)

---

### 3.7 Expenses (Cheltuieli)
**Status:** ✅ Complet

**Descriere:** Urmărirea cheltuielilor operaționale ale sălii.

**Funcționalități implementate:**
- [x] CRUD cheltuieli cu soft delete (name, category, amount, date, due_date, vendor, notes)
- [x] Status tracking: pending / paid / overdue / cancelled
- [x] `paid_at` conditional (vizibil doar la status=paid)
- [x] Categorii configurabile din Settings
- [x] Tabs: All / Pending / Paid / Overdue / Cancelled
- [x] Integrare în analytics (expense trend, category breakdown)
- [x] API v1 CRUD complet

**Funcționalități lipsă:** —

---

### 3.8 Member Portal (Self-Service)
**Status:** ✅ Complet

**Descriere:** Interfața web dedicată membrilor — autentificare, profil, abonament, QR, facturi.

**Funcționalități implementate:**
- [x] Înregistrare self-service cu email + parolă
- [x] Verificare email (link semnat, throttle 6/min)
- [x] Login / Logout cu guard dedicat (`member`)
- [x] Forgot password (generează parolă nouă, trimite email)
- [x] Schimbare parolă din profil (old password → new password)
- [x] Set password la prima conectare (din invitație portal)
- [x] Dashboard: abonament activ, zile rămase, ultima factură, status
- [x] Pagina planuri disponibile cu selecție (crează Subscription + Invoice în status `pending_payment`)
- [x] Vizualizare și descărcare cod QR personal (SVG)
- [x] Descărcare PDF factură proprie (cu validare că aparține membrului)
- [x] Middleware protecție: autentificare, verificare email, must_change_password
- [x] Invitație portal trimisă din admin (email cu link set-password)
- [x] Rate limiting pe login și resend verification

**Funcționalități lipsă:** —

---

### 3.9 Notifications & Email (Notificări și Email)
**Status:** ✅ Complet

**Descriere:** Sistem de notificări automate și manuale prin email și notificări in-app Filament.

**Funcționalități implementate:**
- [x] Email factură emisă (auto sau manual, cu PDF atașat)
- [x] Email chitanță plată (auto sau manual, cu PDF atașat)
- [x] Email notificare expirare abonament la client (la 7, 3, 1, 0 zile)
- [x] Notificare in-app admin la abonamente care expiră (widget dashboard)
- [x] Email invitație portal (link set-password)
- [x] Email parolă resetată (user admin și member)
- [x] Email notificare grace entry (admin)
- [x] Email notificare client nou selectat plan (admin)
- [x] Toate emailurile queue-ite prin Jobs
- [x] Template subiecte configurabile din Settings (tokens: `{member_name}`, `{invoice_number}` etc.)
- [x] Toggle auto-send invoice/receipt din Settings
- [x] Test email din Settings (trimite email real cu configurația curentă)
- [x] Deduplicare notificări expirare (cache per subscription per user pe 24h)

**Funcționalități lipsă:** —

---

### 3.10 Settings (Configurări)
**Status:** ✅ Complet

**Descriere:** Pagina de configurare centralizată a aplicației — 9 tab-uri, persistată în `storage/data/settingsData.json`.

**Tab-uri implementate:**
- [x] **Gym Info** — nume sală, logo, valută, an financiar, adresă, contact
- [x] **Invoice** — prefix factură, număr curent, tip antet (logo/text), template subiecte email
- [x] **Mail Delivery** — driver (env/Resend/SMTP/log/sendmail), from address, credențiale SMTP, test email
- [x] **Member** — prefix cod membru, număr curent
- [x] **Charges** — admission fee, procent taxă (TVA), reduceri disponibile (%)
- [x] **Expenses** — categorii cheltuieli (tags libere)
- [x] **Subscriptions** — zile "expiring soon", grace minutes check-in
- [x] **Import** — wizard import CSV membri (MemberImportService: analyze + importChunk batch 25)
- [x] **Backup** — configurare backup automat (trigger, path, retention), backup manual, restore din ZIP

**Funcționalități lipsă:** —

---

### 3.11 Users & Roles (Utilizatori și Roluri)
**Status:** ✅ Complet

**Descriere:** Gestionarea conturilor admin/staff și a permisiunilor prin Filament Shield.

**Funcționalități implementate:**
- [x] CRUD utilizatori cu soft delete (name, email, contact, gender, dob, photo, role)
- [x] Roluri și permisiuni granulare prin Filament Shield (spatie/laravel-permission)
- [x] Upload foto cu editor
- [x] Reset parolă din admin (generează parolă securizată, setează `must_change_password`)
- [x] Flag `must_change_password` cu redirect forțat la schimbare parolă
- [x] Status active/inactive
- [x] Acces multi-panel condiționat de roluri (`accessibleDashboards()`)
- [x] API v1 CRUD + endpoint roles/permissions

**Funcționalități lipsă:** —

---

### 3.12 REST API v1
**Status:** ✅ Complet

**Descriere:** API RESTful cu autentificare Sanctum bearer token pentru integrări externe sau aplicație mobilă.

**Endpoint-uri implementate:**
- [x] `POST /api/v1/auth/login` — autentificare, returnează token
- [x] `GET /api/v1/me` — user curent cu roluri
- [x] `POST /api/v1/auth/logout` — revocare token
- [x] `GET/PUT /api/v1/settings` — citire și actualizare setări
- [x] Analytics: `/financial`, `/membership`, `/cashflow-trend`, `/expense-categories`, `/top-plans`, `/recent-transactions`
- [x] CRUD complet (index/store/show/update/destroy/restore/forceDelete): Users, Members, Services, Plans, Subscriptions, Invoices, Expenses, Enquiries, FollowUps
- [x] `POST /api/v1/subscriptions/{id}/renew`
- [x] `GET /api/v1/invoices/{id}/pdf` și `/download`
- [x] CRUD `/api/v1/invoices/{id}/transactions`
- [x] `GET /api/v1/enquiries/{id}/follow-ups` și `POST`
- [x] `GET /api/v1/roles` și `/permissions`
- [x] Filtrare, sortare, câmpuri selectabile prin `spatie/laravel-query-builder`
- [x] Rate limiting: 60 req/min per user autentificat, 5 req/min pe login

**Funcționalități lipsă:** —

---

### 3.13 Public Website
**Status:** ❌ Neimplementat

**Descriere:** Site web public de marketing / landing page pentru sală.

**Funcționalități implementate:** —

**Funcționalități lipsă:**
- [ ] Landing page cu informații sală, planuri, contact
- [ ] Formular de contact / enquiry public
- [ ] Pagină publică de prețuri

> **Notă:** Aplicația actuală este orientată 100% B2B (admin staff) și B2C autentificat (portal membre). Nu există rute publice în afara `/member/login` și `/member/register`.

---

## 4. Fluxuri principale (Happy Path)

### 4.1 Înregistrare client nou la recepție (din lead)

1. Staff deschide **Enquiries → New Enquiry** și completează datele de contact ale prospectului
2. Enquiry-ul apare în lista de lead-uri; staff adaugă follow-up-uri de la telefonul de urmărire
3. Prospectul confirmă înscrierea → staff deschide **ViewEnquiry → "Convert to Member"**
4. Se deschide wizard modal 2 pași:
   - **Pas 1 (Date personale)** — pre-fill cu datele din enquiry, staff editează dacă e cazul
   - **Pas 2 (Abonament + Factură)** — staff selectează planul, data start, metoda de plată
5. La submit: `MemberOnboardingService` creează atomic Member → Subscription → Invoice → actualizează enquiry la status "member"
6. Redirect automat la pagina noului membru; notificare success
7. `SubscriptionObserver` setează automat status-ul membrului la "active"
8. Dacă emailul e configurat: `InvoiceObserver` dispatch-uiește email factură

---

### 4.2 Înregistrare client nou la recepție (fără enquiry anterior)

1. Staff deschide **Members** → click **"New Member"** (buton header)
2. Se deschide wizard modal 2 pași:
   - **Pas 1** — date personale completate manual
   - **Pas 2** — plan, date abonament, factură
3. La submit: același flux `MemberOnboardingService::create()`
4. Redirect la pagina membrului creat

---

### 4.3 Check-in cu QR la recepție

1. Membrul prezintă telefonul cu QR din portalul `/member/qr`
2. Recepția scanează codul → redirect la `/checkin/{token}`
3. `CheckInService::recordScan()` verifică:
   - Token valid → membre existent
   - Abonament activ în ziua curentă
   - Nu există sesiune deschisă (rate limit)
4. **Rezultat succes:** CheckIn creat cu `checked_in_at = now()`, notificare success
5. **Grace entry:** Abonament expirat recent → check-in permis cu notificare warning (un singur grace per expirare)
6. **Blocat:** Fără abonament valid și fără grace → check-in refuzat cu mesaj
7. La plecare: recepția click pe **Check Out** în lista check-in-urilor de azi

---

### 4.4 Check-in manual (fără QR)

1. Recepția click pe **"Manual Check-in"** din headerul paginii Check-ins
2. Modal cu selector: listează doar membrii cu abonament activ azi
3. Confirmare cu numele și planul afișat
4. CheckIn creat cu `method = 'manual'`

---

### 4.5 Reînnoire abonament

1. Staff accesează **Subscriptions** sau **Dashboard → Expiring Soon widget**
2. Click pe acțiunea **Renew** pe abonamentul care expiră/a expirat
3. Se deschide modal wizard cu:
   - Plan (pre-fill cu planul curent)
   - Start date (default = end_date + 1 zi sau azi)
   - End date (auto-calculat din plan)
   - Factură: dată, scadență, număr, metodă plată, sumă
4. La submit: `SubscriptionRenewalService::renew()` creează Subscription nouă + Invoice, marchează vechea ca "renewed"
5. Status nou member sincronizat automat

---

### 4.6 Înregistrare self-service (portal membre)

1. Prospectul accesează `/member/register` și completează name, email, contact, parolă
2. Email de verificare trimis automat (`MemberVerifyEmailNotification`)
3. Membrul confirmă emailul → redirect la `/member/plans`
4. Alege planul dorit → `MemberPlanSelectionService::select()` creează Subscription + Invoice în status `pending_payment`
5. Admin primește notificare email "Client nou selectat plan"
6. Recepția procesează plata și actualizează invoiceful la "paid"
7. Membrul vizualizează abonamentul activ în dashboard

---

### 4.7 Emitere și plată factură

1. Factura se creează automat la crearea abonamentului
2. Email factură trimis automat (dacă activat în Settings) sau manual din **Invoice → Email Invoice**
3. La încasare: **Add Payment** → introduce suma, data, metoda de plată
4. `Invoice::syncFromTransactions()` recalculează automat paid_amount, due_amount, status
5. Dacă total achitat ≥ total factură: status → "paid" automat
6. Email chitanță trimis automat (dacă activat) sau manual din **Email Receipt**

---

## 5. Bugs cunoscuți și limitări

### ⚠️ B-1 — EnquiryTable: `convert_to_member` face redirect în loc de wizard
**Fișier:** `app/Filament/Resources/Enquiries/Tables/EnquiryTable.php`
**Problemă:** Acțiunea `convert_to_member` din lista de enquiry-uri folosește `->url()` redirect la `CreateMember?enquiry_id=X`. ViewEnquiry folosește wizardul modal (L-1). UX inconsistent: comportament diferit în funcție de unde dai click.
**Impact:** Degradează experiența — staff ajunge pe pagina plată veche cu 25+ câmpuri în loc de wizard
**Fix:** Înlocuiește `->url()` cu redirect la `ViewEnquiry` sau replică wizardul în `EnquiryTable`
**Efort:** Mic (< 1h)

---

### ℹ️ L-1 — Abonamente "pending_payment" din portal neprocesat automat
**Fișier:** `app/Services/Members/MemberPlanSelectionService.php`
**Comportament actual:** Membrii care selectează un plan din portal creează Subscription cu status `pending_payment`. Trebuie procesate manual de staff.
**Impact:** UX — nu există integrare cu gateway de plată online
**Fix posibil:** Integrare Stripe/PayU pentru plată online (Efort: Mare)

---

### ℹ️ L-2 — Public website inexistent
**Comportament actual:** Nu există landing page public. Orice vizitator care ajunge la `/` vede direct pagina de login admin sau primește 404.
**Impact:** Nu există prezență web publică pentru potențiali clienți
**Fix posibil:** Landing page simplu cu informații sală, planuri, formular contact (Efort: Mare)

---

## 6. Roadmap prioritizat

### Prioritate 1 — Critice (blochează utilizarea normală)

> **Niciun bug critic identificat.** Aplicația este production-ready.

---

### Prioritate 2 — Importante (degradează experiența)

| # | Feature | Modul | Efort |
|---|---------|-------|-------|
| P2-1 | Fix inconsistență `convert_to_member` în EnquiryTable (→ wizard sau redirect ViewEnquiry) | Enquiries | Mic (< 1h) |
| P2-2 | Quick Subscription modal din ListMembers (L-3 din backlog) — reînnoire rapidă 2 câmpuri direct din tabelul de membri | Members / Subscriptions | Mediu (1-4h) |
| P2-3 | Smart defaults country/city din Settings gym pe formularul de creare member (M-3 din backlog) | Members / Settings | Mic (< 1h) |

---

### Prioritate 3 — Nice to have

| # | Feature | Modul | Efort |
|---|---------|-------|-------|
| P3-1 | Integrare gateway plată online (Stripe) pentru abonamente din portal | Member Portal | Mare (> 4h) |
| P3-2 | Landing page public cu planuri și formular de contact | Public Website | Mare (> 4h) |
| P3-3 | Dashboard quick actions: "Membru nou", "Lead nou", "Check-in rapid" ca header actions | Dashboard | Mediu |
| P3-4 | Rapoarte financiare export CSV/PDF (revenue per perioadă, cheltuieli vs încasări) | Analytics / Expenses | Mediu |
| P3-5 | Notificări push browser (PWA) pentru check-in în timp real la recepție | Check-in | Mare |
| P3-6 | Multi-sală (multi-tenant) — izolare date per locație | Infrastructure | Foarte mare |

---

## 7. Arhitectură tehnică — referință rapidă

| Layer | Detaliu |
|-------|---------|
| **Auth admin** | Filament cu Laravel Auth (guard `web`) |
| **Auth member** | Guard dedicat `member` cu model `App\Models\Member` |
| **Auth API** | Laravel Sanctum bearer token |
| **Permisiuni** | Filament Shield + spatie/laravel-permission |
| **Settings** | `storage/data/settingsData.json` (nu DB) prin `JsonSettingsRepository` |
| **Secvențe** | Invoice numbers, member codes — `JsonSequenceRepository` |
| **Queue** | Laravel Queue — toate emailurile trimise async prin Jobs |
| **PDF** | barryvdh/laravel-dompdf |
| **QR Code** | chillerlan/php-qrcode |
| **CSV Import** | `MemberImportService` cu analysis + chunk import (25/batch) |
| **DB dev** | SQLite (`database/database.sqlite`) |
| **DB prod** | MySQL |
| **Test DB** | SQLite in-memory (`phpunit.xml`) |
| **Backup** | ZIP (database.sqlite + settingsData.json) stocat local |
| **i18n** | EN / RO — `resources/lang/{en,ro}/app.php` |
