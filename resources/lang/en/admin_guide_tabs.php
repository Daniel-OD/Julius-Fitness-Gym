<?php

return [

    'admin.settings.tabs.gym_info' => [
        'title' => 'Let\'s set up your gym profile',
        'greeting' => 'Hi! This is the first thing you should complete — clients will see this info on invoices and emails.',
        'summary' => 'Take 5 minutes to fill in the basics. You can always come back and edit later.',
        'steps' => [
            [
                'title' => 'Name, logo and currency',
                'body' => 'These three fields define how your gym appears on every document.',
                'fields' => [
                    ['name' => 'Gym name', 'hint' => 'Use the name clients know — e.g. Julius Fitness Gym.'],
                    ['name' => 'Logo', 'hint' => 'Square PNG works best. Skip for now if you don\'t have one yet.'],
                    ['name' => 'Currency', 'hint' => 'Choose RON if you bill in lei. Hard to change later without reviewing old invoices.'],
                ],
            ],
            [
                'title' => 'Financial year (optional for now)',
                'body' => 'Only needed if your accountant uses a custom fiscal year. Most gyms leave the default calendar year.',
                'fields' => [
                    ['name' => 'Financial year start / end', 'hint' => 'Set both dates if you use a non-calendar fiscal year.'],
                ],
            ],
            [
                'title' => 'Address',
                'body' => 'Shows on invoices and official documents. Pick country first — county and city lists load automatically.',
                'fields' => [
                    ['name' => 'Address', 'hint' => 'Street, number, building — as on your registration.'],
                    ['name' => 'Country → State → City', 'hint' => 'Fill in order: country, then county, then city.'],
                    ['name' => 'ZIP / Postcode', 'hint' => 'Optional but recommended for complete invoices.'],
                ],
            ],
            [
                'title' => 'Contact details',
                'body' => 'Members and the system use these for receipts and notifications.',
                'fields' => [
                    ['name' => 'Email address', 'hint' => 'The gym\'s main email — not your personal one unless it\'s the same.'],
                    ['name' => 'Contact number', 'hint' => 'Reception or office phone clients can call.'],
                ],
            ],
        ],
        'checklist' => [
            'Gym name is filled in',
            'Currency is set correctly',
            'At least email or phone is added',
            'Address is complete (if you issue invoices)',
        ],
        'save_reminder' => 'Done? Scroll down and click Save settings — changes are not applied until you save.',
    ],

    'admin.settings.tabs.invoice' => [
        'title' => 'Invoice numbering and emails',
        'greeting' => 'Here you control how invoice numbers are generated and whether clients receive emails automatically.',
        'summary' => 'Set this up once before issuing your first invoice — it avoids gaps or duplicate numbers.',
        'steps' => [
            [
                'title' => 'Invoice numbering',
                'body' => 'Each new invoice gets the next number automatically. The prefix helps you recognise gym invoices at a glance.',
                'fields' => [
                    ['name' => 'Prefix', 'hint' => 'Example: INV- or JFG-. Appears before the number: INV-00042.'],
                    ['name' => 'Last invoice number', 'hint' => 'If you already issued invoices elsewhere, set this to your last number so the next one continues correctly. New gym? Leave 0 or 1.'],
                    ['name' => 'Brand display', 'hint' => 'Choose gym name or logo at the top of the PDF — whatever looks more professional for you.'],
                ],
            ],
            [
                'title' => 'Email templates',
                'body' => 'Customize subject lines. Keep the tokens {invoice_number}, {member_name}, {gym_name} — they are replaced automatically.',
                'fields' => [
                    ['name' => 'Invoice email subject', 'hint' => 'Example: Invoice {invoice_number} from {gym_name}'],
                    ['name' => 'Receipt email subject', 'hint' => 'Sent when a payment is recorded.'],
                ],
            ],
            [
                'title' => 'Automatic sending',
                'body' => 'Turn these on only when your email (SMTP) is configured on the server. Otherwise invoices stay in the app and you send manually.',
                'fields' => [
                    ['name' => 'Enable email sending', 'hint' => 'Master switch for all invoice emails.'],
                    ['name' => 'Auto-send on issue', 'hint' => 'Email goes out as soon as you create an invoice.'],
                    ['name' => 'Auto-send receipt on payment', 'hint' => 'Client gets confirmation when you record a payment.'],
                ],
            ],
        ],
        'checklist' => [
            'Prefix chosen and last number set',
            'Brand display selected (name or logo)',
            'Email toggles match how you want to work (manual vs automatic)',
        ],
        'save_reminder' => 'Save settings after changing numbering — the next invoice uses these values immediately.',
    ],

    'admin.settings.tabs.mail' => [
        'title' => 'Email delivery (SMTP / Resend)',
        'greeting' => 'Configure how the gym sends emails: invoices, member verification, portal invitations, and reminders.',
        'summary' => 'Set this up before enabling automatic invoice emails or member registration on production.',
        'steps' => [
            [
                'title' => 'Choose transport',
                'body' => 'Pick how mail leaves the server. Hosted on Render, Railway, or Docker? Use Environment (.env) and set RESEND_API_KEY in the platform dashboard. Local Windows/macOS? Use Resend or SMTP here.',
                'fields' => [
                    ['name' => 'Mail transport', 'hint' => 'Environment = .env / platform variables. Resend = API key. SMTP = classic mail server. Log = development only (writes to logs).'],
                    ['name' => 'From address', 'hint' => 'Must match a verified domain (Resend) or allowed sender (SMTP). Example: noreply@yourgym.com'],
                    ['name' => 'From name', 'hint' => 'How recipients see the sender — usually your gym name.'],
                ],
            ],
            [
                'title' => 'Resend or SMTP credentials',
                'body' => 'Resend: paste API key from resend.com after verifying your domain. SMTP: host, port, username, password from your email provider (Gmail, Office 365, etc.).',
                'fields' => [
                    ['name' => 'Resend API key', 'hint' => 'Starts with re_. Leave blank when saving to keep the existing key.'],
                    ['name' => 'SMTP host / port', 'hint' => 'Common: smtp.gmail.com:587 with TLS.'],
                ],
            ],
            [
                'title' => 'Test before going live',
                'body' => 'Click Send test email — it goes to your admin account inbox. Fix errors before enabling auto-send on the Invoice tab.',
                'fields' => [
                    ['name' => 'Send test email', 'hint' => 'Uses current form values (save after testing if it works).'],
                ],
            ],
        ],
        'checklist' => [
            'Transport chosen and credentials saved',
            'Test email received in inbox',
            'Queue worker running on production (emails are queued)',
        ],
        'save_reminder' => 'Save settings after changing mail transport — queued jobs use the saved configuration.',
    ],

    'admin.settings.tabs.member' => [
        'title' => 'Member codes',
        'greeting' => 'Every member gets a unique code — like an invoice prefix, but for people.',
        'summary' => 'Configure this before adding members manually or importing from Excel.',
        'steps' => [
            [
                'title' => 'How member codes work',
                'body' => 'When you create a member, the app generates the next code: prefix + number. You rarely type codes by hand.',
                'fields' => [
                    ['name' => 'Prefix', 'hint' => 'Example: MEM- or JFG-M-. Keep it short.'],
                    ['name' => 'Last number', 'hint' => 'If importing existing members with codes, set this to your highest number so new ones don\'t collide.'],
                ],
            ],
        ],
        'checklist' => [
            'Prefix defined',
            'Last number reflects existing members (if any)',
        ],
        'save_reminder' => 'Save before creating or importing members.',
    ],

    'admin.settings.tabs.charges' => [
        'title' => 'Taxes, fees and discounts',
        'greeting' => 'These values appear when you create subscriptions and invoices — set them once, use them everywhere.',
        'summary' => 'Wrong tax rate = wrong invoices. Double-check before going live.',
        'steps' => [
            [
                'title' => 'Admission fee',
                'body' => 'One-time fee for new members (registration). Enter 0 if you don\'t charge separately.',
                'fields' => [
                    ['name' => 'Admission fee', 'hint' => 'Amount in your gym currency, without VAT if you add VAT separately.'],
                ],
            ],
            [
                'title' => 'Tax rate (VAT)',
                'body' => 'The percentage applied on invoices. In Romania this is often 19% for standard services — confirm with your accountant.',
                'fields' => [
                    ['name' => 'Tax rates (%)', 'hint' => 'Enter 19 for 19% VAT. Use the rate your accountant approved.'],
                ],
            ],
            [
                'title' => 'Discount options',
                'body' => 'Predefine allowed discount percentages so staff pick from a list instead of typing random numbers.',
                'fields' => [
                    ['name' => 'Discount options (%)', 'hint' => 'Type a number (e.g. 10) and press Enter. Add 10, 15, 20 if those are your standard offers.'],
                ],
            ],
        ],
        'checklist' => [
            'VAT / tax rate matches your accounting setup',
            'Admission fee set (or 0)',
            'Common discount percentages added',
        ],
        'save_reminder' => 'Save — new subscriptions and invoices will use these rates.',
    ],

    'admin.settings.tabs.expenses' => [
        'title' => 'Expense categories',
        'greeting' => 'Categories keep your spending organised and make the dashboard charts meaningful.',
        'summary' => 'Think about how you talk about costs with your accountant — use the same category names here.',
        'steps' => [
            [
                'title' => 'Build your category list',
                'body' => 'When someone records an expense, they pick from this list. Consistent names = clear reports.',
                'fields' => [
                    ['name' => 'Expense categories', 'hint' => 'Type a name (Rent, Utilities, Payroll…) and press Enter after each. Start with 5–8 categories, add more later.'],
                ],
            ],
        ],
        'checklist' => [
            'At least Rent, Utilities and Payroll (or equivalents) added',
            'Names match what you use in accounting',
        ],
        'save_reminder' => 'Save — categories appear immediately in Expenses and on the dashboard chart.',
    ],

    'admin.settings.tabs.subscriptions' => [
        'title' => 'Subscriptions and check-in',
        'greeting' => 'Control when staff get warned about expiring memberships and how the front desk sees who\'s in the gym.',
        'summary' => 'Small numbers here prevent awkward moments at reception (“I thought I was still active!”).',
        'steps' => [
            [
                'title' => 'Expiry warnings',
                'body' => 'Dashboard and front desk highlight subscriptions ending within this many days.',
                'fields' => [
                    ['name' => 'Warn expiring subscriptions (days)', 'hint' => '7 is a good default — staff can call or message members a week before expiry. Use 14 if renewals need more lead time.'],
                ],
            ],
            [
                'title' => 'Present now (front desk)',
                'body' => 'After check-out, members stay on the “Present now” list for a few minutes — useful if they forgot to scan out.',
                'fields' => [
                    ['name' => 'Present now grace period (minutes)', 'hint' => '15 minutes works for most gyms. Set 0 for instant removal after check-out.'],
                ],
            ],
        ],
        'checklist' => [
            'Expiring days set (7 or 14)',
            'Grace period set for front desk display',
        ],
        'save_reminder' => 'Save — warnings update on dashboard and office panel.',
    ],

    'admin.settings.tabs.import' => [
        'title' => 'Import members from Excel',
        'greeting' => 'Moving from another system or a spreadsheet? You can bulk-import members here.',
        'summary' => 'Follow the wizard step by step — you can download a template to avoid formatting surprises.',
        'steps' => [
            [
                'title' => 'Step 1 — Upload',
                'body' => 'Use .xlsx or .csv. Download the template first if you\'re unsure about column layout.',
                'fields' => [
                    ['name' => 'Template', 'hint' => 'Click “Download Excel template” — fill it, then upload.'],
                    ['name' => 'Required data', 'hint' => 'Each row needs at least an email OR a name.'],
                ],
            ],
            [
                'title' => 'Step 2 — Map columns',
                'body' => 'Match each file column to a member field. Ignore columns you don\'t need.',
                'fields' => [
                    ['name' => 'Email / Name', 'hint' => 'Map at least one — the import will skip rows with neither.'],
                    ['name' => 'First row headers', 'hint' => 'Keep checked if row 1 contains column titles.'],
                ],
            ],
            [
                'title' => 'Step 3 — Confirm',
                'body' => 'Review the preview, choose what to do with duplicate emails, then import.',
                'fields' => [
                    ['name' => 'Duplicate emails', 'hint' => 'Skip = keep existing member. Update = overwrite with file data.'],
                ],
            ],
        ],
        'checklist' => [
            'Member prefix configured (Settings → Member tab)',
            'File prepared from template or exported list',
            'Duplicate policy chosen before import',
        ],
        'tips' => [
            'Import does not create subscriptions — add those separately or after import.',
            'Download the error report if some rows fail; fix the file and re-import.',
        ],
        'save_reminder' => 'Import runs on its own button — other tabs still need Save settings.',
    ],

    'admin.settings.tabs.backup' => [
        'title' => 'Backup and restore',
        'greeting' => 'Your member and invoice data is valuable — set up backups before you need them.',
        'summary' => 'You can backup manually anytime or schedule automatic copies to a folder on your computer (synced to cloud).',
        'steps' => [
            [
                'title' => 'Automatic backup',
                'body' => 'Point to a folder that syncs with Google Drive, iCloud or OneDrive so copies survive disk failure.',
                'fields' => [
                    ['name' => 'Enable backup', 'hint' => 'Turn on when path is configured.'],
                    ['name' => 'Backup folder path', 'hint' => 'Full path on the server/PC where the app runs — e.g. C:\\Users\\You\\Google Drive\\GymBackups'],
                    ['name' => 'When to backup', 'hint' => 'End of day fits most gyms. “After member” creates a copy on every new registration.'],
                    ['name' => 'Keep last N backups', 'hint' => '5–10 is usually enough; older files are deleted automatically.'],
                ],
            ],
            [
                'title' => 'Manual backup and restore',
                'body' => 'Use “Backup now” before big changes. Restore only when you\'re sure — it replaces the whole database.',
                'fields' => [
                    ['name' => 'Restore ZIP', 'hint' => 'A safety backup is created automatically before restore.'],
                    ['name' => 'Include settings', 'hint' => 'Check if the backup should also restore currency, prefixes, etc.'],
                ],
            ],
        ],
        'checklist' => [
            'Backup path tested (folder exists and is writable)',
            'At least one manual backup taken',
            'Team knows restore is last resort only',
        ],
        'save_reminder' => 'Save after changing schedule or path — then try “Backup now” to verify it works.',
    ],

];
