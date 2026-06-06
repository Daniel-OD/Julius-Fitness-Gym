<?php

return [

    'badge' => 'Ghid administrator',
    'toggle_on' => 'Activează ghidul administrator',
    'toggle_off' => 'Dezactivează ghidul administrator',
    'enabled' => 'Ghid administrator activat',
    'disabled' => 'Ghid administrator dezactivat',
    'tips_heading' => 'La ce să te uiți',
    'widgets_heading' => 'Grafice și widget-uri explicate',
    'collapse' => 'Ascunde ghidul',
    'expand' => 'Arată ghidul',

    'pages' => [

        'admin.dashboard' => [
            'title' => 'Dashboard — imagine de ansamblu',
            'summary' => 'Panoul principal al sălii. Folosește filtrul de perioadă (dreapta sus) — toate graficele și totalurile se actualizează împreună.',
            'tips' => [
                'Începe cu cardurile KPI: membri activi, abonamente expirante, venit lunar.',
                'Cifrele roșii sau de avertizare indică acțiuni necesare (reînnoiri, follow-up, facturi neplătite).',
                'Tabelele de jos duc direct la membru, abonament sau factură.',
                '„Abonamente fără factură” arată plăți fără document formal — util pentru încasări cash.',
            ],
            'widgets' => [
                'today_checkins' => 'Intrări și ieșiri înregistrate azi — pulsul traficului din sală.',
                'gym_overview' => 'Membri activi, abonamente care expiră curând, venit încasat luna aceasta.',
                'membership_metrics' => 'Abonamente noi, reînnoite și anulate în perioada selectată.',
                'financial_metrics' => 'Sumă încasată, facturi restante, cheltuieli și profit net.',
                'expense_categories' => 'Diagramă cheltuieli pe categorii — vezi unde se duc banii.',
                'membership_overview_table' => 'Activitate recentă abonamente cu status și date de expirare.',
                'recent_transactions' => 'Ultimele plăți pe facturi — verifică fluxul zilnic.',
                'uninvoiced_subscriptions' => 'Abonamente active fără factură în perioada aleasă.',
                'cashflow_trend' => 'Grafic încasări vs cheltuieli în timp.',
            ],
        ],

        'office.dashboard' => [
            'title' => 'Recepție — situația de azi',
            'summary' => 'Vedere simplificată pentru angajați: cine e în sală, intrările de azi și abonamente care necesită atenție.',
            'tips' => [
                'Folosește „Prezenți acum” pentru membrii aflați în sală.',
                'Urmărește abonamentele expirante și expirate înainte să ajungă membrii la desk.',
                'Încasările de azi arată plățile primite — nu raportul financiar complet.',
            ],
            'widgets' => [
                'today_stats' => 'Check-in-uri, check-out-uri și încasări înregistrate azi.',
                'present_now' => 'Membri încă în sală după ultimul check-in.',
                'expiring_soon' => 'Abonamente care expiră în fereastra de avertizare — reamintește reînnoirea.',
                'expired_subscriptions' => 'Membri cu abonament deja expirat.',
            ],
        ],

        'admin.members.index' => [
            'title' => 'Membri — lista membrilor',
            'summary' => 'Lista completă a membrilor. Caută, filtrează și deschide profilul pentru abonamente, facturi și QR check-in.',
            'tips' => [
                'Caută după nume, email sau cod membru.',
                'Filtrele de status ajută la membri inactivi sau expirați.',
                'Click pe rând pentru detalii, cod QR și istoric abonamente.',
                'Import în masă din Setări → Import.',
            ],
            'widgets' => [],
        ],

        'admin.members.create' => [
            'title' => 'Membru nou',
            'summary' => 'Înregistrează o persoană nouă. Poți adăuga abonament imediat sau mai târziu.',
            'tips' => [
                'Email-ul e recomandat — folosit la facturi și detectare duplicate la import.',
                'Codul membru se generează automat din setări.',
                'După salvare, deschide profilul pentru a printa QR-ul de check-in.',
            ],
            'widgets' => [],
        ],

        'admin.members.edit' => [
            'title' => 'Editare membru',
            'summary' => 'Actualizează date de contact, note sau status. Modificările se aplică facturilor și comunicărilor viitoare.',
            'tips' => [
                'Dezactivarea unui membru nu anulează abonamentele active — gestionează-le separat.',
                'Păstrează telefonul și emailul la zi pentru chitanțe și remindere.',
            ],
            'widgets' => [],
        ],

        'admin.members.view' => [
            'title' => 'Profil membru',
            'summary' => 'Vedere completă: date personale, abonament activ, facturi și QR check-in.',
            'tips' => [
                'Secțiunea QR — printează cardul de check-in.',
                'Tab-ul abonament arată reînnoiri și date de expirare.',
                'Istoricul facturilor listează toată facturarea pentru persoana respectivă.',
            ],
            'widgets' => [],
        ],

        'admin.subscriptions.index' => [
            'title' => 'Abonamente — membership-uri active',
            'summary' => 'Toate abonamentele: plan, date, status și facturi legate.',
            'tips' => [
                'Filtrează după status pentru expirate sau de reînnoit.',
                '„Expiră curând” folosește numărul de zile din Setări → Abonamente.',
                'Reînnoiește de aici sau din profilul membrului.',
                'Fiecare abonament poate genera facturi automat.',
            ],
            'widgets' => [],
        ],

        'admin.subscriptions.create' => [
            'title' => 'Abonament nou',
            'summary' => 'Atribuie un plan (și opțional un serviciu) unui membru cu date de început și sfârșit.',
            'tips' => [
                'Alege membrul, apoi planul — prețul vine din plan.',
                'Reducerile disponibile sunt în Setări → Taxe.',
                'La salvare se poate crea o factură, în funcție de fluxul tău.',
            ],
            'widgets' => [],
        ],

        'admin.plans.index' => [
            'title' => 'Planuri — produse de abonament',
            'summary' => 'Definește ce vinzi: durată, preț, descriere. Planurile se leagă de abonamente.',
            'tips' => [
                'Schimbarea prețului unui plan nu modifică abonamentele existente.',
                'Dezactivează planurile vechi în loc să le ștergi dacă au istoric.',
            ],
            'widgets' => [],
        ],

        'admin.services.index' => [
            'title' => 'Servicii — extra-opțiuni',
            'summary' => 'Opțiuni suplimentare (PT, dulap, etc.) ce pot fi atașate unui abonament.',
            'tips' => [
                'Serviciile adaugă cost peste planul de bază.',
                'Folosește nume clare ca staff-ul să aleagă corect la recepție.',
            ],
            'widgets' => [],
        ],

        'admin.check-ins.index' => [
            'title' => 'Check-in-uri — jurnal prezență',
            'summary' => 'Istoric intrări și ieșiri din sală. Pentru urmărirea prezenței și verificare la recepție.',
            'tips' => [
                'Membrii scanează QR la intrare — fiecare scan apare aici.',
                'Abonamentele expirate pot permite check-in în funcție de Setări → Check-in.',
                'Filtrează pe dată pentru orele de vârf.',
            ],
            'widgets' => [],
        ],

        'office.check-ins.index' => [
            'title' => 'Check-in-uri — înregistrare prezență',
            'summary' => 'Înregistrează intrarea și ieșirea membrilor de la recepție.',
            'tips' => [
                'Scanează sau caută membrul, apoi confirmă check-in.',
                'Sistemul avertizează dacă abonamentul e expirat.',
                'Fă check-out când membrul pleacă — lista „Prezenți acum” rămâne corectă.',
            ],
            'widgets' => [],
        ],

        'admin.invoices.index' => [
            'title' => 'Facturi — documente de facturare',
            'summary' => 'Toate facturile: emise, plătite, parțial sau restante. Loc central pentru veniturile sălii.',
            'tips' => [
                'Înregistrează plățile aici pentru solduri actualizate și chitanțe.',
                'Facturile restante sunt evidențiate — contactează membrii.',
                'PDF și email folosesc șabloane din Setări → Factură.',
                'Prefixul și numerotarea se configurează în Setări.',
            ],
            'widgets' => [],
        ],

        'admin.invoices.create' => [
            'title' => 'Factură nouă',
            'summary' => 'Creează manual o factură pentru un membru, adesea legată de un abonament.',
            'tips' => [
                'Verifică TVA și moneda din Setări → Taxe și Info sală.',
                'După emitere, înregistrează plățile pe pagina de detaliu factură.',
            ],
            'widgets' => [],
        ],

        'admin.expenses.index' => [
            'title' => 'Cheltuieli — costuri sală',
            'summary' => 'Urmărește chirie, utilități, echipament și alte plăți.',
            'tips' => [
                'Categoriile vin din Setări → Cheltuieli — păstrează-le consistente pentru grafice.',
                'Cheltuielile alimentează calculul profitului din dashboard (încasări minus cheltuieli).',
                'Adaugă note pentru export contabil.',
            ],
            'widgets' => [],
        ],

        'admin.enquiries.index' => [
            'title' => 'Solicitări — lead-uri vânzări',
            'summary' => 'Persoane interesate care nu sunt încă membri. Primul pas în pipeline-ul de vânzări.',
            'tips' => [
                'Notează apeluri și vizite aici înainte de a deveni membri.',
                'Programează follow-up ca niciun lead să nu fie uitat.',
                'Convertește în membru când se înscriu.',
            ],
            'widgets' => [],
        ],

        'admin.follow-ups.index' => [
            'title' => 'Follow-up — contacte programate',
            'summary' => 'Reminder-e de sunat sau mesajat enquiry-uri și membri. Menține vânzările și retenția.',
            'tips' => [
                'Sortează după dată scadentă pentru acțiunile de azi.',
                'Marchează complet după contact și lasă note pentru colegul de tură.',
            ],
            'widgets' => [],
        ],

        'admin.users.index' => [
            'title' => 'Utilizatori — conturi staff',
            'summary' => 'Persoane cu acces în panoul admin. Fiecare are un rol cu permisiuni.',
            'tips' => [
                'Staff recepție primește rol employee — folosește panoul Office, nu admin complet.',
                'Forțează schimbarea parolei la prima autentificare.',
                'Nu partaja contul super admin.',
            ],
            'widgets' => [],
        ],

        'admin.roles.index' => [
            'title' => 'Roluri și permisiuni',
            'summary' => 'Controlează ce poate vedea și face fiecare rol (membri, facturi, setări, etc.).',
            'tips' => [
                'Modificările se aplică imediat după salvare.',
                'Super admin ocolește restricțiile — atribuie cu grijă.',
                'După funcții noi, regenerează permisiunile dacă paginile returnează 404.',
            ],
            'widgets' => [],
        ],

        'admin.settings' => [
            'title' => 'Setări — configurează sala',
            'summary' => 'Configurarea aplicației: identitate sală, reguli facturare, import, backup.',
            'tips' => [
                'Info sală: nume, logo, adresă, monedă — apare pe facturi.',
                'Factură / Membru: prefixe numerotare documente.',
                'Taxe: cote TVA, taxă înscriere, reduceri.',
                'Cheltuieli: categorii pentru formular și grafice.',
                'Abonamente: câte zile înainte de expirare să avertizeze staff-ul.',
                'Import: încărcare membri din Excel.',
                'Backup: programare și restaurare baze de date.',
                'Comută iconița bec din meniul profilului (lângă light/dark) pentru a afișa sau ascunde ajutorul contextual.',
            ],
            'widgets' => [],
        ],

    ],

];
