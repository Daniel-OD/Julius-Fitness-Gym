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
    'steps_heading' => 'Follow these steps',
    'checklist_heading' => 'Your setup checklist',

    'pages' => [

        'admin.dashboard' => [
            'title' => 'Dashboard — business overview',
            'greeting' => 'Your command centre — the best place to start every morning.',
            'summary' => 'Use the date filter (top right) to change the period — all charts and totals update together.',
            'steps' => [
                [
                    'title' => 'Set your period',
                    'body' => 'The date filter top-right controls everything on this page. Change it first.',
                    'fields' => [
                        ['name' => 'Date filter', 'hint' => 'Pick today, this week, this month, or a custom range. All cards and charts follow.'],
                    ],
                ],
                [
                    'title' => 'Read the KPI cards',
                    'body' => 'Four tiles summarise where the gym stands right now.',
                    'fields' => [
                        ['name' => 'Active members', 'hint' => 'Members with an ongoing subscription.'],
                        ['name' => 'Expiring soon', 'hint' => 'Subscriptions ending within your warning window — act before they lapse.'],
                        ['name' => 'Revenue this month', 'hint' => 'Payments collected in the selected period.'],
                        ['name' => 'Outstanding invoices', 'hint' => 'Money owed but not yet collected.'],
                    ],
                ],
                [
                    'title' => 'Act on warning signals',
                    'body' => 'Red or amber numbers usually mean something needs attention today.',
                    'fields' => [
                        ['name' => 'Expiring subscriptions', 'hint' => 'Open the row to renew directly from the table.'],
                        ['name' => 'Overdue invoices', 'hint' => 'Follow up with the member before the debt grows.'],
                        ['name' => 'Uninvoiced subscriptions', 'hint' => 'Active plans with no formal invoice — common for cash collections.'],
                    ],
                ],
            ],
            'tips' => [
                'Start with the KPI cards: active members, expiring subscriptions, and monthly revenue.',
                'Red or warning numbers usually mean action is needed (renewals, follow-ups, unpaid invoices).',
                'Use tables at the bottom to jump directly to a member, subscription, or invoice.',
                '"Uninvoiced subscriptions" shows memberships paid without a formal invoice — useful for cash collections.',
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
            'greeting' => 'Start of shift — everything you need before the first member walks through the door.',
            'summary' => 'Simplified view for reception staff: who is in the gym, today\'s entries, and subscriptions that need attention.',
            'steps' => [
                [
                    'title' => 'Check who\'s currently in',
                    'body' => 'Before you do anything else, glance at the Present now widget.',
                    'fields' => [
                        ['name' => 'Present now', 'hint' => 'Members who checked in but haven\'t checked out yet. Should be empty at shift start.'],
                    ],
                ],
                [
                    'title' => 'Review expiring and expired subscriptions',
                    'body' => 'Spot members who need a renewal call or an in-person conversation.',
                    'fields' => [
                        ['name' => 'Expiring soon', 'hint' => 'Still valid but ending soon — mention renewal when they arrive.'],
                        ['name' => 'Expired', 'hint' => 'Subscription already ended — flag to manager if needed.'],
                    ],
                ],
                [
                    'title' => 'Log today\'s collections',
                    'body' => 'Verify that payments received during your shift appear in the totals.',
                    'fields' => [
                        ['name' => 'Collections today', 'hint' => 'Payments recorded in this shift — not the full financial report.'],
                    ],
                ],
            ],
            'tips' => [
                'Use "Present now" to see members currently checked in.',
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
            'greeting' => 'Your full member directory — the starting point for everything member-related.',
            'summary' => 'Search, filter, and open a profile to manage subscriptions, invoices, and check-in history.',
            'steps' => [
                [
                    'title' => 'Find a member',
                    'body' => 'The fastest route to any member record.',
                    'fields' => [
                        ['name' => 'Search bar', 'hint' => 'Type a name, email address, or member code. Results appear instantly.'],
                        ['name' => 'Status filter', 'hint' => 'Narrow down to active, inactive, or all members.'],
                    ],
                ],
                [
                    'title' => 'Open a member profile',
                    'body' => 'Click any row to open the full profile — subscriptions and personal details all in one place.',
                    'fields' => [
                        ['name' => 'Row click', 'hint' => 'Opens the member view with their details and subscription history.'],
                        ['name' => 'Action menu', 'hint' => 'Quick actions per row: edit, delete, or other available operations.'],
                    ],
                ],
                [
                    'title' => 'Add a new member',
                    'body' => 'Use the button in the top-right corner to register someone new.',
                    'fields' => [
                        ['name' => 'New member button', 'hint' => 'Takes you to the registration form — takes about 60 seconds.'],
                        ['name' => 'Bulk import', 'hint' => 'Moving from another system? Use Settings → Import to upload an Excel file.'],
                    ],
                ],
            ],
            'tips' => [
                'Use search for name, email, or member code.',
                'Status filters help find inactive or expired members.',
                'Click a row to see full details and subscription history.',
                'Import many members at once from Settings → Import.',
            ],
        ],

        'admin.members.create' => [
            'title' => 'New member',
            'greeting' => 'Registering someone new? Takes about 60 seconds.',
            'summary' => 'Register a new person in the system. You can add a subscription immediately or later.',
            'steps' => [
                [
                    'title' => 'Fill in the details',
                    'body' => 'Only name is required, but the more you add now, the less you need to chase later.',
                    'fields' => [
                        ['name' => 'Name', 'hint' => 'Full name as the member uses it.'],
                        ['name' => 'Email', 'hint' => 'Recommended — used for invoice emails and duplicate detection on import.'],
                        ['name' => 'Contact', 'hint' => 'Phone number for renewal reminders and follow-ups.'],
                    ],
                ],
                [
                    'title' => 'Check the member code',
                    'body' => 'The code is generated automatically from the prefix in Settings → Member.',
                    'fields' => [
                        ['name' => 'Member code', 'hint' => 'Edit only if you need a specific code (e.g. migrating from another system).'],
                    ],
                ],
                [
                    'title' => 'Save and add a subscription',
                    'body' => 'After saving the member, open their profile to create a subscription.',
                    'fields' => [
                        ['name' => 'Save button', 'hint' => 'Creates the member record. You\'ll be taken to their profile.'],
                        ['name' => 'Add subscription', 'hint' => 'Available on the member profile — assign a plan, dates, and generate an invoice.'],
                    ],
                ],
            ],
            'tips' => [
                'Email is recommended — used for invoices and duplicate detection on import.',
                'Member code is generated automatically from settings.',
                'After saving, open the profile to add a subscription.',
            ],
        ],

        'admin.members.edit' => [
            'title' => 'Edit member',
            'greeting' => 'Updating this member\'s details — changes apply to future invoices and communications.',
            'summary' => 'Update contact details, notes, or status. Changes apply to future invoices and communications.',
            'steps' => [
                [
                    'title' => 'Update the fields you need',
                    'body' => 'Any field can be changed. Contact details are the most commonly updated.',
                    'fields' => [
                        ['name' => 'Email / Contact', 'hint' => 'Keep these current for receipts and renewal reminders.'],
                        ['name' => 'Notes', 'hint' => 'Internal notes visible only to staff — useful for health info or special arrangements.'],
                        ['name' => 'Status', 'hint' => 'Deactivating a member does not cancel active subscriptions — manage those separately.'],
                    ],
                ],
                [
                    'title' => 'Save your changes',
                    'body' => 'Click Save to apply. Changes take effect on the next invoice or communication.',
                    'fields' => [
                        ['name' => 'Save button', 'hint' => 'Bottom of the form — don\'t navigate away without saving.'],
                    ],
                ],
            ],
            'tips' => [
                'Deactivating a member does not cancel active subscriptions — manage those separately.',
                'Keep phone and email current for receipts and reminders.',
            ],
        ],

        'admin.members.view' => [
            'title' => 'Member profile',
            'greeting' => 'Full picture of this member — personal details and subscription history in one place.',
            'summary' => 'Single view of a member: personal data, contact details, and active subscriptions.',
            'steps' => [
                [
                    'title' => 'Review personal details',
                    'body' => 'The top section shows contact info, goals, and health notes.',
                    'fields' => [
                        ['name' => 'Details section', 'hint' => 'Name, email, contact, source — everything entered at registration.'],
                        ['name' => 'Edit button', 'hint' => 'Header button to update any detail without leaving this page.'],
                    ],
                ],
                [
                    'title' => 'Manage subscriptions',
                    'body' => 'The Subscriptions panel below the details lists current and past memberships.',
                    'fields' => [
                        ['name' => 'Subscriptions panel', 'hint' => 'Shows plan, dates, status, and linked invoice for each subscription.'],
                        ['name' => 'Add subscription', 'hint' => 'Button in the panel to create a new subscription for this member.'],
                        ['name' => 'Renew', 'hint' => 'Row action on an active subscription — opens the renewal form with dates pre-filled.'],
                    ],
                ],
            ],
            'tips' => [
                'Subscription panel shows renewals and end dates.',
                'Invoice history is available on each subscription row.',
                'Use the Edit button to update contact details without leaving this page.',
            ],
        ],

        'admin.subscriptions.index' => [
            'title' => 'Subscriptions — active memberships',
            'greeting' => 'Your renewal pipeline — keep expiring memberships from slipping through the cracks.',
            'summary' => 'All member subscriptions: plan, dates, status, and linked invoices.',
            'steps' => [
                [
                    'title' => 'Filter to find what needs action',
                    'body' => 'The most useful filter is status — use it to focus on what matters today.',
                    'fields' => [
                        ['name' => 'Status filter', 'hint' => '"Expiring soon" and "Expired" are the ones to check first every day.'],
                        ['name' => 'Member filter', 'hint' => 'See all subscriptions for one specific person.'],
                    ],
                ],
                [
                    'title' => 'Renew a subscription',
                    'body' => 'Open the row action menu to renew — the form pre-fills the plan and new start date.',
                    'fields' => [
                        ['name' => 'Renew action', 'hint' => 'Opens a renewal form. Confirm dates, choose discount if applicable, and save.'],
                        ['name' => 'New subscription button', 'hint' => 'Creates a brand-new subscription for any member, independent of renewal.'],
                    ],
                ],
                [
                    'title' => 'Check linked invoices',
                    'body' => 'Each subscription shows whether it has a linked invoice.',
                    'fields' => [
                        ['name' => 'Invoice column', 'hint' => 'Click the invoice link to open the billing details.'],
                    ],
                ],
            ],
            'tips' => [
                'Filter by status to find expired or pending renewals.',
                '"Expiring soon" uses the day count from Settings → Subscriptions.',
                'Renew from here or from the member profile.',
                'Each subscription can generate invoices automatically.',
            ],
        ],

        'admin.subscriptions.create' => [
            'title' => 'New subscription',
            'greeting' => 'Adding a membership for someone — three decisions to make and you\'re done.',
            'summary' => 'Assign a plan (and optional service) to a member with start and end dates.',
            'steps' => [
                [
                    'title' => 'Choose member and plan',
                    'body' => 'Select the member first, then pick the plan — price and duration come from the plan automatically.',
                    'fields' => [
                        ['name' => 'Member', 'hint' => 'Search by name or member code.'],
                        ['name' => 'Plan', 'hint' => 'Shows plan name, price, and duration in days. End date fills in automatically.'],
                        ['name' => 'Start date', 'hint' => 'Defaults to today. Changing it recalculates the end date.'],
                        ['name' => 'End date', 'hint' => 'Calculated from the plan — read-only unless you manually override.'],
                    ],
                ],
                [
                    'title' => 'Set the invoice',
                    'body' => 'An invoice is created together with the subscription. Review the amounts before saving.',
                    'fields' => [
                        ['name' => 'Discount', 'hint' => 'Pick from the preset list (configured in Settings → Charges). Amounts update automatically.'],
                        ['name' => 'Paid amount', 'hint' => 'Enter what was collected now. The due amount shows the remainder.'],
                        ['name' => 'Payment method', 'hint' => 'Cash, card, or transfer — affects how the receipt is recorded.'],
                    ],
                ],
            ],
            'tips' => [
                'Pick the member first, then the plan — price comes from the plan.',
                'Discounts available are defined in Settings → Charges.',
                'End date is calculated automatically from the plan duration.',
            ],
        ],

        'admin.plans.index' => [
            'title' => 'Plans — membership products',
            'greeting' => 'Plans are what you sell — keep this list clean and current.',
            'summary' => 'Define what you sell: duration, price, and description. Plans are linked to subscriptions.',
            'steps' => [
                [
                    'title' => 'Create a new plan',
                    'body' => 'Click New plan and fill in the basics. Members pick from this list when subscribing.',
                    'fields' => [
                        ['name' => 'Name', 'hint' => 'e.g. Monthly, Quarterly, Annual — keep it short and recognisable.'],
                        ['name' => 'Duration (days)', 'hint' => 'Used to auto-calculate subscription end dates.'],
                        ['name' => 'Price', 'hint' => 'Base price before tax. Tax is applied from Settings → Charges.'],
                    ],
                ],
                [
                    'title' => 'Manage existing plans',
                    'body' => 'Edit prices or deactivate plans you no longer offer. Never delete a plan with subscription history.',
                    'fields' => [
                        ['name' => 'Active toggle', 'hint' => 'Turn off plans you no longer sell without losing historical data.'],
                        ['name' => 'Edit price', 'hint' => 'Only affects future subscriptions — past ones keep their original price.'],
                    ],
                ],
            ],
            'checklist' => [
                'At least one active plan exists',
                'Plan prices reflect current rates',
                'Old or discontinued plans are deactivated, not deleted',
            ],
            'tips' => [
                'Changing a plan price does not alter existing subscriptions.',
                'Deactivate old plans instead of deleting them if they have history.',
            ],
        ],

        'admin.services.index' => [
            'title' => 'Services — add-ons',
            'greeting' => 'Optional extras that can be attached to any subscription — PT, locker, sauna, and more.',
            'summary' => 'Optional extras (PT sessions, locker, etc.) that can be attached to a subscription.',
            'steps' => [
                [
                    'title' => 'Create a service',
                    'body' => 'Click New service. Keep names short and obvious so front desk picks the right one.',
                    'fields' => [
                        ['name' => 'Name', 'hint' => 'e.g. Personal Training, Locker Rental, Sauna Access.'],
                        ['name' => 'Price', 'hint' => 'Added on top of the base plan price when attached to a subscription.'],
                    ],
                ],
                [
                    'title' => 'Attach to subscriptions',
                    'body' => 'Services appear as an optional field on the subscription form.',
                    'fields' => [
                        ['name' => 'Service field (on subscription form)', 'hint' => 'Select when creating or renewing a subscription. Adds the service price to the total.'],
                    ],
                ],
            ],
            'checklist' => [
                'Service names are clear for front desk staff',
                'Service prices are correct and include any markup',
            ],
            'tips' => [
                'Services add cost on top of the base plan.',
                'Use clear names so staff select the right add-on at the desk.',
            ],
        ],

        'admin.check-ins.index' => [
            'title' => 'Check-ins — attendance log',
            'greeting' => 'The complete attendance record — useful for disputes, traffic analysis, and reporting.',
            'summary' => 'History of gym entries and exits. Used for attendance tracking and front-desk verification.',
            'steps' => [
                [
                    'title' => 'Find entries',
                    'body' => 'Use filters to narrow the list to what you need.',
                    'fields' => [
                        ['name' => 'Date filter', 'hint' => 'Narrow down to a specific day or shift.'],
                        ['name' => 'Member filter', 'hint' => 'See the full attendance history for one person.'],
                    ],
                ],
                [
                    'title' => 'Verify a check-in',
                    'body' => 'Each row shows the member, check-in time, and check-out time.',
                    'fields' => [
                        ['name' => 'Check-in / Check-out columns', 'hint' => 'Pair of timestamps per visit. A missing check-out means the member is still in the gym.'],
                    ],
                ],
            ],
            'tips' => [
                'Members scan QR codes at entry — each scan appears here.',
                'Expired subscriptions may still check in depending on Settings → Check-in rules.',
                'Filter by date to reconcile busy hours.',
            ],
        ],

        'office.check-ins.index' => [
            'title' => 'Check-ins — register attendance',
            'greeting' => 'Your core desk task — log every arrival and departure so the system stays accurate.',
            'summary' => 'Record member entry and exit from the front desk.',
            'steps' => [
                [
                    'title' => 'Check a member in',
                    'body' => 'Find the member and record their arrival.',
                    'fields' => [
                        ['name' => 'Search / Scan', 'hint' => 'Search by name or scan a QR code. The system warns if the subscription is expired.'],
                        ['name' => 'Check in button', 'hint' => 'Records a timestamp. Member appears in "Present now" on the dashboard.'],
                    ],
                ],
                [
                    'title' => 'Check a member out',
                    'body' => 'Record departure so "Present now" stays clean.',
                    'fields' => [
                        ['name' => 'Check out button', 'hint' => 'Only available for members who are currently checked in. Removes them from "Present now".'],
                    ],
                ],
            ],
            'tips' => [
                'Scan or search the member, then confirm check-in.',
                'The system warns if the subscription is expired.',
                'Check-out when the member leaves to keep "Present now" accurate.',
            ],
        ],

        'admin.invoices.index' => [
            'title' => 'Invoices — billing documents',
            'greeting' => 'Your billing register — keep it accurate and up to date.',
            'summary' => 'All invoices: issued, paid, partial, or overdue. Central place for gym revenue tracking.',
            'steps' => [
                [
                    'title' => 'Find what you need',
                    'body' => 'The status filter is your best friend here.',
                    'fields' => [
                        ['name' => 'Status filter', 'hint' => '"Overdue" and "Unpaid" are the ones to check first.'],
                        ['name' => 'Member filter', 'hint' => 'See all invoices for one person.'],
                        ['name' => 'Date range', 'hint' => 'Filter by invoice date or due date.'],
                    ],
                ],
                [
                    'title' => 'Record a payment',
                    'body' => 'Open the invoice, then use the Add payment action.',
                    'fields' => [
                        ['name' => 'Add payment', 'hint' => 'Enter the amount collected and the payment method. Balance updates automatically.'],
                        ['name' => 'Partial payment', 'hint' => 'Allowed — the due amount tracks the remainder.'],
                    ],
                ],
                [
                    'title' => 'Send to the member',
                    'body' => 'Deliver the invoice by email or PDF download.',
                    'fields' => [
                        ['name' => 'Send email', 'hint' => 'Sends the PDF invoice to the member\'s email. Requires SMTP configured in Settings.'],
                        ['name' => 'Download PDF', 'hint' => 'For manual delivery — hand it to the member or print it.'],
                    ],
                ],
            ],
            'tips' => [
                'Record payments here to update balances and send receipts.',
                'Overdue invoices are highlighted — follow up with members.',
                'PDF and email use templates from Settings → Invoice.',
                'Prefix and numbering are configured in Settings.',
            ],
        ],

        'admin.invoices.create' => [
            'title' => 'New invoice',
            'greeting' => 'Creating a bill manually — most invoices are created automatically with subscriptions, but you can always add one here.',
            'summary' => 'Create a bill manually for a member, often linked to a subscription.',
            'steps' => [
                [
                    'title' => 'Select the member and subscription',
                    'body' => 'Link the invoice to the right person and their active subscription.',
                    'fields' => [
                        ['name' => 'Member', 'hint' => 'Start typing to search by name or code.'],
                        ['name' => 'Subscription', 'hint' => 'Optional link — auto-fills the amount from the plan if selected.'],
                    ],
                ],
                [
                    'title' => 'Review the amounts',
                    'body' => 'Check tax rate and currency before issuing — you can\'t change them after.',
                    'fields' => [
                        ['name' => 'Subscription fee', 'hint' => 'Base amount before tax and discount.'],
                        ['name' => 'Tax', 'hint' => 'Applied from Settings → Charges. Confirm with your accountant.'],
                        ['name' => 'Discount', 'hint' => 'Optional. Pick from the preset list or enter a custom amount.'],
                        ['name' => 'Due date', 'hint' => 'Payment deadline shown on the invoice.'],
                    ],
                ],
            ],
            'tips' => [
                'Verify tax rate and currency match Settings → Charges and Gym info.',
                'After issuing, record payments on the invoice detail page.',
            ],
        ],

        'admin.expenses.index' => [
            'title' => 'Expenses — gym costs',
            'greeting' => 'Track every cost so the dashboard profit figure is real, not estimated.',
            'summary' => 'Track rent, utilities, equipment, and other outgoing payments.',
            'steps' => [
                [
                    'title' => 'Add an expense',
                    'body' => 'Use the New expense button. Fill in the category, amount, and date.',
                    'fields' => [
                        ['name' => 'Category', 'hint' => 'From your list in Settings → Expenses. Consistent categories = clear monthly reports.'],
                        ['name' => 'Amount', 'hint' => 'Full cost. Include VAT if it\'s not reclaimed.'],
                        ['name' => 'Date', 'hint' => 'When the cost was incurred, not when you enter it.'],
                        ['name' => 'Notes', 'hint' => 'Supplier name, invoice reference, or a short memo for accounting.'],
                    ],
                ],
                [
                    'title' => 'Review your spending',
                    'body' => 'Use filters to see costs by category or time period.',
                    'fields' => [
                        ['name' => 'Category filter', 'hint' => 'See all rent, all utilities, etc. separately.'],
                        ['name' => 'Date filter', 'hint' => 'Monthly view helps with budget comparisons.'],
                    ],
                ],
            ],
            'tips' => [
                'Categories come from Settings → Expenses — keep them consistent for charts.',
                'Expenses feed the dashboard profit calculation (collected minus expenses).',
                'Attach notes for accounting exports.',
            ],
        ],

        'admin.enquiries.index' => [
            'title' => 'Enquiries — sales leads',
            'greeting' => 'Every potential new member starts here — don\'t let them go cold.',
            'summary' => 'People interested in joining who are not members yet. First step in your sales pipeline.',
            'steps' => [
                [
                    'title' => 'Log a new enquiry',
                    'body' => 'Capture the lead while the conversation is fresh.',
                    'fields' => [
                        ['name' => 'Name', 'hint' => 'First contact name — doesn\'t need to be formal.'],
                        ['name' => 'Source', 'hint' => 'Where they came from: walk-in, phone, social media, referral.'],
                        ['name' => 'Notes', 'hint' => 'What they asked about — goals, budget, preferred schedule.'],
                    ],
                ],
                [
                    'title' => 'Schedule a follow-up',
                    'body' => 'Always set a follow-up date before closing the form.',
                    'fields' => [
                        ['name' => 'Follow-up date', 'hint' => '24–48 hours is usually right. Longer and they forget who you are.'],
                        ['name' => 'Assign to', 'hint' => 'Which staff member is responsible — prevents leads falling through the cracks.'],
                    ],
                ],
                [
                    'title' => 'Convert to member',
                    'body' => 'When they sign up, use the Convert to member action — no re-typing.',
                    'fields' => [
                        ['name' => 'Convert to member', 'hint' => 'Action on the enquiry row or view page — creates a member record from the enquiry data.'],
                    ],
                ],
            ],
            'tips' => [
                'Log phone calls and walk-ins here before they become members.',
                'Assign follow-up dates so no lead is forgotten.',
                'Convert to member when they sign up.',
            ],
        ],

        'admin.follow-ups.index' => [
            'title' => 'Follow-ups — scheduled contacts',
            'greeting' => 'Your to-do list for sales and retention — check this at the start of every shift.',
            'summary' => 'Reminders to call or message enquiries and members. Keeps sales and retention on track.',
            'steps' => [
                [
                    'title' => 'See what\'s due today',
                    'body' => 'Sort by due date to surface the most urgent items.',
                    'fields' => [
                        ['name' => 'Due date sort', 'hint' => 'Click the column header to sort oldest-first.'],
                        ['name' => 'Overdue items', 'hint' => 'Past-due follow-ups — handle these before today\'s fresh ones.'],
                    ],
                ],
                [
                    'title' => 'Complete a follow-up',
                    'body' => 'After making contact, mark it done and add a note.',
                    'fields' => [
                        ['name' => 'Mark complete', 'hint' => 'Logs the contact and removes it from the active list.'],
                        ['name' => 'Notes', 'hint' => 'What was said — useful if a colleague picks this up on the next shift.'],
                    ],
                ],
            ],
            'tips' => [
                'Sort by due date to see what needs action today.',
                'Mark complete after contact and add notes for the next person on shift.',
            ],
        ],

        'admin.users.index' => [
            'title' => 'Users — staff accounts',
            'greeting' => 'Control who can access the system and what each person can do.',
            'summary' => 'People who can log into the admin panel. Each user has a role that controls permissions.',
            'steps' => [
                [
                    'title' => 'Create a new user',
                    'body' => 'Each staff member should have their own account — never share logins.',
                    'fields' => [
                        ['name' => 'Name + Email', 'hint' => 'The email becomes their login. Use a real address so password resets work.'],
                        ['name' => 'Role', 'hint' => 'Controls what they can see and do. Front desk staff → Employee role.'],
                        ['name' => 'Must change password', 'hint' => 'Enable for new accounts so they set their own password on first login.'],
                    ],
                ],
                [
                    'title' => 'Assign the right role',
                    'body' => 'The role is the most important setting — it determines which panel and pages they can access.',
                    'fields' => [
                        ['name' => 'Super admin', 'hint' => 'Full access to everything. Assign only to the gym owner or senior manager.'],
                        ['name' => 'Employee', 'hint' => 'Front desk role — accesses the Office panel only. Cannot see Settings, financials, or admin reports.'],
                    ],
                ],
            ],
            'checklist' => [
                'Every staff member has their own account (no shared logins)',
                'Front desk staff have the Employee role, not Super admin',
                'New accounts have "Must change password" enabled',
            ],
            'tips' => [
                'Give front-desk staff the employee role — they use the Office panel, not full admin.',
                'Force password change on first login for new accounts.',
                'Never share the super admin account.',
            ],
        ],

        'admin.roles.index' => [
            'title' => 'Roles & permissions',
            'greeting' => 'Fine-tune who can do what — changes apply immediately after saving.',
            'summary' => 'Control what each role can see and do (members, invoices, settings, etc.).',
            'steps' => [
                [
                    'title' => 'Review permissions per role',
                    'body' => 'Click a role to see and edit its permission list.',
                    'fields' => [
                        ['name' => 'Permission list', 'hint' => 'Grouped by resource — members, invoices, settings, etc. Toggle each on or off.'],
                        ['name' => 'Save', 'hint' => 'Changes apply immediately — all users with that role are affected straight away.'],
                    ],
                ],
                [
                    'title' => 'After adding new features',
                    'body' => 'New Filament resources need their permissions generated before they appear for non-super-admin users.',
                    'fields' => [
                        ['name' => 'Regenerate permissions', 'hint' => 'Run: php artisan shield:generate --resource=ResourceName --panel=admin. Required if a new page shows 404.'],
                    ],
                ],
            ],
            'checklist' => [
                'Employee role cannot access Settings or financial reports',
                'Super admin assigned only to owner / senior manager',
                'Permissions tested after any role changes',
            ],
            'tips' => [
                'Changes apply immediately after save.',
                'Super admin bypasses all restrictions — assign sparingly.',
                'After adding new features, regenerate permissions if pages return 404.',
            ],
        ],

        'admin.settings.overview' => [
            'title' => 'Welcome to Settings',
            'greeting' => 'You\'re in the right place — this is where you teach the app how your gym works.',
            'summary' => 'Use the tabs below. Each tab has its own step-by-step guide when the light bulb is on. Work through them in order if you\'re setting up for the first time.',
            'steps' => [
                [
                    'title' => 'Suggested order for a new gym',
                    'body' => 'You don\'t have to follow this exactly, but it saves time:',
                    'fields' => [
                        ['name' => '1. Gym Info', 'hint' => 'Name, currency, contact — foundation for everything else.'],
                        ['name' => '2. Member + Invoice', 'hint' => 'Numbering before you add members or issue bills.'],
                        ['name' => '3. Charges + Expenses', 'hint' => 'Tax rates and categories for clean accounting.'],
                        ['name' => '4. Subscriptions', 'hint' => 'When to warn staff about expiring memberships.'],
                        ['name' => '5. Import / Backup', 'hint' => 'Bulk data and safety net — when you\'re ready.'],
                    ],
                ],
            ],
            'tips' => [
                'Open a tab — the guide at the top of that tab explains each field in plain language.',
                'Tick items in the checklist as you go; progress is saved in your browser.',
                'Always click Save settings after editing a tab (except Import, which has its own button).',
            ],
            'widgets' => [],
        ],

    ],

];
