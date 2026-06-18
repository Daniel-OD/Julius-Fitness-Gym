# Admin Automation Backlog — Julius Fitness Gym
> Prioritizat după: reducere click-uri/câmpuri, risc scăzut, reutilizare cod existent
> Data: 2026-06-18

---

## Quick Wins — Batch 1 (< 1 zi total, risc minim)

### QW-1 — Smart default `due_date` pe create invoice path
**BN-1 | Impact H | Efort XS**
- Fișier: `app/Filament/Resources/Subscriptions/Schemas/SubscriptionForm.php:187`
- Schimbare: `DatePicker::make('due_date')->default(fn (Get $get) => $get('date') ?? now()->toDateString())`
- Efect: Elimină 1 câmp obligatoriu manual la fiecare abonament nou
- Reutilizează: logica deja prezentă pe calea de renew (`invoice_due_date` default today)

### QW-2 — Smart default `due_date` pe EditInvoice
**BN-1 | Impact M | Efort XS**
- Fișier: `app/Filament/Resources/Invoices/Schemas/InvoiceForm.php:107`
- Schimbare: `DatePicker::make('due_date')->default(fn (Get $get) => $get('date') ?? now()->toDateString())`
- `afterStateUpdated` pe `date` trebuie să actualizeze și `due_date` când e gol

### QW-3 — Auto `paid_amount = total_amount` când metoda = cash (create path)
**BN-2 | Impact H | Efort XS**
- Fișier: `app/Filament/Resources/Subscriptions/Schemas/SubscriptionForm.php` — `afterStateUpdated` pe `payment_method` + `afterStateUpdated` pe `plan_id`
- Schimbare: când `$state === 'cash'`, `$set('paid_amount', $get('total_amount') ?? 0)`
- Override manual rămâne posibil (câmpul nu devine `readOnly`)

### QW-4 — Auto `paid_amount = total_amount` când metoda = cash (renew path)
**BN-2 | Impact M | Efort XS**
- Fișier: `app/Filament/Resources/Subscriptions/Schemas/SubscriptionForm.php` — `renewSchema()` — `afterStateUpdated` pe `payment_method`
- Aceeași logică ca QW-3, calea de renew

### QW-5 — Default `schedule_date = today + 7 zile` pe FollowUp
**BN-N/A | Impact L | Efort XS**
- Fișier: `app/Filament/Resources/FollowUps/Schemas/FollowUpForm.php`
- Schimbare: `DatePicker::make('schedule_date')->default(now()->addDays(7))`

---

## Medium — Batch 2 (1-3 zile, risc moderat)

### M-1 — Filtrare membri activi în manual check-in
**BN-5 | Impact M | Efort S**
- Fișier: `app/Filament/Resources/CheckIns/CheckInResource.php:204`
- Schimbare: query filtrat după membrii cu `subscriptions` active la ziua curentă; afișare plan activ în label
- Reutilizează: `activeSubscriptionFor()` deja exist în același fișier
- Risc: nici unul — doar filtrare UI, logica check-in nu se schimbă

### M-2 — Deduplicare `handleRenew` → `SubscriptionRenewalService`
**BN-6 | Impact M (code quality) | Efort S**
- Fișier: `app/Filament/Resources/Subscriptions/Schemas/SubscriptionForm.php:513-583`
- Schimbare: `handleRenew` mapează form data la formatul serviciului și apelează `SubscriptionRenewalService::renew()`
- Beneficiu: o singură sursă de adevăr pentru logica de renewal
- Risc: mic dacă testele acoperă calea Filament

### M-3 — Smart defaults country/city/pincode din settings gym
**BN-7 | Impact M | Efort S**
- Fișier: `app/Filament/Resources/Members/Schemas/MemberForm.php:143`
- Condiție: verifică dacă `JsonSettingsRepository` stochează locația gimnasiului
- Schimbare: `->default(fn () => app(SettingsRepository::class)->get('country'))`

---

## Large — Batch 3 (3-5 zile, risc mai mare)

### L-1 — Enquiry → Member wizard modal in-situ
**BN-3 | Impact M | Efort M**
- Fișier: `app/Filament/Resources/Enquiries/Pages/ViewEnquiry.php:34`
- Schimbare: înlocuiește `url()` cu `Action` modal wizard 3 pași: Date personale (pre-fill din enquiry) → Abonament + factură → Confirmare
- Reutilizează: logica din `CreateMember::mount()` + `SubscriptionForm` pentru pași 2-3
- Risc: logica de creare member + subscription + invoice + actualizare enquiry status trebuie mutată într-un service dedicat (`MemberOnboardingService`)
- Nu schimbă: calea existentă `?enquiry_id=` rămâne ca fallback

### L-2 — Member Onboarding Wizard (3 pași)
**BN-4 | Impact H | Efort L**
- Fișier: `app/Filament/Resources/Members/Pages/CreateMember.php` + `MemberForm.php`
- Schimbare: `CreateMember` extinde `CreateRecord` cu wizard Filament; Pasul 1: date esențiale (name, email, contact, dob, gender); Pasul 2: locație + plan + plată; Pasul 3: confirmare + opțional photo/health
- Reutilizează: `SubscriptionForm::configure()` pentru pasul 2
- Risc: breaking change pentru UX existent — necesită aprobare explicită înainte de implementare
- Beneficiu: reduce câmpurile vizibile simultan de la ~25 la ~6 pe pas

### L-3 — Modal „Abonament rapid" din ListMembers
**BN-N/A | Impact H | Efort M**
- Fișier: `app/Filament/Resources/Members/Tables/MemberTable.php`
- Schimbare: action pe fiecare rând: modal 2 câmpuri (plan + payment) → creează subscription + invoice automat
- Reutilizează: `SubscriptionRenewalService` (sau serviciu nou `QuickSubscriptionService`)
- Risc: moderat — logică transacțională nouă

---

## Propunere 5 automatizări cu cel mai mare ROI

| Prio | Automatizare | Reducere câmpuri manuale | Reducere click-uri | Efort | Risc |
|------|-------------|--------------------------|-------------------|-------|------|
| 1 | QW-3: paid_amount=total la cash (create) | 1 câmp eliminat / tranzacție | 0 | XS | minim |
| 2 | QW-1: due_date default pe create | 1 câmp eliminat / tranzacție | 0 | XS | minim |
| 3 | QW-4: paid_amount=total la cash (renew) | 1 câmp eliminat / reînnoire | 0 | XS | minim |
| 4 | M-1: filtrare membri activi check-in | 0 câmpuri, dar elimină căutare manuală | -2 scroll | S | minim |
| 5 | L-1: Enquiry → modal wizard | ~10 câmpuri eliminare navegare | -3 click-uri | M | moderat |

**Recomandare batch 1:** QW-1 + QW-2 + QW-3 + QW-4 + QW-5 — toate sunt modificări de 5-10 linii, fără schimbări de logică business, fără dependențe noi, cu impact imediat la fiecare operație de creare abonament.

---

## Estimare efort total
| Batch | Items | Efort estimat |
|-------|-------|--------------|
| Batch 1 (QW-1..5) | 5 quick wins | 2-4 ore |
| Batch 2 (M-1..3) | 3 medium | 2-4 zile |
| Batch 3 (L-1..3) | 3 large | 1-3 săptămâni |
