# Admin UX Audit — Julius Fitness Gym
> Data audit: 2026-06-18 | Baza: cod sursă + explorare resurse Filament

---

## Metodologie
Fiecare flux a fost urmărit din sursă: Pages, Schemas, RelationManagers, Tables, Actions. Click-urile sunt numărate din punctul de start (ListResource sau Dashboard) până la salvarea datelor.

---

## Tabel fluxuri

| # | Flux | Pași click | Câmpuri manuale | Pagini/modaluri | Poate fi automatizat? | Impact |
|---|------|-----------|-----------------|-----------------|----------------------|--------|
| 1 | Onboarding membru nou | 2+ (nav + submit) | ~18 câmpuri: name, email, contact, dob, gender, address, country, state, city, pincode, source, goal, health_issue, emergency_contact, photo + plan + invoice fields (due_date lipsă default!) | 1 pagină full CreateMember cu repeater nested subscription → repeater nested invoice | DA — wizard multi-step sau smart defaults | **H** |
| 2 | Abonament nou la membru existent | 3 (list → view → modal) | plan, start_date (default today ✓), end_date (auto ✓), invoice date (default today ✓), **due_date (FĂRĂ default)**, payment_method (default cash ✓), **paid_amount (default 0 chiar dacă cash)** | Modal CreateAction în SubscriptionsRelationManager pe ViewMember | DA — smart defaults | **H** |
| 3 | Reînnoire abonament | 2-3 (list/widget → modal renew) | plan (pre-fill same plan ✓), start_date (default end+1 ✓), end_date (auto ✓), invoice_date (default today ✓), invoice_due_date (default today ✓), **paid_amount (default 0 chiar dacă cash)** | Modal `renew` Action din SubscriptionTable sau ExpiringSoonWidget | DA — smart defaults (paid_amount) | **M** |
| 4 | Lead → Membru (Enquiry convert) | 4-5 (list → view → click → redirect full page → submit) | Date personale pre-fill ✓ din enquiry; **subscriptia + factura complet manuale** după redirect; după save, staff trebuie să navigheze înapoi manual | Redirect complet la CreateMember cu `?enquiry_id=` — pierde contextul, nu e modal | DA — wizard modal in-situ în ViewEnquiry | **M** |
| 5 | Follow-up lead | 2-3 (view → modal create) | method (default call ✓), **schedule_date (FĂRĂ default)**, note | Modal CreateAction în FollowUpsRelationManager | DA — default schedule_date = +7 zile | **L** |
| 6 | Emitere / plată factură | 2-3 (list → action menu → modal) | `add_payment`: amount (pre-fill = due_amount ✓), occurred_at (default now ✓), payment_method (default cash ✓), note opțional. `email_invoice`: to_email (pre-fill ✓). **`due_date` pe creare invoice: FĂRĂ default** | Acțiuni inline în InvoiceTable: `add_payment`, `refund`, `email_invoice`, `email_receipt`, `cancel_invoice` — toate funcționează bine | Parțial — `due_date` default missing pe create path | **L** |
| 7 | Check-in recepție | 1 (buton `Manual Check-in` → modal) | member (select — **listează TOȚI membrii, nu doar activi**), confirmare | Modal `manualCheckIn` header action în CheckInResource | DA — filtru membri activi | **M** |
| 8 | Dashboard acțiuni | n/a | Statistici vizibile, `renew` direct în `ExpiringSoonWidget` ✓, `notify_expiration` în `AtRiskMembersWidget`. **Nu există quick actions „Membru nou", „Lead nou", „Check-in rapid"** | Widget-uri stats, table widgets cu acțiuni renew/notify | DA — header actions contextuale pe Dashboard | **L** |

---

## Bottleneck-uri principale

### BN-1 — `due_date` fără default pe create paths ★★★
**Locații:** `SubscriptionForm.php:187` (invoice în subscription create), `InvoiceForm.php:107` (EditInvoice).
**Problema:** Staff trebuie să seteze manual due_date de fiecare dată. Pe calea de reînnoire (`renewSchema`), `invoice_due_date` are default today — inconsistență.
**Fix:** `->default(fn (Get $get) => $get('date') ?? now()->toDateString())` pe `due_date`.

### BN-2 — `paid_amount` default 0 chiar dacă payment_method = cash ★★★
**Locații:** `SubscriptionForm.php:233` (create), `SubscriptionForm.php:436` (renew).
**Problema:** La plata cash, staff trebuie să introducă manual suma exactă care e deja calculată în `total_amount`. Pe calea de renew e la fel.
**Fix:** La `afterStateUpdated` pe `payment_method`, când `cash`, seta `paid_amount = total_amount`. Sau `->default()` reactiv la schimbarea planului.

### BN-3 — Enquiry → Member: redirect full-page în loc de modal ★★
**Locație:** `ViewEnquiry.php:34-43` — `url()` redirect la CreateMember cu query string.
**Problema:** Staff pierde contextul paginii ViewEnquiry. După creare member, nu există redirecționare automată înapoi. Subscriptia + factura sunt complet manuale (enquiry-ul conține `interested_in` și `goal`, date relevante, dar subscriptia nu e pre-fill).
**Fix:** Wizard modal direct în ViewEnquiry (3 pași).

### BN-4 — Formularul CreateMember e vast și plat ★★
**Locație:** `MemberForm.php:23-186` — toate câmpurile pe o singură pagină incluzând nested repeater subscription → nested repeater invoice.
**Problema:** Recepția vede ~25 câmpuri simultan, inclusiv câmpuri rar folosite (health_issue, emergency_contact, photo). UX copleșitor pentru înregistrare rapidă.
**Fix:** Wizard 3 pași: (1) Date esențiale, (2) Abonament + plată, (3) Confirmare/opțional detalii.

### BN-5 — Manual check-in listează toți membrii ★
**Locație:** `CheckInResource.php:204-209` — `Member::query()->orderBy('name')->pluck('name', 'id')`.
**Problema:** La recepție, staff trebuie să caute prin toți membrii inclusiv cei fără abonament activ.
**Fix:** Filtrare membri cu abonament activ la ziua curentă; afișare status abonament în dropdown.

### BN-6 — Duplicare logică renewal (code quality) ★
**Locație:** `SubscriptionForm::handleRenew` vs `SubscriptionRenewalService::renew` — logică identică în două locuri.
**Problema:** CLAUDE.md descrie serviciul ca „shared by Filament and API" — incorect. Filament folosește `handleRenew` inline; API folosește `SubscriptionRenewalService`. Orice schimbare de business trebuie actualizată în ambele locuri.
**Fix:** Refactorizare `handleRenew` să apeleze `SubscriptionRenewalService`.

### BN-7 — Country/state/city fără smart defaults ★
**Locație:** `MemberForm.php:143-167`.
**Problema:** Nu există pre-fill din settings (există `JsonSettingsRepository` cu locație gym). Majority membrii sunt din aceeași zonă.
**Fix posibil:** `->default(fn () => Helpers::getSetting('default_country'))` dacă settings conțin locația.

---

## Ce există deja și funcționează bine (NU reinventa)

| Funcționalitate | Locație |
|---|---|
| Quick payment (add_payment modal) | `InvoiceTable.php:179` — pre-fill amount=due, default cash |
| Manual check-in modal | `CheckInResource.php:194` — selectare + confirmare |
| Renewal modal cu plan pre-fill | `SubscriptionTable.php:253` + `ExpiringSoonWidget.php:103` |
| Invoice number auto-gen | `SubscriptionForm::invoiceNumberField()` |
| Member code auto-gen | `MemberForm.php:58` via `Helpers::generateLastNumber` |
| End date auto-calc | `SubscriptionForm.php:121` via `Helpers::calculateSubscriptionEndDate` |
| Invoice fee/tax/total auto-calc | `InvoiceCalculator::summary()` |
| Enquiry → Member pre-fill date | `CreateMember::mount()` via `?enquiry_id=` |
| Bulk notify expiration | `SubscriptionTable.php:292` |
| Renew din dashboard widget | `ExpiringSoonSubscriptionsTableWidget.php:103` |
| CSV export check-ins | `CheckInResource.php:154` |
| Email invoice/receipt | `InvoiceTable.php:337-462` |
