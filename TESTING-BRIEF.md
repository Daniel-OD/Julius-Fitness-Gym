# Julius Fitness Gym — Brief de testare manuală

Document pentru testeri (QA manual). Scopul nu este doar găsirea de bug-uri, ci și documentarea **fricțiunii din fluxurile zilnice**: câmpuri repetitive, pagini în lanț, acțiuni care ar putea fi într-un singur wizard sau quick action.

---

## 1. Context aplicație

**Julius Fitness Gym** este o aplicație de management pentru sală de fitness.

| Interfață | URL tipic | Utilizatori |
|-----------|-----------|-------------|
| Admin panel (Filament) | `/admin` | Admin, staff cu drepturi |
| Staff login | `/staff/login` | Personal sală |
| Office panel | `/office` | Rol office (dacă e activ) |
| Portal membru | `/member/login` | Membri |
| Recepție (scan QR) | `/reception/scan` | Personal recepție |

**Stack relevant pentru testare:** formulare web, modale, tabele, email-uri tranzacționale, export/import Excel.

**Limbi suportate:** română (RO) și engleză (EN). Testează ambele unde e posibil (switcher în admin).

---

## 2. Înainte de a începe

### 2.1 Confirmă cu echipa de dezvoltare

- [ ] URL de test (staging / production) — **nu testa pe date reale de clienți**
- [ ] Conturi de test pentru fiecare rol (vezi secțiunea 3)
- [ ] Setări inițiale: monedă, prefix facturi, taxă, planuri active
- [ ] Email de test configurat (pentru facturi, reset parolă)
- [ ] Versiunea / commit-ul deployat (pentru raportare bug-uri)

### 2.2 Mediu de test recomandat

| Element | Recomandare |
|---------|-------------|
| Browser desktop | Chrome (ultima versiune) |
| Browser mobil | Safari iOS sau Chrome Android |
| Rezoluție | Laptop 1366×768 + tabletă dacă e disponibilă |
| Date | Doar date fictive (nume, email, telefon de test) |

### 2.3 Ce NU trebuie să faci

- Nu modifica cod sau baza de date direct
- Nu folosi date personale reale ale clienților
- Nu raporta preferințe estetice subiective fără legătură cu un flux de lucru
- Nu ignora pașii din scenarii — notează exact ce ai făcut

---

## 3. Roluri de testat

Cere conturi separate pentru fiecare rol. Dacă un rol lipsește, notează în raport.

| Rol | Ce testezi |
|-----|------------|
| **Super admin** | Acces complet la toate resursele din `/admin` |
| **Staff** (rol limitat) | Ce resurse/acțiuni sunt vizibile vs ascunse |
| **Office** | Panel `/office` — vizibilitate facturi interne etc. |
| **Recepție** | Scan QR, check-in membri |
| **Membru** | Login portal, forgot password, funcții client |

**Verificări permisiuni:**
- Resurse care returnează 404 pentru rol greșit
- Acțiuni ascunse corect (ex. reset parolă, ștergere)
- Butoane vizibile dar care eșuează la submit

---

## 4. Priorități de testare

### P0 — Blocante (testează primul)

Fluxuri fără de care sala nu poate opera zilnic:

1. Creare membru + abonament + factură
2. Adăugare abonament la membru existent
3. Plată / înregistrare plată pe factură
4. Reînnoire abonament expirat
5. Login admin + login membru

### P1 — Importante

6. Enquiry → follow-up → convert to member
7. Import membri din Excel (Settings)
8. Reset parolă (admin + self-service membru)
9. Email factură emisă / chitanță plată
10. Liste + filtre (membri, abonamente, facturi)

### P2 — Secundare

11. Cheltuieli (expenses)
12. Planuri și servicii (CRUD)
13. Utilizatori și roluri (Shield)
14. Export CSV / acțiuni bulk
15. Responsive pe modaluri și tabele lungi

---

## 5. Scenarii obligatorii

Parcurge toate scenariile de mai jos. Pentru fiecare completează **Friction log** (secțiunea 7).

---

### Scenariul 1 — Zi tipică la recepție

**Context:** Client nou la ușă, vrea abonament 1 lună, plătește cash.

**Pași:**
1. Autentifică-te în `/admin` (sau `/staff/login`).
2. Creează membru nou cu abonament și factură (flux complet din formularul de create member sau echivalent).
3. Selectează un plan activ.
4. Completează datele facturii (dată, scadență, metodă plată cash, sumă plătită).
5. Salvează.
6. Deschide membru creat și verifică: abonament activ, factură cu status corect.

**Verifică:**
- [ ] Număr factură generat automat (ex. `GY-1`, `GY-2`)
- [ ] Totaluri: taxă, discount, total, rest de plată
- [ ] Status abonament (ongoing / upcoming)
- [ ] Câmpuri obligatorii clare; erori ușor de înțeles

**Notează:** câte click-uri, câte pagini, câte câmpuri ai completat manual, timp estimat.

---

### Scenariul 2 — Abonament la membru existent

**Context:** Membru deja în sistem, vine să prelungească sau ia abonament nou.

**Pași:**
1. Deschide `/admin/members/{id}` (view membru).
2. Din tab-ul Subscriptions, adaugă abonament nou (modal Create).
3. Completează plan, date, factură.
4. Salvează.

**Verifică:**
- [ ] Formularul din modal este același ca la create member?
- [ ] Invoice number se completează singur?
- [ ] Nu apare eroare „invoice number required” pe câmp gol/blocat
- [ ] După salvare, lista de abonamente se actualizează

---

### Scenariul 3 — Lead de pe site / telefon

**Context:** Prospect interesat, încă nu e membru.

**Pași:**
1. Creează **Enquiry** nou (nume, contact, sursă, obiectiv).
2. Adaugă **Follow-up** („sunat, interesat, revine săptămâna viitoare”).
3. Marchează follow-up ca done (dacă există acțiune).
4. Din view enquiry, folosește **Convert to member**.
5. Verifică ce date s-au pre-populat în formularul de membru.
6. Finalizează crearea membru + abonament.

**Verifică:**
- [ ] Câte date s-au pierdut la conversie?
- [ ] Câte pagini ai deschis în lanț?
- [ ] Status enquiry devine „member” după creare?

---

### Scenariul 4 — Reînnoire abonament

**Context:** Abonament expirat sau aproape de expirare.

**Pași:**
1. Găsește un abonament expirat (listă Subscriptions sau widget dashboard).
2. Folosește acțiunea **Renew**.
3. Confirmă plan, date start/end, factură.
4. Salvează.

**Verifică:**
- [ ] Abonament nou creat cu legătură la cel vechi
- [ ] Factură nouă cu număr secvențial corect
- [ ] Status abonament vechi actualizat (ex. renewed / expired)

---

### Scenariul 5 — Factură și plăți

**Context:** Factură emisă, plată parțială apoi totală.

**Pași:**
1. Creează sau găsește factură neplătită.
2. Adaugă plată parțială (transaction).
3. Verifică rest de plată și status.
4. Adaugă plata finală.
5. Verifică status „paid”.

**Verifică:**
- [ ] `paid_amount`, `due_amount`, `total_amount` sincronizate
- [ ] Email chitanță (dacă e configurat)
- [ ] PDF factură se deschide / se descarcă

---

### Scenariul 6 — Import membri Excel

**Context:** Migrare sau import lot de membri.

**Pași:**
1. Mergi la **Settings** → tab import membri.
2. Descarcă template-ul.
3. Completează 5 rânduri fictive (cu plan, date abonament dacă e cazul).
4. Încarcă fișierul, mapează coloanele.
5. Rulează importul.
6. Verifică în lista Members că datele sunt corecte.

**Verifică:**
- [ ] Mapare automată coloane (RO + EN)
- [ ] Raport erori clar la rânduri invalide
- [ ] Abonamente create corect din import

---

### Scenariul 7 — Reset parolă

**Context:** Membru uită parola; admin intervine.

**Pași A — Self-service:**
1. Deschide `/member/forgot-password`.
2. Introdu email membru de test.
3. Verifică inbox (și spam).
4. Deschide linkul, setează parolă nouă.
5. Login cu parola nouă.

**Pași B — Admin reset:**
1. Din `/admin/members` sau Users, găsește acțiunea **Reset password**.
2. Confirmă.
3. Verifică email primit de membru/staff.
4. Login cu parola nouă din email.

**Verifică:**
- [ ] Email ajunge (inbox vs spam)
- [ ] Link valid, nu expirat prematur
- [ ] Mesaje de eroare clare la email inexistent

---

### Scenariul 8 — Recepție check-in

**Context:** Membru vine la sală, scanare QR.

**Pași:**
1. Autentifică-te ca utilizator recepție.
2. Deschide `/reception/scan`.
3. Scanează QR membru (sau introdu token manual dacă există fallback).
4. Verifică check-in înregistrat în admin (Check-ins / profil membru).

**Verifică:**
- [ ] Membru cu abonament activ — check-in reușit
- [ ] Membru cu abonament expirat — mesaj clar
- [ ] Funcționează pe mobil

---

### Scenariul 9 — Permisiuni și roluri

**Pași:**
1. Login cu cont staff limitat.
2. Încearcă să accesezi: Members, Invoices, Users, Settings.
3. Notează ce e vizibil, ce dă 403/404.

**Verifică:**
- [ ] Meniul reflectă permisiunile
- [ ] Nu există acțiuni „moarte” (vizibile dar nefuncționale)

---

### Scenariul 10 — Limbi RO / EN

**Pași:**
1. Schimbă limba în EN din admin.
2. Parcurge rapid: create member, listă invoices, notificări.
3. Revino la RO.

**Verifică:**
- [ ] Etichete traduse, nu chei tehnice (`app.fields.*`)
- [ ] Mesaje validare în limba activă
- [ ] Email-uri în limba corectă (dacă e configurat)

---

## 6. Format raport bug-uri

Pentru fiecare problemă folosește șablonul:

```text
ID: BUG-001
Titlu: [scurt, descriptiv]
Severitate: P0 | P1 | P2
Rol: admin | staff | member | reception
URL: [ex. /admin/members/create]
Browser: Chrome 124 / Safari iOS 17
Limbă: RO | EN

Pași de reproducere:
1. ...
2. ...
3. ...

Rezultat așteptat:
...

Rezultat actual:
...

Screenshot / video:
[atașament sau link]

Note suplimentare:
...
```

### Severitate

| Nivel | Definiție | Exemple |
|-------|-----------|---------|
| **P0** | Blochează flux critic; nu există workaround | Nu poți salva membru; factură fără număr; login imposibil |
| **P1** | Funcționalitate majoră stricată; workaround dificil | Import eșuează silent; totaluri greșite; email nu pleacă |
| **P2** | Cosmetic, minor, workaround ușor | Etichetă greșită; aliniere UI; typo |

---

## 7. Friction log (obligatoriu)

Completează pentru **fiecare scenariu** din secțiunea 5:

| Scenariu | Nr. click-uri | Nr. câmpuri manuale | Nr. pagini | Timp (min) | Probleme UX | Propunere automatizare |
|----------|---------------|---------------------|------------|------------|-------------|------------------------|
| 1 — Recepție | | | | | | |
| 2 — Abonament membru | | | | | | |
| 3 — Lead → membru | | | | | | |
| 4 — Reînnoire | | | | | | |
| 5 — Factură/plăți | | | | | | |
| 6 — Import | | | | | | |
| 7 — Reset parolă | | | | | | |
| 8 — Check-in | | | | | | |

**Exemple de propuneri bune:**
- „Due date = invoice date by default”
- „Wizard unic: membru + abonament + plată cash”
- „După create member → redirect la view member, nu listă”
- „Precompletează planul din enquiry la convert”
- „Quick action din listă membri: Renew subscription”

---

## 8. Checklist zone admin de verificat

### Resurse principale (`/admin`)

| Zonă | Ce verifici |
|------|-------------|
| **Dashboard** | Widget-uri se încarcă; linkuri funcționează |
| **Members** | CRUD, view, QR, subscriptions tab, reset password |
| **Subscriptions** | Listă, filtre status, renew, notify expiration |
| **Invoices** | Create/edit, plăți, PDF, email, overdue |
| **Enquiries** | CRUD, convert to member |
| **Follow-ups** | Create, mark done, legătură enquiry |
| **Plans** | Planuri active apar în select-uri |
| **Services** | Opțional pe abonament |
| **Expenses** | CRUD simplu |
| **Check-ins** | Istoric check-in |
| **Users** | CRUD, roluri, reset password |
| **Settings** | General, facturi, import membri, secvențe |

### Acțiuni din tabele (sample)

- View / Edit / Delete pe fiecare resursă principală
- Acțiuni custom: Renew, Convert, Reset password, Export CSV
- Bulk delete (dacă există) — cu confirmare

### Formulare — ce să urmărești

- Câmpuri cu `default` care rămân goale în UI
- Câmpuri `disabled` care blochează validarea
- Repeaters imbricate (subscription + invoice)
- Modale care nu se închid după succes
- Validări duplicate sau contradictorii

---

## 9. Email-uri de verificat

| Email | Declanșator | Verifică |
|-------|-------------|----------|
| Factură emisă | Creare factură | PDF atașat, date corecte, limbă |
| Chitanță plată | Plată înregistrată | Sumă, număr factură |
| Reset parolă | Forgot password / admin reset | Link funcțional, parolă nouă |
| Notificare expirare | Acțiune notify (dacă folosită) | Conținut clar |

**Notează pentru fiecare:** inbox / spam / nu a sosit / întârziat.

---

## 10. Livrabile finale (acceptance)

Testerul livrează:

- [ ] Toate cele 10 scenarii parcurse (sau explicație pentru ce nu a putut fi testat)
- [ ] Listă bug-uri cu ID, severitate, pași de reproducere
- [ ] Friction log complet (secțiunea 7)
- [ ] **Top 10 propuneri de automatizare**, ordonate după impact pentru staff
- [ ] Screenshot/video pentru fiecare P0 și P1
- [ ] Rezumat executiv (max 1 pagină): ce merge bine, ce e blocant, ce îmbunătățește cel mai mult productivitatea

### Rezumat executiv — șablon

```text
Perioada testării: [date]
Mediu: [URL]
Versiune: [commit/tag dacă e cunoscut]

Bug-uri: P0: X | P1: Y | P2: Z

Top 3 probleme blocante:
1. ...
2. ...
3. ...

Top 3 îmbunătățiri UX cu cel mai mare impact:
1. ...
2. ...
3. ...

Concluzie: [Gata de producție / Necesită fixuri P0 / Necesită iterare UX]
```

---

## 11. Resurse utile în cod (pentru dezvoltatori, nu tester)

Dacă raportezi un bug tehnic, menționează pagina Filament:

| Flux | Locație probabilă în cod |
|------|--------------------------|
| Formular membru + abonament | `app/Filament/Resources/Members/Schemas/MemberForm.php` |
| Formular abonament + factură | `app/Filament/Resources/Subscriptions/Schemas/SubscriptionForm.php` |
| Formular factură | `app/Filament/Resources/Invoices/Schemas/InvoiceForm.php` |
| Convert enquiry | `app/Filament/Resources/Enquiries/Pages/ViewEnquiry.php` |
| Import membri | Settings → `member-import-wizard` |
| Secvențe numere (GY-1…) | `app/Services/JsonSequenceRepository.php` |

---

## 12. Contact și întrebări

În timpul testării, notează întrebările pentru echipa de dezvoltare:

- Comportament neclar (feature sau bug?)
- Lipsă cont de test pentru un rol
- Date lipsă în staging (planuri, setări)
- Email care nu pleacă — verificat și în spam?

---

*Ultima actualizare: iunie 2026 — Julius Fitness Gym*
