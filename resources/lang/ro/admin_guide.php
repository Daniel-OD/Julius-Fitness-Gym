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
    'steps_heading' => 'Urmează pașii',
    'checklist_heading' => 'Lista ta de configurare',

    'pages' => [

        'admin.dashboard' => [
            'title' => 'Dashboard — imagine de ansamblu',
            'greeting' => 'Centrul tău de comandă — cel mai bun loc de unde să începi în fiecare dimineață.',
            'summary' => 'Folosește filtrul de perioadă (dreapta sus) — toate graficele și totalurile se actualizează împreună.',
            'steps' => [
                [
                    'title' => 'Setează perioada',
                    'body' => 'Filtrul de dată din dreapta sus controlează tot ce apare pe această pagină. Schimbă-l primul.',
                    'fields' => [
                        ['name' => 'Filtru dată', 'hint' => 'Alege azi, săptămâna aceasta, luna aceasta sau un interval personalizat. Toate cardurile și graficele se actualizează.'],
                    ],
                ],
                [
                    'title' => 'Citește cardurile KPI',
                    'body' => 'Patru tile-uri îți spun dintr-o privire cum stă sala.',
                    'fields' => [
                        ['name' => 'Membri activi', 'hint' => 'Membri cu abonament în desfășurare.'],
                        ['name' => 'Expiră curând', 'hint' => 'Abonamente care se termină în fereastra de avertizare — acționează înainte să expire.'],
                        ['name' => 'Venit luna aceasta', 'hint' => 'Plăți încasate în perioada selectată.'],
                        ['name' => 'Facturi restante', 'hint' => 'Bani datorați dar neîncasați încă.'],
                    ],
                ],
                [
                    'title' => 'Acționează la semnalele de avertizare',
                    'body' => 'Cifrele roșii sau portocalii înseamnă de obicei că ceva necesită atenție azi.',
                    'fields' => [
                        ['name' => 'Abonamente expirante', 'hint' => 'Deschide rândul pentru a reînnoi direct din tabel.'],
                        ['name' => 'Facturi restante', 'hint' => 'Contactează membrul înainte ca datoria să crească.'],
                        ['name' => 'Abonamente fără factură', 'hint' => 'Planuri active fără document formal — frecvent la încasări cash.'],
                    ],
                ],
            ],
            'tips' => [
                'Începe cu cardurile KPI: membri activi, abonamente expirante, venit lunar.',
                'Cifrele roșii sau de avertizare indică acțiuni necesare (reînnoiri, follow-up, facturi neplătite).',
                'Tabelele de jos duc direct la membru, abonament sau factură.',
                '„Abonamente fără factură" arată plăți fără document formal — util pentru încasări cash.',
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
            'greeting' => 'Începutul turei — tot ce ai nevoie înainte să intre primul membru pe ușă.',
            'summary' => 'Vedere simplificată pentru angajați: cine e în sală, intrările de azi și abonamente care necesită atenție.',
            'steps' => [
                [
                    'title' => 'Verifică cine e în sală',
                    'body' => 'Înainte de orice, aruncă un ochi la widget-ul „Prezenți acum".',
                    'fields' => [
                        ['name' => 'Prezenți acum', 'hint' => 'Membri care au făcut check-in dar nu au ieșit. Ar trebui să fie gol la începutul turei.'],
                    ],
                ],
                [
                    'title' => 'Verifică abonamentele expirante și expirate',
                    'body' => 'Identifică membrii care au nevoie de un apel de reînnoire.',
                    'fields' => [
                        ['name' => 'Expiră curând', 'hint' => 'Încă valid dar se termină în curând — menționează reînnoirea când ajung la recepție.'],
                        ['name' => 'Expirat', 'hint' => 'Abonamentul s-a încheiat deja — semnalează managerului dacă e cazul.'],
                    ],
                ],
                [
                    'title' => 'Verifică încasările de azi',
                    'body' => 'Asigură-te că plățile din tura ta apar în totaluri.',
                    'fields' => [
                        ['name' => 'Încasări azi', 'hint' => 'Plăți înregistrate în această tură — nu raportul financiar complet.'],
                    ],
                ],
            ],
            'tips' => [
                'Folosește „Prezenți acum" pentru membrii aflați în sală.',
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
            'greeting' => 'Directorul complet al membrilor — punctul de start pentru tot ce ține de un client.',
            'summary' => 'Caută, filtrează și deschide profilul pentru abonamente, facturi și istoricul check-in.',
            'steps' => [
                [
                    'title' => 'Găsește un membru',
                    'body' => 'Cel mai rapid drum spre orice fișă de client.',
                    'fields' => [
                        ['name' => 'Bara de căutare', 'hint' => 'Tastează un nume, adresă de email sau cod de membru. Rezultatele apar instant.'],
                        ['name' => 'Filtru status', 'hint' => 'Restrânge la activi, inactivi sau toți membrii.'],
                    ],
                ],
                [
                    'title' => 'Deschide un profil',
                    'body' => 'Click pe oricare rând pentru profilul complet — abonamente și date personale într-un singur loc.',
                    'fields' => [
                        ['name' => 'Click pe rând', 'hint' => 'Deschide profilul cu datele și istoricul abonamentelor.'],
                        ['name' => 'Meniu acțiuni', 'hint' => 'Acțiuni rapide pe fiecare rând: editare, ștergere sau alte operații disponibile.'],
                    ],
                ],
                [
                    'title' => 'Adaugă un membru nou',
                    'body' => 'Folosește butonul din dreapta sus pentru a înregistra pe cineva nou.',
                    'fields' => [
                        ['name' => 'Buton Membru nou', 'hint' => 'Duce la formularul de înregistrare — durează circa 60 de secunde.'],
                        ['name' => 'Import în masă', 'hint' => 'Vii din alt sistem? Folosește Setări → Import pentru a încărca un fișier Excel.'],
                    ],
                ],
            ],
            'tips' => [
                'Caută după nume, email sau cod membru.',
                'Filtrele de status ajută la membri inactivi sau expirați.',
                'Click pe rând pentru detalii și istoricul abonamentelor.',
                'Import în masă din Setări → Import.',
            ],
        ],

        'admin.members.create' => [
            'title' => 'Membru nou',
            'greeting' => 'Înregistrezi pe cineva nou? Durează circa 60 de secunde.',
            'summary' => 'Înregistrează o persoană nouă. Poți adăuga abonament imediat sau mai târziu.',
            'steps' => [
                [
                    'title' => 'Completează datele',
                    'body' => 'Doar numele e obligatoriu, dar cu cât adaugi mai mult acum, cu atât mai puțin de urmărit ulterior.',
                    'fields' => [
                        ['name' => 'Nume', 'hint' => 'Numele complet așa cum îl folosește clientul.'],
                        ['name' => 'Email', 'hint' => 'Recomandat — folosit pentru facturi și detectare duplicate la import.'],
                        ['name' => 'Contact', 'hint' => 'Număr de telefon pentru remindere de reînnoire și follow-up.'],
                    ],
                ],
                [
                    'title' => 'Verifică codul de membru',
                    'body' => 'Codul se generează automat din prefixul definit în Setări → Membru.',
                    'fields' => [
                        ['name' => 'Cod de membru', 'hint' => 'Modifică doar dacă ai nevoie de un cod specific (ex. migrezi din alt sistem).'],
                    ],
                ],
                [
                    'title' => 'Salvează și adaugă un abonament',
                    'body' => 'După salvarea membrului, deschide profilul pentru a crea un abonament.',
                    'fields' => [
                        ['name' => 'Buton Salvează', 'hint' => 'Creează fișa de membru. Vei fi dus automat în profil.'],
                        ['name' => 'Adaugă abonament', 'hint' => 'Disponibil în profilul membrului — atribuie un plan, date și generează factură.'],
                    ],
                ],
            ],
            'tips' => [
                'Email-ul e recomandat — folosit la facturi și detectare duplicate la import.',
                'Codul membru se generează automat din setări.',
                'După salvare, deschide profilul pentru a adăuga abonament.',
            ],
        ],

        'admin.members.edit' => [
            'title' => 'Editare membru',
            'greeting' => 'Actualizezi datele acestui membru — modificările se aplică facturilor și comunicărilor viitoare.',
            'summary' => 'Actualizează date de contact, note sau status. Modificările se aplică facturilor și comunicărilor viitoare.',
            'steps' => [
                [
                    'title' => 'Modifică câmpurile necesare',
                    'body' => 'Orice câmp poate fi modificat. Datele de contact sunt cele mai des actualizate.',
                    'fields' => [
                        ['name' => 'Email / Contact', 'hint' => 'Menține-le la zi pentru chitanțe și remindere de reînnoire.'],
                        ['name' => 'Note', 'hint' => 'Note interne vizibile doar pentru staff — utile pentru info medicale sau aranjamente speciale.'],
                        ['name' => 'Status', 'hint' => 'Dezactivarea unui membru nu anulează abonamentele active — gestionează-le separat.'],
                    ],
                ],
                [
                    'title' => 'Salvează modificările',
                    'body' => 'Click Salvează. Modificările se aplică la următoarea factură sau comunicare.',
                    'fields' => [
                        ['name' => 'Buton Salvează', 'hint' => 'La baza formularului — nu naviga în altă parte fără să salvezi.'],
                    ],
                ],
            ],
            'tips' => [
                'Dezactivarea unui membru nu anulează abonamentele active — gestionează-le separat.',
                'Păstrează telefonul și emailul la zi pentru chitanțe și remindere.',
            ],
        ],

        'admin.members.view' => [
            'title' => 'Profil membru',
            'greeting' => 'Imaginea completă a acestui membru — date personale și istoricul abonamentelor într-un singur loc.',
            'summary' => 'Vedere completă: date personale, detalii de contact și abonamente active.',
            'steps' => [
                [
                    'title' => 'Consultă datele personale',
                    'body' => 'Secțiunea de sus afișează datele de contact, obiective și note medicale.',
                    'fields' => [
                        ['name' => 'Secțiunea Detalii', 'hint' => 'Nume, email, telefon, sursă — tot ce a fost introdus la înregistrare.'],
                        ['name' => 'Buton Editare', 'hint' => 'Buton din antet pentru a actualiza orice dată fără a părăsi pagina.'],
                    ],
                ],
                [
                    'title' => 'Gestionează abonamentele',
                    'body' => 'Panoul Abonamente de mai jos listează memberships-urile curente și anterioare.',
                    'fields' => [
                        ['name' => 'Panoul Abonamente', 'hint' => 'Afișează planul, datele, statusul și factura asociată pentru fiecare abonament.'],
                        ['name' => 'Adaugă abonament', 'hint' => 'Buton din panou pentru a crea un abonament nou pentru acest membru.'],
                        ['name' => 'Reînnoire', 'hint' => 'Acțiune pe rândul unui abonament activ — deschide formularul de reînnoire cu datele pre-completate.'],
                    ],
                ],
            ],
            'tips' => [
                'Panoul abonamente afișează reînnoiri și date de expirare.',
                'Istoricul facturilor e disponibil pe fiecare rând de abonament.',
                'Folosește butonul Editare pentru a actualiza datele de contact fără să părăsești pagina.',
            ],
        ],

        'admin.subscriptions.index' => [
            'title' => 'Abonamente — membership-uri active',
            'greeting' => 'Pipeline-ul de reînnoire — nu lăsa abonamentele expirante să scape printre degete.',
            'summary' => 'Toate abonamentele: plan, date, status și facturi legate.',
            'steps' => [
                [
                    'title' => 'Filtrează pentru a găsi ce necesită acțiune',
                    'body' => 'Filtrul de status e cel mai util — ajută să te concentrezi pe ce contează azi.',
                    'fields' => [
                        ['name' => 'Filtru status', 'hint' => '„Expiră curând" și „Expirat" sunt primele de verificat în fiecare zi.'],
                        ['name' => 'Filtru membru', 'hint' => 'Vede toate abonamentele unei singure persoane.'],
                    ],
                ],
                [
                    'title' => 'Reînnoiește un abonament',
                    'body' => 'Deschide meniul de acțiuni de pe rând pentru a reînnoi — formularul pre-completează planul și noua dată de start.',
                    'fields' => [
                        ['name' => 'Acțiunea Reînnoire', 'hint' => 'Deschide formularul de reînnoire. Confirmă datele, alege reducerea dacă e cazul, și salvează.'],
                        ['name' => 'Buton Abonament nou', 'hint' => 'Creează un abonament nou pentru orice membru, independent de o reînnoire.'],
                    ],
                ],
                [
                    'title' => 'Verifică facturile asociate',
                    'body' => 'Fiecare abonament arată dacă are o factură asociată.',
                    'fields' => [
                        ['name' => 'Coloana Factură', 'hint' => 'Click pe linkul facturii pentru a deschide detaliile de facturare.'],
                    ],
                ],
            ],
            'tips' => [
                'Filtrează după status pentru expirate sau de reînnoit.',
                '„Expiră curând" folosește numărul de zile din Setări → Abonamente.',
                'Reînnoiește de aici sau din profilul membrului.',
                'Fiecare abonament poate genera facturi automat.',
            ],
        ],

        'admin.subscriptions.create' => [
            'title' => 'Abonament nou',
            'greeting' => 'Adaugi un membership pentru cineva — trei decizii de luat și ești gata.',
            'summary' => 'Atribuie un plan (și opțional un serviciu) unui membru cu date de început și sfârșit.',
            'steps' => [
                [
                    'title' => 'Alege membrul și planul',
                    'body' => 'Selectează mai întâi membrul, apoi planul — prețul și durata vin automat din plan.',
                    'fields' => [
                        ['name' => 'Membru', 'hint' => 'Caută după nume sau cod de membru.'],
                        ['name' => 'Plan', 'hint' => 'Afișează numele planului, prețul și durata în zile. Data de sfârșit se completează automat.'],
                        ['name' => 'Data de start', 'hint' => 'Implicit azi. Modificând-o se recalculează data de sfârșit.'],
                        ['name' => 'Data de sfârșit', 'hint' => 'Calculată din plan — câmp read-only.'],
                    ],
                ],
                [
                    'title' => 'Setează factura',
                    'body' => 'O factură se creează împreună cu abonamentul. Verifică sumele înainte de salvare.',
                    'fields' => [
                        ['name' => 'Reducere', 'hint' => 'Alege din lista predefinită (configurată în Setări → Taxe). Sumele se actualizează automat.'],
                        ['name' => 'Sumă plătită', 'hint' => 'Introdu ce s-a încasat acum. Suma rămasă apare în câmpul „Datorat".'],
                        ['name' => 'Metodă de plată', 'hint' => 'Cash, card sau transfer — influențează cum se înregistrează chitanța.'],
                    ],
                ],
            ],
            'tips' => [
                'Alege membrul, apoi planul — prețul vine din plan.',
                'Reducerile disponibile sunt în Setări → Taxe.',
                'Data de sfârșit se calculează automat din durata planului.',
            ],
        ],

        'admin.plans.index' => [
            'title' => 'Planuri — produse de abonament',
            'greeting' => 'Planurile sunt ce vinzi — menține această listă clară și actualizată.',
            'summary' => 'Definește ce vinzi: durată, preț, descriere. Planurile se leagă de abonamente.',
            'steps' => [
                [
                    'title' => 'Creează un plan nou',
                    'body' => 'Click Nou plan și completează datele de bază. Membrii aleg din această listă la abonare.',
                    'fields' => [
                        ['name' => 'Nume', 'hint' => 'ex. Lunar, Trimestrial, Anual — scurt și ușor de recunoscut.'],
                        ['name' => 'Durată (zile)', 'hint' => 'Folosită pentru calculul automat al datei de sfârșit la abonamente.'],
                        ['name' => 'Preț', 'hint' => 'Sumă de bază înainte de TVA. TVA-ul se aplică din Setări → Taxe.'],
                    ],
                ],
                [
                    'title' => 'Gestionează planurile existente',
                    'body' => 'Editează prețuri sau dezactivează planurile pe care nu le mai oferi. Nu șterge planuri cu istoric.',
                    'fields' => [
                        ['name' => 'Toggle activ', 'hint' => 'Oprește planurile pe care nu le mai vinzi fără să pierzi datele istorice.'],
                        ['name' => 'Editare preț', 'hint' => 'Afectează doar abonamentele viitoare — cele existente păstrează prețul original.'],
                    ],
                ],
            ],
            'checklist' => [
                'Cel puțin un plan activ există',
                'Prețurile planurilor reflectă tarifele curente',
                'Planurile vechi sau întrerupte sunt dezactivate, nu șterse',
            ],
            'tips' => [
                'Schimbarea prețului unui plan nu modifică abonamentele existente.',
                'Dezactivează planurile vechi în loc să le ștergi dacă au istoric.',
            ],
        ],

        'admin.services.index' => [
            'title' => 'Servicii — extra-opțiuni',
            'greeting' => 'Extra-opțiuni ce pot fi atașate oricărui abonament — PT, dulap, saună și altele.',
            'summary' => 'Opțiuni suplimentare (PT, dulap, etc.) ce pot fi atașate unui abonament.',
            'steps' => [
                [
                    'title' => 'Creează un serviciu',
                    'body' => 'Click Serviciu nou. Ține numele scurt și clar pentru ca staff-ul să aleagă corect.',
                    'fields' => [
                        ['name' => 'Nume', 'hint' => 'ex. Antrenament Personal, Dulap, Acces Saună.'],
                        ['name' => 'Preț', 'hint' => 'Se adaugă peste prețul planului de bază când e atașat la un abonament.'],
                    ],
                ],
                [
                    'title' => 'Atașează la abonamente',
                    'body' => 'Serviciile apar ca un câmp opțional în formularul de abonament.',
                    'fields' => [
                        ['name' => 'Câmpul Serviciu (în formularul de abonament)', 'hint' => 'Selectabil la crearea sau reînnoirea unui abonament. Adaugă prețul serviciului la total.'],
                    ],
                ],
            ],
            'checklist' => [
                'Numele serviciilor sunt clare pentru staff-ul de la recepție',
                'Prețurile serviciilor sunt corecte și includ orice adaos',
            ],
            'tips' => [
                'Serviciile adaugă cost peste planul de bază.',
                'Folosește nume clare ca staff-ul să aleagă corect la recepție.',
            ],
        ],

        'admin.check-ins.index' => [
            'title' => 'Check-in-uri — jurnal prezență',
            'greeting' => 'Istoricul complet al prezenței — util pentru contestații, analiză trafic și rapoarte.',
            'summary' => 'Istoric intrări și ieșiri din sală. Pentru urmărirea prezenței și verificare la recepție.',
            'steps' => [
                [
                    'title' => 'Găsește înregistrările',
                    'body' => 'Folosește filtrele pentru a restrânge lista la ce ai nevoie.',
                    'fields' => [
                        ['name' => 'Filtru dată', 'hint' => 'Restrânge la o zi sau tură specifică.'],
                        ['name' => 'Filtru membru', 'hint' => 'Vede istoricul complet de prezență pentru o persoană.'],
                    ],
                ],
                [
                    'title' => 'Verifică un check-in',
                    'body' => 'Fiecare rând afișează membrul, ora de check-in și ora de check-out.',
                    'fields' => [
                        ['name' => 'Coloanele Check-in / Check-out', 'hint' => 'Pereche de timestamp-uri per vizită. Un check-out lipsă înseamnă că membrul e încă în sală.'],
                    ],
                ],
            ],
            'tips' => [
                'Membrii scanează QR la intrare — fiecare scan apare aici.',
                'Abonamentele expirate pot permite check-in în funcție de Setări → Check-in.',
                'Filtrează pe dată pentru orele de vârf.',
            ],
        ],

        'office.check-ins.index' => [
            'title' => 'Check-in-uri — înregistrare prezență',
            'greeting' => 'Sarcina ta principală la recepție — înregistrează fiecare intrare și ieșire pentru ca sistemul să fie corect.',
            'summary' => 'Înregistrează intrarea și ieșirea membrilor de la recepție.',
            'steps' => [
                [
                    'title' => 'Înregistrează intrarea unui membru',
                    'body' => 'Găsește membrul și înregistrează sosirea.',
                    'fields' => [
                        ['name' => 'Căutare / Scanare', 'hint' => 'Caută după nume sau scanează codul QR. Sistemul avertizează dacă abonamentul e expirat.'],
                        ['name' => 'Buton Check-in', 'hint' => 'Înregistrează un timestamp. Membrul apare în „Prezenți acum" pe dashboard.'],
                    ],
                ],
                [
                    'title' => 'Înregistrează ieșirea',
                    'body' => 'Înregistrează plecarea ca „Prezenți acum" să rămână corect.',
                    'fields' => [
                        ['name' => 'Buton Check-out', 'hint' => 'Disponibil doar pentru membrii cu check-in activ. Îi scoate din lista „Prezenți acum".'],
                    ],
                ],
            ],
            'tips' => [
                'Scanează sau caută membrul, apoi confirmă check-in.',
                'Sistemul avertizează dacă abonamentul e expirat.',
                'Fă check-out când membrul pleacă — lista „Prezenți acum" rămâne corectă.',
            ],
        ],

        'admin.invoices.index' => [
            'title' => 'Facturi — documente de facturare',
            'greeting' => 'Registrul tău de facturare — menține-l precis și la zi.',
            'summary' => 'Toate facturile: emise, plătite, parțial sau restante. Loc central pentru veniturile sălii.',
            'steps' => [
                [
                    'title' => 'Găsește ce ai nevoie',
                    'body' => 'Filtrul de status e cel mai util.',
                    'fields' => [
                        ['name' => 'Filtru status', 'hint' => '„Restante" și „Neplătite" sunt primele de verificat.'],
                        ['name' => 'Filtru membru', 'hint' => 'Vede toate facturile pentru o persoană.'],
                        ['name' => 'Interval dată', 'hint' => 'Filtrează după data emiterii sau data scadentă.'],
                    ],
                ],
                [
                    'title' => 'Înregistrează o plată',
                    'body' => 'Deschide factura, apoi folosește acțiunea Adaugă plată.',
                    'fields' => [
                        ['name' => 'Adaugă plată', 'hint' => 'Introdu suma încasată și metoda de plată. Soldul se actualizează automat.'],
                        ['name' => 'Plată parțială', 'hint' => 'Permisă — suma rămasă se urmărește automat.'],
                    ],
                ],
                [
                    'title' => 'Trimite membrului',
                    'body' => 'Livrează factura prin email sau descarcă PDF.',
                    'fields' => [
                        ['name' => 'Trimite email', 'hint' => 'Trimite PDF-ul facturii la emailul membrului. Necesită SMTP configurat în Setări.'],
                        ['name' => 'Descarcă PDF', 'hint' => 'Pentru livrare manuală — înmânează membrului sau printează.'],
                    ],
                ],
            ],
            'tips' => [
                'Înregistrează plățile aici pentru solduri actualizate și chitanțe.',
                'Facturile restante sunt evidențiate — contactează membrii.',
                'PDF și email folosesc șabloane din Setări → Factură.',
                'Prefixul și numerotarea se configurează în Setări.',
            ],
        ],

        'admin.invoices.create' => [
            'title' => 'Factură nouă',
            'greeting' => 'Creezi o factură manual — cele mai multe se creează automat cu abonamentele, dar poți adăuga una și de aici.',
            'summary' => 'Creează manual o factură pentru un membru, adesea legată de un abonament.',
            'steps' => [
                [
                    'title' => 'Selectează membrul și abonamentul',
                    'body' => 'Leagă factura de persoana și abonamentul corect.',
                    'fields' => [
                        ['name' => 'Membru', 'hint' => 'Caută după nume sau cod.'],
                        ['name' => 'Abonament', 'hint' => 'Link opțional — completează automat suma din plan dacă e selectat.'],
                    ],
                ],
                [
                    'title' => 'Verifică sumele',
                    'body' => 'Verifică TVA și moneda înainte de emitere — nu pot fi modificate după.',
                    'fields' => [
                        ['name' => 'Tarif abonament', 'hint' => 'Suma de bază înainte de TVA și reducere.'],
                        ['name' => 'TVA', 'hint' => 'Aplicat din Setări → Taxe. Confirmă cu contabilul.'],
                        ['name' => 'Reducere', 'hint' => 'Opțional. Alege din lista predefinită sau introdu o sumă personalizată.'],
                        ['name' => 'Dată scadentă', 'hint' => 'Termenul de plată afișat pe factură.'],
                    ],
                ],
            ],
            'tips' => [
                'Verifică TVA și moneda din Setări → Taxe și Info sală.',
                'După emitere, înregistrează plățile pe pagina de detaliu factură.',
            ],
        ],

        'admin.expenses.index' => [
            'title' => 'Cheltuieli — costuri sală',
            'greeting' => 'Înregistrează fiecare cost ca profitul din dashboard să fie real, nu estimat.',
            'summary' => 'Urmărește chirie, utilități, echipament și alte plăți.',
            'steps' => [
                [
                    'title' => 'Adaugă o cheltuială',
                    'body' => 'Folosește butonul Cheltuială nouă. Completează categoria, suma și data.',
                    'fields' => [
                        ['name' => 'Categorie', 'hint' => 'Din lista ta din Setări → Cheltuieli. Categorii consistente = rapoarte lunare clare.'],
                        ['name' => 'Sumă', 'hint' => 'Cost complet. Include TVA dacă nu e recuperat.'],
                        ['name' => 'Dată', 'hint' => 'Când a apărut costul, nu când îl introduci.'],
                        ['name' => 'Note', 'hint' => 'Numele furnizorului, referința facturii sau un memo scurt pentru contabilitate.'],
                    ],
                ],
                [
                    'title' => 'Analizează cheltuielile',
                    'body' => 'Folosește filtrele pentru a vedea costurile pe categorie sau perioadă.',
                    'fields' => [
                        ['name' => 'Filtru categorie', 'hint' => 'Vede separat toată chiria, toate utilitățile, etc.'],
                        ['name' => 'Filtru dată', 'hint' => 'Vedere lunară pentru comparații bugetare.'],
                    ],
                ],
            ],
            'tips' => [
                'Categoriile vin din Setări → Cheltuieli — păstrează-le consistente pentru grafice.',
                'Cheltuielile alimentează calculul profitului din dashboard (încasări minus cheltuieli).',
                'Adaugă note pentru export contabil.',
            ],
        ],

        'admin.enquiries.index' => [
            'title' => 'Solicitări — clienți potențiali',
            'greeting' => 'Fiecare viitor membru potențial începe de aici — nu-i lăsa să se răcească.',
            'summary' => 'Persoane interesate care nu sunt încă membri. Primul pas în pipeline-ul de vânzări.',
            'steps' => [
                [
                    'title' => 'Înregistrează o solicitare nouă',
                    'body' => 'Capturează clientul potențial cât conversația e proaspătă.',
                    'fields' => [
                        ['name' => 'Nume', 'hint' => 'Primul contact — nu trebuie să fie formal.'],
                        ['name' => 'Sursă', 'hint' => 'De unde a venit: walk-in, telefon, social media, recomandare.'],
                        ['name' => 'Note', 'hint' => 'Ce a întrebat — obiective, buget, program preferat.'],
                    ],
                ],
                [
                    'title' => 'Programează un follow-up',
                    'body' => 'Setează întotdeauna o dată de follow-up înainte de a închide formularul.',
                    'fields' => [
                        ['name' => 'Dată follow-up', 'hint' => '24–48 de ore e de obicei potrivit. Mai mult și uită cine ești.'],
                        ['name' => 'Atribuit la', 'hint' => 'Care angajat e responsabil — previne clienți potențiali uitați.'],
                    ],
                ],
                [
                    'title' => 'Convertește în membru',
                    'body' => 'Când se înscriu, folosește acțiunea Convertește în membru — fără a retasta datele.',
                    'fields' => [
                        ['name' => 'Convertește în membru', 'hint' => 'Acțiune disponibilă pe rândul solicitării sau în pagina de detalii — creează fișă de membru din datele solicitării.'],
                    ],
                ],
            ],
            'tips' => [
                'Notează apeluri și vizite aici înainte de a deveni membri.',
                'Programează follow-up ca niciun client potențial să nu fie uitat.',
                'Convertește în membru când se înscriu.',
            ],
        ],

        'admin.follow-ups.index' => [
            'title' => 'Follow-up — contacte programate',
            'greeting' => 'Lista ta de sarcini pentru vânzări și retenție — verifică-o la începutul fiecărei ture.',
            'summary' => 'Reminder-e de sunat sau mesajat enquiry-uri și membri. Menține vânzările și retenția.',
            'steps' => [
                [
                    'title' => 'Vezi ce e scadent azi',
                    'body' => 'Sortează după dată scadentă pentru a aduce la suprafață elementele urgente.',
                    'fields' => [
                        ['name' => 'Sortare după dată scadentă', 'hint' => 'Click pe antetul coloanei pentru sortare crescătoare.'],
                        ['name' => 'Elemente restante', 'hint' => 'Follow-up-uri cu termen depășit — gestionează-le înaintea celor de azi.'],
                    ],
                ],
                [
                    'title' => 'Finalizează un follow-up',
                    'body' => 'După ce ai luat legătura, marchează-l complet și adaugă o notă.',
                    'fields' => [
                        ['name' => 'Marchează complet', 'hint' => 'Înregistrează contactul și îl scoate din lista activă.'],
                        ['name' => 'Note', 'hint' => 'Ce s-a discutat — util dacă un coleg preia în tura următoare.'],
                    ],
                ],
            ],
            'tips' => [
                'Sortează după dată scadentă pentru acțiunile de azi.',
                'Marchează complet după contact și lasă note pentru colegul de tură.',
            ],
        ],

        'admin.users.index' => [
            'title' => 'Utilizatori — conturi staff',
            'greeting' => 'Controlează cine poate accesa sistemul și ce poate face fiecare persoană.',
            'summary' => 'Persoane cu acces în panoul admin. Fiecare are un rol cu permisiuni.',
            'steps' => [
                [
                    'title' => 'Creează un utilizator nou',
                    'body' => 'Fiecare angajat ar trebui să aibă propriul cont — nu partaja login-uri.',
                    'fields' => [
                        ['name' => 'Nume + Email', 'hint' => 'Emailul devine login-ul. Folosește o adresă reală ca resetarea parolei să funcționeze.'],
                        ['name' => 'Rol', 'hint' => 'Controlează ce poate vedea și face. Staff recepție → rolul Employee.'],
                        ['name' => 'Schimbare parolă obligatorie', 'hint' => 'Activează pentru conturi noi ca aceștia să-și seteze propria parolă la prima autentificare.'],
                    ],
                ],
                [
                    'title' => 'Atribuie rolul potrivit',
                    'body' => 'Rolul e setarea cea mai importantă — determină ce panou și pagini poate accesa.',
                    'fields' => [
                        ['name' => 'Super admin', 'hint' => 'Acces complet la tot. Atribuie doar proprietarului sau managerului senior.'],
                        ['name' => 'Employee', 'hint' => 'Rol recepție — accesează doar panoul Office. Nu poate vedea Setări, financiar sau rapoarte admin.'],
                    ],
                ],
            ],
            'checklist' => [
                'Fiecare angajat are propriul cont (fără login-uri partajate)',
                'Staff recepție are rolul Employee, nu Super admin',
                'Conturile noi au „Schimbare parolă obligatorie" activat',
            ],
            'tips' => [
                'Staff recepție primește rol employee — folosește panoul Office, nu admin complet.',
                'Forțează schimbarea parolei la prima autentificare.',
                'Nu partaja contul super admin.',
            ],
        ],

        'admin.roles.index' => [
            'title' => 'Roluri și permisiuni',
            'greeting' => 'Ajustează fin cine poate face ce — modificările se aplică imediat după salvare.',
            'summary' => 'Controlează ce poate vedea și face fiecare rol (membri, facturi, setări, etc.).',
            'steps' => [
                [
                    'title' => 'Verifică permisiunile per rol',
                    'body' => 'Click pe un rol pentru a vedea și edita lista de permisiuni.',
                    'fields' => [
                        ['name' => 'Lista permisiuni', 'hint' => 'Grupate pe resurse — membri, facturi, setări, etc. Activează sau dezactivează fiecare.'],
                        ['name' => 'Salvează', 'hint' => 'Modificările se aplică imediat — toți utilizatorii cu acel rol sunt afectați pe loc.'],
                    ],
                ],
                [
                    'title' => 'După adăugarea de funcții noi',
                    'body' => 'Resursele Filament noi au nevoie de permisiunile generate înainte să apară utilizatorilor non-super-admin.',
                    'fields' => [
                        ['name' => 'Regenerare permisiuni', 'hint' => 'Rulează: php artisan shield:generate --resource=NumeResursa --panel=admin. Necesar dacă o pagină nouă returnează 404.'],
                    ],
                ],
            ],
            'checklist' => [
                'Rolul Employee nu poate accesa Setări sau rapoarte financiare',
                'Super admin atribuit doar proprietarului sau managerului senior',
                'Permisiunile testate după orice modificare de rol',
            ],
            'tips' => [
                'Modificările se aplică imediat după salvare.',
                'Super admin ocolește restricțiile — atribuie cu grijă.',
                'După funcții noi, regenerează permisiunile dacă paginile returnează 404.',
            ],
        ],

        'admin.settings.overview' => [
            'title' => 'Bine ai venit în Setări',
            'greeting' => 'Ești în locul potrivit — aici înveți aplicația cum funcționează sala ta.',
            'summary' => 'Folosește tab-urile de mai jos. Fiecare are ghid pas cu pas când becul e activ. Parcurge-le în ordine dacă configurezi prima dată.',
            'steps' => [
                [
                    'title' => 'Ordine recomandată pentru sală nouă',
                    'body' => 'Nu e obligatoriu, dar economisește timp:',
                    'fields' => [
                        ['name' => '1. Info sală', 'hint' => 'Nume, monedă, contact — baza pentru tot restul.'],
                        ['name' => '2. Membru + Factură', 'hint' => 'Numerotare înainte de membri sau facturi.'],
                        ['name' => '3. Taxe + Cheltuieli', 'hint' => 'TVA și categorii pentru contabilitate clară.'],
                        ['name' => '4. Abonamente', 'hint' => 'Când avertizezi staff-ul despre expirări.'],
                        ['name' => '5. Import / Backup', 'hint' => 'Date în masă și siguranță — când ești gata.'],
                    ],
                ],
            ],
            'tips' => [
                'Deschide un tab — ghidul de sus explică fiecare câmp pe limba ta.',
                'Bifează checklist-ul pe măsură ce avansezi; progresul se salvează în browser.',
                'Apasă mereu Salvează setările după ce editezi un tab (exceptând Import, care are buton propriu).',
            ],
            'widgets' => [],
        ],

    ],

];
