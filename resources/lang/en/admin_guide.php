<?php

return [

    'badge' => 'Admin guide',
    'toggle_on' => 'Enable admin guide',
    'toggle_off' => 'Disable admin guide',
    'enabled' => 'Admin guide enabled',
    'disabled' => 'Admin guide disabled',
    'tips_heading' => 'What to look for',
    'widgets_heading' => 'Charts & widgets explained',
    'collapse' => 'Hide guide',
    'expand' => 'Show guide',

    'pages' => [

        'admin.dashboard' => [
            'title' => 'Dashboard — business overview',
            'summary' => 'Your control panel for the gym. Use the date filter (top right) to change the period — all charts and totals update together.',
            'tips' => [
                'Start with the KPI cards: active members, expiring subscriptions, and monthly revenue.',
                'Red or warning numbers usually mean action is needed (renewals, follow-ups, unpaid invoices).',
                'Use tables at the bottom to jump directly to a member, subscription, or invoice.',
                '“Uninvoiced subscriptions” shows memberships paid without a formal invoice — useful for cash collections.',
            ],
            'widgets' => [
                'today_checkins' => 'Check-ins and check-outs recorded today — quick pulse of gym traffic.',
                'gym_overview' => 'Active members, subscriptions expiring soon, and revenue collected this month.',
                'membership_metrics' => 'New, renewed, and cancelled memberships in the selected period.',
                'financial_metrics' => 'Collected amounts, outstanding invoices, expenses, and net profit.',
                'expense_categories' => 'Donut chart of spending by category — see where money goes.',
                'membership_overview_table' => 'Recent subscription activity with status and end dates.',
                'recent_transactions' => 'Latest invoice payments — verify cash flow day to day.',
                'uninvoiced_subscriptions' => 'Active subscriptions without an invoice in this period.',
                'cashflow_trend' => 'Line chart of collections vs expenses over time.',
            ],
        ],

        'office.dashboard' => [
            'title' => 'Front desk — today at a glance',
            'summary' => 'Simplified view for reception staff: who is in the gym, today’s entries, and subscriptions that need attention.',
            'tips' => [
                'Use “Present now” to see members currently checked in.',
                'Watch expiring and expired subscriptions before members arrive at the desk.',
                'Collections today shows payments received — not the full financial report.',
            ],
            'widgets' => [
                'today_stats' => 'Check-ins, check-outs, and collections recorded today.',
                'present_now' => 'Members still in the gym after their last check-in.',
                'expiring_soon' => 'Subscriptions ending within the warning window — remind members to renew.',
                'expired_subscriptions' => 'Members whose subscription has already ended.',
            ],
        ],

        'admin.members.index' => [
            'title' => 'Members — member directory',
            'summary' => 'Complete list of gym members. Search, filter, and open a profile for subscriptions, invoices, and QR check-in.',
            'tips' => [
                'Use search for name, email, or member code.',
                'Status filters help find inactive or expired members.',
                'Click a row to see full details, QR code, and subscription history.',
                'Import many members at once from Settings → Import.',
            ],
            'widgets' => [],
        ],

        'admin.members.create' => [
            'title' => 'New member',
            'summary' => 'Register a new person in the system. You can add a subscription immediately or later.',
            'tips' => [
                'Email is recommended — used for invoices and duplicate detection on import.',
                'Member code is generated automatically from settings.',
                'After saving, open the profile to print the QR check-in code.',
            ],
            'widgets' => [],
        ],

        'admin.members.edit' => [
            'title' => 'Edit member',
            'summary' => 'Update contact details, notes, or status. Changes apply to future invoices and communications.',
            'tips' => [
                'Deactivating a member does not cancel active subscriptions — manage those separately.',
                'Keep phone and email current for receipts and reminders.',
            ],
            'widgets' => [],
        ],

        'admin.members.view' => [
            'title' => 'Member profile',
            'summary' => 'Single view of a member: personal data, active subscription, invoices, and check-in QR.',
            'tips' => [
                'Use the QR section for printing a check-in card.',
                'Subscription tab shows renewals and end dates.',
                'Invoice history lists all billing for this person.',
            ],
            'widgets' => [],
        ],

        'admin.subscriptions.index' => [
            'title' => 'Subscriptions — active memberships',
            'summary' => 'All member subscriptions: plan, dates, status, and linked invoices.',
            'tips' => [
                'Filter by status to find expired or pending renewals.',
                '“Expiring soon” uses the day count from Settings → Subscriptions.',
                'Renew from here or from the member profile.',
                'Each subscription can generate invoices automatically.',
            ],
            'widgets' => [],
        ],

        'admin.subscriptions.create' => [
            'title' => 'New subscription',
            'summary' => 'Assign a plan (and optional service) to a member with start and end dates.',
            'tips' => [
                'Pick the member first, then the plan — price comes from the plan.',
                'Discounts available are defined in Settings → Charges.',
                'Saving may create an invoice depending on your workflow.',
            ],
            'widgets' => [],
        ],

        'admin.plans.index' => [
            'title' => 'Plans — membership products',
            'summary' => 'Define what you sell: duration, price, and description. Plans are linked to subscriptions.',
            'tips' => [
                'Changing a plan price does not alter existing subscriptions.',
                'Deactivate old plans instead of deleting them if they have history.',
            ],
            'widgets' => [],
        ],

        'admin.services.index' => [
            'title' => 'Services — add-ons',
            'summary' => 'Optional extras (PT sessions, locker, etc.) that can be attached to a subscription.',
            'tips' => [
                'Services add cost on top of the base plan.',
                'Use clear names so staff select the right add-on at the desk.',
            ],
            'widgets' => [],
        ],

        'admin.check-ins.index' => [
            'title' => 'Check-ins — attendance log',
            'summary' => 'History of gym entries and exits. Used for attendance tracking and front-desk verification.',
            'tips' => [
                'Members scan QR codes at entry — each scan appears here.',
                'Expired subscriptions may still check in depending on Settings → Check-in rules.',
                'Filter by date to reconcile busy hours.',
            ],
            'widgets' => [],
        ],

        'office.check-ins.index' => [
            'title' => 'Check-ins — register attendance',
            'summary' => 'Record member entry and exit from the front desk.',
            'tips' => [
                'Scan or search the member, then confirm check-in.',
                'The system warns if the subscription is expired.',
                'Check-out when the member leaves to keep “Present now” accurate.',
            ],
            'widgets' => [],
        ],

        'admin.invoices.index' => [
            'title' => 'Invoices — billing documents',
            'summary' => 'All invoices: issued, paid, partial, or overdue. Central place for gym revenue tracking.',
            'tips' => [
                'Record payments here to update balances and send receipts.',
                'Overdue invoices are highlighted — follow up with members.',
                'PDF and email use templates from Settings → Invoice.',
                'Prefix and numbering are configured in Settings.',
            ],
            'widgets' => [],
        ],

        'admin.invoices.create' => [
            'title' => 'New invoice',
            'summary' => 'Create a bill manually for a member, often linked to a subscription.',
            'tips' => [
                'Verify tax rate and currency match Settings → Charges and Gym info.',
                'After issuing, record payments on the invoice detail page.',
            ],
            'widgets' => [],
        ],

        'admin.expenses.index' => [
            'title' => 'Expenses — gym costs',
            'summary' => 'Track rent, utilities, equipment, and other outgoing payments.',
            'tips' => [
                'Categories come from Settings → Expenses — keep them consistent for charts.',
                'Expenses feed the dashboard profit calculation (collected minus expenses).',
                'Attach notes for accounting exports.',
            ],
            'widgets' => [],
        ],

        'admin.enquiries.index' => [
            'title' => 'Enquiries — sales leads',
            'summary' => 'People interested in joining who are not members yet. First step in your sales pipeline.',
            'tips' => [
                'Log phone calls and walk-ins here before they become members.',
                'Assign follow-up dates so no lead is forgotten.',
                'Convert to member when they sign up.',
            ],
            'widgets' => [],
        ],

        'admin.follow-ups.index' => [
            'title' => 'Follow-ups — scheduled contacts',
            'summary' => 'Reminders to call or message enquiries and members. Keeps sales and retention on track.',
            'tips' => [
                'Sort by due date to see what needs action today.',
                'Mark complete after contact and add notes for the next person on shift.',
            ],
            'widgets' => [],
        ],

        'admin.users.index' => [
            'title' => 'Users — staff accounts',
            'summary' => 'People who can log into the admin panel. Each user has a role that controls permissions.',
            'tips' => [
                'Give front-desk staff the employee role — they use the Office panel, not full admin.',
                'Force password change on first login for new accounts.',
                'Never share the super admin account.',
            ],
            'widgets' => [],
        ],

        'admin.roles.index' => [
            'title' => 'Roles & permissions',
            'summary' => 'Control what each role can see and do (members, invoices, settings, etc.).',
            'tips' => [
                'Changes apply immediately after save.',
                'Super admin bypasses all restrictions — assign sparingly.',
                'After adding new features, regenerate permissions if pages return 404.',
            ],
            'widgets' => [],
        ],

        'admin.settings' => [
            'title' => 'Settings — configure the gym',
            'summary' => 'Application configuration: gym identity, billing rules, imports, and backups.',
            'tips' => [
                'Gym Info: name, logo, address, currency — appears on invoices.',
                'Invoice / Member: numbering prefixes for documents.',
                'Charges: tax rates, admission fee, discount options.',
                'Expenses: categories for the expense form and charts.',
                'Subscriptions: how many days before expiry to warn staff.',
                'Import: bulk upload members from Excel.',
                'Backup: schedule and restore database copies.',
                'Toggle the light bulb icon in your profile menu (next to light/dark theme) to show or hide contextual help.',
            ],
            'widgets' => [],
        ],

    ],

];
