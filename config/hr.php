<?php

return [

    'attendance' => [
        'enabled' => env('HR_ATTENDANCE_ENABLED', true),
        'grace_period_minutes' => (int) env('HR_GRACE_PERIOD_MINUTES', 15),
        'rate_limit_minutes' => (int) env('HR_RATE_LIMIT_MINUTES', 5),
        'standard_hours_per_day' => (float) env('HR_STANDARD_HOURS_PER_DAY', 8),
        'overtime_multiplier' => (float) env('HR_OVERTIME_MULTIPLIER', 1.5),
    ],

    'payroll' => [
        'working_days_basis' => env('HR_WORKING_DAYS_BASIS', 'weekdays'),
    ],

];
