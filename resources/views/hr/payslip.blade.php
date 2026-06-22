<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('app.hr.payslip.title') }} — {{ $user?->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .totals { margin-top: 16px; width: 50%; margin-left: auto; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .label { text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ __('app.hr.payslip.title') }}</h1>
    <p class="muted">{{ $period?->label() }}</p>

    <p><strong>{{ $user?->name }}</strong><br>
        {{ $profile?->employee_code }} · {{ $profile?->position }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('app.hr.fields.base_salary') }}</th>
                <th>{{ __('app.hr.fields.working_days') }}</th>
                <th>{{ __('app.hr.fields.present_days') }}</th>
                <th>{{ __('app.hr.fields.overtime_hours') }}</th>
                <th>{{ __('app.hr.fields.gross') }}</th>
                <th>{{ __('app.hr.fields.net') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format((float) $item->base_salary, 2) }}</td>
                <td>{{ $item->working_days }}</td>
                <td>{{ $item->present_days }}</td>
                <td>{{ number_format((float) $item->overtime_hours, 2) }}</td>
                <td>{{ number_format((float) $item->gross, 2) }}</td>
                <td>{{ number_format((float) $item->net, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if (filled($item->deductions))
        <h3>{{ __('app.hr.payslip.deductions') }}</h3>
        <table>
            @foreach ($item->deductions as $row)
                <tr>
                    <td>{{ $row['label'] ?? '' }}</td>
                    <td>{{ number_format((float) ($row['amount'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if (filled($item->bonuses))
        <h3>{{ __('app.hr.payslip.bonuses') }}</h3>
        <table>
            @foreach ($item->bonuses as $row)
                <tr>
                    <td>{{ $row['label'] ?? '' }}</td>
                    <td>{{ number_format((float) ($row['amount'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
