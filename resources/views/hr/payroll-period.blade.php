<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('app.hr.titles.payroll_period', ['period' => $period->label()]) }}</title>
    <style>
        @page { size: A4 landscape; margin: 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .muted { color: #666; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 5px 7px; text-align: left; white-space: nowrap; }
        th { background: #f5f5f5; font-weight: bold; }
        td.num { text-align: right; }
        tr.totals td { background: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $gymName }}</h1>
    <p class="muted">
        {{ __('app.hr.titles.payroll_period', ['period' => $period->label()]) }}<br>
        {{ __('app.hr.fields.generated_at') }}: {{ $generatedAt->format('d/m/Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>{{ __('app.hr.fields.employee_code') }}</th>
                <th>{{ __('app.hr.fields.user') }}</th>
                <th>{{ __('app.hr.fields.position') }}</th>
                <th class="num">{{ __('app.hr.fields.base_salary') }}</th>
                <th class="num">{{ __('app.hr.fields.present_days') }} / {{ __('app.hr.fields.working_days') }}</th>
                <th class="num">{{ __('app.hr.fields.gross') }}</th>
                <th class="num">{{ __('app.hr.payslip.deductions') }}</th>
                <th class="num">{{ __('app.hr.payslip.bonuses') }}</th>
                <th class="num">{{ __('app.hr.fields.net') }}</th>
                <th>{{ __('app.fields.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $row)
                <tr>
                    <td>{{ $row['employee_code'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['position'] }}</td>
                    <td class="num">{{ number_format($row['base_salary'], 2) }} {{ $currency }}</td>
                    <td class="num">{{ number_format($row['present_days'], 1) }} / {{ $row['working_days'] }}</td>
                    <td class="num">{{ number_format($row['gross'], 2) }} {{ $currency }}</td>
                    <td class="num">{{ number_format($row['deductions_total'], 2) }} {{ $currency }}</td>
                    <td class="num">{{ number_format($row['bonuses_total'], 2) }} {{ $currency }}</td>
                    <td class="num">{{ number_format($row['net'], 2) }} {{ $currency }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="5">{{ $staffCount }} {{ __('app.hr.fields.items_count') }}</td>
                <td class="num">{{ number_format($totalGross, 2) }} {{ $currency }}</td>
                <td class="num"></td>
                <td class="num"></td>
                <td class="num">{{ number_format($totalNet, 2) }} {{ $currency }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
