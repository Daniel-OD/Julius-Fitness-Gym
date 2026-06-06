<?php

return [

    'admin.settings.tabs.gym_info' => [
        'title' => 'Hai să configurăm profilul sălii',
        'greeting' => 'Salut! Aici începi — informațiile apar pe facturi și în emailurile trimise membrilor.',
        'summary' => 'Alocă 5 minute pentru datele de bază. Poți reveni oricând să le modifici.',
        'steps' => [
            [
                'title' => 'Nume, logo și monedă',
                'body' => 'Aceste trei câmpuri definesc cum apare sala pe fiecare document.',
                'fields' => [
                    ['name' => 'Numele sălii', 'hint' => 'Numele pe care îl știu clienții — ex. Julius Fitness Gym.'],
                    ['name' => 'Logo', 'hint' => 'PNG pătrat merge cel mai bine. Poți sări peste dacă nu ai încă logo.'],
                    ['name' => 'Monedă', 'hint' => 'Alege RON dacă facturezi în lei. E greu de schimbat ulterior fără să revizuiești facturile vechi.'],
                ],
            ],
            [
                'title' => 'An fiscal (opțional deocamdată)',
                'body' => 'Necesar doar dacă contabilul folosește an fiscal diferit de calendar. Majoritatea sălilor lasă anul calendaristic.',
                'fields' => [
                    ['name' => 'Început / sfârșit an fiscal', 'hint' => 'Completează ambele date doar dacă nu folosești anul calendaristic.'],
                ],
            ],
            [
                'title' => 'Adresă',
                'body' => 'Apare pe facturi. Alege țara prima — județul și orașul se încarcă automat.',
                'fields' => [
                    ['name' => 'Adresă', 'hint' => 'Stradă, număr, bloc — ca în actele firmei.'],
                    ['name' => 'Țară → Județ → Oraș', 'hint' => 'Completează în ordine: țară, apoi județ, apoi oraș.'],
                    ['name' => 'Cod poștal', 'hint' => 'Opțional, dar recomandat pentru facturi complete.'],
                ],
            ],
            [
                'title' => 'Date de contact',
                'body' => 'Membrii și sistemul le folosesc pentru chitanțe și notificări.',
                'fields' => [
                    ['name' => 'Adresă email', 'hint' => 'Emailul principal al sălii — nu neapărat cel personal.'],
                    ['name' => 'Număr de contact', 'hint' => 'Telefon recepție sau birou, apelabil de clienți.'],
                ],
            ],
        ],
        'checklist' => [
            'Numele sălii este completat',
            'Moneda este setată corect',
            'Cel puțin email sau telefon adăugat',
            'Adresa este completă (dacă emiți facturi)',
        ],
        'save_reminder' => 'Gata? Derulează jos și apasă Salvează setările — modificările nu se aplică până nu salvezi.',
    ],

    'admin.settings.tabs.invoice' => [
        'title' => 'Numerotare facturi și emailuri',
        'greeting' => 'Aici controlezi cum se generează numerele de factură și dacă clienții primesc email automat.',
        'summary' => 'Configurează o dată înainte de prima factură — evită goluri sau duplicate în numerotare.',
        'steps' => [
            [
                'title' => 'Numerotare facturi',
                'body' => 'Fiecare factură nouă primește următorul număr automat. Prefixul te ajută să recunoști facturile sălii.',
                'fields' => [
                    ['name' => 'Prefix', 'hint' => 'Exemplu: INV- sau JFG-. Apare înainte de număr: INV-00042.'],
                    ['name' => 'Ultimul număr factură', 'hint' => 'Dacă ai emis facturi în alt sistem, pune ultimul număr ca următoarea să continue corect. Sală nouă? Lasă 0 sau 1.'],
                    ['name' => 'Afișare brand', 'hint' => 'Nume sală sau logo sus în PDF — ce arată mai profesional pentru tine.'],
                ],
            ],
            [
                'title' => 'Șabloane email',
                'body' => 'Personalizează subiectele. Păstrează tokenii {invoice_number}, {member_name}, {gym_name} — se înlocuiesc automat.',
                'fields' => [
                    ['name' => 'Subiect email factură', 'hint' => 'Exemplu: Factura {invoice_number} de la {gym_name}'],
                    ['name' => 'Subiect email chitanță', 'hint' => 'Trimis când înregistrezi o plată.'],
                ],
            ],
            [
                'title' => 'Trimitere automată',
                'body' => 'Activează doar când emailul (SMTP) e configurat pe server. Altfel facturile rămân în app și le trimiți manual.',
                'fields' => [
                    ['name' => 'Activare trimitere email', 'hint' => 'Comutator principal pentru toate emailurile de factură.'],
                    ['name' => 'Trimite automat la emitere', 'hint' => 'Email la crearea facturii.'],
                    ['name' => 'Trimite chitanță la plată', 'hint' => 'Clientul primește confirmare când înregistrezi plata.'],
                ],
            ],
        ],
        'checklist' => [
            'Prefix ales și ultimul număr setat',
            'Afișare brand selectată (nume sau logo)',
            'Toggle-uri email setate cum vrei să lucrezi (manual vs automat)',
        ],
        'save_reminder' => 'Salvează după modificarea numerotării — următoarea factură folosește noile valori.',
    ],

    'admin.settings.tabs.member' => [
        'title' => 'Coduri membri',
        'greeting' => 'Fiecare membru primește un cod unic — ca un prefix de factură, dar pentru persoane.',
        'summary' => 'Configurează înainte de a adăuga membri manual sau de import din Excel.',
        'steps' => [
            [
                'title' => 'Cum funcționează codurile',
                'body' => 'La membru nou, aplicația generează următorul cod: prefix + număr. Rareori tastezi coduri manual.',
                'fields' => [
                    ['name' => 'Prefix', 'hint' => 'Exemplu: MEM- sau JFG-M-. Păstrează-l scurt.'],
                    ['name' => 'Ultimul număr', 'hint' => 'La import membri existenți, pune cel mai mare număr ca noile coduri să nu se suprapună.'],
                ],
            ],
        ],
        'checklist' => [
            'Prefix definit',
            'Ultimul număr reflectă membrii existenți (dacă există)',
        ],
        'save_reminder' => 'Salvează înainte de creare sau import membri.',
    ],

    'admin.settings.tabs.charges' => [
        'title' => 'Taxe, tarife și reduceri',
        'greeting' => 'Valorile apar când creezi abonamente și facturi — le setezi o dată, le folosești peste tot.',
        'summary' => 'TVA greșit = facturi greșite. Verifică de două ori înainte să intri live.',
        'steps' => [
            [
                'title' => 'Taxă de înscriere',
                'body' => 'Taxă unică pentru membri noi. Pune 0 dacă nu o percepi separat.',
                'fields' => [
                    ['name' => 'Taxă de înscriere', 'hint' => 'Sumă în moneda sălii.'],
                ],
            ],
            [
                'title' => 'Cotă TVA',
                'body' => 'Procentul de pe facturi. În România e adesea 19% — confirmă cu contabilul.',
                'fields' => [
                    ['name' => 'Cote TVA (%)', 'hint' => 'Introdu 19 pentru 19% TVA. Folosește cota aprobată de contabil.'],
                ],
            ],
            [
                'title' => 'Opțiuni reducere',
                'body' => 'Predefinește procentele permise ca staff-ul să aleagă din listă, nu să scrie la întâmplare.',
                'fields' => [
                    ['name' => 'Reduceri disponibile (%)', 'hint' => 'Scrie un număr (ex. 10) și apasă Enter. Adaugă 10, 15, 20 dacă astea sunt ofertele standard.'],
                ],
            ],
        ],
        'checklist' => [
            'TVA aliniat cu contabilitatea',
            'Taxă înscriere setată (sau 0)',
            'Procente reducere uzuale adăugate',
        ],
        'save_reminder' => 'Salvează — abonamentele și facturile noi vor folosi aceste rate.',
    ],

    'admin.settings.tabs.expenses' => [
        'title' => 'Categorii cheltuieli',
        'greeting' => 'Categoriile țin cheltuielile organizate și fac graficele din dashboard utile.',
        'summary' => 'Gândește-te cum vorbești cu contabilul despre costuri — folosește aceleași denumiri aici.',
        'steps' => [
            [
                'title' => 'Construiește lista de categorii',
                'body' => 'Când cineva înregistrează o cheltuială, alege din listă. Nume consistente = rapoarte clare.',
                'fields' => [
                    ['name' => 'Categorii cheltuieli', 'hint' => 'Scrie un nume (Chirie, Utilități, Salarii…) și apasă Enter după fiecare. Începe cu 5–8 categorii.'],
                ],
            ],
        ],
        'checklist' => [
            'Cel puțin Chirie, Utilități și Salarii (sau echivalente)',
            'Denumiri aliniate cu contabilitatea',
        ],
        'save_reminder' => 'Salvează — categoriile apar imediat la Cheltuieli și în graficul dashboard.',
    ],

    'admin.settings.tabs.subscriptions' => [
        'title' => 'Abonamente și check-in',
        'greeting' => 'Controlează când staff-ul e avertizat despre expirări și cum vede recepția cine e în sală.',
        'summary' => 'Cifre mici aici evită momente stânjenitoare la recepție („Credeam că mai am abonament!”).',
        'steps' => [
            [
                'title' => 'Avertizări expirare',
                'body' => 'Dashboard și recepția evidențiază abonamentele care expiră în acest număr de zile.',
                'fields' => [
                    ['name' => 'Avertizare abonamente expirante (zile)', 'hint' => '7 e un default bun — staff poate suna cu o săptămână înainte. Folosește 14 dacă reînnoirile necesită mai mult timp.'],
                ],
            ],
            [
                'title' => 'Prezenți acum (recepție)',
                'body' => 'După check-out, membrii rămân pe lista „Prezenți acum” câteva minute — util dacă au uitat să scaneze ieșirea.',
                'fields' => [
                    ['name' => 'Perioadă grație „Prezenți acum” (minute)', 'hint' => '15 minute merge pentru majoritatea sălilor. 0 = eliminare imediată după check-out.'],
                ],
            ],
        ],
        'checklist' => [
            'Zile expirare setate (7 sau 14)',
            'Perioadă grație setată pentru recepție',
        ],
        'save_reminder' => 'Salvează — avertizările se actualizează în dashboard și panoul recepție.',
    ],

    'admin.settings.tabs.import' => [
        'title' => 'Import membri din Excel',
        'greeting' => 'Vii din alt sistem sau din Excel? Poți importa membri în masă de aici.',
        'summary' => 'Urmează pașii din wizard — descarcă șablonul ca să eviți surprize la format.',
        'steps' => [
            [
                'title' => 'Pasul 1 — Încărcare',
                'body' => 'Folosește .xlsx sau .csv. Descarcă șablonul dacă nu ești sigur de coloane.',
                'fields' => [
                    ['name' => 'Șablon', 'hint' => 'Apasă „Descarcă șablon Excel” — completează, apoi încarcă.'],
                    ['name' => 'Date obligatorii', 'hint' => 'Fiecare rând trebuie să aibă cel puțin email SAU nume.'],
                ],
            ],
            [
                'title' => 'Pasul 2 — Mapare coloane',
                'body' => 'Potrivește fiecare coloană din fișier cu un câmp membru. Ignoră coloanele inutile.',
                'fields' => [
                    ['name' => 'Email / Nume', 'hint' => 'Mapează cel puțin unul — rândurile fără ambele sunt sărite.'],
                    ['name' => 'Primul rând conține antete', 'hint' => 'Lasă bifat dacă rândul 1 are titluri de coloane.'],
                ],
            ],
            [
                'title' => 'Pasul 3 — Confirmare',
                'body' => 'Verifică previzualizarea, alege ce faci cu emailurile duplicate, apoi importă.',
                'fields' => [
                    ['name' => 'Email duplicat', 'hint' => 'Sari = păstrezi membrul existent. Actualizează = suprascrii cu datele din fișier.'],
                ],
            ],
        ],
        'checklist' => [
            'Prefix membri configurat (Setări → tab Membru)',
            'Fișier pregătit din șablon sau export',
            'Politică duplicate aleasă înainte de import',
        ],
        'tips' => [
            'Importul nu creează abonamente — adaugă-le separat după import.',
            'Descarcă raportul de erori dacă unele rânduri eșuează; corectează fișierul și reimportă.',
        ],
        'save_reminder' => 'Importul are buton propriu — celelalte tab-uri tot necesită Salvează setările.',
    ],

    'admin.settings.tabs.backup' => [
        'title' => 'Backup și restaurare',
        'greeting' => 'Datele despre membri și facturi sunt valoroase — configurează backup înainte să ai nevoie.',
        'summary' => 'Poți face backup manual oricând sau programa copii automate într-un folder sincronizat în cloud.',
        'steps' => [
            [
                'title' => 'Backup automat',
                'body' => 'Indică un folder sincronizat cu Google Drive, iCloud sau OneDrive.',
                'fields' => [
                    ['name' => 'Backup activat', 'hint' => 'Pornește când calea e configurată.'],
                    ['name' => 'Cale folder backup', 'hint' => 'Cale completă pe server/PC unde rulează app-ul.'],
                    ['name' => 'Când să facă backup', 'hint' => 'Sfârșit de zi convine majorității sălilor.'],
                    ['name' => 'Păstrează ultimele N backup-uri', 'hint' => '5–10 e de obicei suficient.'],
                ],
            ],
            [
                'title' => 'Backup manual și restaurare',
                'body' => 'Folosește „Backup acum” înainte de modificări mari. Restaurarea înlocuiește întreaga bază de date.',
                'fields' => [
                    ['name' => 'ZIP restaurare', 'hint' => 'Se creează automat un backup de siguranță înainte de restaurare.'],
                    ['name' => 'Include setări', 'hint' => 'Bifează dacă vrei să restaurezi și moneda, prefixele etc.'],
                ],
            ],
        ],
        'checklist' => [
            'Cale backup testată (folder există și e inscriptibil)',
            'Cel puțin un backup manual făcut',
            'Echipa știe că restaurarea e ultimă soluție',
        ],
        'save_reminder' => 'Salvează după modificarea programării — apoi încearcă „Backup acum” ca să verifici.',
    ],

];
